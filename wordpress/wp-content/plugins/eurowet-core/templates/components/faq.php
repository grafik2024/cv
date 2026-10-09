<?php
/**
 * FAQ accordion (visible text — the same items feed FAQPage schema). Args: items [{q,a}], heading.
 *
 * @package Eurowet\Core
 */

defined( 'ABSPATH' ) || exit;

$items = array_values( array_filter( (array) ( $args['items'] ?? array() ), static fn( $i ) => is_array( $i ) && ! empty( $i['q'] ) && ! empty( $i['a'] ) ) );
if ( ! $items ) {
	return;
}
$id = wp_unique_id( 'ew-faq-' );
?>
<section class="ew-faq" aria-labelledby="<?php echo esc_attr( $id ); ?>">
	<h2 id="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( (string) ( $args['heading'] ?? __( 'Najczęstsze pytania', 'eurowet-core' ) ) ); ?></h2>
	<?php foreach ( $items as $i => $it ) : ?>
		<details class="ew-faq__item" <?php echo 0 === $i ? 'open' : ''; ?>>
			<summary class="ew-faq__q"><h3><?php echo esc_html( wp_strip_all_tags( (string) $it['q'] ) ); ?></h3></summary>
			<div class="ew-faq__a"><?php echo wp_kses_post( wpautop( (string) $it['a'] ) ); ?></div>
		</details>
	<?php endforeach; ?>
</section>
