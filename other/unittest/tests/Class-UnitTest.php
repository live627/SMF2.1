<?php

	abstract class UnitTest
	{
		public $isMultiThreadSafe = true;

		// A function that does initializations needed for any of the tests to start.
		public function initialize()
		{
		}

		// A function that should return an array of tests for the class.
		// The array should consist of <ID> => <name> pairs.
		public function getTests()
		{
			return array();
		}

		// Should return true on success or a string on failure.
		abstract public function doTest($testID);

		public function getTestDescription($testID)
		{
			// By default no description is available.
			return 'No description available';
		}


		// Get a the board ID of the unit testing board, or create such a thing
		// if it didn't exist yet.
		protected function _getUnitTestBoardId()
		{
			global $smcFunc, $sourcedir;

			$request = $smcFunc['db_query']('', '
				SELECT id_board
				FROM {db_prefix}boards
				WHERE name = {string:unit_testing}
				LIMIT 1',
				array(
					'unit_testing' => '[UnitTest] Testing Board',
				)
			);

			if ($smcFunc['db_num_rows']($request) === 0)
			{
				require_once($sourcedir . '/Subs-Categories.php');
				require_once($sourcedir . '/Subs-Boards.php');

				$id_cat = createCategory(array(
					'cat_name' => '[UnitTest] Testing Category',
					'is_collapsible' => true,
				));

				$id_board = createBoard(array(
					'move_to' => 'bottom',
					'target_category' => $id_cat,
					'posts_count' => true,
					'override_theme' => false,
					'board_theme' => 1,
					'access_groups' => array(),
					'board_name' => '[UnitTest] Testing Board',
					'board_description' => 'Board used for some of the unit tests.',
					'redirect' => '',
				));
			}
			else
			{
				list($id_board) = $smcFunc['db_fetch_row']($request);
				$smcFunc['db_free_result']($request);
			}

			return $id_board;
		}

		protected function _getUnitTestCatId()
		{
			global $smcFunc, $sourcedir;

			$request = $smcFunc['db_query']('', '
				SELECT id_cat
				FROM {db_prefix}boards
				WHERE name = {string:unit_testing}
				LIMIT 1',
				array(
					'unit_testing' => '[UnitTest] Testing Board',
				)
			);
			if ($smcFunc['db_num_rows']($request) === 0)
				trigger_error('Category not found.', E_USER_ERROR);

			list ($categoryID) = $smcFunc['db_fetch_row']($request);
			$smcFunc['db_free_result']($request);

			return $categoryID;
		}

		protected function _getUnitTestMemberId($role)
		{
			global $sourcedir, $smcFunc;

			switch ($role)
			{
				case 'admin':
					$regOptions = array(
						'interface' => 'admin',
						'username' => '[UnitTest]Admin',
						'email' => 'unittest_admin@example.com',
						'password' => 'foo',
						'password_check' => 'foo',
						'openid' => '',
						'auth_method' => '',
						'check_reserved_name' => false,
						'check_password_strength' => false,
						'check_email_ban' => false,
						'send_welcome_email' => false,
						'require' => 'nothing',
						'extra_register_vars' => array(),
						'theme_vars' => array(),
					);
				break;

				default:
					trigger_error('Unknown unit testing role', E_USER_ERROR);
				break;
			}

			$request = $smcFunc['db_query']('', '
				SELECT id_member
				FROM {db_prefix}members
				WHERE member_name = {string:unit_testing}
				LIMIT 1',
				array(
					'unit_testing' => $regOptions['username'],
				)
			);

			if ($smcFunc['db_num_rows']($request) === 0)
			{
				require_once($sourcedir . '/Subs-Members.php');
				$id_member = registerMember($regOptions);
			}
			else
			{
				list($id_member) = $smcFunc['db_fetch_row']($request);
				$smcFunc['db_free_result']($request);
			}

			return $id_member;
		}

		protected function _getUnitTestTopic($id_board, $id_member, $subject, $body)
		{
			global $sourcedir, $smcFunc;

			$request = $smcFunc['db_query']('', '
				SELECT t.id_topic, m.id_msg
				FROM {db_prefix}topics AS t
					INNER JOIN {db_prefix}messages AS m ON (m.id_topic = t.id_topic)
				WHERE m.subject = {string:subject}
					AND t.id_board = {int:id_board}
				LIMIT 1',
				array(
					'subject' => '[UnitTest] ' . $subject,
					'id_board' => $id_board,
				)
			);

			if ($smcFunc['db_num_rows']($request) === 0)
			{
				require_once($sourcedir . '/Subs-Post.php');

				$msgOptions = array(
					'id' => 0,
					'subject' => '[UnitTest] ' . $subject,
					'body' => $body,
					'icon' => 'xx',
					'smileys_enabled' => false,
					'attachments' => array(),
					'approved' => true,
				);
				$topicOptions = array(
					'id' => 0,
					'board' => $id_board,
					'poll' => null,
					'lock_mode' => null,
					'sticky_mode' => null,
					'mark_as_read' => true,
					'is_approved' => true,
				);
				$posterOptions = array(
					'id' => $id_member,
					'update_post_count' => true,
				);

				createPost($msgOptions, $topicOptions, $posterOptions);

				$id_topic = $topicOptions['id'];
				$id_msg = $msgOptions['id'];
			}
			else
			{
				list($id_topic, $id_msg) = $smcFunc['db_fetch_row']($request);
				$smcFunc['db_free_result']($request);
			}

			return array($id_msg, $id_topic);
		}

		protected function _createReply($id_board, $id_topic, $id_member, $subject, $body)
		{
			global $sourcedir;

			require_once($sourcedir . '/Subs-Post.php');

			$msgOptions = array(
				'id' => 0,
				'subject' => $subject,
				'body' => $body,
				'icon' => 'xx',
				'smileys_enabled' => false,
				'attachments' => array(),
				'approved' => true,
			);
			$topicOptions = array(
				'id' => $id_topic,
				'board' => $id_board,
				'poll' => null,
				'lock_mode' => null,
				'sticky_mode' => null,
				'mark_as_read' => true,
				'is_approved' => true,
			);
			$posterOptions = array(
				'id' => $id_member,
				'update_post_count' => true,
			);

			createPost($msgOptions, $topicOptions, $posterOptions);

			$id_msg = $msgOptions['id'];

			return $id_msg;
		}

		protected function _simulateClick($URL, $memberID = 0, $sessionID = null, $cookies = array())
		{
			global $cookiename, $smcFunc, $modSettings;

			if ($sessionID !== null)
				$cookies['PHPSESSID'] = $sessionID;

			if ($memberID != 0)
			{
				$request = $smcFunc['db_query']('', '
					SELECT passwd, password_salt
					FROM {db_prefix}members
					WHERE id_member = {int:id_member}',
					array(
						'id_member' => $memberID,
					)
				);

				if ($smcFunc['db_num_rows']($request) === 0)
					trigger_error('Member not found', E_USER_ERROR);

				list ($passwd, $password_salt) = $smcFunc['db_fetch_row']($request);

				$cookies[$cookiename] = serialize(array($memberID, sha1($passwd . $password_salt), time() + 60 * $modSettings['cookieTime'], (empty($modSettings['localCookies']) ? 0 : 1) | (empty($modSettings['globalCookies']) ? 0 : 2)));
			}

			$cookieString = '';
			foreach ($cookies as $cookieID => $cookieValue)
				$cookieString .= $cookieID . '=' . urlencode($cookieValue) . '; ';
			$cookieString = substr($cookieString, 0, -1);

			$parsed_url = parse_url($URL);
			$packet =
				"GET {$parsed_url['path']}" . (isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '') . " HTTP/1.0\r\n" .
				"Pragma: no-cache\r\n" .
				"Accept: */*\r\n" .
				"Accept-Language: en-us\r\n" .
				"User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322; .NET CLR 2.0.40607)\r\n" . (empty($cookies) ? '' :
				"Cookie: $cookieString\r\n") .
				"Host: {$parsed_url['host']}\r\n" .
				"\r\n";

			$socket = fsockopen(gethostbyname($parsed_url['host']), 80, $errno, $errstr, 30);
			stream_set_timeout($socket, 30);
			if (!$socket)
			{
				echo "No response from $host:$port\n";
				return '';
			}
			fputs($socket, $packet);
			$html = '';
			while (!feof($socket))
				$html .= fgets($socket);

			$html = strtr($html, array("\r" => ''));

			$headerLines = explode("\n", substr($html, 0, strpos($html, "\n\n")));
			$headers = array();
			foreach ($headerLines as $headerLine)
				$headers[substr($headerLine, 0, strpos($headerLine, ':'))] = trim(substr($headerLine, strpos($headerLine, ':') + 1));
			$html = substr($html, strpos($html, "\n\n"));

			return array(
				'sessionID' => isset($headers['Set-Cookie']) && preg_match('~PHPSESSID=([0-9a-fA-F]+)~', $headers['Set-Cookie'], $match) === 1 ? $match[1] : $sessionID,
				'headers' => $headers,
				'html' => trim($html),
			);
		}

	}

?>