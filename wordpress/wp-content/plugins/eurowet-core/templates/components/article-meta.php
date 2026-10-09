<?php
/**
 * Byline + dates (published, updated, reviewed). Never shows invented people. Args: post.
 *
 * @package Eurowet\Core
 */

defined( 'ABSPATH' ) || exit;

$post = get_post( $args['post'] ?? 0 );
if ( ! $post ) {
	return;
}
$author   = (string) ew_meta( $post->ID, '_ew_author_label', '' ) ?: __( 'Zespół Eurowet', 'eurowet-core' );
$reviewer = trim( (string) ew_meta( $post->ID, '_ew_reviewer', '' ) );
$reviewed = (string) ew_meta( $post->ID, '_ew_reviewed', '' );
$fmt      = get_option( 'date_format', 'd.m.Y' );
?>
<dl class="ew-meta-line">
	<div><dt><?php esc_html_e( 'Autor', 'eurowet-core' ); ?></dt><dd><?php echo esc_html( $author ); ?></dd></div>
	<div><dt><?php esc_html_e( 'Opublikowano', 'eurowet-core' ); ?></dt><dd><time datetime="<?php echo esc_attr( get_the_date( 'c', $post ) ); ?>"><?php echo esc_html( get_the_date( $fmt, $post ) ); ?></time></dd></div>
	<?php if ( get_the_modified_date( 'Y-m-d', $post ) !== get_the_date( 'Y-m-d', $post ) ) : ?>
		<div><dt><?php esc_html_e( 'Zaktualizowano', 'eurowet-core' ); ?></dt><dd><time datetime="<?php echo esc_attr( get_the_modified_date( 'c', $post ) ); ?>"><?php echo esc_html( get_the_modified_date( $fmt, $post ) ); ?></time></dd></div>
	<?php endif; ?>
	<?php if ( '' !== $reviewed ) : ?>
		<div><dt><?php esc_html_e( 'Zweryfikowano', 'eurowet-core' ); ?></dt><dd><time datetime="<?php echo esc_attr( $reviewed ); ?>"><?php echo esc_html( mysql2date( $fmt, $reviewed ) ); ?></time><?php echo $reviewer ? ' — ' . esc_html( $reviewer ) : ''; ?></dd></div>
	<?php endif; ?>
</dl>
