<?php

declare(strict_types=1);

require_once('./SSI.php');
require_once($sourcedir . '/Subs-Boards.php');
require_once($sourcedir . '/Subs-Members.php');
require_once($sourcedir . '/Subs-Post.php');
$modSettings['bcrypt_hash_cost'] = 4;
$modSettings['disableRegisterCheck'] = 4;

$request = $smcFunc['db_query']('', 'SELECT id_board FROM {db_prefix}boards WHERE id_board != 1');
$board_ids = [];

while ([$id_board] = $smcFunc['db_fetch_row']($request))
	$board_ids[] = $id_board;
$smcFunc['db_free_result']($request);
deleteBoards($board_ids);
$request = $smcFunc['db_query']('', 'SELECT id_member FROM {db_prefix}members WHERE id_member != 1');
$members = [];

while ([$id_member] = $smcFunc['db_fetch_row']($request))
	$members[] = $id_member;
$smcFunc['db_free_result']($request);

if (!empty($members))
	deleteMembers($members);
$board_ids = [];
$members = [];
$topics = [];

for ($i = 0; $i < 10; $i++)
{
	$regOptions = [
		'interface' => 'guest',
		'username' => 'user' . $i,
		'email' => 'user' . $i . '@mydomain.com',
		'password' => 'user' . $i,
		'password_check' => 'user' . $i,
		'require' => 'nothing',
		'send_welcome_email' => false,
		'check_password_strength' => false,
		'check_email_ban' => false,
	];

	$id_member = registerMember($regOptions, true);

	echo "Regstered user $i\n";
	$members[$i] = $id_member;
}

for ($i = 0; $i < 10; $i++)
{
	$boardOptions = [
		'board_name' => 'Automated Board #' . $i,
		'target_category' => 1,
		'target_board' => 1,
		'move_to' => 'before',
	];

	$board_ids[$i] = createBoard($boardOptions);

	echo "Created board $i\n";
}

for ($i = 0; $i < 10; $i++)
{
	$msgOptions = [
		'body' => 'Automated Topic #' . $i,
		'id' => 0,
		'subject' => 'Automated Subject #' . $i,
	];
	$topicOptions = [
		'id' => 0,
		'board' => $board_ids[$i],
		'mark_as_read' => false,
	];
	$posterOptions = [
		'id' => $members[$i],
	];
	createPost($msgOptions, $topicOptions, $posterOptions);
	$topics[$i] = $topicOptions['id'];

	echo "Created topic $i\n";
}

for ($i = 0; $i < 10; $i++)
{
	$msgOptions = [
		'body' => 'Automated Test #' . $i,
		'id' => 0,
		'subject' => 'Automated Test #' . $i,
	];
	$topicOptions = [
		'id' => $topics[$i],
		'board' => $board_ids[$i],
		'mark_as_read' => false,
	];
	$posterOptions = [
		'id' => $members[$i],
	];
	createPost($msgOptions, $topicOptions, $posterOptions);
	$msgOptions = [
		'id' => $msgOptions['id'],
		'body' => "Board $board_ids[$i] - Topic $topics[$i] - Messsage $msgOptions[id]",
		'approved' => true,
	];
	$topicOptions = [];
	modifyPost($msgOptions, $topicOptions, $posterOptions);

	echo "Created message $i\n";
}
