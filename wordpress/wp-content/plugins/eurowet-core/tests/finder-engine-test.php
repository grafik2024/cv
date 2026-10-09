<?php
/**
 * Self-contained tests for the Product Finder engine (no WordPress, no PHPUnit needed).
 * Run: php tests/finder-engine-test.php
 */
declare(strict_types=1);

require __DIR__ . '/../src/Finder/Normalizer.php';
require __DIR__ . '/../src/Finder/Engine.php';

use Eurowet\Core\Finder\Engine;

$needs = array(
	array( 'id' => 1, 'slug' => 'swiad-i-drapanie', 'title' => 'Świąd i drapanie skóry', 'species' => array( 'pies', 'kot' ), 'areas' => array( 'skora' ), 'priority' => 90,
		'synonyms' => array( 'pies się drapie', 'kot się drapie', 'swędząca skóra', 'świąd skóry', 'drapanie', 'ciągle się drapie', 'gryzie się', 'wylizuje łapy', 'swędzenie' ),
		'questions' => array( 'co na świąd u psa', 'dlaczego pies się drapie' ) ),
	array( 'id' => 2, 'slug' => 'sucha-skora', 'title' => 'Sucha skóra', 'species' => array( 'pies', 'kot' ), 'areas' => array( 'skora' ), 'priority' => 80,
		'synonyms' => array( 'sucha skóra', 'przesuszona skóra', 'łuszcząca się skóra', 'łupież', 'suchy łupież', 'skóra się sypie' ), 'questions' => array( 'co na suchą skórę psa' ) ),
	array( 'id' => 3, 'slug' => 'tlusta-siersc-lojotok', 'title' => 'Tłusta sierść i łojotok', 'species' => array( 'pies', 'kot' ), 'areas' => array( 'skora', 'siersc' ), 'priority' => 60,
		'synonyms' => array( 'tłusta sierść', 'przetłuszczająca się sierść', 'łojotok', 'nadmiar sebum', 'tłusty łupież', 'sierść się klei' ), 'questions' => array( 'co na tłustą sierść' ) ),
	array( 'id' => 4, 'slug' => 'higiena-uszu', 'title' => 'Higiena uszu i nadmiar woskowiny', 'species' => array( 'pies', 'kot' ), 'areas' => array( 'uszy' ), 'priority' => 85,
		'synonyms' => array( 'czyszczenie uszu', 'brudne uszy', 'woskowina', 'nadmiar woskowiny', 'płyn do uszu', 'czym czyścić uszy' ), 'questions' => array( 'czym czyścić psu uszy', 'jak czyścić uszy kotu' ) ),
	array( 'id' => 5, 'slug' => 'higiena-oczu', 'title' => 'Higiena oczu i okolic oczu', 'species' => array( 'pies', 'kot' ), 'areas' => array( 'oczy' ), 'priority' => 75,
		'synonyms' => array( 'przemywanie oczu', 'zacieki łzowe', 'łzawienie', 'wydzielina z oczu', 'płyn do oczu', 'brudne oczy' ), 'questions' => array( 'czym przemywać oczy psa' ) ),
	array( 'id' => 6, 'slug' => 'higiena-jamy-ustnej', 'title' => 'Higiena jamy ustnej i zębów', 'species' => array( 'pies', 'kot' ), 'areas' => array( 'jama-ustna-i-zeby' ), 'priority' => 80,
		'synonyms' => array( 'nieświeży oddech', 'brzydki zapach z pyska', 'kamień nazębny', 'płytka nazębna', 'mycie zębów', 'higiena zębów' ), 'questions' => array( 'preparat na higienę zębów kota', 'jak dbać o zęby psa' ) ),
	array( 'id' => 7, 'slug' => 'kapiel-psa', 'title' => 'Kąpiel psa i wybór szamponu', 'species' => array( 'pies' ), 'areas' => array( 'siersc', 'skora' ), 'priority' => 70,
		'synonyms' => array( 'kąpiel psa', 'mycie psa', 'szampon dla psa', 'jak często kąpać psa', 'kąpanie' ), 'questions' => array( 'jak często kąpać psa', 'czy można myć psa ludzkim szamponem' ) ),
	array( 'id' => 8, 'slug' => 'skora-malych-ssakow', 'title' => 'Pielęgnacja skóry małych ssaków', 'species' => array( 'male-ssaki' ), 'areas' => array( 'skora', 'siersc' ), 'priority' => 40,
		'synonyms' => array( 'królik sierść', 'świnka morska skóra', 'kąpiel gryzonia', 'pielęgnacja chomika' ), 'questions' => array() ),
	array( 'id' => 9, 'slug' => 'stawy', 'title' => 'Wsparcie stawów', 'species' => array( 'pies', 'kot' ), 'areas' => array( 'stawy' ), 'priority' => 50,
		'synonyms' => array( 'sztywne stawy', 'ruchomość stawów', 'starszy pies stawy' ), 'questions' => array() ),
);
$lex = json_decode( (string) file_get_contents( __DIR__ . '/../data/lexicon-pl.json' ), true );
$e   = new Engine( $needs, $lex, 'pl' );

