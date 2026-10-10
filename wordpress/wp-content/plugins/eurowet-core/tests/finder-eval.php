<?php
/**
 * Product Finder evaluation against the REAL needs (content/build/needs.json) and the test set (tests/finder-cases.json).
 * Mirrors Finder\Service: top-ranked need is a match when confidence >= threshold (default 0.45); red-flag level and
 * species detection are checked too. No WordPress needed.
 * Run: php tests/finder-eval.php [repo-root] [--verbose]
 */
declare(strict_types=1);

require __DIR__ . '/../src/Finder/Normalizer.php';
require __DIR__ . '/../src/Finder/Engine.php';

use Eurowet\Core\Finder\Engine;

$root    = $argv[1] ?? dirname( __DIR__, 5 );
$verbose = in_array( '--verbose', $argv, true );
$needs   = json_decode( (string) file_get_contents( $root . '/content/build/needs.json' ), true ) ?: array();
$cases   = json_decode( (string) file_get_contents( $root . '/tests/finder-cases.json' ), true ) ?: array();
$lex     = json_decode( (string) file_get_contents( __DIR__ . '/../data/lexicon-pl.json' ), true );
$thr     = 0.45;

$list = array();
foreach ( $needs as $i => $n ) {
	if ( isset( $n['meta']['_ew_active'] ) && ! $n['meta']['_ew_active'] ) {
		continue;
	}
	$list[] = array(
		'id'        => $i + 1,
		'slug'      => $n['slug'],
		'title'     => $n['title'],
		'synonyms'  => $n['meta']['_ew_synonyms'] ?? array(),
		'questions' => $n['meta']['_ew_questions'] ?? array(),
		'species'   => $n['terms']['ew_species'] ?? array(),
		'areas'     => $n['terms']['ew_area'] ?? array(),
		'priority'  => (int) ( $n['meta']['_ew_priority'] ?? 0 ),
	);
}
$engine = new Engine( $list, $lex, 'pl' );
$stats  = array( 'need' => array( 0, 0 ), 'nomatch' => array( 0, 0 ), 'flag' => array( 0, 0 ), 'species' => array( 0, 0 ) );
$fails  = array();
foreach ( $cases as $c ) {
	$a    = $engine->analyze( (string) $c['q'] );
	$top  = $a['ranked'][0] ?? null;
	$got  = ( $top && $top['confidence'] >= $thr ) ? $top['slug'] : null;
	$want = $c['expect'] ?? null;
	$k    = null === $want ? 'nomatch' : 'need';
	++$stats[ $k ][1];
	$ok = $got === $want;
	if ( $ok ) {
		++$stats[ $k ][0];
	}
	$flag_ok = ( $c['red_flag'] ?? 'none' ) === $a['red_flag']['level'];
	++$stats['flag'][1];
	$stats['flag'][0] += $flag_ok ? 1 : 0;
	if ( array_key_exists( 'species', $c ) && null !== $c['species'] ) {
		++$stats['species'][1];
		$stats['species'][0] += $a['species'] === $c['species'] ? 1 : 0;
	}
	if ( ! $ok || ! $flag_ok || $verbose ) {
		$fails[] = sprintf( "%s %-48s want=%-28s got=%-28s conf=%s flag=%s/%s%s", $ok && $flag_ok ? 'ok  ' : 'FAIL', $c['q'], $want ?? '—', $got ?? '—', $top ? number_format( $top['confidence'], 2 ) : '-', $a['red_flag']['level'], $c['red_flag'] ?? 'none', $top && ! $ok ? '  top2=' . ( $a['ranked'][1]['slug'] ?? '-' ) : '' );
	}
}
echo implode( "\n", $fails ), "\n\n";
foreach ( $stats as $k => [ $ok, $all ] ) {
	printf( "%-8s %3d/%-3d %5.1f%%\n", $k, $ok, $all, $all ? 100 * $ok / $all : 0 );
}
echo 'needs loaded: ', count( $list ), "\n";
