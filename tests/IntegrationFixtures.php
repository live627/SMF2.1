<?php

declare(strict_types=1);

function testing_hook(): string
{
	return 'integrate_test';
}

function testing_hook2(): string
{
	return 'integrate_test';
}

class testing_class
{
	public static function staticHook(): string
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

/**
 * @return       (float|int|string)[][]
 *
 * @psalm-return array{0: array{a: int, b: string, d: float, e: int}}
 */
function get(): array
{
	return [
		['a' => 1, 'b' => 'j&j', 'd' => 123456.78, 'e' => 12345678],
	];
}

function num(): int
{
	return 5;
}