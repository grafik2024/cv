<?php
/**
 * Data module: content model registration, schema upgrades, reverse index.
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Data;

use Eurowet\Core\Contracts\ModuleInterface;
use Eurowet\Core\Lifecycle;

defined( 'ABSPATH' ) || exit;

final class Module implements ModuleInterface {

	public function register(): void {
		// Priority 9: after WooCommerce (5) registers `product`, before most consumers (10).
		add_action( 'init', array( Registry::class, 'register' ), 9 );
		add_action( 'init', array( Lifecycle::class, 'checkVersion' ), 30 );
		ReverseIndex::register();
	}
}
