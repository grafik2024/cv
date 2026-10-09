<?php
/**
 * Company contact card (data from eurowet-core settings imported from the current eurowet.pl contact page;
 * empty fields are omitted — nothing is invented). Args: heading.
 *
 * @package Eurowet2026
 */

defined( 'ABSPATH' ) || exit;

$c = ew_theme_company();
if ( ! $c ) {
	return;
}
$tel = static fn( string $n ): string => 'tel:' . preg_replace( '/[^0-9+]/', '', $n );
?>
<section class="ew-company-card" aria-labelledby="ew-company-card-title">
	<h2 id="ew-company-card-title" class="ew-h3"><?php echo esc_html( (string) ( $args['heading'] ?? __( 'Dane kontaktowe', 'eurowet-2026' ) ) ); ?></h2>
	<?php if ( ! empty( $c['name'] ) ) : ?><p class="ew-company-card__name"><strong><?php echo esc_html( $c['name'] ); ?></strong></p><?php endif; ?>
	<?php if ( ! empty( $c['address'] ) ) : ?>
		<address><?php echo nl2br( esc_html( $c['address'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></address>
	<?php endif; ?>
	<ul class="ew-company-card__list ew-list-reset">
		<?php if ( ! empty( $c['phone'] ) ) : ?>
			<li><?php echo ew_theme_ui_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <span><?php esc_html_e( 'Biuro:', 'eurowet-2026' ); ?></span> <a href="<?php echo esc_attr( $tel( $c['phone'] ) ); ?>"><?php echo esc_html( $c['phone'] ); ?></a></li>
		<?php endif; ?>
		<?php if ( ! empty( $c['phone_sales'] ) ) : ?>
			<li><?php echo ew_theme_ui_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <span><?php esc_html_e( 'Sprzedaż:', 'eurowet-2026' ); ?></span> <a href="<?php echo esc_attr( $tel( $c['phone_sales'] ) ); ?>"><?php echo esc_html( $c['phone_sales'] ); ?></a></li>
		<?php endif; ?>
		<?php if ( ! empty( $c['email'] ) && is_email( $c['email'] ) ) : ?>
			<li><?php echo ew_theme_ui_icon( 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <a href="mailto:<?php echo esc_attr( antispambot( $c['email'] ) ); ?>"><?php echo esc_html( antispambot( $c['email'] ) ); ?></a></li>
		<?php endif; ?>
	</ul>
	<?php if ( ! empty( $c['ids'] ) ) : ?><p class="ew-text-muted"><?php echo esc_html( $c['ids'] ); ?></p><?php endif; ?>
	<?php if ( ! empty( $c['address'] ) ) : ?>
		<p><a class="ew-btn ew-btn--ghost ew-btn--sm" href="<?php echo esc_url( 'https://www.openstreetmap.org/search?query=' . rawurlencode( preg_replace( '/\s+/', ' ', $c['address'] ) ) ); ?>" rel="noopener" target="_blank"><?php echo ew_theme_ui_icon( 'pin' ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php esc_html_e( 'Pokaż na mapie', 'eurowet-2026' ); ?><span class="ew-visually-hidden"> <?php esc_html_e( '(otwiera się w nowej karcie)', 'eurowet-2026' ); ?></span></a></p>
	<?php endif; ?>
</section>
