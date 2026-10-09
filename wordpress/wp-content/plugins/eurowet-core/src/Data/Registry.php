<?php
/**
 * Single source of truth for content types, taxonomies and meta keys (ARCHITECTURE §3).
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Data;

use Eurowet\Core\Reps\Voivodeships;

defined( 'ABSPATH' ) || exit;

/**
 * Registers CPTs, taxonomies, post meta and term meta.
 *
 * Meta storage types (Registry::META):
 *  - str   single-line text (sanitize_text_field)
 *  - text  multi-line plain text (sanitize_textarea_field) — e.g. lead messages
 *  - html  limited HTML (wp_kses_post)
 *  - url   URL string (esc_url_raw)          - email  e-mail string (sanitize_email)
 *  - int   integer                            - bool   '1' / '' (read back as bool)
 *  - date  Y-m-d                              - ids    list<int> (stored as PHP array)
 *  - json  structured array (stored as PHP array), shape in Registry::JSON_SHAPES / Registry::SHAPES
 */
final class Registry {

	public const POST_TYPES = array( 'ew_need', 'ew_guide', 'ew_ingredient', 'ew_rep', 'ew_material', 'ew_lead' );

	public const TAXONOMIES = array( 'ew_line', 'ew_family', 'ew_species', 'ew_area', 'ew_hub', 'ew_material_type' );

	/** Post types translated by Polylang (all ew_* except ew_rep and ew_lead). */
	public const TRANSLATABLE_POST_TYPES = array( 'ew_need', 'ew_guide', 'ew_ingredient', 'ew_material' );

	/** Taxonomies translated by Polylang (all ew_* except ew_family). */
	public const TRANSLATABLE_TAXONOMIES = array( 'ew_line', 'ew_species', 'ew_area', 'ew_hub', 'ew_material_type' );

	/** Public URL bases (PL). */
	public const SLUG_NEED       = 'potrzeby';
	public const SLUG_GUIDE      = 'porady';
	public const SLUG_INGREDIENT = 'skladniki';
	public const SLUG_LINE       = 'linia';
	public const SLUG_HUB        = 'porady';

	/** Taxonomy => object types. */
	public const TAXONOMY_OBJECTS = array(
		'ew_line'          => array( 'product' ),
		'ew_family'        => array( 'product' ),
		'ew_species'       => array( 'product', 'ew_need', 'ew_guide', 'ew_ingredient' ),
		'ew_area'          => array( 'product', 'ew_need', 'ew_guide' ),
		'ew_hub'           => array( 'ew_guide' ),
		'ew_material_type' => array( 'ew_material' ),
	);

