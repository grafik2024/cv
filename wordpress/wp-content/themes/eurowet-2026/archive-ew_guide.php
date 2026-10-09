<?php
/**
 * Knowledge Hub "Porady i wiedza" (/porady/): finder, topic hubs, species hubs, all guides.
 *
 * @package Eurowet2026
 */

defined( 'ABSPATH' ) || exit;

get_header();

$topics  = ew_theme_hubs( 'topic' );
$species = ew_theme_hubs( 'species' );
$paged   = max( 1, (int) get_query_var( 'paged' ) );

ew_theme_page_header(
	__( 'Porady i wiedza', 'eurowet-2026' ),
	ew_theme_home_text( 'knowledge_lead' ),
	__( 'Centrum wiedzy Eurowet', 'eurowet-2026' )
);
?>

<?php if ( 1 === $paged ) : ?>
	<section class="ew-section ew-section--tight" aria-label="<?php esc_attr_e( 'Wyszukaj poradę lub produkt', 'eurowet-2026' ); ?>">
		<div class="ew-container ew-container--narrow">
			<?php echo ew_theme_render( 'finder', array( 'variant' => 'page', 'heading' => __( 'Opisz, co dzieje się z Twoim zwierzęciem', 'eurowet-2026' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>
	</section>

	<?php if ( $topics ) : ?>
		<section class="ew-section" aria-labelledby="ew-hubs-topics">
			<div class="ew-container">
				<div class="ew-section__header"><h2 id="ew-hubs-topics"><?php esc_html_e( 'Tematy', 'eurowet-2026' ); ?></h2></div>
				<?php ew_theme_hub_cards( $topics ); ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $species ) : ?>
		<section class="ew-section ew-section--alt" aria-labelledby="ew-hubs-species">
			<div class="ew-container">
				<div class="ew-section__header"><h2 id="ew-hubs-species"><?php esc_html_e( 'Według gatunku', 'eurowet-2026' ); ?></h2></div>
				<?php ew_theme_hub_cards( $species ); ?>
			</div>
		</section>
	<?php endif; ?>
<?php endif; ?>

<section class="ew-section" aria-labelledby="ew-all-guides">
	<div class="ew-container">
		<div class="ew-section__header"><h2 id="ew-all-guides"><?php esc_html_e( 'Wszystkie porady', 'eurowet-2026' ); ?></h2></div>
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
			<p><?php esc_html_e( 'Porady są w przygotowaniu.', 'eurowet-2026' ); ?></p>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
