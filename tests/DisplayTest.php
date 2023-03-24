<?php

declare(strict_types=1);

namespace SMF\Tests;

use PHPUnit\Framework\TestCase;

final class DisplayTest extends TestCase
{
	public static function setUpBeforeClass(): void
	{
		require_once __DIR__ . '/../Sources/Display.php';
	}

	public function test(): void
	{
		global $context, $modSettings, $settings, $smcFunc, $topic;

		$topic = 1;
		loadBoard();

		$_REQUEST['prev_next'] = 'next';
		$modSettings['enablePreviousNext'] = 1;
		$settings['display_who_viewing'] = 1;
		Display();

		$smcFunc['db_query']('', '
			UPDATE {db_prefix}topics
			SET num_views = 0
			WHERE id_topic = 1');
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}log_topics
			WHERE id_topic = 1');

		$this->assertEquals(0, $context['start_from']);
		$this->assertEquals(0, $context['num_replies']);
		$this->assertEquals(0, $context['real_num_replies']);
		$this->assertEquals(1, $context['num_views']);
		$this->assertEquals(1, $context['topic_first_message']);
		$this->assertEquals(1, $context['topic_last_message']);
		$this->assertFalse($context['user']['started']);
		$this->assertEquals(0, $context['topic_starter_id']);
		$this->assertEquals('Simple Machines', $context['topic_poster_name']);
		$this->assertEquals('Welcome to SMF!', $context['subject']);
		$this->assertIsArray($context['mod_buttons']);

		$message = $context['get_message']();
		$this->assertNotFalse($message);
		$this->assertIsArray($message);
		$this->assertEquals(1, $message['id']);
		$this->assertEquals('Welcome to Simple Machines Forum!<br><br>We hope you enjoy using your forum.&nbsp; If you have any problems, please feel free to <a href="https://www.simplemachines.org/community/index.php" class="bbc_link" target="_blank" rel="noopener">ask us for assistance</a>.<br><br>Thanks!<br>Simple Machines', $message['body']);
		$this->assertEquals('Simple Machines', $message['member']['name']);
	}
}