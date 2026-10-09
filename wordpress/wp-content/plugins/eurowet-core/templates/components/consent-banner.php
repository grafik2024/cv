<?php
/**
 * Cookie consent: banner (Akceptuj / Odrzuć / Ustawienia — equal weight) + settings dialog.
 * Rendered always (hidden when a choice exists) so "Ustawienia cookies" in the footer can reopen it.
 *
 * @package Eurowet\Core
 */

defined( 'ABSPATH' ) || exit;

$has = isset( $_COOKIE[ \Eurowet\Core\Consent\Module::COOKIE ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
?>
<div class="ew-consent" data-ew-consent <?php echo $has ? 'hidden' : ''; ?> role="region" aria-label="<?php esc_attr_e( 'Zgoda na pliki cookies', 'eurowet-core' ); ?>">
	<div class="ew-consent__inner">
		<p class="ew-consent__text">
			<strong><?php esc_html_e( 'Szanujemy Twoją prywatność.', 'eurowet-core' ); ?></strong>
			<?php esc_html_e( 'Niezbędne pliki cookies zapewniają działanie sklepu. Za Twoją zgodą użyjemy też analitycznych (statystyki odwiedzin) i marketingowych. Możesz zmienić decyzję w każdej chwili.', 'eurowet-core' ); ?>
			<a href="<?php echo esc_url( home_url( '/polityka-prywatnosci/' ) ); ?>"><?php esc_html_e( 'Polityka prywatności', 'eurowet-core' ); ?></a>
		</p>
		<div class="ew-consent__actions">
			<button type="button" class="ew-btn ew-btn--secondary" data-ew-consent-action="reject"><?php esc_html_e( 'Odrzuć', 'eurowet-core' ); ?></button>
			<button type="button" class="ew-btn ew-btn--secondary" data-ew-consent-action="settings"><?php esc_html_e( 'Ustawienia', 'eurowet-core' ); ?></button>
			<button type="button" class="ew-btn" data-ew-consent-action="accept"><?php esc_html_e( 'Akceptuj', 'eurowet-core' ); ?></button>
		</div>
	</div>
</div>
<dialog class="ew-consent-dialog" data-ew-consent-dialog aria-labelledby="ew-consent-title">
	<form method="dialog" class="ew-stack">
		<h2 id="ew-consent-title"><?php esc_html_e( 'Ustawienia plików cookies', 'eurowet-core' ); ?></h2>
		<fieldset class="ew-stack">
			<legend class="ew-visually-hidden"><?php esc_html_e( 'Kategorie', 'eurowet-core' ); ?></legend>
			<label class="ew-consent-dialog__row"><input type="checkbox" checked disabled> <span><strong><?php esc_html_e( 'Niezbędne', 'eurowet-core' ); ?></strong> — <?php esc_html_e( 'koszyk, logowanie, bezpieczeństwo, zapamiętanie tej decyzji.', 'eurowet-core' ); ?></span></label>
			<label class="ew-consent-dialog__row"><input type="checkbox" name="analytics" value="1"> <span><strong><?php esc_html_e( 'Analityczne', 'eurowet-core' ); ?></strong> — <?php esc_html_e( 'anonimowe statystyki odwiedzin, które pomagają ulepszać stronę.', 'eurowet-core' ); ?></span></label>
			<label class="ew-consent-dialog__row"><input type="checkbox" name="marketing" value="1"> <span><strong><?php esc_html_e( 'Marketingowe', 'eurowet-core' ); ?></strong> — <?php esc_html_e( 'dopasowanie reklam w innych serwisach.', 'eurowet-core' ); ?></span></label>
		</fieldset>
		<div class="ew-consent__actions">
			<button type="button" class="ew-btn ew-btn--secondary" data-ew-consent-action="reject"><?php esc_html_e( 'Odrzuć wszystkie', 'eurowet-core' ); ?></button>
			<button type="button" class="ew-btn ew-btn--secondary" data-ew-consent-action="save"><?php esc_html_e( 'Zapisz wybór', 'eurowet-core' ); ?></button>
			<button type="button" class="ew-btn" data-ew-consent-action="accept"><?php esc_html_e( 'Akceptuj wszystkie', 'eurowet-core' ); ?></button>
		</div>
	</form>
</dialog>
