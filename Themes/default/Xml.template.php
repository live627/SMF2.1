<?php
// Version: 2.0 Alpha; Xml

function template_sendbody()
{
	global $context, $settings, $options, $txt;

	echo '<', '?xml version="1.0" encoding="', $context['character_set'], '"?', '>
<smf>
	<message view="', $context['view'], '">', $context['message'], '</message>
</smf>';
}

function template_quotefast()
{
	global $context, $settings, $options, $txt;

	echo '<', '?xml version="1.0" encoding="', $context['character_set'], '"?', '>
<smf>
	<quote>', $context['quote']['xml'], '</quote>
</smf>';
}

function template_modifyfast()
{
	global $context, $settings, $options, $txt;

	echo '<', '?xml version="1.0" encoding="', $context['character_set'], '"?', '>
<smf>
	<subject><![CDATA[', $context['message']['subject'], ']]></subject>
	<message id="msg_', $context['message']['id'], '"><![CDATA[', $context['message']['body'], ']]></message>
</smf>';
}

function template_modifydone()
{
	global $context, $settings, $options, $txt;

	echo '<', '?xml version="1.0" encoding="', $context['character_set'], '"?', '>
<smf>
	<message id="msg_', $context['message']['id'], '">';
	if (empty($context['message']['errors']))
	{
		echo '
		<modified><![CDATA[', empty($context['message']['modified']['time']) ? '' : '&#171; <i>' . $txt['last_edit'] . ': ' . $context['message']['modified']['time'] . ' ' . $txt['by'] . ' ' . $context['message']['modified']['name'] . '</i> &#187;', ']]></modified>
		<subject is_first="', $context['message']['first_in_topic'] ? '1' : '0', '"><![CDATA[', $context['message']['subject'], ']]></subject>
		<body><![CDATA[', $context['message']['body'], ']]></body>';
	}
	else
		echo '
		<error in_subject="', $context['message']['error_in_subject'] ? '1' : '0', '" in_body="', $context['message']['error_in_body'] ? '1' : '0', '"><![CDATA[', implode('<br />', $context['message']['errors']), ']]></error>';
	echo '
	</message>
</smf>';
}

function template_modifytopicdone()
{
	global $context, $settings, $options, $txt;

	echo '<', '?xml version="1.0" encoding="', $context['character_set'], '"?', '>
<smf>
	<message id="msg_', $context['message']['id'], '">';
	if (empty($context['message']['errors']))
	{
		echo '
		<modified><![CDATA[', empty($context['message']['modified']['time']) ? '' : '&#171; <i>' . $txt['last_edit'] . ': ' . $context['message']['modified']['time'] . ' ' . $txt['by'] . ' ' . $context['message']['modified']['name'] . '</i> &#187;', ']]></modified>';
		if (!empty($context['message']['subject']))
			echo '
		<subject><![CDATA[', $context['message']['subject'], ']]></subject>';
	}
	else
		echo '
		<error in_subject="', $context['message']['error_in_subject'] ? '1' : '0', '"><![CDATA[', implode('<br />', $context['message']['errors']), ']]></error>';
	echo '
	</message>
</smf>';
}

function template_post()
{
	global $context, $settings, $options, $txt;

	echo '<', '?xml version="1.0" encoding="', $context['character_set'], '"?', '>
<smf>
	<preview>
		<subject><![CDATA[', $context['preview_subject'], ']]></subject>
		<body><![CDATA[', $context['preview_message'], ']]></body>
	</preview>
	<errors serious="', empty($context['error_type']) || $context['error_type'] != 'serious' ? '0' : '1', '" topic_locked="', $context['locked'] ? '1' : '0', '">';
	if (!empty($context['post_error']['messages']))
		foreach ($context['post_error']['messages'] as $message)
			echo '
		<error><![CDATA[', $message, ']]></error>';
	echo '
		<caption name="guestname" color="', isset($context['post_error']['long_name']) || isset($context['post_error']['no_name']) || isset($context['post_error']['bad_name']) ? 'red' : '', '" />
		<caption name="email" color="', isset($context['post_error']['no_email']) || isset($context['post_error']['bad_email']) ? 'red' : '', '" />
		<caption name="evtitle" color="', isset($context['post_error']['no_event']) ? 'red' : '', '" />
		<caption name="subject" color="', isset($context['post_error']['no_subject']) ? 'red' : '', '" />
		<caption name="question" color="', isset($context['post_error']['no_question']) ? 'red' : '', '" />', isset($context['post_error']['no_message']) || isset($context['post_error']['long_message']) ? '
		<post_error />' : '', '
	</errors>
	<num_replies>', isset($context['num_replies']) ? $context['num_replies'] : '0', '</num_replies>';

	if (!empty($context['previous_posts']))
	{
		echo '
	<new_posts>';
		foreach ($context['previous_posts'] as $post)
			echo '
		<post id="', $post['id'], '">
			<time><![CDATA[', $post['time'], ']]></time>
			<poster><![CDATA[', $post['poster'], ']]></poster>
			<message><![CDATA[', $post['message'], ']]></message>
		</post>';
		echo '
	</new_posts>';
	}

	echo '
</smf>';
}

function template_stats()
{
	global $context, $settings, $options, $txt, $modSettings;

	echo '<', '?xml version="1.0" encoding="', $context['character_set'], '"?', '>
<smf>';
	foreach ($context['monthly'] as $month)
	{
		echo '
	<month id="', $month['date']['year'], $month['date']['month'], '">';
		foreach ($month['days'] as $day)
			echo '
		<day date="', $day['year'], '-', $day['month'], '-', $day['day'], '" new_topics="', $day['new_topics'], '" new_posts="', $day['new_posts'], '" new_members="', $day['new_members'], '" most_members_online="', $day['most_members_online'], '"', empty($modSettings['hitStats']) ? '' : ' hits="' . $day['hits'] . '"', ' />';
		echo '
	</month>';
	}
	echo '
</smf>';
}

function template_split()
{
	global $context, $settings, $options;

	echo '<', '?xml version="1.0" encoding="', $context['character_set'], '"?', '>
<smf>
	<pageIndex section="not_selected" startFrom="', $context['not_selected']['start'], '"><![CDATA[', $context['not_selected']['page_index'], ']]></pageIndex>
	<pageIndex section="selected" startFrom="', $context['selected']['start'], '"><![CDATA[', $context['selected']['page_index'], ']]></pageIndex>';
	foreach ($context['changes'] as $change)
	{
		if ($change['type'] == 'remove')
			echo '
	<change id="', $change['id'], '" curAction="remove" section="', $change['section'], '" />';
		else
			echo '
	<change id="', $change['id'], '" curAction="insert" section="', $change['section'], '">
		<subject><![CDATA[', $change['insert_value']['subject'], ']]></subject>
		<body><![CDATA[', $change['insert_value']['body'], ']]></body>
		<poster><![CDATA[', $change['insert_value']['poster'], ']]></poster>
	</change>';
	}
	echo '
</smf>';
}

// This is just to hold off some errors if people are stupid.
if (!function_exists('template_button_strip'))
{
	function template_button_strip($button_strip, $direction = 'top', $force_reset = false, $custom_td = '')
	{
	}
	function template_menu()
	{
	}
	function theme_linktree()
	{
	}
}

function template_results()
{
	global $context, $settings, $options, $txt;
	echo '<', '?xml version="1.0" encoding="', $context['character_set'], '"?', '>
<smf>';
	
	if (empty($context['topics']))
		echo '
		<noresults>', $txt['search_no_results'], '</noresults>';
	else
	{
		echo '
		<results>';
		
		while ($topic = $context['get_topics']())
		{
			echo '
			<result>
				<id>', $topic['id'], '</id>
				<relevance>', $topic['relevance'], '</relevance>
				<board>
					<id>', $topic['board']['id'], '</id>
					<name>', $topic['board']['name'], '</name>
					<href>', $topic['board']['href'], '</href>
				</board>
				<category>
					<id>', $topic['category']['id'], '</id>
					<name>', $topic['category']['name'], '</name>
					<href>', $topic['category']['href'], '</href>
				</category>
				<messages>';
			foreach($topic['matches'] AS $message)
			{
				echo '
					<message>
						<id>', $message['id'], '</id>
						<subject><![CDATA[', $message['subject_highlighted'] != '' ? $message['subject_highlighted'] : $message['subject'], ']]></subject>
						<body><![CDATA[', $message['body_highlighted'] != '' ? $message['body_highlighted'] : $message['body'], ']]></body>
						<time>', $message['time'], '</time>
						<timestamp>', $message['timestamp'], '</timestamp>
						<start>', $message['start'], '</start>
						
						<author>
							<id>', $message['member']['id'], '</id>
							<name>', $message['member']['name'], '</name>
							<href>', $message['member']['href'], '</href>
						</author>
					</message>';
			}
			echo '
				</messages>
			</result>';
		}
		
		echo '
		</results>';
	}

	echo '
</smf>';
}

function template_jump_to()
{
	global $context, $settings, $options;

	echo '<', '?xml version="1.0" encoding="', $context['character_set'], '"?', '>
<smf>';
	foreach ($context['jump_to'] as $category)
	{
		echo '
	<item type="category" id="', $category['id'], '"><![CDATA[', $category['name'], ']]></item>';
		foreach ($category['boards'] as $board)
			echo '
	<item type="board" id="', $board['id'], '" childlevel="', $board['child_level'], '"><![CDATA[', $board['name'], ']]></item>';
	}
	echo '
</smf>';
}

function template_message_icons()
{
	global $context, $settings, $options;

	echo '<', '?xml version="1.0" encoding="', $context['character_set'], '"?', '>
<smf>';
	foreach ($context['icons'] as $icon)
		echo '
	<icon value="', $icon['value'], '" url="', $icon['url'], '"><![CDATA[', $icon['name'], ']]></icon>';
	echo '
</smf>';
}

function template_check_username()
{
	global $context, $settings, $options, $txt;

	echo '<', '?xml version="1.0" encoding="', $context['character_set'], '"?', '>
<smf>
	<username valid="', $context['valid_username'] ? 1 : 0, '">', $context['checked_username'], '</username>
</smf>';
}

?>