$cases = array(
	// q, expected need slug or null, expected red flag level, expected species
	array( 'pies cały czas się drapie', 'swiad-i-drapanie', 'none', 'pies' ),
	array( 'co na suchą skórę psa', 'sucha-skora', 'none', 'pies' ),
	array( 'czym czyścić psu uszy', 'higiena-uszu', 'none', 'pies' ),
	array( 'kot ma brudne uszy', 'higiena-uszu', 'none', 'kot' ),
	array( 'czym przemywać oczy psa', 'higiena-oczu', 'none', 'pies' ),
	array( 'co na tłustą sierść', 'tlusta-siersc-lojotok', 'none', null ),
	array( 'jak często kąpać psa', 'kapiel-psa', 'none', 'pies' ),
	array( 'preparat na higienę zębów kota', 'higiena-jamy-ustnej', 'none', 'kot' ),
	array( 'pies drapie sie', 'swiad-i-drapanie', 'none', 'pies' ),
	array( 'psiak sie drapie i swedzi go', 'swiad-i-drapanie', 'none', 'pies' ),
	array( 'lupiez u kota', 'sucha-skora', 'none', 'kot' ),
	array( 'szampom dla psa', 'kapiel-psa', 'none', 'pies' ),
	array( 'nieswiezy oddech z pyska', 'higiena-jamy-ustnej', 'none', null ),
	array( 'uszu kota czyszczenie', 'higiena-uszu', 'none', 'kot' ),
	array( 'pies drapie ucho do krwi', null, 'urgent', 'pies' ),
	array( 'kot ma ropę w oku', null, 'caution', 'kot' ),
	array( 'karma dla rybek', null, 'none', null ),
	array( 'pies', null, 'none', 'pies' ),
	array( 'królik ma suchą sierść', 'skora-malych-ssakow', 'none', 'male-ssaki' ),
);
$fail = 0;
foreach ( $cases as [ $q, $want, $flag, $sp ] ) {
	$r    = $e->analyze( $q );
	$top  = $r['ranked'][0] ?? null;
	$got  = ( $top && $top['confidence'] >= 0.45 ) ? $top['slug'] : null;
	$ok   = ( null === $want ? true : $got === $want ) && $r['red_flag']['level'] === $flag && $r['species'] === $sp;
	if ( null === $want && 'none' === $flag && null !== $got ) {
		$ok = false; // must not invent a match
	}
	$fail += $ok ? 0 : 1;
	printf( "%s %-38s → %-24s conf=%.2f flag=%-7s sp=%s%s\n", $ok ? 'PASS' : 'FAIL', $q, $got ?? '—', $top['confidence'] ?? 0, $r['red_flag']['level'], $r['species'] ?? '—', $r['corrected'] ? ' (popr.: ' . $r['corrected'] . ')' : '' );
}
echo $fail ? "\n{$fail} FAILED\n" : "\nALL PASSED\n";
exit( $fail ? 1 : 0 );