	/** Post type => meta key => storage type. */
	public const META = array(
		'product'       => array(
			'_ew_subtitle'          => 'str',
			'_ew_badges'            => 'json',
			'_ew_properties'        => 'html',
			'_ew_usage'             => 'html',
			'_ew_indications'       => 'html',
			'_ew_intended_for'      => 'html',
			'_ew_precautions'       => 'html',
			'_ew_composition'       => 'html',
			'_ew_analytical'        => 'html',
			'_ew_notes'             => 'html',
			'_ew_capacity'          => 'str',
			'_ew_product_type'      => 'str',
			'_ew_catalog_only'      => 'bool',
			'_ew_where_to_buy'      => 'json',
			'_ew_documents'         => 'ids',
			'_ew_video'             => 'int',
			'_ew_video_transcript'  => 'html',
			'_ew_spin360'           => 'ids',
			'_ew_model3d'           => 'int',
			'_ew_rel_similar'       => 'ids',
			'_ew_rel_complementary' => 'ids',
			'_ew_key_ingredients'   => 'ids',
			'_ew_evidence'          => 'json',
			'_ew_need_ids'          => 'ids',
			'_ew_source_path'       => 'str',
			'_ew_source_key'        => 'str',
		),
		'ew_need'       => array(
			'_ew_short_answer'   => 'html',
			'_ew_synonyms'       => 'json',
			'_ew_questions'      => 'json',
			'_ew_products'       => 'json',
			'_ew_guides'         => 'ids',
			'_ew_red_flags'      => 'json',
			'_ew_red_flag_level' => 'str',
			'_ew_priority'       => 'int',
			'_ew_active'         => 'bool',
			'_ew_faq'            => 'json',
			'_ew_care_steps'     => 'html',
			'_ew_avoid'          => 'html',
			'_ew_source_key'     => 'str',
		),
		'ew_guide'      => array(
			'_ew_tldr'         => 'html',
			'_ew_stage'        => 'str',
			'_ew_reviewed'     => 'date',
			'_ew_needs_review' => 'bool',
			'_ew_review_note'  => 'str',
			'_ew_author_label' => 'str',
			'_ew_reviewer'     => 'str',
			'_ew_sources'      => 'json',
			'_ew_faq'          => 'json',
			'_ew_needs'        => 'ids',
			'_ew_products'     => 'ids',
			'_ew_related'      => 'ids',
			'_ew_next'         => 'int',
			'_ew_red_flag'     => 'bool',
			'_ew_red_flags'    => 'json',
			'_ew_legacy_path'  => 'str',
			'_ew_video'        => 'int',
			'_ew_source_key'   => 'str',
		),
		'ew_ingredient' => array(
			'_ew_inci'            => 'str',
			'_ew_aliases'         => 'json',
			'_ew_summary'         => 'html',
			'_ew_function_quotes' => 'json',
			'_ew_sources'         => 'json',
			'_ew_guides'          => 'ids',
			'_ew_source_key'      => 'str',
		),
		'ew_rep'        => array(
			'_ew_first_name'   => 'str',
			'_ew_last_name'    => 'str',
			'_ew_position'     => 'str',
			'_ew_phone'        => 'str',
			'_ew_email'        => 'email',
			'_ew_voivodeships' => 'json',
			'_ew_segments'     => 'json',
			'_ew_active'       => 'bool',
			'_ew_order'        => 'int',
		),
		'ew_material'   => array(
			'_ew_file'       => 'int',
			'_ew_products'   => 'ids',
			'_ew_lang'       => 'str',
			'_ew_source_url' => 'url',
		),
		'ew_lead'       => array(
			'_ew_form'        => 'str',
			'_ew_company'     => 'str',
			'_ew_name'        => 'str',
			'_ew_email'       => 'email',
			'_ew_phone'       => 'str',
			'_ew_voivodeship' => 'str',
			'_ew_segment'     => 'str',
			'_ew_message'     => 'text',
			'_ew_consent'     => 'json',
			'_ew_status'      => 'str',
			'_ew_source_url'  => 'url',
			'_ew_lang'        => 'str',
		),
	);

	/** Post type => json meta key => shape name (see SHAPES). */
	public const JSON_SHAPES = array(
		'product'       => array(
			'_ew_badges'       => 'strings',
			'_ew_where_to_buy' => 'links',
			'_ew_evidence'     => 'evidence',
		),
		'ew_need'       => array(
			'_ew_synonyms'  => 'strings',
			'_ew_questions' => 'strings',
			'_ew_products'  => 'need_products',
			'_ew_red_flags' => 'strings',
			'_ew_faq'       => 'faq',
		),
		'ew_guide'      => array(
			'_ew_sources'   => 'sources',
			'_ew_faq'       => 'faq',
			'_ew_red_flags' => 'strings',
		),
		'ew_ingredient' => array(
			'_ew_aliases'         => 'strings',
			'_ew_function_quotes' => 'quotes',
			'_ew_sources'         => 'sources',
		),
		'ew_rep'        => array(
			'_ew_voivodeships' => 'voivodeships',
			'_ew_segments'     => 'strings',
		),
		'ew_lead'       => array(
			'_ew_consent' => 'consent',
		),
	);

	/**
	 * Shape definitions. 'list' => list of items; 'item' => scalar item type; 'fields' => row fields (name => field type);
	 * 'required' => fields that must be non-empty for a row to be kept; 'enums' => allowed values per field;
	 * 'defaults' => value used for an invalid enum field (otherwise the first allowed value);
	 * 'relations' => row field => target post type (used for translation of IDs and admin pickers).
	 */
	public const SHAPES = array(
		'strings'       => array(
			'list' => true,
			'item' => 'str',
		),
		'voivodeships'  => array(
			'list' => true,
			'item' => 'voivodeship',
		),
		'faq'           => array(
			'list'     => true,
			'fields'   => array(
				'q' => 'str',
				'a' => 'html',
			),
			'required' => array( 'q', 'a' ),
		),
		'sources'       => array(
			'list'     => true,
			'fields'   => array(
				'title'     => 'str',
				'url'       => 'url',
				'publisher' => 'str',
				'year'      => 'str',
			),
			'required' => array( 'title' ),
		),
		'links'         => array(
			'list'     => true,
			'fields'   => array(
				'label' => 'str',
				'url'   => 'url',
			),
			'required' => array( 'label', 'url' ),
		),
		'need_products' => array(
			'list'      => true,
			'fields'    => array(
				'product_id' => 'int',
				'role'       => 'enum',
				'reason'     => 'str',
				'evidence'   => 'text',
			),
			'required'  => array( 'product_id' ),
			'enums'     => array( 'role' => array( 'primary', 'similar', 'complementary' ) ),
			// An unknown role never silently becomes "primary".
			'defaults'  => array( 'role' => 'similar' ),
			'relations' => array( 'product_id' => 'product' ),
		),
		'evidence'      => array(
			'list'      => true,
			'fields'    => array(
				'need'  => 'ref',
				'quote' => 'text',
				'field' => 'str',
			),
			'required'  => array( 'quote' ),
			'relations' => array( 'need' => 'ew_need' ),
		),
		'quotes'        => array(
			'list'      => true,
			'fields'    => array(
				'product_id' => 'int',
				'quote'      => 'text',
			),
			'required'  => array( 'product_id', 'quote' ),
			'relations' => array( 'product_id' => 'product' ),
		),
		'consent'       => array(
			'list'   => false,
			'fields' => array(
				'text'    => 'text',
				'version' => 'str',
				'at'      => 'str',
			),
		),
	);

