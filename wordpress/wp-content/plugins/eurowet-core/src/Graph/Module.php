<?php
/**
 * Graph module: cache invalidation and CLI rebuild.
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Graph;

use Eurowet\Core\Contracts\ModuleInterface;

defined( 'ABSPATH' ) || exit;

final class Module implements ModuleInterface {

	public function register(): void {
		Cache::register();
		add_action( 'pre_get_posts', array( Hubs::class, 'mainQuery' ) );
	}
}
