<?php

function testing_hook()
{
	return 'integrate_test';
}

function testing_hook2()
{
	return 'integrate_test';
}

class testing_class
{
	public static function staticHook()
	{
		return 'integrate_test1';
	}

	private $hook;

	public function __construct()
	{
		$this->hook = 'integrate_test2';
	}

	public function instantiatedHook()
	{
		return $this->hook;
	}
}

function get()
{
	return array(
		array('a' => 1, 'b' => 'j&j', 'd' => 123456.78, 'e' => 12345678),
	);
}

function num()
{
	return 5;
}