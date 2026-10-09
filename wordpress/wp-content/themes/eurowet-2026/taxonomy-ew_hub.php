<?php
/**
 * Knowledge hub (/porady/{hub}/): topic or species hub — intro, image, needs of the area, guides, products.
 *
 * @package Eurowet2026
 */

defined( 'ABSPATH' ) || exit;

get_header();

$hub     = get_queried_object();
$hub_id  = $hub instanceof WP_Term ? (int) $hub->term_id : 0;
$intro   = $hub_id ? (string) ew_theme_term_meta( $hub_id, 'intro', '' ) : '';
$intro   = '' !== $intro ? $intro : ( $hub instanceof WP_Term ? (string) $hub->description : '' );
$type    = $hub_id ? (string) ew_theme_term_meta( $hub_id, 'hub_type', 'topic' ) : 'topic';
$species = $hub_id ? array_filter( array_map( 'trim', explode( ',', (string) ew_theme_term_meta( $hub_id, 'species_slug', '' ) ) ) ) : array();
$paged   = max( 1, (int) get_query_var( 'paged' ) );
$picture = $hub instanceof WP_Term ? ew_theme_hub_picture( $hub->slug, '', array( 'class' => 'ew-hub-hero__media', 'loading' => 'eager', 'sizes' => '(min-width: 960px) 46vw, 100vw' ) ) : '';
?>
<header class="ew-hub-hero<?php echo $picture ? '' : ' ew-hub-hero--plain'; ?>">
	<div class="ew-container ew-hub-hero__grid">
		<div class="ew-hub-hero__text">
			<?php echo ew_theme_render( 'breadcrumbs' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<p class="ew-eyebrow"><?php echo 'species' === $type ? esc_html__( 'Porady według gatunku', 'eurowet-2026' ) : esc_html__( 'Porady i wiedza', 'eurowet-2026' ); ?></p>
			<h1 class="ew-page-header__title"><?php single_term_title(); ?></h1>
			<?php if ( '' !== trim( $intro ) ) : ?>
				<div class="ew-page-header__intro"><?php echo wp_kses_post( wpautop( $intro ) ); ?></div>
			<?php endif; ?>
		</div>
		<?php if ( $picture ) : ?>
			<figure class="ew-hub-hero__figure">
				<?php echo $picture; // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<figcaption><?php echo ew_theme_image_disclosure( $hub->slug ); // phpcs:ignore WordPress.Security.EscapeOutput ?></figcaption>
			</figure>
		<?php endif; ?>
	</div>
</header>

<?php
if ( 1 === $paged ) {
	// Most common needs for this hub's species (species hubs) — links into the Product Finder graph.
	$needs = $species ? ew_theme_render( 'need-tiles', array( 'species' => $species, 'limit' => 8, 'heading' => __( 'Najczęstsze potrzeby', 'eurowet-2026' ) ) ) : '';
	if ( $needs ) {
		echo '<section class="ew-section ew-section--tight"><div class="ew-container">' . $needs . '</div></section>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
}
?>

<section class="ew-section" aria-labelledby="ew-hub-guides">
	<div class="ew-container">
		<div class="ew-section__header"><h2 id="ew-hub-guides"><?php esc_html_e( 'Porady w tym dziale', 'eurowet-2026' ); ?></h2></div>
		<?php if ( have_posts() ) : ?>
			<ul class="ew-grid ew-grid--fill ew-list-reset" role="list">
				<?php
				while ( have_posts() ) :
					the_post();
					echo '<li>' . ew_theme_render( 'guide-card', array( 'post' => get_post(), 'heading_level' => 3 ) ) . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput
				endwhile;
				?>
			</ul>
			<?php ew_theme_pagination(); ?>
		<?php else : ?>
			<p><?php esc_html_e( 'Porady w tym dziale są w przygotowaniu.', 'eurowet-2026' ); ?> <a href="<?php echo esc_url( ew_theme_url( 'guides' ) ); ?>"><?php esc_html_e( 'Zobacz wszystkie porady', 'eurowet-2026' ); ?></a></p>
		<?php endif; ?>
	</div>
</section>

<?php
$others = array_values( array_filter( array_merge( ew_theme_hubs( 'topic' ), ew_theme_hubs( 'species' ) ), static fn( $t ) => $t->term_id !== $hub_id ) );
if ( $others ) :
	?>
	<nav class="ew-section ew-section--alt" aria-labelledby="ew-hub-others">
		<div class="ew-container">
			<h2 id="ew-hub-others" class="ew-h3"><?php esc_html_e( 'Inne działy porad', 'eurowet-2026' ); ?></h2>
			<ul class="ew-chips ew-list-reset" role="list">
				<?php foreach ( $others as $t ) : ?>
					<li><a class="ew-chip" href="<?php echo esc_url( get_term_link( $t ) ); ?>"><?php echo esc_html( $t->name ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</nav>
	<?php
endif;

get_footer();
