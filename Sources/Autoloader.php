<?php

/**
 * Simple Machines Forum (SMF)
 *
 * @package SMF
 * @author Simple Machines https://www.simplemachines.org
 * @copyright 2024 Simple Machines and individual contributors
 * @license https://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 3.0 Alpha 2
 */

declare(strict_types=1);

namespace SMF;

use Composer\Autoload\ClassLoader;

/**
 * Autoloader class for initializing the SMF autoloader.
 */
class Autoloader
{
	/**
	 * @var ClassLoader Composer's class loader instance.
	 */
	private ClassLoader $loader;

	/**
	 * Constructor for the Autoloader class.
	 *
	 * @param ClassLoader $loader Composer's class loader instance.
	 */
	public function __construct(ClassLoader $loader)
	{
		$this->loader = $loader;
	}

	/**
	 * Initializes the autoloader by loading third-party mappers and integration hooks.
	 * 
	 * This method sets up the autoloader by building a class map and registering
	 * namespaces. It first checks if the forum is not in the installation phase 
	 * and if the `IntegrationHook` class exists. If both conditions are met, it 
	 * calls the `integrate_autoload` hook which allows third-party integrations 
	 * to modify the class map.
	 * 
	 * The `$classMap` array is built by third-party integrations, where each key
	 * is a namespace prefix and each value is the corresponding directory path.
	 * These mappings are then registered with the Composer autoloader.
	 */
	public function callIntegrations(): void
	{
		$classMap = [];

		// Load third-party integration hooks if necessary
		if (!defined('SMF_INSTALLING') && class_exists(IntegrationHook::class, false)) {
			/**
			 * Calls the integration hook for autoload.
			 * 
			 * The `integrate_autoload` hook allows third-party integrations to add
			 * their own namespace mappings to the autoloader. The integrations can
			 * modify the `$classMap` array to register their namespaces.
			 * 
			 * Example:
			 * ```
			 * $classMap['Vendor\\Package\\'] = '/path/to/vendor/package/src';
			 * ```
			 * 
			 * @param array $classMap Reference to the class map array.
			 */
			IntegrationHook::call('integrate_autoload', [&$classMap]);
		}

		// Register third-party namespace mappers
		foreach ($classMap as $prefix => $dirname) {
			$this->loader->addPsr4($prefix, $dirname);
		}
	}

	/**
	 * Gets the Composer class loader instance.
	 *
	 * @return ClassLoader The Composer class loader instance.
	 */
	public function getLoader(): ClassLoader
	{
		return $this->loader;
	}
}

?>