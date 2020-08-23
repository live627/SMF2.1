<?php
namespace PHPTDD;

use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Include functionality for accessing protected/private members and methods
 */
abstract class BaseTestCase extends TestCase
{
	protected function setProperty($object, $propertyName, $propertyValue)
	{
		$reflection = new ReflectionClass($object);
		$reflection_property = $reflection->getProperty($propertyName);
		$reflection_property->setAccessible(true);
		$reflection_property->setValue($object, $propertyValue);
	}

	protected function getProperty($object, $propertyName)
	{
		$reflection = new ReflectionClass($object);
		$reflection_property = $reflection->getProperty($propertyName);
		$reflection_property->setAccessible(true);

		return $reflection_property->getValue($object);
	}

	protected function callMethod($object, $methodName, $arguments = [])
	{
		$reflection = new ReflectionClass($object);
		$reflection_method = $reflection->getMethod($methodName);
		$reflection_method->setAccessible(true);

		return $reflection_method->invokeArgs($object, $arguments);
	}
function FeignLogin(int $id = 1)
{
	global $mem;
	$mem = $id;
	loadUserSettings();
	loadPermissions();
}
}
