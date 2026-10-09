<?php
/**
 * Finder results (server-side; the JS module renders the same structure from REST).
 * Args: result (array from Finder\Service::query()).
 *
 * @package Eurowet\Core
 */

defined( 'ABSPATH' ) || exit;
require_once __DIR__ . '/_helpers.php';

$r = (array) ( $args['result'] ?? array() );
if ( ! $r ) {
	return;
}
$flag    = (array) ( $r['red_flag'] ?? array() );
$primary = $r['primary'] ?? null;
$product_block = static function ( array $p, bool $main ) use ( $r ): void {
	$cta = $p['buy_url'] ? $p['buy_url'] : $p['url'];
	?>
	<article class="ew-fres__product <?php echo $main ? 'ew-fres__product--main' : ''; ?>">
		<div class="ew-fres__stage"><?php if ( $p['image'] ) : ?><img src="<?php echo esc_url( $p['image'] ); ?>" alt="<?php echo esc_attr( $p['image_alt'] ?: $p['name'] ); ?>" loading="lazy" decoding="async" width="300" height="300"><?php endif; ?></div>
		<div class="ew-fres__info">
			<h3 class="ew-fres__name"><a href="<?php echo esc_url( $p['url'] ); ?>" data-ew-track="product_click" data-ew-target="<?php echo (int) $p['id']; ?>" data-ew-log="<?php echo (int) ( $r['log_id'] ?? 0 ); ?>"><?php echo esc_html( $p['name'] ); ?></a></h3>
			<?php if ( $p['capacity'] ) : ?><p class="ew-fres__cap"><?php echo esc_html( $p['capacity'] ); ?></p><?php endif; ?>
			<?php if ( $p['intended_for'] ) : ?>
				<p class="ew-fres__meta"><strong><?php esc_html_e( 'Potwierdzone przeznaczenie:', 'eurowet-core' ); ?></strong> <?php echo esc_html( $p['intended_for'] ); ?></p>
			<?php endif; ?>
			<?php if ( $main && ( $p['reason'] || $p['evidence'] ) ) : ?>
				<div class="ew-fres__why">
					<p><strong><?php esc_html_e( 'Dlaczego pasuje:', 'eurowet-core' ); ?></strong> <?php echo esc_html( $p['reason'] ); ?></p>
					<?php if ( $p['evidence'] ) : ?><blockquote class="ew-fres__quote"><p>„<?php echo esc_html( $p['evidence'] ); ?>”</p><footer><?php esc_html_e( 'z opisu produktu', 'eurowet-core' ); ?></footer></blockquote><?php endif; ?>
				</div>
			<?php elseif ( $p['reason'] ) : ?>
				<p class="ew-fres__meta"><?php echo esc_html( $p['reason'] ); ?></p>
			<?php endif; ?>
			<p class="ew-cluster ew-fres__ctas">
				<a class="ew-btn <?php echo $main ? '' : 'ew-btn--secondary ew-btn--sm'; ?>" href="<?php echo esc_url( $p['url'] ); ?>" data-ew-track="product_click" data-ew-target="<?php echo (int) $p['id']; ?>" data-ew-log="<?php echo (int) ( $r['log_id'] ?? 0 ); ?>"><?php esc_html_e( 'Zobacz produkt', 'eurowet-core' ); ?></a>
				<?php if ( $main ) : ?>
					<?php if ( $p['buy_url'] ) : ?>
						<a class="ew-btn ew-btn--secondary" href="<?php echo esc_url( $cta ); ?>" rel="nofollow" data-ew-track="buy_click" data-ew-target="<?php echo (int) $p['id']; ?>" data-ew-log="<?php echo (int) ( $r['log_id'] ?? 0 ); ?>"><?php esc_html_e( 'Kup produkt', 'eurowet-core' ); ?></a>
					<?php else : ?>
						<a class="ew-btn ew-btn--secondary" href="<?php echo esc_url( home_url( '/znajdz-przedstawiciela/' ) ); ?>"><?php esc_html_e( 'Zapytaj o dostępność', 'eurowet-core' ); ?></a>
					<?php endif; ?>
				<?php endif; ?>
			</p>
		</div>
	</article>
	<?php
};
?>
<div class="ew-fres" data-log-id="<?php echo (int) ( $r['log_id'] ?? 0 ); ?>">
	<?php if ( ! empty( $r['corrected'] ) ) : ?>
		<p class="ew-fres__corrected ew-text-muted"><?php printf( /* translators: %s: corrected query */ esc_html__( 'Szukaliśmy: %s', 'eurowet-core' ), '<em>' . esc_html( (string) $r['corrected'] ) . '</em>' ); ?></p>
	<?php endif; ?>
	<?php if ( 'none' !== ( $flag['level'] ?? 'none' ) ) : ?>
		<?php echo ew_render( 'vet-notice', array( 'level' => $flag['level'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	<?php endif; ?>

	<?php if ( ! empty( $r['need'] ) ) : ?>
		<section class="ew-fres__need" aria-labelledby="ew-fres-need">
			<p class="ew-eyebrow"><?php esc_html_e( 'Rozpoznana potrzeba', 'eurowet-core' ); ?></p>
			<h2 id="ew-fres-need" class="ew-fres__need-title"><a href="<?php echo esc_url( $r['need']['url'] ); ?>"><?php echo esc_html( $r['need']['title'] ); ?></a></h2>
			<?php if ( ! empty( $r['need']['short_answer'] ) ) : ?><p class="ew-lead"><?php echo esc_html( $r['need']['short_answer'] ); ?></p><?php endif; ?>
		</section>
		<?php if ( $primary ) : ?>
			<section class="ew-fres__block" aria-labelledby="ew-fres-primary">
				<h2 id="ew-fres-primary" class="ew-fres__h"><?php esc_html_e( 'Najlepiej dopasowany produkt', 'eurowet-core' ); ?></h2>
				<?php $product_block( $primary, true ); ?>
			</section>
		<?php elseif ( 'urgent' !== ( $flag['level'] ?? '' ) ) : ?>
			<p class="ew-alert ew-alert--info"><?php esc_html_e( 'Dla tej potrzeby nie mamy produktu, którego opis potwierdzałby takie zastosowanie. Zobacz porady poniżej lub skontaktuj się z nami.', 'eurowet-core' ); ?></p>
		<?php endif; ?>
		<?php if ( ! empty( $r['complementary'] ) ) : ?>
			<section class="ew-fres__block" aria-labelledby="ew-fres-comp">
				<h2 id="ew-fres-comp" class="ew-fres__h"><?php esc_html_e( 'Produkty uzupełniające', 'eurowet-core' ); ?></h2>
				<div class="ew-fres__comp">
					<?php
					foreach ( array_slice( (array) $r['complementary'], 0, 4 ) as $p ) {
						$product_block( (array) $p, false );
					}
					?>
				</div>
			</section>
		<?php endif; ?>
		<?php if ( ! empty( $r['guides'] ) ) : ?>
			<section class="ew-fres__block" aria-labelledby="ew-fres-guides">
				<h2 id="ew-fres-guides" class="ew-fres__h"><?php esc_html_e( 'Przeczytaj również', 'eurowet-core' ); ?></h2>
				<ul class="ew-fres__guides">
					<?php foreach ( (array) $r['guides'] as $g ) : ?>
						<li><a href="<?php echo esc_url( $g['url'] ); ?>" data-ew-track="guide_click" data-ew-target="<?php echo (int) $g['id']; ?>" data-ew-log="<?php echo (int) ( $r['log_id'] ?? 0 ); ?>"><?php echo esc_html( $g['title'] ); ?></a><?php echo $g['tldr'] ? '<span class="ew-text-muted"> — ' . esc_html( $g['tldr'] ) . '</span>' : ''; ?></li>
					<?php endforeach; ?>
				</ul>
			</section>
		<?php endif; ?>
	<?php elseif ( '' !== (string) ( $r['query'] ?? '' ) || ! empty( $r['alternatives'] ) ) : ?>
		<div class="ew-fres__nomatch">
			<?php if ( '' !== (string) ( $r['query'] ?? '' ) ) : ?>
				<h2 class="ew-fres__h"><?php esc_html_e( 'Nie znaleźliśmy pewnego dopasowania', 'eurowet-core' ); ?></h2>
				<p><?php esc_html_e( 'Nie chcemy zgadywać — pokazujemy produkt tylko wtedy, gdy jego opis potwierdza zastosowanie. Spróbuj opisać problem inaczej albo wybierz jedną z potrzeb.', 'eurowet-core' ); ?></p>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $r['alternatives'] ) ) : ?>
		<section class="ew-fres__block" aria-labelledby="ew-fres-alt">
			<h2 id="ew-fres-alt" class="ew-fres__h"><?php echo esc_html( ! empty( $r['need'] ) ? __( 'Może chodzi Ci o', 'eurowet-core' ) : __( 'Wybierz potrzebę', 'eurowet-core' ) ); ?></h2>
			<ul class="ew-chips ew-list-reset">
				<?php foreach ( (array) $r['alternatives'] as $a ) : ?>
					<li><a class="ew-chip" href="<?php echo esc_url( $a['url'] ); ?>"><?php echo esc_html( $a['title'] ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</section>
	<?php endif; ?>
	<?php if ( empty( $r['need'] ) ) : ?>
		<p class="ew-fres__contact"><?php printf( wp_kses( /* translators: %s: contact URL */ __( 'Masz pytanie o dobór produktu? <a href="%s">Napisz lub zadzwoń do nas</a>.', 'eurowet-core' ), array( 'a' => array( 'href' => array() ) ) ), esc_url( home_url( '/kontakt/' ) ) ); ?></p>
	<?php endif; ?>
</div>
