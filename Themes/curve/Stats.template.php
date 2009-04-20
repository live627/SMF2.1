<?php
// Version: 2.0 RC1; Stats

function template_main()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	echo '
		<h4 class="titlebg" align="center"><span class="left"></span><span class="right"></span>
			<span>', $context['page_title'], '</span>
		</h4>

		<div class="stat_toppad">
			<h3 class="catbg"><span class="left"></span><span class="right"></span>
				<img src="', $settings['images_url'], '/stats_info.gif" class="icon" alt="" /> ', $txt['general_stats'], '
			</h3>
		</div>

		<div id="stats_left" class="windowbg">
			<dl id="stats_totals">
				<dt>', $txt['total_members'], ':</dt>
				<dd>', $context['show_member_list'] ? '<a href="' . $scripturl . '?action=mlist">' . $context['num_members'] . '</a>' : $context['num_members'], '</dd>
				<dt>', $txt['total_posts'], ':</dt>
				<dd>', $context['num_posts'], '</dd>
				<dt>', $txt['total_topics'], ':</dt>
				<dd>', $context['num_topics'], '</dd>
				<dt>', $txt['total_cats'], ':</dt>
				<dd>', $context['num_categories'], '</dd>
				<dt>', $txt['users_online'], ':</dt>
				<dd>', $context['users_online'], '</dd>
				<dt>', $txt['most_online'], ':</dt>
				<dd>', $context['most_members_online']['number'], ' - ', $context['most_members_online']['date'], '</dd>
				<dt>', $txt['users_online_today'], ':</dt>
				<dd>', $context['online_today'], '</dd>';

	if (!empty($modSettings['hitStats']))
		echo '
				<dt>', $txt['num_hits'], ':</dt>
				<dd>', $context['num_hits'], '</dd>';

	echo '
			</dl>
		</div>

		<div id="stats_right" class="windowbg2">
			<dl id="stats_averages">
				<dt>', $txt['average_members'], ':</dt>
				<dd>', $context['average_members'], '</dd>
				<dt>', $txt['average_posts'], ':</dt>
				<dd>', $context['average_posts'], '</dd>
				<dt>', $txt['average_topics'], ':</dt>
				<dd>', $context['average_topics'], '</dd>
				<dt>', $txt['total_boards'], ':</dt>
				<dd>', $context['num_boards'], '</dd>
				<dt>', $txt['latest_member'], ':</dt>
				<dd>', $context['common_stats']['latest_member']['link'], '</dd>
				<dt>', $txt['average_online'], ':</dt>
				<dd>', $context['average_online'], '</dd>
				<dt>', $txt['gender_ratio'], ':</dt>
				<dd>', $context['gender']['ratio'], '</dd>';

	if (!empty($modSettings['hitStats']))
		echo '
				<dt>', $txt['average_hits'], ':</dt>
				<dd>', $context['average_hits'], '</dd>';

	echo '
			</dl>
		</div>

		<div>
			<div class="stat_left_splitter">
				<h3 class="catbg"><span class="left"></span><span class="right"></span>
					<img src="', $settings['images_url'], '/stats_posters.gif" class="icon" alt="" /> ', $txt['top_posters'], '
				</h3>
			</div>
			<div class="stat_right_splitter">
				<h3 class="catbg"><span class="left"></span><span class="right"></span>
					<img src="', $settings['images_url'], '/stats_board.gif" class="icon" alt="" /> ', $txt['top_boards'], '
				</h3>
			</div>
		</div>

		<div class="stats_topten_left windowbg2">
			<ul class="stats_topten">';

	foreach ($context['top_posters'] as $poster)
		echo '
				<li class="left">', $poster['link'], '</li>
				<li class="middle">', $poster['num_posts'] > 0 ? '<img src="' . $settings['images_url'] . '/bar.gif" width="' . $poster['post_percent'] . '" height="15" alt="" />' : '&nbsp;', '</li>
				<li class="right">', $poster['num_posts'], '</li>';

	echo '
			</ul>
		</div>
		<div class="stats_topten_right windowbg">
			<ul class="stats_topten">';

	foreach ($context['top_boards'] as $board)
		echo '
				<li class="left">', $board['link'], '</li>
				<li class="middle">', $board['num_posts'] > 0 ? '<img src="' . $settings['images_url'] . '/bar.gif" width="' . $board['post_percent'] . '" height="15" alt="" />' : '&nbsp;', '</li>
				<li class="right">', $board['num_posts'], '</li>';

	echo '
			</ul>
		</div>

		<div class="stat_poppad">
			<div class="stat_left_splitter">
				<h3 class="catbg"><span class="left"></span><span class="right"></span>
					<img src="', $settings['images_url'], '/stats_replies.gif" class="icon" alt="" /> ', $txt['top_topics_replies'], '
				</h3>
			</div>
			<div class="stat_right_splitter">
				<h3 class="catbg"><span class="left"></span><span class="right"></span>
					<img src="', $settings['images_url'], '/stats_views.gif" class="icon" alt="" /> ', $txt['top_topics_views'], '
				</h3>
			</div>
		</div>

		<div class="stats_topten_left windowbg">
			<ul class="stats_topten">';

	foreach ($context['top_topics_replies'] as $topic)
		echo '
				<li class="left">', $topic['link'], '</li>
				<li class="middle">', $topic['num_replies'] > 0 ? '<img src="' . $settings['images_url'] . '/bar.gif" width="' . $topic['post_percent'] . '" height="15" alt="" />' : '&nbsp;', '</li>
				<li class="right">', $topic['num_replies'], '</li>';

	echo '
			</ul>
		</div>
		<div class="stats_topten_right windowbg2">
			<ul class="stats_topten">';

	foreach ($context['top_topics_views'] as $topic)
		echo '
				<li class="left">', $topic['link'], '</li>
				<li class="middle">', $topic['num_views'] > 0 ? '<img src="' . $settings['images_url'] . '/bar.gif" width="' . $topic['post_percent'] . '" height="15" alt="" />' : '&nbsp;', '</li>
				<li class="right">', $topic['num_views'], '</li>';

	echo '
			</ul>
		</div>

		<div class="stat_poppad">
			<div class="stat_left_splitter">
				<h3 class="catbg"><span class="left"></span><span class="right"></span>
					<img src="', $settings['images_url'], '/stats_replies.gif" class="icon" alt="" /> ', $txt['top_starters'], '
				</h3>
			</div>
			<div class="stat_right_splitter">
				<h3 class="catbg"><span class="left"></span><span class="right"></span>
					<img src="', $settings['images_url'], '/stats_views.gif" class="icon" alt="" /> ', $txt['most_time_online'], '
				</h3>
			</div>
		</div>

		<div class="stats_topten_left windowbg2">
			<ul class="stats_topten">';

	foreach ($context['top_starters'] as $poster)
		echo '
				<li class="left">', $poster['link'], '</li>
				<li class="middle">', $poster['num_topics'] > 0 ? '<img src="' . $settings['images_url'] . '/bar.gif" width="' . $poster['post_percent'] . '" height="15" alt="" />' : '&nbsp;', '</li>
				<li class="right">', $poster['num_topics'], '</li>';

	echo '
			</ul>
		</div>
		<div class="stats_topten_right windowbg">
			<ul class="stats_topten">';

	foreach ($context['top_time_online'] as $poster)
		echo '
				<li class="left">', $poster['link'], '</li>
				<li class="middle">', $poster['time_online'] > 0 ? '<img src="' . $settings['images_url'] . '/bar.gif" width="' . $poster['time_percent'] . '" height="15" alt="" />' : '&nbsp;', '</li>
				<li class="right">', $poster['time_online'], '</li>';

	echo '
			</ul>
		</div>

		<div class="stat_toppad">
			<h3 class="catbg"><span class="left"></span><span class="right"></span>
				<img src="', $settings['images_url'], '/stats_history.gif" class="icon" alt="" /> ', $txt['forum_history'], '
			</h3>
		</div>';

	if (!empty($context['yearly']))
	{
		echo '
		<table border="0" width="100%" cellspacing="1" cellpadding="4" class="tborder" style="margin-bottom: 1ex;" id="stats">
			<tr class="titlebg" valign="middle" align="center">
				<td width="25%">', $txt['yearly_summary'], '</td>
				<td width="15%">', $txt['stats_new_topics'], '</td>
				<td width="15%">', $txt['stats_new_posts'], '</td>
				<td width="15%">', $txt['stats_new_members'], '</td>
				<td width="15%">', $txt['smf_stats_14'], '</td>';

		if (!empty($modSettings['hitStats']))
			echo '
				<td>', $txt['page_views'], '</td>';

		echo '
			</tr>';

		foreach ($context['yearly'] as $id => $year)
		{
			echo '
			<tr class="windowbg2" valign="middle" id="year_', $id, '">
				<th align="left" width="25%">
					<a href="#" onclick="yearElements[', $id, '].toggle(); return false;"><img id="year_img_', $id, '" src="', $settings['images_url'], '/collapse.gif" alt="*" /> ', $year['year'], '</a>
				</th>
				<th align="center" width="15%">', $year['new_topics'], '</th>
				<th align="center" width="15%">', $year['new_posts'], '</th>
				<th align="center" width="15%">', $year['new_members'], '</th>
				<th align="center" width="15%">', $year['most_members_online'], '</th>';

			if (!empty($modSettings['hitStats']))
				echo '
				<th align="center">', $year['hits'], '</th>';

			echo '
			</tr>';

			foreach ($year['months'] as $month)
			{
				echo '
			<tr class="windowbg2" valign="middle" id="tr_month_', $month['id'], '">
				<th align="left" width="25%" style="padding-left: 3ex;">
					<a name="m', $month['id'], '" id="m', $month['id'], '" href="', $month['href'], '" onclick="return doingExpandCollapse || yearElements[', $id, '].toggleMonth(', $month['id'], ');"><img src="', $settings['images_url'], '/', $month['expanded'] ? 'collapse.gif' : 'expand.gif', '" alt="" id="img_', $month['id'], '" /> ', $month['month'], ' ', $month['year'], '</a>
				</th>
				<th align="center" width="15%">', $month['new_topics'], '</th>
				<th align="center" width="15%">', $month['new_posts'], '</th>
				<th align="center" width="15%">', $month['new_members'], '</th>
				<th align="center" width="15%">', $month['most_members_online'], '</th>';

				if (!empty($modSettings['hitStats']))
					echo '
				<th align="center">', $month['hits'], '</th>';

				echo '
			</tr>';

				if ($month['expanded'])
				{
					foreach ($month['days'] as $day)
					{
						echo '
			<tr class="windowbg2" valign="middle" align="left" id="tr_day_', $day['year'], '-', $day['month'], '-', $day['day'], '">
				<td align="left" style="padding-left: 6ex;">', $day['year'], '-', $day['month'], '-', $day['day'], '</td>
				<td align="center">', $day['new_topics'], '</td>
				<td align="center">', $day['new_posts'], '</td>
				<td align="center">', $day['new_members'], '</td>
				<td align="center">', $day['most_members_online'], '</td>';

						if (!empty($modSettings['hitStats']))
							echo '
				<td align="center">', $day['hits'], '</td>';

						echo '
			</tr>';
					}
				}
			}
		}

		echo '
		</table>';
	}

	echo '
		<script type="text/javascript" src="', $settings['default_theme_url'], '/scripts/stats.js"></script>
		<script type="text/javascript"><!-- // --><![CDATA[';

	if (!empty($context['yearly']))
	{
		echo '
			var yearElements = new Array();';
		
		foreach ($context['yearly'] as $id => $year)
		{
			echo '
			yearElements[', $id, '] = new smfStats_year("', $id, '", false);';
		
			foreach ($year['months'] as $month)
			{
				echo '
			yearElements[', $id, '].addMonth("', $month['id'], '", ', $month['expanded'] ? 'false' : 'true', ');';
		
			if ($month['expanded'])
			{
				foreach ($month['days'] as $day)
					echo '
			yearElements[', $id, '].addDay(', $month['id'], ', "', $day['year'], '-', $day['month'], '-', $day['day'], '");';
			}
		
			if (!$year['expanded'] && !$year['current_year'])
				echo '
			yearElements[', $id, '].toggle()';
			}
		}
		
		echo '
		// ]]></script>';
	}
}

?>