<?php
/**
 * 07 Knowledge hub: topic hubs + newest/updated guides.
 *
 * @package Eurowet2026
 */

defined( 'ABSPATH' ) || exit;

$hubs   = ew_theme_hubs( 'topic' );
$guides = get_posts( array( 'post_type' => 'ew_guide', 'post_status' => 'publish', 'posts_per_page' => 3, 'orderby' => 'modified', 'order' => 'DESC', 'no_found_rows' => true ) );
if ( ! $hubs && ! $guides ) {
	return;
}
?>
<section class="ew-section ew-section--alt ew-home-knowledge" aria-labelledby="ew-home-knowledge-title">
	<div class="ew-container">
		<div class="ew-section__header ew-section__header--split">
			<div>
				<h2 id="ew-home-knowledge-title"><?php echo esc_html( ew_theme_home_text( 'knowledge_title' ) ?: __( 'Porady i wiedza', 'eurowet-2026' ) ); ?></h2>
				<?php $lead = ew_theme_home_text( 'knowledge_lead' ); ?>
				<?php if ( $lead ) : ?><p class="ew-lead"><?php echo esc_html( $lead ); ?></p><?php endif; ?>
			</div>
			<a class="ew-btn ew-btn--ghost" href="<?php echo esc_url( ew_theme_url( 'guides' ) ); ?>"><?php esc_html_e( 'Wszystkie porady', 'eurowet-2026' ); ?></a>
		</div>
		<?php ew_theme_hub_cards( array_slice( $hubs, 0, 6 ), 'ew-home-knowledge-title' ); ?>
		<?php
		if ( $guides ) {
			echo ew_theme_render( 'guide-grid', array( 'posts' => $guides, 'heading' => __( 'Najnowsze porady', 'eurowet-2026' ), 'heading_level' => 3, 'id' => 'ew-home-guides' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
		}
		?>
	</div>
</section>
