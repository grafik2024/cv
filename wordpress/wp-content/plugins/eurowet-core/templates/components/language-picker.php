<?php
/**
 * Language picker: PL/EN/FR/UA (translated URLs) + searchable "Inne języki" (reviewed/machine extra languages).
 *
 * @package Eurowet\Core
 */

defined( 'ABSPATH' ) || exit;
require_once __DIR__ . '/_helpers.php';

use Eurowet\Core\I18n\Module as I18n;

$core  = I18n::coreLinks();
$extra = I18n::extraLanguages();
$cur   = 'pl';
foreach ( $core as $l ) {
	if ( $l['current'] ) {
		$cur = $l['slug'];
	}
}
$uid  = wp_unique_id( 'ew-lang-' );
$path = (string) wp_parse_url( home_url( add_query_arg( array() ) ), PHP_URL_PATH );
?>
<div class="ew-lang" data-ew-lang>
	<button type="button" class="ew-lang__toggle" aria-expanded="false" aria-controls="<?php echo esc_attr( $uid ); ?>">
		<?php echo ew_icon( 'globe' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<span class="ew-visually-hidden"><?php esc_html_e( 'Język:', 'eurowet-core' ); ?></span> <span><?php echo esc_html( strtoupper( 'ua' === $cur ? 'UA' : $cur ) ); ?></span>
	</button>
	<div class="ew-lang__panel" id="<?php echo esc_attr( $uid ); ?>" hidden>
		<ul class="ew-lang__core ew-list-reset">
			<?php foreach ( $core as $l ) : ?>
				<li><a href="<?php echo esc_url( $l['url'] ); ?>" hreflang="<?php echo esc_attr( $l['hreflang'] ); ?>" lang="<?php echo esc_attr( 'ua' === $l['slug'] ? 'uk' : $l['slug'] ); ?>" <?php echo $l['current'] ? 'aria-current="true"' : ''; ?>><?php echo esc_html( $l['name'] ); ?></a></li>
			<?php endforeach; ?>
		</ul>
		<?php if ( $extra ) : ?>
			<details class="ew-lang__more">
				<summary><?php esc_html_e( 'Inne języki', 'eurowet-core' ); ?></summary>
				<label class="ew-visually-hidden" for="<?php echo esc_attr( $uid ); ?>-q"><?php esc_html_e( 'Szukaj języka', 'eurowet-core' ); ?></label>
				<input id="<?php echo esc_attr( $uid ); ?>-q" type="search" class="ew-lang__search" placeholder="<?php esc_attr_e( 'Szukaj języka…', 'eurowet-core' ); ?>" data-ew-lang-search>
				<ul class="ew-lang__extra ew-list-reset">
					<?php foreach ( $extra as $code => $name ) : ?>
						<li><a href="<?php echo esc_url( home_url( '/x/' . $code . ( '/' === $path ? '/' : $path ) ) ); ?>" hreflang="<?php echo esc_attr( $code ); ?>" lang="<?php echo esc_attr( $code ); ?>" rel="nofollow"><?php echo esc_html( $name ); ?></a></li>
					<?php endforeach; ?>
				</ul>
				<p class="ew-lang__note ew-text-muted"><?php esc_html_e( 'Tłumaczenia automatyczne — weryfikowane stopniowo.', 'eurowet-core' ); ?></p>
			</details>
		<?php endif; ?>
	</div>
</div>
