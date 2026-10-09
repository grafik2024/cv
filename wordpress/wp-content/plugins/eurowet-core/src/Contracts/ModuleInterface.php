<?php
/**
 * Contract for plugin modules.
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Contracts;

defined( 'ABSPATH' ) || exit;

/**
 * Every module listed in Plugin::MODULES exposes a register() method that only adds hooks.
 * Implementing this interface is recommended but not required (Plugin checks for register()).
 */
interface ModuleInterface {

	/**
	 * Adds the module's hooks. Must not perform expensive work or output anything.
	 */
	public function register(): void;
}
