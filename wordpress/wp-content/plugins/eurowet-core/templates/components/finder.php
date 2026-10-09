<?php
/**
 * Product Finder form (progressive enhancement: works as a GET form to /potrzeby/ without JS).
 * Args: variant (hero|page|compact), species, area, heading, lead, value.
 *
 * @package Eurowet\Core
 */

defined( 'ABSPATH' ) || exit;
require_once __DIR__ . '/_helpers.php';

$variant = in_array( $args['variant'] ?? '', array( 'hero', 'page', 'compact' ), true ) ? $args['variant'] : 'page';
$action  = get_post_type_archive_link( 'ew_need' ) ?: home_url( '/potrzeby/' );
$uid     = wp_unique_id( 'ew-finder-' );
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$value   = (string) ( $args['value'] ?? ( isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['q'] ) ) : '' ) );
$examples = array( __( 'pies cały czas się drapie', 'eurowet-core' ), __( 'czym czyścić psu uszy', 'eurowet-core' ), __( 'co na suchą skórę', 'eurowet-core' ), __( 'kot ma brudne uszy', 'eurowet-core' ), __( 'higiena zębów kota', 'eurowet-core' ) );
$species = get_terms( array( 'taxonomy' => 'ew_species', 'hide_empty' => false, 'slug' => array( 'pies', 'kot', 'male-ssaki', 'ptaki-ozdobne' ) ) );
\Eurowet\Core\Components\Renderer::printConfig();
?>
<div class="ew-finder ew-finder--<?php echo esc_attr( $variant ); ?>" data-ew-finder>
	<?php if ( 'compact' !== $variant && ! empty( $args['heading'] ) ) : ?>
		<h2 class="ew-finder__title" id="<?php echo esc_attr( $uid ); ?>-title"><?php echo esc_html( (string) $args['heading'] ); ?></h2>
		<?php if ( ! empty( $args['lead'] ) ) : ?>
			<p class="ew-finder__lead"><?php echo esc_html( (string) $args['lead'] ); ?></p>
		<?php endif; ?>
	<?php endif; ?>
	<form class="ew-finder__form" role="search" method="get" action="<?php echo esc_url( $action ); ?>" <?php echo ! empty( $args['heading'] ) ? 'aria-labelledby="' . esc_attr( $uid ) . '-title"' : 'aria-label="' . esc_attr__( 'Dobierz produkt do potrzeby', 'eurowet-core' ) . '"'; ?>>
		<label class="ew-finder__label" for="<?php echo esc_attr( $uid ); ?>"><?php esc_html_e( 'Opisz, czego potrzebuje Twoje zwierzę', 'eurowet-core' ); ?></label>
		<div class="ew-finder__field">
			<span class="ew-finder__icon" aria-hidden="true"><?php echo ew_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
			<input class="ew-finder__input" id="<?php echo esc_attr( $uid ); ?>" type="search" name="q" value="<?php echo esc_attr( $value ); ?>" maxlength="200" autocomplete="off" enterkeyhint="search" placeholder="<?php esc_attr_e( 'np. pies się drapie, czym czyścić uszy kotu…', 'eurowet-core' ); ?>" aria-describedby="<?php echo esc_attr( $uid ); ?>-hint">
			<?php if ( ! empty( $args['species'] ) ) : ?>
				<input type="hidden" name="gatunek" value="<?php echo esc_attr( sanitize_key( (string) $args['species'] ) ); ?>">
			<?php endif; ?>
			<button class="ew-btn ew-finder__submit" type="submit"><?php esc_html_e( 'Szukaj', 'eurowet-core' ); ?></button>
		</div>
		<p class="ew-finder__hint ew-text-muted" id="<?php echo esc_attr( $uid ); ?>-hint"><?php esc_html_e( 'Dopasowujemy wyłącznie produkty, których przeznaczenie potwierdza ich opis. Porady nie zastępują wizyty u lekarza weterynarii.', 'eurowet-core' ); ?></p>
	</form>
	<?php if ( 'compact' !== $variant ) : ?>
		<div class="ew-finder__quick">
			<?php if ( is_array( $species ) && $species ) : ?>
				<p class="ew-finder__quick-label" id="<?php echo esc_attr( $uid ); ?>-sp"><?php esc_html_e( 'Albo zacznij od gatunku:', 'eurowet-core' ); ?></p>
				<ul class="ew-chips ew-list-reset" aria-labelledby="<?php echo esc_attr( $uid ); ?>-sp">
					<?php foreach ( $species as $sp ) : ?>
						<li><a class="ew-chip" href="<?php echo esc_url( add_query_arg( 'gatunek', $sp->slug, $action ) ); ?>"><?php echo esc_html( $sp->name ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
			<p class="ew-finder__quick-label" id="<?php echo esc_attr( $uid ); ?>-ex"><?php esc_html_e( 'Przykładowe pytania:', 'eurowet-core' ); ?></p>
			<ul class="ew-chips ew-list-reset" aria-labelledby="<?php echo esc_attr( $uid ); ?>-ex">
				<?php foreach ( $examples as $ex ) : ?>
					<li><a class="ew-chip" href="<?php echo esc_url( add_query_arg( 'q', rawurlencode( $ex ), $action ) ); ?>" data-ew-example="<?php echo esc_attr( $ex ); ?>"><?php echo esc_html( $ex ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>
	<div class="ew-finder__live" aria-live="polite" aria-atomic="false" data-ew-finder-results></div>
</div>
