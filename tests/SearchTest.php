<?php

declare(strict_types=1);

namespace PHPTDD;

class SearchTest extends BaseTestCase
{
	public function setUp() : void
	{
		global $sourcedir;

		require_once($sourcedir . '/Search.php');
	}

	public function testSearch(): void
	{
		global $context;

		unset($context['topics']);
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
		$this->assertEquals('Welcome to <span class="highlight">Simple</span> <span class="highlight">Machines</span> Forum!<br><br>We hope you enjoy using your forum.&nbsp; If you have any problems, please feel free to <a href="https://www.<span class="highlight">simple</span><span class="highlight">machines</span>.org/community/index.php" class="bbc_link" target="_blank" rel="noopener">ask us for assistance</a>.<br><br>Thanks!<br><span class="highlight">Simple</span> <span class="highlight">Machines</span>', $message['matches'][0]['body_highlighted']);
		unset($_REQUEST['start'], $_POST['search'], $context['topics']);
	}

	public function testSearch2(): void
	{
		global $context;

		$_REQUEST['start'] = '0';
		$_REQUEST['show_complete'] = '0';
		$_POST['search'] = 'automated';
		PlushSearch2();

		$this->assertTrue($context['compact']);
		$this->assertCount(10, $context['topics']);

		$message = $context['get_topics']();
		$this->assertNotFalse($message);
		$this->assertIsArray($message);
		$this->assertStringContainsString('Automated', $message['matches'][0]['subject']);
		$this->assertStringContainsString('user', $message['matches'][0]['member']['name']);
		$this->assertStringContainsString('Automated', $message['matches'][0]['body']);
		$this->assertStringContainsString('<span class="highlight">Automated</span>', $message['matches'][0]['body_highlighted']);
		unset($_REQUEST['start'], $_POST['search'], $context['topics']);
	}

	public function testSearchSubject(): void
	{
		global $context;

		$_REQUEST['start'] = '0';
		$_REQUEST['subject_only'] = '1';
		$_POST['search'] = 'subject #0';
		PlushSearch2();

		$this->assertTrue($context['compact']);
		$this->assertCount(1, $context['topics']);

		$message = $context['get_topics']();
		$this->assertNotFalse($message);
		$this->assertIsArray($message);
		$this->assertEquals('Automated Subject #0', $message['matches'][0]['subject']);
		$this->assertStringContainsString('user', $message['matches'][0]['member']['name']);
		$this->assertStringContainsString('Automated', $message['matches'][0]['body']);
		$this->assertStringContainsString('Topic <span class="highlight">#0</span>', $message['matches'][0]['body_highlighted']);
		$this->assertEquals('Automated <span class="highlight">Subject</span> <span class="highlight">#0</span>', $message['matches'][0]['subject_highlighted']);
		unset($_REQUEST, $_POST['search'], $context['topics']);
	}

	public function testSearchUser(): void
	{
		global $context;

		$_REQUEST['start'] = '0';
		$_REQUEST['userspec'] = 'user0,user1';
		$_POST['search'] = 'subject';
		PlushSearch2();

		$this->assertTrue($context['compact']);
		$this->assertCount(2, $context['topics']);

		$message = $context['get_topics']();
		$this->assertNotFalse($message);
		$this->assertIsArray($message);
		$this->assertEquals('Automated Subject #0', $message['matches'][0]['subject']);
		$this->assertStringContainsString('user', $message['matches'][0]['member']['name']);
		$this->assertStringContainsString('Automated', $message['matches'][0]['body']);
		$this->assertStringContainsString('Topic #0', $message['matches'][0]['body_highlighted']);
		$this->assertEquals('Automated <span class="highlight">Subject</span> #0', $message['matches'][0]['subject_highlighted']);

		$message = $context['get_topics']();
		$this->assertNotFalse($message);
		$this->assertIsArray($message);
		$this->assertEquals('Automated Subject #1', $message['matches'][0]['subject']);
		$this->assertStringContainsString('user', $message['matches'][0]['member']['name']);
		$this->assertStringContainsString('Automated', $message['matches'][0]['body']);
		$this->assertStringContainsString('Topic #1', $message['matches'][0]['body_highlighted']);
		$this->assertEquals('Automated <span class="highlight">Subject</span> #1', $message['matches'][0]['subject_highlighted']);

		$message = $context['get_topics']();
		$this->assertFalse($message);
		unset($_REQUEST, $_POST['search'], $context['topics']);
	}

	public function highlightProvider()
	{
		return [
			[
				'đã đÃ ĐÃ Đã',
				['đã'],
				'<span class="highlight">đã</span> <span class="highlight">đÃ</span> <span class="highlight">ĐÃ</span> <span class="highlight">Đã</span>',
			],
			[
				'đã đÃ ĐÃ Đã da da',
				['da'],
				'đã đÃ ĐÃ Đã <span class="highlight">da</span> <span class="highlight">da</span>',
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