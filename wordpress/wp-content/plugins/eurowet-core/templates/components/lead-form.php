<?php
/**
 * B2B / private label / rep contact / contact form. Works without JS (admin-post), enhanced with fetch.
 * Args: form, heading, voivodeship.
 *
 * @package Eurowet\Core
 */

defined( 'ABSPATH' ) || exit;

use Eurowet\Core\Leads\Module as Leads;

if ( ! class_exists( Leads::class ) ) {
	return;
}
$form   = in_array( $args['form'] ?? '', Leads::FORMS, true ) ? (string) $args['form'] : 'contact';
$uid    = wp_unique_id( 'ew-lead-' );
$flash  = Leads::flash();
$errors = (array) ( $flash['errors'] ?? array() );
$titles = array( 'b2b' => __( 'Zapytanie o współpracę B2B', 'eurowet-core' ), 'private_label' => __( 'Zapytanie o markę własną', 'eurowet-core' ), 'rep_contact' => __( 'Poproś o kontakt przedstawiciela', 'eurowet-core' ), 'contact' => __( 'Napisz do nas', 'eurowet-core' ) );
$field  = static function ( string $name, string $label, string $type = 'text', bool $required = false, string $autocomplete = '' ) use ( $uid, $errors ): void {
	$id  = $uid . '-' . $name;
	$err = $errors[ $name ] ?? '';
	?>
	<div class="ew-field<?php echo $err ? ' has-error' : ''; ?>">
		<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?><?php echo $required ? ' <span aria-hidden="true">*</span>' : ' <span class="ew-text-muted">' . esc_html__( '(opcjonalnie)', 'eurowet-core' ) . '</span>'; ?></label>
		<?php if ( 'textarea' === $type ) : ?>
			<textarea id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" rows="5" <?php echo $required ? 'required aria-required="true"' : ''; ?> <?php echo $err ? 'aria-invalid="true" aria-describedby="' . esc_attr( $id ) . '-err"' : ''; ?>></textarea>
		<?php else : ?>
			<input id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" type="<?php echo esc_attr( $type ); ?>" <?php echo $autocomplete ? 'autocomplete="' . esc_attr( $autocomplete ) . '"' : ''; ?> <?php echo $required ? 'required aria-required="true"' : ''; ?> <?php echo $err ? 'aria-invalid="true" aria-describedby="' . esc_attr( $id ) . '-err"' : ''; ?>>
		<?php endif; ?>
		<p class="ew-field__error" id="<?php echo esc_attr( $id ); ?>-err" <?php echo $err ? '' : 'hidden'; ?>><?php echo esc_html( $err ); ?></p>
	</div>
	<?php
};
\Eurowet\Core\Components\Renderer::printConfig();
?>
<section class="ew-lead" id="ew-lead-form" aria-labelledby="<?php echo esc_attr( $uid ); ?>-h">
	<h2 id="<?php echo esc_attr( $uid ); ?>-h"><?php echo esc_html( (string) ( $args['heading'] ?? $titles[ $form ] ) ); ?></h2>
	<div class="ew-lead__status" role="status" aria-live="polite" tabindex="-1" data-ew-lead-status>
		<?php if ( $flash ) : ?>
			<p class="ew-alert <?php echo $flash['ok'] ? 'ew-alert--success' : 'ew-alert--danger'; ?>"><?php echo esc_html( (string) $flash['message'] ); ?></p>
		<?php endif; ?>
	</div>
	<form class="ew-lead__form ew-stack" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-ew-lead novalidate>
		<input type="hidden" name="action" value="ew_lead">
		<input type="hidden" name="form" value="<?php echo esc_attr( $form ); ?>">
		<input type="hidden" name="ew_ts" value="<?php echo esc_attr( Leads::timestamp() ); ?>">
		<input type="hidden" name="source_url" value="<?php echo esc_url( home_url( add_query_arg( array() ) ) ); ?>">
		<div class="ew-lead__hp" aria-hidden="true"><label><?php esc_html_e( 'Nie wypełniaj tego pola', 'eurowet-core' ); ?> <input type="text" name="ew_hp" tabindex="-1" autocomplete="off"></label></div>
		<div class="ew-lead__grid">
			<?php
			$field( 'name', __( 'Imię i nazwisko', 'eurowet-core' ), 'text', true, 'name' );
			$field( 'company', __( 'Firma', 'eurowet-core' ), 'text', in_array( $form, array( 'b2b', 'private_label' ), true ), 'organization' );
			$field( 'email', __( 'E-mail', 'eurowet-core' ), 'email', true, 'email' );
			$field( 'phone', __( 'Telefon', 'eurowet-core' ), 'tel', false, 'tel' );
			?>
			<?php if ( in_array( $form, array( 'b2b', 'rep_contact' ), true ) ) : ?>
				<div class="ew-field">
					<label for="<?php echo esc_attr( $uid ); ?>-segment"><?php esc_html_e( 'Rodzaj działalności', 'eurowet-core' ); ?></label>
					<select id="<?php echo esc_attr( $uid ); ?>-segment" name="segment">
						<option value=""><?php esc_html_e( '— wybierz —', 'eurowet-core' ); ?></option>
						<?php foreach ( Leads::segments() as $k => $l ) : ?>
							<option value="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $l ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="ew-field">
					<label for="<?php echo esc_attr( $uid ); ?>-voiv"><?php esc_html_e( 'Województwo', 'eurowet-core' ); ?></label>
					<select id="<?php echo esc_attr( $uid ); ?>-voiv" name="voivodeship">
						<option value=""><?php esc_html_e( '— wybierz —', 'eurowet-core' ); ?></option>
						<?php foreach ( ew_voivodeships() as $k => $l ) : ?>
							<option value="<?php echo esc_attr( $k ); ?>" <?php selected( (string) ( $args['voivodeship'] ?? '' ), $k ); ?>><?php echo esc_html( $l ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			<?php endif; ?>
		</div>
		<?php $field( 'message', __( 'Wiadomość', 'eurowet-core' ), 'textarea', true ); ?>
		<div class="ew-field ew-field--check<?php echo isset( $errors['consent'] ) ? ' has-error' : ''; ?>">
			<input type="checkbox" id="<?php echo esc_attr( $uid ); ?>-consent" name="consent" value="1" required aria-required="true" aria-describedby="<?php echo esc_attr( $uid ); ?>-consent-err">
			<label for="<?php echo esc_attr( $uid ); ?>-consent"><?php echo wp_kses( Leads::consentText(), array( 'a' => array( 'href' => array() ) ) ); ?> <span aria-hidden="true">*</span></label>
			<p class="ew-field__error" id="<?php echo esc_attr( $uid ); ?>-consent-err" <?php echo isset( $errors['consent'] ) ? '' : 'hidden'; ?>><?php echo esc_html( (string) ( $errors['consent'] ?? '' ) ); ?></p>
		</div>
		<p class="ew-text-muted ew-lead__req"><span aria-hidden="true">*</span> <?php esc_html_e( 'pole wymagane', 'eurowet-core' ); ?></p>
		<button class="ew-btn" type="submit"><?php esc_html_e( 'Wyślij zapytanie', 'eurowet-core' ); ?></button>
	</form>
</section>
