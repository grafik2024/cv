<?php
/**
 * "When to see a vet" notice. Args: level (caution|urgent), items (string[]), heading.
 *
 * @package Eurowet\Core
 */

defined( 'ABSPATH' ) || exit;
require_once __DIR__ . '/_helpers.php';

$level = 'urgent' === ( $args['level'] ?? '' ) ? 'urgent' : 'caution';
$items = array_filter( array_map( 'strval', (array) ( $args['items'] ?? array() ) ) );
$svc   = class_exists( \Eurowet\Core\Finder\Service::class );
$msg   = $svc ? \Eurowet\Core\Finder\Service::redFlagMessage( $level ) : '';
// The standard sentence (brief §27) is always shown; the urgent level adds the stronger message before it.
$std   = $svc ? \Eurowet\Core\Finder\Service::redFlagMessage( 'caution' ) : '';
$hid   = 'ew-vet-' . wp_unique_id();
?>
<aside class="ew-alert <?php echo 'urgent' === $level ? 'ew-alert--danger' : 'ew-alert--warning'; ?> ew-vet" aria-labelledby="<?php echo esc_attr( $hid ); ?>" <?php echo 'urgent' === $level ? 'role="alert"' : ''; ?>>
	<p class="ew-alert__title" id="<?php echo esc_attr( $hid ); ?>"><?php echo ew_icon( 'vet' ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php echo esc_html( (string) ( $args['heading'] ?? __( 'Kiedy skontaktować się z lekarzem weterynarii', 'eurowet-core' ) ) ); ?></p>
	<?php if ( $items ) : ?>
		<ul>
			<?php foreach ( $items as $i ) : ?>
				<li><?php echo esc_html( $i ); ?></li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
	<?php if ( 'urgent' === $level && $msg ) : ?>
		<p><strong><?php echo esc_html( $msg ); ?></strong></p>
	<?php endif; ?>
	<p><?php echo esc_html( $std ); ?></p>
</aside>
