<?php
/**
 * Accessibility panel (compact popover — not a floating widget covering content).
 *
 * @package Eurowet2026
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="ew-a11y" id="ew-a11y-panel" role="dialog" aria-modal="false" aria-labelledby="ew-a11y-title" hidden data-ew-a11y data-msg-size="<?php esc_attr_e( 'Rozmiar tekstu', 'eurowet-2026' ); ?>" data-msg-reset="<?php esc_attr_e( 'Przywrócono ustawienia domyślne', 'eurowet-2026' ); ?>" data-msg-on="<?php esc_attr_e( 'włączone', 'eurowet-2026' ); ?>" data-msg-off="<?php esc_attr_e( 'wyłączone', 'eurowet-2026' ); ?>">
	<div class="ew-a11y__head">
		<h2 class="ew-a11y__title" id="ew-a11y-title"><?php esc_html_e( 'Ustawienia dostępności', 'eurowet-2026' ); ?></h2>
		<button type="button" class="ew-tool" data-ew-a11y-close><?php echo ew_theme_ui_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span class="ew-visually-hidden"><?php esc_html_e( 'Zamknij', 'eurowet-2026' ); ?></span></button>
	</div>
	<fieldset class="ew-a11y__group">
		<legend><?php esc_html_e( 'Rozmiar tekstu', 'eurowet-2026' ); ?></legend>
		<div class="ew-a11y__seg" role="group">
			<button type="button" data-ew-a11y-set="text" data-value="" aria-pressed="true">A</button>
			<button type="button" data-ew-a11y-set="text" data-value="1" aria-pressed="false">A+</button>
			<button type="button" data-ew-a11y-set="text" data-value="2" aria-pressed="false">A++</button>
			<button type="button" data-ew-a11y-set="text" data-value="3" aria-pressed="false">A+++</button>
		</div>
	</fieldset>
	<ul class="ew-a11y__toggles ew-list-reset">
		<li><label><input type="checkbox" data-ew-a11y-toggle="contrast" value="high"> <?php esc_html_e( 'Wysoki kontrast', 'eurowet-2026' ); ?></label></li>
		<li><label><input type="checkbox" data-ew-a11y-toggle="links" value="underline"> <?php esc_html_e( 'Podkreślaj linki', 'eurowet-2026' ); ?></label></li>
		<li><label><input type="checkbox" data-ew-a11y-toggle="spacing" value="wide"> <?php esc_html_e( 'Zwiększone odstępy', 'eurowet-2026' ); ?></label></li>
		<li><label><input type="checkbox" data-ew-a11y-toggle="motion" value="reduce"> <?php esc_html_e( 'Ogranicz animacje', 'eurowet-2026' ); ?></label></li>
	</ul>
	<button type="button" class="ew-btn ew-btn--secondary ew-btn--sm" data-ew-a11y-reset><?php esc_html_e( 'Resetuj ustawienia', 'eurowet-2026' ); ?></button>
	<p class="ew-visually-hidden" aria-live="polite" data-ew-a11y-status></p>
</div>
