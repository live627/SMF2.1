<?php

declare(strict_types=1);

namespace SMF\Tests;

use PHPUnit\Framework\TestCase;

use Mentions;

global $sourcedir;
require_once __DIR__ . '/../Sources/Mentions.php';
class MockMentions extends Mentions
{
	public static function getExcludedBbcRegex()
	{
		if (empty(self::$excluded_bbc_regex))
			self::setExcludedBbcRegex();

		return self::$excluded_bbc_regex;
	}
}

class MentionsTest extends TestCase
{
	public function testPut(): void
	{
		global $smcFunc;

		$request = $smcFunc['db_query']('', '
			SELECT id_msg, id_member
			FROM {db_prefix}messages
			ORDER BY id_msg DESC'
		);
		[$id_msg, $id_member] = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		$actual = Mentions::getMentionsByContent('test', $id_msg);
		$this->assertCount(0, $actual);

		Mentions::insertMentions('test', $id_msg, [1 => ['id' => '1']], $id_member);
		$actual = Mentions::getMentionsByContent('test', $id_msg);
		$this->assertCount(1, $actual);
		$this->assertArrayHasKey(1, $actual);
		$this->assertEquals(1, $actual[1]['id']);
		$this->assertEquals('test', $actual[1]['real_name']);
		$this->assertEquals($id_member, $actual[1]['mentioned_by']['id']);
		$this->assertEquals('user9', $actual[1]['mentioned_by']['name']);

		$actual = Mentions::modifyMentions('test', $id_msg, [1 => ['id' => '1'], 2 => ['id' => '2']], $id_member);
		$this->assertArrayHasKey(1, $actual['unchanged']);
		$this->assertArrayHasKey(2, $actual['added']);

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}mentions
			WHERE content_type = {literal:test}
				AND content_id = {int:id}',
			[
				'id' => $id_msg
			]
		);
	}

	public function testGet(): void
	{
		$actual = Mentions::getBody('This is text @mentioning @user1 End of post.', [2 => ['id' => '2', 'real_name' => 'user1']]);
		$this->assertEquals('This is text @mentioning [member=2]user1[/member] End of post.', $actual);
	}

	public function testCodes(): void
	{
		$this->assertEquals(
			'(?>youtube|attach|email|nobbc|quote|code|html|time|php|url|f(?>lash|tp)|i(?>url|mg))',
			MockMentions::getExcludedBbcRegex()
		);
	}

	public function data(): array
	{
		return [
			['attach'],
			['code'],
			['email'],
			['flash'],
			['ftp'],
			['html'],
			['img'],
			['iurl'],
			['nobbc'],
			['php'],
			['quote'],
			['time'],
			['url'],
			['youtube'],
		];
	}

	/**
	 * @dataProvider data
	 */
	public function test(string $code): void
	{
		$this->assertEmpty(
			Mentions::getExistingMentions(
				sprintf(
					'%s%sThis is text @mentioning%1$s%2$s@user1 End of post%1$s%2$s',
					str_repeat('[' . $code . ' author=foo]Quote @mentioning [member=2]user1[/member] ', 10),
					str_repeat('[/' . $code . ']', 10)
				)
			)
		);
	}

	public function testGetMentionedMembers(): void
	{
		$actual = array_values(Mentions::getMentionedMembers('This is text.
[quote author=foo]Quoted level 1.
[quote]Quote level 2.[/quote]
A @mentioning @user0 in a quote.
[/quote]
A @mentioning @user1 not in a quote.
End of post.'));
		$this->assertCount(1, $actual);
		$this->assertEquals('user1', $actual[0]['real_name']);
	}

	public function testVerifyMentionedMembers(): void
	{
		$actual = Mentions::verifyMentionedMembers('[member=2]user1[/member]', [2 => ['id' => '2', 'real_name' => 'user1']]);
		$this->assertCount(1, $actual);
		$this->assertArrayHasKey(2, $actual);
		$this->assertEquals(2, $actual[2]['id']);
		$this->assertEquals('user1', $actual[2]['real_name']);
	}

	public function testVerifyMentionedMembers2(): void
	{
		$actual = Mentions::verifyMentionedMembers('[member=2]user11[/member]', [2 => ['id' => '2', 'real_name' => 'user1']]);
		$this->assertCount(0, $actual);
	}

	public function testGetExistingMentions(): void
	{
		$actual = Mentions::getExistingMentions('This is text.
[quote author=foo]Quoted level 1.
[quote]Quote level 2.[/quote]
[member=1]user0[/member]
[/quote]
[member=2]user1[/member]
End of post.');
		$this->assertCount(1, $actual);
		$this->assertArrayHasKey(2, $actual);
		$this->assertEquals('user1', $actual[2]);
	}

	public function testGetQuotedMembers(): void
	{
		global $smcFunc;

		$request = $smcFunc['db_query']('', '
			SELECT id_msg, id_member
			FROM {db_prefix}messages
			ORDER BY id_msg DESC'
		);
		[$id_msg, $id_member] = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		$actual = Mentions::getQuotedMembers('This is text.
[quote author=live627 link=msg=' . $id_msg . ' date=1582795754]
text text text text text...
[/quote]
[quote author=foo]Quoted level 1.
[quote]Quote level 2.[/quote]
[/quote]
End of post.', 4);
		$this->assertCount(1, $actual);
		$this->assertArrayHasKey($id_member, $actual);
		$this->assertEquals($id_member, $actual[$id_member]['id']);
		$this->assertEquals('user9', $actual[$id_member]['real_name']);
	}

	public function testGetQuotedMembers2(): void
	{
		$actual = Mentions::getQuotedMembers('This is text.
[quote author=foo]Quoted level 1.
[quote]Quote level 2.[/quote]
[quote author=live627 link=msg=222 date=1582795754]
text text text text text...
[/quote]
[/quote]
End of post.', 4);
		$this->assertCount(0, $actual);
	}
}
