<?php

declare(strict_types=1);

namespace SMF\Tests;

use PHPUnit\Framework\TestCase;

class SearchTest extends TestCase
{
	public static function setUpBeforeClass(): void
	{
		require_once __DIR__ . '/../Sources/Search.php';
	}

	public function tearDown(): void
	{
		global $context;

		unset($_REQUEST, $_POST['search'], $context['topics'], $_SESSION['search_cache']);
		global $context, $smcFunc;

		$smcFunc['db_query']('truncate_table', '
			TRUNCATE {db_prefix}log_search_topics');

		$smcFunc['db_query']('truncate_table', '
			TRUNCATE {db_prefix}log_search_messages');

		$smcFunc['db_query']('truncate_table', '
			TRUNCATE {db_prefix}log_search_results');

		updateSettings(array('search_pointer' => 0));
	}

	public function testSearch(): void
	{
		global $context;

		$_REQUEST['start'] = '0';
		$_REQUEST['show_complete'] = '1';
		$_POST['search'] = 'Simple Machines';
		PlushSearch2();

		$this->assertFalse($context['compact']);
		$this->assertCount(1, $context['topics']);
		$this->assertArrayHasKey(1, $context['topics']);

		$message = $context['get_topics']();
		$this->assertNotFalse($message);
		$this->assertIsArray($message);
		$this->assertEquals(1, $message['id']);
		$this->assertStringContainsString('SMF', $message['first_post']['subject']);
		$this->assertStringContainsString('SMF', $message['last_post']['subject']);
		$this->assertEquals(1, $message['matches'][0]['id']);
		$this->assertStringContainsString('SMF', $message['matches'][0]['subject']);
		$this->assertEquals('Simple Machines', $message['matches'][0]['member']['name']);
		$this->assertEquals('Welcome to Simple Machines Forum!<br><br>We hope you enjoy using your forum.&nbsp; If you have any problems, please feel free to <a href="https://www.simplemachines.org/community/index.php" class="bbc_link" target="_blank" rel="noopener">ask us for assistance</a>.<br><br>Thanks!<br>Simple Machines', $message['matches'][0]['body']);
		$this->assertEquals('Welcome to <mark class="highlight">Simple</mark> <mark class="highlight">Machines</mark> Forum!<br><br>We hope you enjoy using your forum.&nbsp; If you have any problems, please feel free to <a href="https://www.simplemachines.org/community/index.php" class="bbc_link" target="_blank" rel="noopener">ask us for assistance</a>.<br><br>Thanks!<br><mark class="highlight">Simple</mark> <mark class="highlight">Machines</mark>', $message['matches'][0]['body_highlighted']);
	}

	public function testSearchWord(): void
	{
		global $context;

		$_REQUEST['start'] = '0';
		$_REQUEST['show_complete'] = '0';
		$_POST['search'] = 'automated';
		PlushSearch2();
		$topics = $context['topics'];
		$fn = $context['get_topics'];

		$this->assertTrue($context['compact']);
		$this->assertArrayHasKey('topics', $context);
		$this->assertCount(10, $topics);

		$message = $fn();
		$this->assertNotFalse($message);
		$this->assertIsArray($message);
		$this->assertStringContainsString('Automated', $message['matches'][0]['subject']);
		$this->assertStringContainsString('user', $message['matches'][0]['member']['name']);
		$this->assertStringContainsString('Automated', $message['matches'][0]['body']);
		$this->assertStringContainsString('<mark class="highlight">Automated</mark>', $message['matches'][0]['body_highlighted']);
	}

	public function indexProvider()
	{
		return [
			['custom'],
			['fulltext'],
			[''],
		];
	}

	/**
	 * @dataProvider indexProvider
	 */
	public function testSearchWholeWord(string $search_class_name): void
	{
		global $context, $modSettings;

		$_REQUEST['start'] = '0';
		$_REQUEST['show_complete'] = '0';
		$_POST['search'] = 'auto';
		$modSettings['search_index'] = $search_class_name;
		$modSettings['search_match_words'] = '1';
		PlushSearch2();
		$modSettings['search_index'] = '';
		$modSettings['search_match_words'] = '0';

		$this->assertArrayNotHasKey('topics', $context);

		$_REQUEST['start'] = '0';
		$_REQUEST['show_complete'] = '0';
		$_POST['search'] = 'automated';
		$modSettings['search_index'] = $search_class_name;
		$modSettings['search_match_words'] = '1';
		PlushSearch2();
		$modSettings['search_index'] = '';
		$modSettings['search_match_words'] = '0';

		$this->assertCount(10, $context['topics']);
		$message = $context['get_topics']();
		$this->assertNotFalse($message);
		$this->assertIsArray($message);
		$this->assertStringContainsString('Automated', $message['matches'][0]['subject']);
		$this->assertStringContainsString('user', $message['matches'][0]['member']['name']);
		$this->assertStringContainsString('Automated', $message['matches'][0]['body']);
		$this->assertStringContainsString('<mark class="highlight">Automated</mark>', $message['matches'][0]['body_highlighted']);
	}

	/**
	 * @dataProvider indexProvider
	 */
	public function testSearchSubject(string $search_class_name): void
	{
		global $context, $modSettings;

		$_REQUEST['start'] = '0';
		$_REQUEST['subject_only'] = '1';
		$_POST['search'] = 'subject #0';
		$modSettings['search_index'] = $search_class_name;
		PlushSearch2();
		$modSettings['search_index'] = '';

		$this->assertTrue($context['compact']);
		$this->assertArrayHasKey('topics', $context);
		$this->assertCount(1, $context['topics']);

		$message = $context['get_topics']();
		$this->assertNotFalse($message);
		$this->assertIsArray($message);
		$this->assertEquals('Automated Subject #0', $message['matches'][0]['subject']);
		$this->assertStringContainsString('user', $message['matches'][0]['member']['name']);
		$this->assertStringContainsString('Automated', $message['matches'][0]['body']);
		$this->assertStringContainsString('Topic <mark class="highlight">#0</mark>', $message['matches'][0]['body_highlighted']);
		$this->assertEquals('Automated <mark class="highlight">Subject</mark> <mark class="highlight">#0</mark>', $message['matches'][0]['subject_highlighted']);
	}

	/**
	 * @dataProvider indexProvider
	 */
	public function testSearchExcluded(string $search_class_name): void
	{
		global $context, $modSettings;

		$_REQUEST['start'] = '0';
		$_POST['search'] = 'Automated -#0 -#1';
		$modSettings['search_index'] = $search_class_name;
		PlushSearch2();
		$modSettings['search_index'] = '';

		$this->assertTrue($context['compact']);
		$this->assertArrayHasKey('topics', $context);
		$this->assertCount(8, $context['topics']);

		for ($i = 2; $i < 10; $i++)
		{
			$message = $context['get_topics']();
			$this->assertNotFalse($message);
			$this->assertIsArray($message);
			$this->assertCount(1, $message['matches']);
			$this->assertEquals('Automated Subject #' . $i, $message['matches'][0]['subject']);
			$this->assertStringContainsString('user', $message['matches'][0]['member']['name']);
			$this->assertStringContainsString('Automated', $message['matches'][0]['body']);
			$this->assertStringContainsString('Topic #' . $i, $message['matches'][0]['body_highlighted']);
			$this->assertEquals('<mark class="highlight">Automated</mark> Subject #' . $i, $message['matches'][0]['subject_highlighted']);
		}

		$message = $context['get_topics']();
		$this->assertFalse($message);
	}

	/**
	 * @dataProvider indexProvider
	 */
	public function testSearchUser(string $search_class_name): void
	{
		global $context, $modSettings;

		$_REQUEST['start'] = '0';
		$_REQUEST['userspec'] = 'user0,user1';
		$_POST['search'] = 'subject -mate';
		$modSettings['search_index'] = $search_class_name;
		PlushSearch2();
		$modSettings['search_index'] = '';

		$this->assertArrayNotHasKey('topics', $context);

		$_POST['search'] = 'subject';
		$modSettings['search_index'] = $search_class_name;
		PlushSearch2();
		$modSettings['search_index'] = '';

		$this->assertTrue($context['compact']);
		$this->assertArrayHasKey('topics', $context);
		$this->assertCount(2, $context['topics']);

		$message = $context['get_topics']();
		$this->assertNotFalse($message);
		$this->assertIsArray($message);
		$this->assertEquals('Automated Subject #0', $message['matches'][0]['subject']);
		$this->assertStringContainsString('user', $message['matches'][0]['member']['name']);
		$this->assertStringContainsString('Automated', $message['matches'][0]['body']);
		$this->assertStringContainsString('Topic #0', $message['matches'][0]['body_highlighted']);
		$this->assertEquals('Automated <mark class="highlight">Subject</mark> #0', $message['matches'][0]['subject_highlighted']);

		$message = $context['get_topics']();
		$this->assertNotFalse($message);
		$this->assertIsArray($message);
		$this->assertEquals('Automated Subject #1', $message['matches'][0]['subject']);
		$this->assertStringContainsString('user', $message['matches'][0]['member']['name']);
		$this->assertStringContainsString('Automated', $message['matches'][0]['body']);
		$this->assertStringContainsString('Topic #1', $message['matches'][0]['body_highlighted']);
		$this->assertEquals('Automated <mark class="highlight">Subject</mark> #1', $message['matches'][0]['subject_highlighted']);

		$message = $context['get_topics']();
		$this->assertFalse($message);
	}

	public function highlightProvider()
	{
		return [
			[
				'đã đÃ ĐÃ Đã',
				['đã'],
				'<mark class="highlight">đã</mark> <mark class="highlight">đÃ</mark> <mark class="highlight">ĐÃ</mark> <mark class="highlight">Đã</mark>',
			],
			[
				'đã đÃ ĐÃ Đã da da',
				['da'],
				'đã đÃ ĐÃ Đã <mark class="highlight">da</mark> <mark class="highlight">da</mark>',
			],
		];
	}

	/**
	 * @dataProvider highlightProvider
	 */
	public function testHighlight(string $string, array $words, string $expected): void
	{
		$this->assertEquals($expected, highlight($string, $words));
	}
}