<?php
/**
 * Module registry and bootstrap.
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Boots core services and every available module.
 *
 * Modules live in their own namespace (src/{Name}/Module.php) and expose register(): void.
 * Modules that are not present yet are skipped silently, so modules can be added independently.
 * A failing module is isolated: the error is recorded (dashboard / `wp eurowet status`) and the site keeps working.
 */
final class Plugin {

	/**
	 * The single list of modules, in load order. Key = module id used in status reports and filters.
	 */
	public const MODULES = array(
		'Data'      => Data\Module::class,
		'Security'  => Security\Module::class,
		'I18n'      => I18n\Module::class,
		'Graph'     => Graph\Module::class,
		'Finder'    => Finder\Module::class,
		'Seo'       => Seo\Module::class,
		'Reps'      => Reps\Module::class,
		'Leads'     => Leads\Module::class,
		'Materials' => Materials\Module::class,
		'Consent'   => Consent\Module::class,
		'Elementor' => Elementor\Module::class,
		'Admin'     => Admin\Module::class,
		'Cli'       => Cli\Module::class,
	);

	/**
	 * Core services registered directly (not optional, owned by the core module).
	 */
	private const CORE_SERVICES = array(
		'I18n\\Polylang' => I18n\Polylang::class,
		'Graph\\Routing' => Graph\Routing::class,
	);

	private static ?Plugin $instance = null;

	private bool $booted = false;

	/**
	 * Module status: id => ['class' => string, 'state' => 'active'|'missing'|'error', 'error' => string, 'core' => bool].
	 *
	 * @var array<string, array{class: string, state: string, error: string, core: bool}>
	 */
	private array $status = array();

	/**
	 * Module instances keyed by id.
	 *
	 * @var array<string, object>
	 */
	private array $modules = array();

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Registers core services and modules. Safe to call more than once.
	 */
	public function boot(): void {
		if ( $this->booted ) {
			return;
		}
		$this->booted = true;

		foreach ( self::CORE_SERVICES as $id => $class ) {
			$this->load( $id, $class, true );
		}

		/**
		 * Filters the module list (id => class) before modules are registered.
		 *
		 * @param array<string, class-string> $modules Module classes in load order.
		 */
		$modules = (array) apply_filters( 'ew_core_modules', self::MODULES );
		foreach ( $modules as $id => $class ) {
			if ( is_string( $id ) && is_string( $class ) ) {
				$this->load( $id, $class, false );
			}
		}

		/**
		 * Fires after all available modules registered their hooks.
		 *
		 * @param Plugin $plugin The plugin instance.
		 */
		do_action( 'ew_core_loaded', $this );
	}

	/**
	 * Instantiates a module and calls register(), isolating failures.
	 */
	private function load( string $id, string $class, bool $core ): void {
		if ( ! class_exists( $class ) ) {
			$this->status[ $id ] = array(
				'class' => $class,
				'state' => 'missing',
				'error' => '',
				'core'  => $core,
			);
			return;
		}
		try {
			$module = new $class();
			if ( ! method_exists( $module, 'register' ) ) {
				throw new \LogicException( sprintf( 'Module %s has no register() method.', $class ) );
			}
			$module->register();
			$this->modules[ $id ] = $module;
			$this->status[ $id ]  = array(
				'class' => $class,
				'state' => 'active',
				'error' => '',
				'core'  => $core,
			);
		} catch ( \Throwable $e ) {
			$this->status[ $id ] = array(
				'class' => $class,
				'state' => 'error',
				'error' => $e->getMessage(),
				'core'  => $core,
			);
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- debug-only diagnostics.
				error_log( sprintf( '[eurowet-core] Module %s failed to register: %s', $id, $e->getMessage() ) );
			}
		}
	}

	/**
	 * Module status for diagnostics.
	 *
	 * @return array<string, array{class: string, state: string, error: string, core: bool}>
	 */
	public function status(): array {
		return $this->status;
	}

	/**
	 * Returns a registered module instance, if active.
	 */
	public function module( string $id ): ?object {
		return $this->modules[ $id ] ?? null;
	}

	/**
	 * Whether a module (or core service) is active.
	 */
	public function isActive( string $id ): bool {
		return 'active' === ( $this->status[ $id ]['state'] ?? '' );
	}

	/**
	 * Human readable (Polish) module labels.
	 *
	 * @return array<string, string>
	 */
	public static function labels(): array {
		return array(
			'I18n\\Polylang' => __( 'Polylang — tłumaczenie typów treści i relacji', 'eurowet-core' ),
			'Graph\\Routing' => __( 'Routing adresów /porady/ i /produkty/', 'eurowet-core' ),
			'Data'           => __( 'Dane: typy treści, taksonomie, pola', 'eurowet-core' ),
			'Security'       => __( 'Bezpieczeństwo', 'eurowet-core' ),
			'I18n'           => __( 'Języki rozszerzone (EN, FR, UA)', 'eurowet-core' ),
			'Graph'          => __( 'Graf powiązań (produkty, potrzeby, porady)', 'eurowet-core' ),
			'Finder'         => __( 'Wyszukiwarka potrzeb (Product Finder)', 'eurowet-core' ),
			'Seo'            => __( 'SEO / GEO (schema, llms.txt, przekierowania)', 'eurowet-core' ),
			'Reps'           => __( 'Przedstawiciele handlowi', 'eurowet-core' ),
			'Leads'          => __( 'Formularze i leady B2B', 'eurowet-core' ),
			'Materials'      => __( 'Materiały do pobrania', 'eurowet-core' ),
			'Consent'        => __( 'Zgody cookies (Consent Mode v2)', 'eurowet-core' ),
			'Elementor'      => __( 'Integracja z Elementorem', 'eurowet-core' ),
			'Admin'          => __( 'Panel administracyjny', 'eurowet-core' ),
			'Cli'            => __( 'Komendy WP-CLI', 'eurowet-core' ),
		);
	}
}
