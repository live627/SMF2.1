<?php

declare(strict_types=1);

namespace SMF\Tests;

use PHPUnit\Framework\TestCase;

final class TaskTest extends TestCase
{
	public function testMenu(): void
	{
		global $smcFunc;

		$smcFunc['db_insert'](
			'insert',
			'{db_prefix}background_tasks',
			['task_file' => 'string', 'task_class' => 'string', 'task_data' => 'string', 'claimed_time' => 'int'],
			['$boarddir/tests/TaskFixtures.php', 'TestTask', '', 0],
			['id_task']
		);
	}
}