	/** Post type => meta key => allowed values (str meta). */
	public const ENUMS = array(
		'ew_need'  => array( '_ew_red_flag_level' => array( 'none', 'caution', 'urgent' ) ),
		'ew_guide' => array( '_ew_stage' => array( 'informational', 'practical', 'product' ) ),
		'ew_lead'  => array(
			'_ew_form'        => array( 'b2b', 'private_label', 'rep_contact', 'contact' ),
			'_ew_status'      => array( 'new', 'in_progress', 'closed' ),
			'_ew_voivodeship' => Voivodeships::SLUGS,
		),
	);

	/** Non-translatable defaults returned by Meta::get() when the key is missing. */
	public const DEFAULTS = array(
		'ew_need'  => array( '_ew_red_flag_level' => 'none' ),
		'ew_guide' => array( '_ew_stage' => 'informational' ),
		'ew_lead'  => array( '_ew_status' => 'new' ),
	);

	/** Post type => int meta key => [min, max]. */
	public const RANGES = array(
		'ew_need' => array( '_ew_priority' => array( 0, 100 ) ),
	);

	/** Post type => ids/int meta key => target post type. */
	public const RELATIONS = array(
		'product'       => array(
			'_ew_documents'         => 'attachment',
			'_ew_video'             => 'attachment',
			'_ew_spin360'           => 'attachment',
			'_ew_model3d'           => 'attachment',
			'_ew_rel_similar'       => 'product',
			'_ew_rel_complementary' => 'product',
			'_ew_key_ingredients'   => 'ew_ingredient',
			'_ew_need_ids'          => 'ew_need',
		),
		'ew_need'       => array(
			'_ew_guides' => 'ew_guide',
		),
		'ew_guide'      => array(
			'_ew_needs'    => 'ew_need',
			'_ew_products' => 'product',
			'_ew_related'  => 'ew_guide',
			'_ew_next'     => 'ew_guide',
			'_ew_video'    => 'attachment',
		),
		'ew_ingredient' => array(
			'_ew_guides' => 'ew_guide',
		),
		'ew_material'   => array(
			'_ew_file'     => 'attachment',
			'_ew_products' => 'product',
		),
	);

	/** Keys exposed in REST only in the 'edit' context (internal/editorial data). */
	public const EDIT_ONLY = array( '_ew_source_key', '_ew_source_path', '_ew_review_note', '_ew_needs_review', '_ew_legacy_path' );

	/** Post types whose meta is exposed in REST (others stay server-side only). */
	public const REST_POST_TYPES = array( 'product', 'ew_need', 'ew_guide', 'ew_ingredient' );

	/** Product text sections (meta key => evidence 'field' value). */
	public const PRODUCT_SECTIONS = array(
		'_ew_properties'   => 'properties',
		'_ew_usage'        => 'usage',
		'_ew_indications'  => 'indications',
		'_ew_intended_for' => 'intended_for',
		'_ew_precautions'  => 'precautions',
		'_ew_composition'  => 'composition',
		'_ew_analytical'   => 'analytical',
		'_ew_notes'        => 'notes',
	);

