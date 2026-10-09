<?php
/**
 * Registers `wp eurowet …` commands.
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Cli;

use Eurowet\Core\Contracts\ModuleInterface;

defined( 'ABSPATH' ) || exit;

final class Module implements ModuleInterface {

	public function register(): void {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			\WP_CLI::add_command( 'eurowet', Command::class );
		}
	}
}
