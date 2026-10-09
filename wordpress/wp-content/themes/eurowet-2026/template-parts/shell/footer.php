<?php
/**
 * Site footer: company data (only real, configured fields), navigation, reps CTA, socials, legal, cookies.
 *
 * @package Eurowet2026
 */

defined( 'ABSPATH' ) || exit;

$c      = ew_theme_company();
$social = ew_theme_social_links();
?>
<footer class="ew-footer">
	<div class="ew-container ew-footer__grid">
		<section class="ew-footer__brand" aria-label="EUROWET">
			<img class="ew-footer__logo" src="<?php echo esc_url( ew_theme_asset_uri( 'assets/brand/eurowet-logo-white-360.png' ) ); ?>" width="180" height="37" alt="EUROWET" loading="lazy">
			<?php if ( ! empty( $c['name'] ) || ! empty( $c['address'] ) ) : ?>
				<address>
					<?php echo ! empty( $c['name'] ) ? '<strong>' . esc_html( $c['name'] ) . '</strong><br>' : ''; ?>
					<?php echo ! empty( $c['address'] ) ? nl2br( esc_html( $c['address'] ) ) : ''; ?>
				</address>
			<?php endif; ?>
			<ul class="ew-footer__contact ew-list-reset">
				<?php if ( ! empty( $c['phone'] ) ) : ?><li><?php echo ew_theme_ui_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <a href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', $c['phone'] ) ); ?>"><?php echo esc_html( $c['phone'] ); ?></a></li><?php endif; ?>
				<?php if ( ! empty( $c['email'] ) ) : ?><li><?php echo ew_theme_ui_icon( 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <a href="mailto:<?php echo esc_attr( antispambot( $c['email'] ) ); ?>"><?php echo esc_html( antispambot( $c['email'] ) ); ?></a></li><?php endif; ?>
				<?php if ( ! empty( $c['ids'] ) ) : ?><li class="ew-footer__ids"><?php echo esc_html( $c['ids'] ); ?></li><?php endif; ?>
			</ul>
		</section>
		<nav class="ew-footer__nav" aria-label="<?php esc_attr_e( 'Menu w stopce', 'eurowet-2026' ); ?>">
			<?php
			if ( has_nav_menu( 'footer' ) ) {
				wp_nav_menu( array( 'theme_location' => 'footer', 'container' => false, 'menu_class' => 'ew-footer__menu', 'depth' => 2 ) );
			} else {
				ew_theme_fallback_menu();
			}
			?>
		</nav>
		<section class="ew-footer__cta" aria-labelledby="ew-footer-cta">
			<h2 id="ew-footer-cta" class="ew-footer__h"><?php esc_html_e( 'Współpraca handlowa', 'eurowet-2026' ); ?></h2>
			<p><a class="ew-btn ew-btn--light" href="<?php echo esc_url( ew_theme_url( 'reps' ) ); ?>"><?php esc_html_e( 'Znajdź przedstawiciela', 'eurowet-2026' ); ?></a></p>
			<p><a class="ew-footer__link" href="<?php echo esc_url( ew_theme_url( 'b2b' ) ); ?>"><?php esc_html_e( 'Współpraca B2B', 'eurowet-2026' ); ?></a> · <a class="ew-footer__link" href="<?php echo esc_url( ew_theme_url( 'private_label' ) ); ?>"><?php esc_html_e( 'Marka własna', 'eurowet-2026' ); ?></a></p>
			<?php if ( $social ) : ?>
				<ul class="ew-footer__social ew-list-reset">
					<?php foreach ( $social as $s ) : ?>
						<li><a href="<?php echo esc_url( $s['url'] ); ?>" rel="noopener" target="_blank"><?php echo esc_html( $s['label'] ); ?><span class="ew-visually-hidden"> <?php esc_html_e( '(otwiera się w nowej karcie)', 'eurowet-2026' ); ?></span></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</section>
	</div>
	<div class="ew-container ew-footer__bottom">
		<p>© <?php echo esc_html( gmdate( 'Y' ) ); ?> EUROWET</p>
		<nav aria-label="<?php esc_attr_e( 'Informacje prawne', 'eurowet-2026' ); ?>">
			<ul class="ew-footer__legal ew-list-reset">
				<?php
				if ( has_nav_menu( 'legal' ) ) {
					wp_nav_menu( array( 'theme_location' => 'legal', 'container' => false, 'items_wrap' => '%3$s', 'depth' => 1 ) );
				} else {
					$privacy = get_privacy_policy_url();
					echo $privacy ? '<li><a href="' . esc_url( $privacy ) . '">' . esc_html__( 'Polityka prywatności', 'eurowet-2026' ) . '</a></li>' : '';
				}
				?>
				<li><a href="#" data-ew-consent-open><?php esc_html_e( 'Ustawienia cookies', 'eurowet-2026' ); ?></a></li>
			</ul>
		</nav>
	</div>
</footer>
