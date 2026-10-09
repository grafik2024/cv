<?php
/**
 * Finder module wiring: REST, purge cron, settings, analytics page.
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Finder;

use Eurowet\Core\Admin\Settings;
use Eurowet\Core\Contracts\ModuleInterface;

defined( 'ABSPATH' ) || exit;

final class Module implements ModuleInterface {

	public const CRON = 'ew_finder_purge';

	public function register(): void {
		add_action( 'rest_api_init', array( RestController::class, 'register' ) );
		add_action( self::CRON, array( Log::class, 'purge' ) );
		add_action(
			'init',
			static function (): void {
				if ( ! wp_next_scheduled( self::CRON ) ) {
					wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', self::CRON );
				}
			}
		);
		add_action( 'ew_admin_menu', array( Analytics::class, 'menu' ) );
		add_action(
			'ew_settings_sections',
			static function (): void {
				Settings::addSection(
					'finder',
					__( 'Product Finder', 'eurowet-core' ),
					array(
						array( 'id' => 'finder_threshold', 'type' => 'number', 'label' => __( 'Próg pewności dopasowania (0–1)', 'eurowet-core' ), 'default' => 0.45, 'step' => '0.01', 'min' => 0.2, 'max' => 0.95, 'help' => __( 'Poniżej progu wyszukiwarka nie pokazuje produktu, tylko propozycje potrzeb.', 'eurowet-core' ) ),
						array( 'id' => 'finder_logging', 'type' => 'checkbox', 'label' => __( 'Zapisuj anonimowe statystyki wyszukiwań', 'eurowet-core' ), 'default' => 1 ),
						array( 'id' => 'finder_retention_months', 'type' => 'number', 'label' => __( 'Przechowywanie statystyk (miesiące)', 'eurowet-core' ), 'default' => 13, 'min' => 1, 'max' => 36 ),
						array( 'id' => 'finder_llm_enabled', 'type' => 'checkbox', 'label' => __( 'Interpretacja AI zapytań o niskiej pewności (Claude API)', 'eurowet-core' ), 'help' => __( 'AI wybiera wyłącznie jedną z istniejących potrzeb albo „brak”. Nie wybiera produktów.', 'eurowet-core' ) ),
						array( 'id' => 'finder_llm_api_key', 'type' => 'password', 'label' => __( 'Klucz API Anthropic', 'eurowet-core' ) ),
						array( 'id' => 'finder_llm_model', 'type' => 'text', 'label' => __( 'Model', 'eurowet-core' ), 'default' => 'claude-opus-5-5' ),
					)
				);
			}
		);
	}
}
