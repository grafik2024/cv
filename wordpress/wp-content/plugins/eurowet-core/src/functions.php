<?php
/**
 * Public PHP API of Eurowet Core (ARCHITECTURE §4). Templates call only these functions.
 * Every function returns data in the current language and never throws into a template.
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

use Eurowet\Core\Components\Renderer;
use Eurowet\Core\Data\Meta;
use Eurowet\Core\Graph;
use Eurowet\Core\I18n\Polylang;
use Eurowet\Core\Reps;

defined( 'ABSPATH' ) || exit;

/**
 * Maps a post ID to its translation in the given (or current) language. Falls back to the original.
 */
function ew_tr_id( int $post_id, ?string $lang = null ): int {
	return Polylang::translatePostId( $post_id, $lang );
}

/**
 * Typed meta read according to the registry.
 *
 * @return mixed
 */
function ew_meta( int $post_id, string $key, $default = null ) {
	return Meta::get( $post_id, $key, $default );
}

/**
 * Plugin setting from the 'ew_settings' option.
 *
 * @return mixed
 */
function ew_get_option( string $key, $default = null ) {
	static $cache = null;
	if ( null === $cache || doing_action( 'update_option_ew_settings' ) ) {
		$cache = (array) get_option( 'ew_settings', array() );
	}
	return array_key_exists( $key, $cache ) && '' !== $cache[ $key ] ? $cache[ $key ] : $default;
}

/**
 * Runs a callable and returns $fallback when it throws (keeps templates alive).
 *
 * @param callable $fn       Callback.
 * @param mixed    $fallback Fallback value.
 * @return mixed
 */
function ew_safe( callable $fn, $fallback = array() ) {
	try {
		return $fn();
	} catch ( \Throwable $e ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( '[eurowet-core] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine() );
		}
		return $fallback;
	}
}

/** @return array<int, array{product: \WC_Product, role: string, reason: string, evidence: string}> */
function ew_need_products( int $need_id, ?string $role = null ): array {
	return ew_safe( static fn() => Graph\Relations::needProducts( $need_id, $role ) );
}

/** @return array{product: \WC_Product, role: string, reason: string, evidence: string}|null */
function ew_primary_product( int $need_id ): ?array {
	return ew_safe( static fn() => Graph\Relations::primaryProduct( $need_id ), null );
}

/** @return WP_Post[] */
function ew_needs_for_product( int $product_id ): array {
	return ew_safe( static fn() => Graph\Relations::needsForProduct( $product_id ) );
}

/** @return array<string, \WC_Product[]> */
function ew_related_products( int $product_id ): array {
	return ew_safe( static fn() => Graph\RelatedProducts::forProduct( $product_id ) );
}

/** @return array<int, array<string, mixed>> */
function ew_family_variants( int $product_id ): array {
	return ew_safe( static fn() => Graph\Families::variants( $product_id ) );
}

/** @return WP_Post[] */
function ew_guides_for_product( int $product_id, int $limit = 3 ): array {
	return ew_safe( static fn() => Graph\GuideRanker::forProduct( $product_id, $limit ) );
}

/** @return WP_Post[] */
function ew_guides_for_need( int $need_id, int $limit = 6 ): array {
	return ew_safe( static fn() => Graph\GuideRanker::forNeed( $need_id, $limit ) );
}

/** @return WP_Post[] */
function ew_related_guides( int $guide_id, int $limit = 3 ): array {
	return ew_safe( static fn() => Graph\GuideRanker::related( $guide_id, $limit ) );
}

/** @return array{post: ?WP_Post, product_cta: ?array, reason: string}|null */
function ew_next_guide( int $guide_id ): ?array {
	return ew_safe( static fn() => Graph\GuideRanker::next( $guide_id ), null );
}

/** @return WP_Post[] */
function ew_product_ingredients( int $product_id ): array {
	return ew_safe( static fn() => Graph\Ingredients::forProduct( $product_id ) );
}

/** @return \WC_Product[] */
function ew_ingredient_products( int $ingredient_id ): array {
	return ew_safe( static fn() => Graph\Ingredients::products( $ingredient_id ) );
}

/** @return array<string, mixed> */
function ew_hub_query_args( WP_Term $hub, array $extra = array() ): array {
	return ew_safe( static fn() => Graph\Hubs::queryArgs( $hub, $extra ) );
}

/** @return array<int, array<string, mixed>> */
function ew_reps_for_voivodeship( string $slug ): array {
	return ew_safe( static fn() => Reps\Directory::forVoivodeship( $slug ) );
}

/** @return array<string, string> slug => Polish name */
function ew_voivodeships(): array {
	return Reps\Voivodeships::all();
}

/**
 * Renders a component (templates/components/{name}.php) to escaped HTML.
 */
function ew_render( string $component, array $args = array() ): string {
	return ew_safe( static fn() => Renderer::render( $component, $args ), '' );
}

/**
 * Finder result for the current search request (search.php), or null.
 *
 * @return array<string, mixed>|null
 */
function ew_finder_for_search(): ?array {
	if ( ! is_search() || ! class_exists( \Eurowet\Core\Finder\Service::class ) ) {
		return null;
	}
	$q = get_search_query( false );
	return '' === trim( $q ) ? null : ew_safe( static fn() => \Eurowet\Core\Finder\Service::instance()->query( array( 'q' => $q, 'source' => 'search' ) ), null );
}

/**
 * Finder result for /potrzeby/?q=… (or facet params), or null when no query is present.
 *
 * @return array<string, mixed>|null
 */
function ew_finder_result_from_request(): ?array {
	if ( ! class_exists( \Eurowet\Core\Finder\Service::class ) ) {
		return null;
	}
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only public search.
	$params = array(
		'q'       => isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['q'] ) ) : '',
		'species' => isset( $_GET['gatunek'] ) ? sanitize_key( wp_unslash( (string) $_GET['gatunek'] ) ) : '',
		'area'    => isset( $_GET['obszar'] ) ? sanitize_key( wp_unslash( (string) $_GET['obszar'] ) ) : '',
	);
	// phpcs:enable
	if ( '' === $params['q'] && '' === $params['species'] && '' === $params['area'] ) {
		return null;
	}
	$params['source'] = 'finder';
	return ew_safe( static fn() => \Eurowet\Core\Finder\Service::instance()->query( $params ), null );
}

/**
 * Product display helpers shared by components.
 *
 * @return array<string, mixed>
 */
function ew_product_summary( \WC_Product $product ): array {
	$id = $product->get_id();
	return array(
		'id'           => $id,
		'name'         => $product->get_name(),
		'url'          => get_permalink( $id ),
		'image_id'     => (int) $product->get_image_id(),
		'capacity'     => (string) Meta::get( $id, '_ew_capacity', '' ),
		'subtitle'     => (string) Meta::get( $id, '_ew_subtitle', '' ),
		'intended_for' => wp_strip_all_tags( (string) Meta::get( $id, '_ew_intended_for', '' ) ),
		'catalog_only' => (bool) Meta::get( $id, '_ew_catalog_only', false ),
		'price_html'   => $product->get_price_html(),
		'purchasable'  => $product->is_purchasable() && $product->is_in_stock() && ! Meta::get( $id, '_ew_catalog_only', false ),
		'buy_url'      => ( $product->is_purchasable() && $product->is_in_stock() && ! Meta::get( $id, '_ew_catalog_only', false ) ) ? $product->add_to_cart_url() : '',
	);
}
