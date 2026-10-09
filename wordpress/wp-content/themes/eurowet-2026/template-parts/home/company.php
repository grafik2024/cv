<?php
/**
 * 09 About Eurowet · 10 B2B · 11 Private label (client's copy).
 *
 * @package Eurowet2026
 */

defined( 'ABSPATH' ) || exit;

$about = ew_theme_home_text( 'about_text' );
$b2b   = ew_theme_home_text( 'b2b_text' );
$pl    = ew_theme_home_text( 'private_label_text' );
?>
<?php if ( $about ) : ?>
	<section class="ew-section ew-home-about" aria-labelledby="ew-home-about-title">
		<div class="ew-container ew-container--narrow ew-stack">
			<p class="ew-eyebrow"><?php esc_html_e( 'O Eurowet', 'eurowet-2026' ); ?></p>
			<h2 id="ew-home-about-title"><?php echo esc_html( ew_theme_home_text( 'about_title' ) ?: __( 'O Eurowet', 'eurowet-2026' ) ); ?></h2>
			<p class="ew-lead"><?php echo esc_html( $about ); ?></p>
			<p><a class="ew-btn ew-btn--ghost" href="<?php echo esc_url( home_url( user_trailingslashit( 'o-firmie' ) ) ); ?>"><?php esc_html_e( 'Więcej o firmie', 'eurowet-2026' ); ?></a></p>
		</div>
	</div>
<?php endif; ?>

<?php if ( $b2b || $pl ) : ?>
	<div class="ew-band ew-home-partners">
		<div class="ew-container ew-partners">
			<?php if ( $b2b ) : ?>
				<section class="ew-partners__col" aria-labelledby="ew-home-b2b-title">
					<h2 id="ew-home-b2b-title"><?php echo esc_html( ew_theme_home_text( 'b2b_title' ) ?: __( 'Współpraca B2B', 'eurowet-2026' ) ); ?></h2>
					<p><?php echo esc_html( $b2b ); ?></p>
					<?php $aud = ew_theme_home_list( 'b2b_audiences' ); ?>
					<?php if ( $aud ) : ?>
						<ul class="ew-partners__list" role="list">
							<?php foreach ( $aud as $a ) : ?><li><?php echo ew_theme_ui_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php echo esc_html( $a ); ?></li><?php endforeach; ?>
						</ul>
					<?php endif; ?>
					<p><a class="ew-btn ew-btn--light" href="<?php echo esc_url( ew_theme_url( 'b2b' ) ); ?>"><?php esc_html_e( 'Zostań partnerem', 'eurowet-2026' ); ?></a></p>
				</section>
			<?php endif; ?>
			<?php if ( $pl ) : ?>
				<section class="ew-partners__col" aria-labelledby="ew-home-pl-title">
					<h2 id="ew-home-pl-title"><?php echo esc_html( ew_theme_home_text( 'private_label_title' ) ?: __( 'Marka własna', 'eurowet-2026' ) ); ?></h2>
					<p><?php echo esc_html( $pl ); ?></p>
					<p><a class="ew-btn ew-btn--outline-light" href="<?php echo esc_url( ew_theme_url( 'private_label' ) ); ?>"><?php esc_html_e( 'Zapytaj o markę własną', 'eurowet-2026' ); ?></a></p>
				</section>
			<?php endif; ?>
		</div>
	</div>
<?php endif; ?>
