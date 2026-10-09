<?php
/**
 * Ingredients A–Z (/skladniki/).
 *
 * @package Eurowet2026
 */

defined( 'ABSPATH' ) || exit;

get_header();

ew_theme_page_header(
	__( 'Składniki i technologie', 'eurowet-2026' ),
	__( 'Składniki, które Eurowet opisuje w swoich produktach — z cytatami z opisów produktów i listą produktów, w których występują.', 'eurowet-2026' ),
	__( 'Porady i wiedza', 'eurowet-2026' )
);

$items = array();
while ( have_posts() ) {
	the_post();
	$letter = mb_strtoupper( mb_substr( remove_accents( get_the_title() ), 0, 1 ) );
	$letter = preg_match( '/[A-Z]/', $letter ) ? $letter : '#';
	$items[ $letter ][] = get_post();
}
ksort( $items );
?>
<div class="ew-section ew-section--tight">
	<div class="ew-container">
		<?php if ( $items ) : ?>
			<nav class="ew-az" aria-label="<?php esc_attr_e( 'Litery', 'eurowet-2026' ); ?>">
				<ul class="ew-chips ew-list-reset">
					<?php foreach ( array_keys( $items ) as $l ) : ?>
						<li><a class="ew-chip" href="#ew-az-<?php echo esc_attr( strtolower( (string) $l ) ); ?>"><?php echo esc_html( (string) $l ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</nav>
			<?php foreach ( $items as $l => $posts ) : ?>
				<section class="ew-az__group" aria-labelledby="ew-az-<?php echo esc_attr( strtolower( (string) $l ) ); ?>">
					<h2 id="ew-az-<?php echo esc_attr( strtolower( (string) $l ) ); ?>" class="ew-az__letter"><?php echo esc_html( (string) $l ); ?></h2>
					<ul class="ew-az__list ew-list-reset" role="list">
						<?php foreach ( $posts as $p ) : ?>
							<?php $inci = (string) ew_theme_meta( $p->ID, '_ew_inci', '' ); ?>
							<li class="ew-az__item">
								<a href="<?php echo esc_url( get_permalink( $p ) ); ?>"><?php echo esc_html( get_the_title( $p ) ); ?></a>
								<?php if ( $inci && 0 !== strcasecmp( $inci, get_the_title( $p ) ) ) : ?><span class="ew-text-muted" lang="la"><?php echo esc_html( $inci ); ?></span><?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endforeach; ?>
		<?php else : ?>
			<p><?php esc_html_e( 'Opisy składników są w przygotowaniu.', 'eurowet-2026' ); ?></p>
		<?php endif; ?>
	</div>
</div>
<?php
get_footer();
