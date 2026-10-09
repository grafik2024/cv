<?php
/**
 * Sources list. Args: items [{title,url,publisher,year}].
 *
 * @package Eurowet\Core
 */

defined( 'ABSPATH' ) || exit;

$items = array_values( array_filter( (array) ( $args['items'] ?? array() ), static fn( $i ) => is_array( $i ) && ! empty( $i['title'] ) ) );
if ( ! $items ) {
	return;
}
$id = wp_unique_id( 'ew-src-' );
?>
<section class="ew-sources" aria-labelledby="<?php echo esc_attr( $id ); ?>">
	<h2 id="<?php echo esc_attr( $id ); ?>" class="ew-sources__title"><?php esc_html_e( 'Źródła', 'eurowet-core' ); ?></h2>
	<ol class="ew-sources__list">
		<?php foreach ( $items as $s ) : ?>
			<li>
				<?php if ( ! empty( $s['url'] ) ) : ?>
					<a href="<?php echo esc_url( (string) $s['url'] ); ?>" rel="noopener" target="_blank"><?php echo esc_html( (string) $s['title'] ); ?><span class="ew-visually-hidden"> <?php esc_html_e( '(otwiera się w nowej karcie)', 'eurowet-core' ); ?></span></a>
				<?php else : ?>
					<?php echo esc_html( (string) $s['title'] ); ?>
				<?php endif; ?>
				<?php echo esc_html( trim( ( ! empty( $s['publisher'] ) ? ' — ' . $s['publisher'] : '' ) . ( ! empty( $s['year'] ) ? ', ' . $s['year'] : '' ) ) ); ?>
			</li>
		<?php endforeach; ?>
	</ol>
</section>
