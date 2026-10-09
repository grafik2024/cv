<?php
/**
 * "Znajdź swojego opiekuna" — accessible SVG map of Poland (16 voivodeships) + select (mobile and
 * assistive tech) + result panel (aria-live). Without JS every voivodeship is listed with its rep(s).
 * Args: variant (full|compact), heading.
 *
 * @package Eurowet\Core
 */

defined( 'ABSPATH' ) || exit;
require_once __DIR__ . '/_helpers.php';

$variant = 'compact' === ( $args['variant'] ?? '' ) ? 'compact' : 'full';
$map     = json_decode( (string) file_get_contents( EW_CORE_DIR . 'assets/img/poland-voivodeships.json' ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions
$all     = class_exists( \Eurowet\Core\Reps\Directory::class ) ? \Eurowet\Core\Reps\Directory::all() : array();
$fb      = class_exists( \Eurowet\Core\Reps\Directory::class ) ? \Eurowet\Core\Reps\Directory::fallback() : array();
$uid     = wp_unique_id( 'ew-reps-' );
$by      = array();
foreach ( ew_voivodeships() as $slug => $name ) {
	$by[ $slug ] = array_values( array_filter( $all, static fn( $r ) => in_array( $slug, $r['voivodeship_slugs'], true ) ) );
}
\Eurowet\Core\Components\Renderer::printConfig();
$card = static function ( array $rep ): void {
	?>
	<div class="ew-rep-card">
		<?php if ( $rep['photo_id'] ) : ?>
			<?php echo wp_get_attachment_image( $rep['photo_id'], 'thumbnail', false, array( 'class' => 'ew-rep-card__photo', 'alt' => '', 'loading' => 'lazy' ) ); ?>
		<?php endif; ?>
		<div>
			<p class="ew-rep-card__name"><?php echo esc_html( $rep['name'] ); ?></p>
			<?php if ( $rep['position'] ) : ?><p class="ew-rep-card__pos"><?php echo esc_html( $rep['position'] ); ?></p><?php endif; ?>
			<ul class="ew-list-reset ew-rep-card__contact">
				<?php if ( $rep['phone'] ) : ?><li><?php echo ew_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <a href="<?php echo esc_attr( $rep['phone_href'] ); ?>"><?php echo esc_html( $rep['phone'] ); ?></a></li><?php endif; ?>
				<?php if ( $rep['email'] ) : ?><li><?php echo ew_icon( 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <a href="mailto:<?php echo esc_attr( antispambot( $rep['email'] ) ); ?>"><?php echo esc_html( antispambot( $rep['email'] ) ); ?></a></li><?php endif; ?>
			</ul>
			<?php if ( $rep['voivodeships'] ) : ?><p class="ew-rep-card__regions ew-text-muted"><?php esc_html_e( 'Obsługuje:', 'eurowet-core' ); ?> <?php echo esc_html( implode( ', ', $rep['voivodeships'] ) ); ?></p><?php endif; ?>
		</div>
	</div>
	<?php
};
?>
<section class="ew-reps ew-reps--<?php echo esc_attr( $variant ); ?>" data-ew-reps aria-labelledby="<?php echo esc_attr( $uid ); ?>-h">
	<h2 id="<?php echo esc_attr( $uid ); ?>-h"><?php echo esc_html( (string) ( $args['heading'] ?? __( 'Znajdź swojego opiekuna handlowego', 'eurowet-core' ) ) ); ?></h2>
	<div class="ew-reps__layout">
		<?php if ( 'full' === $variant && is_array( $map ) ) : ?>
			<div class="ew-reps__map" data-ew-reps-map hidden>
				<svg viewBox="<?php echo esc_attr( (string) $map['viewBox'] ); ?>" role="group" aria-label="<?php esc_attr_e( 'Mapa województw — wybierz województwo', 'eurowet-core' ); ?>" focusable="false">
					<?php foreach ( $map['regions'] as $slug => $r ) : ?>
						<a href="#ph-<?php echo esc_attr( $slug ); ?>" class="ew-reps__region" data-voiv="<?php echo esc_attr( $slug ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s voivodeship */ __( 'Województwo %s', 'eurowet-core' ), ew_voivodeships()[ $slug ] ?? $r['name'] ) ); ?>">
							<path d="<?php echo esc_attr( (string) $r['d'] ); ?>"/>
						</a>
					<?php endforeach; ?>
				</svg>
			</div>
		<?php endif; ?>
		<div class="ew-reps__panel">
			<label class="ew-reps__label" for="<?php echo esc_attr( $uid ); ?>-sel"><?php esc_html_e( 'Wybierz województwo', 'eurowet-core' ); ?></label>
			<select id="<?php echo esc_attr( $uid ); ?>-sel" class="ew-reps__select" data-ew-reps-select>
				<option value=""><?php esc_html_e( '— wybierz —', 'eurowet-core' ); ?></option>
				<?php foreach ( ew_voivodeships() as $slug => $name ) : ?>
					<option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $name ); ?></option>
				<?php endforeach; ?>
			</select>
			<div class="ew-reps__result" aria-live="polite" data-ew-reps-result></div>
		</div>
	</div>
	<dl class="ew-reps__all" data-ew-reps-all>
		<?php foreach ( $by as $slug => $reps ) : ?>
			<div class="ew-reps__entry" id="ph-<?php echo esc_attr( $slug ); ?>" data-voiv="<?php echo esc_attr( $slug ); ?>">
				<dt><?php echo esc_html( ew_voivodeships()[ $slug ] ); ?></dt>
				<dd>
					<?php
					if ( $reps ) {
						foreach ( $reps as $rep ) {
							$card( $rep );
						}
					} else {
						?>
						<p><?php esc_html_e( 'W tym województwie skontaktuj się bezpośrednio z biurem Eurowet:', 'eurowet-core' ); ?>
							<?php echo $fb['phone'] ? '<a href="' . esc_attr( $fb['phone_href'] ) . '">' . esc_html( $fb['phone'] ) . '</a>' : ''; ?>
							<?php echo $fb['email'] ? ' · <a href="mailto:' . esc_attr( antispambot( $fb['email'] ) ) . '">' . esc_html( antispambot( $fb['email'] ) ) . '</a>' : ''; ?>
							<?php echo ( ! $fb['phone'] && ! $fb['email'] ) ? '<a href="' . esc_url( home_url( '/kontakt/' ) ) . '">' . esc_html__( 'Kontakt', 'eurowet-core' ) . '</a>' : ''; ?>
						</p>
					<?php } ?>
				</dd>
			</div>
		<?php endforeach; ?>
	</dl>
</section>