	/** Taxonomy => term meta key => storage type. */
	public const TERM_META = array(
		'ew_hub'     => array(
			'hub_type'     => 'str',
			'species_slug' => 'str',
			'intro'        => 'html',
			'icon'         => 'str',
			'order'        => 'int',
		),
		'ew_area'    => array(
			'icon'         => 'str',
			'order'        => 'int',
			'label_plural' => 'str',
		),
		'ew_species' => array(
			'icon'         => 'str',
			'order'        => 'int',
			'label_plural' => 'str',
		),
	);

	/** Taxonomy => term meta key => allowed values. */
	public const TERM_ENUMS = array(
		'ew_hub' => array( 'hub_type' => array( 'topic', 'species' ) ),
	);

	/** Fixed species vocabulary (ARCHITECTURE §3). */
	public const SPECIES = array( 'pies', 'kot', 'male-ssaki', 'fretka', 'ptaki-ozdobne', 'golebie', 'kon', 'zwierzeta-gospodarskie' );

	/** Fixed body-area vocabulary (ARCHITECTURE §3). */
	public const AREAS = array( 'skora', 'siersc', 'uszy', 'oczy', 'jama-ustna-i-zeby', 'lapy-i-pazury', 'stawy', 'uklad-pokarmowy', 'watroba', 'uklad-moczowy', 'serce', 'odpornosc', 'kondycja', 'stres-i-zachowanie', 'rozrod', 'inne' );

	/**
	 * Registers everything. Hooked on init (priority 9, before WooCommerce product types are used by others).
	 */
	public static function register(): void {
		self::registerTaxonomies();
		self::registerPostTypes();
		self::registerPostMeta();
		self::registerTermMeta();
	}

	public static function registerPostTypes(): void {
		foreach ( self::POST_TYPES as $type ) {
			if ( ! post_type_exists( $type ) ) {
				register_post_type( $type, self::postTypeArgs( $type ) );
			}
		}
	}

	public static function registerTaxonomies(): void {
		foreach ( self::TAXONOMIES as $taxonomy ) {
			if ( ! taxonomy_exists( $taxonomy ) ) {
				register_taxonomy( $taxonomy, self::TAXONOMY_OBJECTS[ $taxonomy ], self::taxonomyArgs( $taxonomy ) );
			}
		}
	}

