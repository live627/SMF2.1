<?php

namespace PHPTDD;

class LoggingTest extends BaseTestCase
{
	public function test(): void
	{
		global $smcFunc;

		logAction('agreement_accepted', array('member_affected' => 111), 'user');
		logAction('delete', array('topic' => 11, 'subject' => 'ha', 'member' => 5, 'board' => 88, 'member_affected' => 111));

		$request = $smcFunc['db_query']('', '
			SELECT action
			FROM {db_prefix}log_actions
			WHERE id_member = 111');
		$actual = array_column($smcFunc['db_fetch_all']($request), 'action');
		$smcFunc['db_free_result']($request);
		$this->assertCount(2, $actual);
		$this->assertContains('agreement_accepted', $actual);
		$this->assertContains('delete', $actual);
	}
}
