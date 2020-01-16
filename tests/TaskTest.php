<?php

namespace PHPTDD;

class TaskTest extends BaseTestCase
{
	public function testMenu()
	{
		global $smcFunc;

		$smcFunc['db_insert']('insert',
			'{db_prefix}background_tasks',
			array('task_file' => 'string', 'task_class' => 'string', 'task_data' => 'string', 'claimed_time' => 'int'),
			array('$boarddir/tests/TaskFixtures.php', 'TestTask', '', 0),
			array('id_task')
		);
	}
}