	/**
	 * Arguments for register_post_type().
	 *
	 * @return array<string, mixed>
	 */
	public static function postTypeArgs( string $type ): array {
		$labels = Labels::postType( $type );
		$base   = array(
			'labels'              => $labels,
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => false,
			'show_in_admin_bar'   => true,
			'show_in_rest'        => false,
			'has_archive'         => false,
			'rewrite'             => false,
			'query_var'           => false,
			'hierarchical'        => false,
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'delete_with_user'    => false,
		);

		switch ( $type ) {
			case 'ew_need':
				$args = array(
					'description'         => __( 'Potrzeby i problemy opiekunów zwierząt powiązane z produktami i poradami.', 'eurowet-core' ),
					'public'              => true,
					'publicly_queryable'  => true,
					'exclude_from_search' => false,
					'show_in_nav_menus'   => true,
					'show_in_rest'        => true,
					'has_archive'         => self::SLUG_NEED,
					'rewrite'             => array(
						'slug'       => self::SLUG_NEED,
						'with_front' => false,
						'feeds'      => false,
					),
					'query_var'           => 'ew_need',
					'menu_position'       => 26,
					'menu_icon'           => 'dashicons-pets',
					'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields' ),
					'taxonomies'          => array( 'ew_species', 'ew_area' ),
				);
				break;
			case 'ew_guide':
				$args = array(
					'description'         => __( 'Porady eksperckie Eurowet (centrum wiedzy).', 'eurowet-core' ),
					'public'              => true,
					'publicly_queryable'  => true,
					'exclude_from_search' => false,
					'show_in_nav_menus'   => true,
					'show_in_rest'        => true,
					'has_archive'         => self::SLUG_GUIDE,
					'rewrite'             => array(
						'slug'       => self::SLUG_GUIDE,
						'with_front' => false,
						'feeds'      => true,
					),
					'query_var'           => 'ew_guide',
					'menu_position'       => 27,
					'menu_icon'           => 'dashicons-welcome-learn-more',
					'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields' ),
					'taxonomies'          => array( 'ew_hub', 'ew_species', 'ew_area' ),
				);
				break;
			case 'ew_ingredient':
				$args = array(
					'description'         => __( 'Składniki występujące w produktach Eurowet.', 'eurowet-core' ),
					'public'              => true,
					'publicly_queryable'  => true,
					'exclude_from_search' => false,
					'show_in_nav_menus'   => true,
					'show_in_rest'        => true,
					'has_archive'         => self::SLUG_INGREDIENT,
					'rewrite'             => array(
						'slug'       => self::SLUG_INGREDIENT,
						'with_front' => false,
						'feeds'      => false,
					),
					'query_var'           => 'ew_ingredient',
					'menu_position'       => 28,
					'menu_icon'           => 'dashicons-carrot',
					'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields' ),
					'taxonomies'          => array( 'ew_species' ),
				);
				break;
			case 'ew_rep':
				$args = array(
					'description'   => __( 'Przedstawiciele handlowi (dane publikowane w wyszukiwarce przedstawicieli).', 'eurowet-core' ),
					'menu_position' => 29,
					'menu_icon'     => 'dashicons-businessperson',
					'supports'      => array( 'title', 'thumbnail', 'revisions' ),
				);
				break;
			case 'ew_material':
				$args = array(
					'description'   => __( 'Materiały do pobrania: katalogi, ulotki, dokumenty.', 'eurowet-core' ),
					'menu_position' => 30,
					'menu_icon'     => 'dashicons-media-document',
					'supports'      => array( 'title', 'thumbnail', 'revisions' ),
					'taxonomies'    => array( 'ew_material_type' ),
				);
				break;
			case 'ew_lead':
				/*
				 * Leads hold personal data: never public, never in REST, created only by the Leads module,
				 * visible to users who can edit others' posts (editors, shop managers, administrators).
				 */
				$cap  = (string) apply_filters( 'ew_lead_capability', 'edit_others_posts' );
				$args = array(
					'description'   => __( 'Zgłoszenia z formularzy B2B, marki własnej i kontaktu z przedstawicielem.', 'eurowet-core' ),
					'menu_position' => 31,
					'menu_icon'     => 'dashicons-email-alt',
					'supports'      => array( 'title' ),
					'capabilities'  => array(
						'create_posts'           => 'do_not_allow',
						'edit_posts'             => $cap,
						'edit_others_posts'      => $cap,
						'edit_private_posts'     => $cap,
						'edit_published_posts'   => $cap,
						'publish_posts'          => $cap,
						'read_private_posts'     => $cap,
						'delete_posts'           => $cap,
						'delete_private_posts'   => $cap,
						'delete_published_posts' => $cap,
						'delete_others_posts'    => $cap,
					),
				);
				break;
			default:
				$args = array();
		}

		/**
		 * Filters the registration arguments of an Eurowet post type.
		 *
		 * @param array<string, mixed> $args Arguments.
		 * @param string               $type Post type.
		 */
		return (array) apply_filters( 'ew_post_type_args', array_merge( $base, $args ), $type );
	}

	/**
	 * Arguments for register_taxonomy().
	 *
	 * @return array<string, mixed>
	 */
	public static function taxonomyArgs( string $taxonomy ): array {
		$base = array(
			'labels'             => Labels::taxonomy( $taxonomy ),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_nav_menus'  => false,
			'show_tagcloud'      => false,
			'show_in_quick_edit' => true,
			'show_admin_column'  => false,
			'show_in_rest'       => true,
			'hierarchical'       => true,
			'rewrite'            => false,
			'query_var'          => $taxonomy,
		);

		switch ( $taxonomy ) {
			case 'ew_line':
				$args = array(
					'description'        => __( 'Linie produktowe Eurowet.', 'eurowet-core' ),
					'public'             => true,
					'publicly_queryable' => true,
					'show_in_nav_menus'  => true,
					'rewrite'            => array(
						'slug'         => self::SLUG_LINE,
						'with_front'   => false,
						'hierarchical' => false,
					),
				);
				break;
			case 'ew_hub':
				$args = array(
					'description'        => __( 'Huby wiedzy grupujące porady (tematyczne i gatunkowe).', 'eurowet-core' ),
					'public'             => true,
					'publicly_queryable' => true,
					'show_in_nav_menus'  => true,
					// Shares the /porady/ base with ew_guide; conflicts are resolved by Graph\Routing.
					'rewrite'            => array(
						'slug'         => self::SLUG_HUB,
						'with_front'   => false,
						'hierarchical' => false,
					),
				);
				break;
			case 'ew_family':
				$args = array(
					'description'        => __( 'Rodzina wariantów pojemności tego samego produktu (przełącznik pojemności).', 'eurowet-core' ),
					'hierarchical'       => false,
					'show_in_rest'       => false,
					'show_in_quick_edit' => false,
				);
				break;
			case 'ew_species':
				$args = array( 'description' => __( 'Gatunki zwierząt.', 'eurowet-core' ) );
				break;
			case 'ew_area':
				$args = array( 'description' => __( 'Obszary ciała i układy.', 'eurowet-core' ) );
				break;
			case 'ew_material_type':
				$args = array(
					'description'  => __( 'Rodzaje materiałów do pobrania.', 'eurowet-core' ),
					'show_in_rest' => false,
				);
				break;
			default:
				$args = array();
		}

		$args = array_merge( $base, $args );

		/**
		 * Filters the registration arguments of an Eurowet taxonomy.
		 *
		 * @param array<string, mixed> $args     Arguments.
		 * @param string               $taxonomy Taxonomy.
		 */
		return (array) apply_filters( 'ew_taxonomy_args', $args, $taxonomy );
	}

	/**
	 * Registers every post meta key of Registry::META.
	 */
	public static function registerPostMeta(): void {
		foreach ( self::META as $post_type => $keys ) {
			foreach ( $keys as $key => $type ) {
				register_post_meta(
					$post_type,
					$key,
					array(
						'type'              => self::wpType( $post_type, $key, $type ),
						'single'            => true,
						'sanitize_callback' => static function ( $value ) use ( $post_type, $key ) {
							return Meta::sanitize( $post_type, $key, $value );
						},
						'auth_callback'     => array( self::class, 'canEditMeta' ),
						'show_in_rest'      => self::restArgs( $post_type, $key, $type ),
					)
				);
			}
		}
	}

	/**
	 * Registers term meta of Registry::TERM_META.
	 */
	public static function registerTermMeta(): void {
		foreach ( self::TERM_META as $taxonomy => $keys ) {
			foreach ( $keys as $key => $type ) {
				$schema = array( 'type' => self::wpType( '', $key, $type ) );
				if ( isset( self::TERM_ENUMS[ $taxonomy ][ $key ] ) ) {
					$schema['enum'] = array_merge( array( '' ), self::TERM_ENUMS[ $taxonomy ][ $key ] );
				}
				register_term_meta(
					$taxonomy,
					$key,
					array(
						'type'              => $schema['type'],
						'single'            => true,
						'sanitize_callback' => static function ( $value ) use ( $taxonomy, $key ) {
							return Meta::sanitizeTerm( $taxonomy, $key, $value );
						},
						'auth_callback'     => static function ( $allowed, $meta_key, $term_id ) {
							return current_user_can( 'edit_term', (int) $term_id );
						},
						'show_in_rest'      => in_array( $taxonomy, array( 'ew_hub', 'ew_area', 'ew_species' ), true ) ? array( 'schema' => $schema ) : false,
					)
				);
			}
		}
	}

	/**
	 * auth_callback for protected post meta: only users who can edit the post.
	 *
	 * @param bool   $allowed  Default.
	 * @param string $meta_key Key.
	 * @param int    $post_id  Post ID.
	 * @param int    $user_id  User ID.
	 */
	public static function canEditMeta( $allowed, $meta_key, $post_id, $user_id = 0 ): bool {
		$user_id = $user_id ? (int) $user_id : get_current_user_id();
		return user_can( $user_id, 'edit_post', (int) $post_id );
	}

	/**
	 * Maps a storage type to a register_meta() type.
	 */
	private static function wpType( string $post_type, string $key, string $type ): string {
		switch ( $type ) {
			case 'int':
				return 'integer';
			case 'bool':
				return 'boolean';
			case 'ids':
				return 'array';
			case 'json':
				$shape = self::SHAPES[ self::JSON_SHAPES[ $post_type ][ $key ] ?? '' ] ?? null;
				return ( null !== $shape && false === $shape['list'] ) ? 'object' : 'array';
			default:
				return 'string';
		}
	}

	/**
	 * REST exposure arguments for a meta key.
	 *
	 * @return bool|array<string, mixed>
	 */
	private static function restArgs( string $post_type, string $key, string $type ) {
		if ( ! in_array( $post_type, self::REST_POST_TYPES, true ) ) {
			return false;
		}
		$schema = self::restSchema( $post_type, $key, $type );
		if ( in_array( $key, self::EDIT_ONLY, true ) ) {
			$schema['context'] = array( 'edit' );
		}
		return array( 'schema' => $schema );
	}

	/**
	 * JSON schema of a meta value.
	 *
	 * @return array<string, mixed>
	 */
	public static function restSchema( string $post_type, string $key, string $type ): array {
		switch ( $type ) {
			case 'int':
				return array( 'type' => 'integer' );
			case 'bool':
				return array( 'type' => 'boolean' );
			case 'date':
				return array(
					'type'    => 'string',
					'pattern' => '^(\d{4}-\d{2}-\d{2})?$',
				);
			case 'ids':
				return array(
					'type'  => 'array',
					'items' => array( 'type' => 'integer' ),
				);
			case 'json':
				$shape_name = self::JSON_SHAPES[ $post_type ][ $key ] ?? '';
				return self::shapeSchema( $shape_name );
			default:
				$schema = array( 'type' => 'string' );
				if ( isset( self::ENUMS[ $post_type ][ $key ] ) ) {
					$schema['enum'] = array_merge( array( '' ), self::ENUMS[ $post_type ][ $key ] );
				}
				return $schema;
		}
	}

	/**
	 * JSON schema for a shape.
	 *
	 * @return array<string, mixed>
	 */
	public static function shapeSchema( string $shape_name ): array {
		$shape = self::SHAPES[ $shape_name ] ?? null;
		if ( null === $shape ) {
			return array(
				'type'  => 'array',
				'items' => array( 'type' => 'string' ),
			);
		}
		if ( isset( $shape['item'] ) ) {
			return array(
				'type'  => 'array',
				'items' => array( 'type' => 'string' ),
			);
		}
		$properties = array();
		foreach ( $shape['fields'] as $field => $field_type ) {
			if ( 'int' === $field_type ) {
				$properties[ $field ] = array( 'type' => 'integer' );
			} elseif ( 'ref' === $field_type ) {
				$properties[ $field ] = array( 'type' => array( 'integer', 'string' ) );
			} elseif ( 'enum' === $field_type ) {
				$properties[ $field ] = array(
					'type' => 'string',
					'enum' => $shape['enums'][ $field ] ?? array(),
				);
			} else {
				$properties[ $field ] = array( 'type' => 'string' );
			}
		}
		$object = array(
			'type'                 => 'object',
			'properties'           => $properties,
			'additionalProperties' => false,
		);
		if ( false === $shape['list'] ) {
			return $object;
		}
		return array(
			'type'  => 'array',
			'items' => $object,
		);
	}

	/**
	 * Storage type of a post meta key, or null when not registered for the post type.
	 */
	public static function type( string $post_type, string $key ): ?string {
		return self::META[ $post_type ][ $key ] ?? null;
	}

	/**
	 * Shape name of a json key.
	 */
	public static function shape( string $post_type, string $key ): ?string {
		return self::JSON_SHAPES[ $post_type ][ $key ] ?? null;
	}

	/**
	 * Target post type of an ids/int relation key.
	 */
	public static function relation( string $post_type, string $key ): ?string {
		return self::RELATIONS[ $post_type ][ $key ] ?? null;
	}

	/**
	 * Default value for a key when it is not stored (null = none). Includes translatable defaults.
	 *
	 * @return mixed
	 */
	public static function defaultValue( string $post_type, string $key ) {
		if ( 'ew_guide' === $post_type && '_ew_author_label' === $key ) {
			return __( 'Zespół Eurowet', 'eurowet-core' );
		}
		return self::DEFAULTS[ $post_type ][ $key ] ?? null;
	}

	/**
	 * Default Polish names of the fixed species and area vocabularies (for seeding).
	 *
	 * @return array<string, array<string, string>> taxonomy => slug => name
	 */
	public static function defaultTerms(): array {
		return array(
			'ew_species' => array(
				'pies'                   => __( 'Pies', 'eurowet-core' ),
				'kot'                    => __( 'Kot', 'eurowet-core' ),
				'male-ssaki'             => __( 'Małe ssaki', 'eurowet-core' ),
				'fretka'                 => __( 'Fretka', 'eurowet-core' ),
				'ptaki-ozdobne'          => __( 'Ptaki ozdobne', 'eurowet-core' ),
				'golebie'                => __( 'Gołębie', 'eurowet-core' ),
				'kon'                    => __( 'Koń', 'eurowet-core' ),
				'zwierzeta-gospodarskie' => __( 'Zwierzęta gospodarskie', 'eurowet-core' ),
			),
			'ew_area'    => array(
				'skora'              => __( 'Skóra', 'eurowet-core' ),
				'siersc'             => __( 'Sierść', 'eurowet-core' ),
				'uszy'               => __( 'Uszy', 'eurowet-core' ),
				'oczy'               => __( 'Oczy', 'eurowet-core' ),
				'jama-ustna-i-zeby'  => __( 'Jama ustna i zęby', 'eurowet-core' ),
				'lapy-i-pazury'      => __( 'Łapy i pazury', 'eurowet-core' ),
				'stawy'              => __( 'Stawy', 'eurowet-core' ),
				'uklad-pokarmowy'    => __( 'Układ pokarmowy', 'eurowet-core' ),
				'watroba'            => __( 'Wątroba', 'eurowet-core' ),
				'uklad-moczowy'      => __( 'Układ moczowy', 'eurowet-core' ),
				'serce'              => __( 'Serce', 'eurowet-core' ),
				'odpornosc'          => __( 'Odporność', 'eurowet-core' ),
				'kondycja'           => __( 'Kondycja', 'eurowet-core' ),
				'stres-i-zachowanie' => __( 'Stres i zachowanie', 'eurowet-core' ),
				'rozrod'             => __( 'Rozród', 'eurowet-core' ),
				'inne'               => __( 'Inne', 'eurowet-core' ),
			),
		);
	}

	/**
	 * Idempotently inserts or updates terms (used by the importer and `wp eurowet seed-terms`).
	 * Never called on regular requests.
	 *
	 * Each term: ['slug' => string, 'name' => string, 'description' => string, 'parent' => slug, 'meta' => [key => value]]
	 * or a plain slug => name map.
	 *
	 * @param string                    $taxonomy Taxonomy.
	 * @param array<int|string, mixed>  $terms    Terms.
	 * @param string|null               $lang     Polylang language slug to assign (null = do not assign).
	 * @return array<string, int> slug => term_id
	 */
	public static function seedTerms( string $taxonomy, array $terms, ?string $lang = null ): array {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			return array();
		}
		$result = array();
		foreach ( $terms as $index => $term ) {
			if ( is_string( $term ) && is_string( $index ) ) {
				$term = array(
					'slug' => $index,
					'name' => $term,
				);
			}
			if ( ! is_array( $term ) || empty( $term['slug'] ) || empty( $term['name'] ) ) {
				continue;
			}
			$slug        = sanitize_title( (string) $term['slug'] );
			$name        = sanitize_text_field( (string) $term['name'] );
			$description = isset( $term['description'] ) ? wp_kses_post( (string) $term['description'] ) : null;
			$parent_id   = 0;
			if ( ! empty( $term['parent'] ) ) {
				$parent_id = $result[ (string) $term['parent'] ] ?? self::findTermId( $taxonomy, sanitize_title( (string) $term['parent'] ), $lang );
			}

			$term_id = self::findTermId( $taxonomy, $slug, $lang );
			if ( $term_id ) {
				$update = array(
					'name'   => $name,
					'parent' => $parent_id,
				);
				if ( null !== $description ) {
					$update['description'] = $description;
				}
				wp_update_term( $term_id, $taxonomy, $update );
			} else {
				$insert = array(
					'slug'   => $slug,
					'parent' => $parent_id,
				);
				if ( null !== $description ) {
					$insert['description'] = $description;
				}
				$created = wp_insert_term( $name, $taxonomy, $insert );
				if ( is_wp_error( $created ) ) {
					continue;
				}
				$term_id = (int) $created['term_id'];
			}

			if ( null !== $lang && function_exists( 'pll_set_term_language' ) && function_exists( 'pll_is_translated_taxonomy' ) && pll_is_translated_taxonomy( $taxonomy ) ) {
				pll_set_term_language( $term_id, $lang );
			}
			foreach ( (array) ( $term['meta'] ?? array() ) as $meta_key => $meta_value ) {
				if ( isset( self::TERM_META[ $taxonomy ][ (string) $meta_key ] ) ) {
					update_term_meta( $term_id, (string) $meta_key, $meta_value );
				}
			}
			$result[ $slug ] = $term_id;
		}
		return $result;
	}

	/**
	 * Finds a term ID by slug (optionally within a Polylang language), ignoring language filters otherwise.
	 */
	public static function findTermId( string $taxonomy, string $slug, ?string $lang = null ): int {
		$args = array(
			'taxonomy'   => $taxonomy,
			'slug'       => $slug,
			'hide_empty' => false,
			'number'     => 1,
			'fields'     => 'ids',
		);
		if ( function_exists( 'pll_is_translated_taxonomy' ) && pll_is_translated_taxonomy( $taxonomy ) ) {
			$args['lang'] = $lang ?? '';
		}
		$ids = get_terms( $args );
		return ( is_array( $ids ) && $ids ) ? (int) $ids[0] : 0;
	}
}
