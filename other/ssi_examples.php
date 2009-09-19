<?php

require(dirname(__FILE__) . '/SSI.php');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>SMF 2.0 RC2 Installation Guide</title>
		<link rel="stylesheet" type="text/css" href="Themes/default/css/index.css?rc2" />
		<style type="text/css">
			#upper_section .user
			{
				height: 4em;
			}
			#upper_section .news
			{
				height: 80px;
			}
			#main_screen
			{
				padding: 0 40px;
			}
			#main_screen h2
			{
				font-size: 2em;
				border-bottom: solid 1px #d05800;
				line-height: 2em;
				margin: 0 0 0.5em 0;
				color: #d05800;
			}
			#content_section
			{
				position: relative;
				top: -20px;
			}
			#main_content_section
			{
			}
			#main_content_section .panel
			{
				padding: 1em 2em 1em 1em;
				line-height: 1.6em;
			}
			#main_content_section h3
			{
				font-size: 1.1em;
				line-height: 1.3em;
				color: #902800;
			}
			#main_content_section li
			{
				line-height: 1.6em;
				font-weight: bold;
			}
			#main_content_section li li
			{
				font-weight: normal;
				line-height: 1.6em;
			}
			#liftup
			{
				position: relative;
				top: -70px;
			}
			#footer_section
			{
				position: relative;
				top: -20px;
			}
			#footer_section
			{
				position: relative;
				top: -20px;
			}
			tt
			{
				font-family: verdana, sans-serif;
				letter-spacing: 1px;
				font-weight: bold;
				font-size: 90%;
				font-style: italic;
			}
			hr
			{
				margin: 1em 0;
				color: #ccc;
				background: #ccc;
			}
		</style>
	</head>
	<body>
	<div id="header"><div class="frame">
		<div id="top_section">
			<h1 class="forumtitle">SSI.php functions (SMF 2.0 RC2)</h1>
			<img id="smflogo" src="Themes/default/images/smflogo.png" alt="Simple Machines Forum" title="Simple Machines Forum" />
		</div>
		<div id="upper_section" class="middletext" style="overflow: hidden;">
			<div class="user"></div>
			<div class="news normaltext">
			</div>
		</div>
	</div></div>
	<div id="content_section"><div class="frame">
		<div id="main_content_section">
			<div id="liftup">
			This file is used to demonstrate the capabilities of SSI.php using PHP include functions.<br />
			The examples show the include tag, then the results of it. Examples are separated by horizontal rules.<br />

		<hr />

			<br />
			To use SSI.php in your page add at the very top of your page before the &lt;html&gt; tag on line 1:<br />
			<tt>
				&lt;?php require(&quot;<?php echo ($user_info['is_admin'] ? realpath($boarddir . '/SSI.php') : 'SSI.php'); ?>&quot;); ?&gt;
			</tt>
			<br />

		<hr />

			<h3>Recent Topics Function: &lt;?php ssi_recentTopics(); ?&gt;</h3>
			<?php ssi_recentTopics(); flush(); ?>

		<hr />

			<h3>Recent Posts Function: &lt;?php ssi_recentPosts(); ?&gt;</h3>
			<?php ssi_recentPosts(); flush(); ?>

		<hr />

			<h3>Recent Poll Function: &lt;?php ssi_recentPoll(); ?&gt;</h3>
			<?php ssi_recentPoll(); flush(); ?>

		<hr />

			<h3>Top Boards Function: &lt;?php ssi_topBoards(); ?&gt;</h3>
			<?php ssi_topBoards(); flush(); ?>

		<hr />

			<h3>Top Topics by View Function: &lt;?php ssi_topTopicsViews(); ?&gt;</h3>
			<?php ssi_topTopicsViews(); flush(); ?>

		<hr />

			<h3>Top Topics by Replies Function: &lt;?php ssi_topTopicsReplies(); ?&gt;</h3>
			<?php ssi_topTopicsReplies(); flush(); ?>

		<hr />

			<h3>Top Poll Function: &lt;?php ssi_topPoll(); ?&gt;</h3>
			<?php ssi_topPoll(); flush(); ?>

		<hr />

			<h3>Top Poster Function: &lt;?php ssi_topPoster(); ?&gt;</h3>
			<?php ssi_topPoster(); flush(); ?>

		<hr />

			<h3>Topic's Poll Function: &lt;?php ssi_showPoll($topic); ?&gt;</h3>
			<?php ssi_showPoll(); flush(); ?>

		<hr />

			<h3>Latest Member Function: &lt;?php ssi_latestMember(); ?&gt;</h3>
			<?php ssi_latestMember(); flush(); ?>

		<hr />

			<h3>Member of the Day: &lt;?php ssi_randomMember('day'); ?&gt;</h3>
			<?php ssi_randomMember('day'); flush(); ?>

		<hr />

			<h3>Board Stats: &lt;?php ssi_boardStats(); ?&gt;</h3>
			<?php ssi_boardStats(); flush(); ?>

		<hr />

			<h3>Who's Online Function: &lt;?php ssi_whosOnline(); ?&gt;</h3>
			<?php ssi_whosOnline(); flush(); ?>

		<hr />

			<h3>Log Online Presence + Who's Online Function: &lt;?php ssi_logOnline(); ?&gt;</h3>
			<?php ssi_logOnline(); flush(); ?>

		<hr />

			<h3>Welcome Function: &lt;?php ssi_welcome(); ?&gt;</h3>
			<?php ssi_welcome(); flush(); ?>

		<hr />

			<h3>News Function: &lt;?php ssi_news(); ?&gt;</h3>
			<?php ssi_news(); flush(); ?>

		<hr />

			<h3>Board News Function: &lt;?php ssi_boardNews(); ?&gt;</h3>
			<?php ssi_boardNews(); flush(); ?>

		<hr />

			<h3>Menubar Function: &lt;?php ssi_menubar(); ?&gt;</h3>
			<?php ssi_menubar(); flush(); ?>

		<hr />

			<h3>Quick Search Function: &lt;?php ssi_quickSearch(); ?&gt;</h3>
			<?php ssi_quickSearch(); flush(); ?>

		<hr />

			<h3>Login Function: &lt;?php ssi_login(); ?&gt;</h3>
			<?php ssi_login(); flush(); ?>

		<hr />

			<h3>Log Out Function: &lt;?php ssi_logout(); ?&gt;</h3>
			<?php ssi_logout(); flush(); ?>

		<hr />

			<h3>Today's Birthdays Function: &lt;?php ssi_todaysBirthdays(); ?&gt;</h3>
			<?php ssi_todaysBirthdays(); flush(); ?>

		<hr />

			<h3>Today's Holidays Function: &lt;?php ssi_todaysHolidays(); ?&gt;</h3>
			<?php ssi_todaysHolidays(); flush(); ?>

		<hr />

			<h3>Today's Events Function: &lt;?php ssi_todaysEvents(); ?&gt;</h3>
			<?php ssi_todaysEvents(); flush(); ?>

		<hr />

			<h3>Today's Calendar Function: &lt;?php ssi_todaysCalendar(); ?&gt;</h3>
			<?php ssi_todaysCalendar(); flush(); ?>

		<hr />

			<h3>Recent Calendar Events Function: &lt;?php ssi_recentEvents(); ?&gt;</h3>
			<?php ssi_recentEvents(); flush(); ?>

		<hr />

			<h3>Recent Attachments Function &lt;?php ssi_recentAttachments(); ?&gt;</h3>
			<?php ssi_recentAttachments(); flush(); ?>

		<hr />

			<h3>Some notes on usage</h3>
			All the functions have an output method parameter.  This can either be &quot;echo&quot; (the default) or &quot;array&quot;.<br />
			If it is &quot;echo&quot;, the function will act normally - otherwise, it will return an array containing information about the requested task.<br />
			For example, it might return a list of topics for ssi_recentTopics.<br />
			<br />
			<span onclick="if (getInnerHTML(this).indexOf('Bird') == -1) setInnerHTML(this, getInnerHTML(this) + '<br /><img src=&quot;http://www.simplemachines.org/images/chocobo.jpg&quot; title=&quot;Bird-san&quot; alt=&quot;Chocobo!&quot; />'); return false;">This functionality can be used to allow you to present the information in any way you wish.</span>

		<hr />

		<br />
		<br />
		<span style="color: #CCCCCC; font-size: smaller;">
			<?php
				echo 'This page took ', round(array_sum(explode(' ', microtime())) - array_sum(explode(' ', $time_start)), 4), ' seconds to load.<br />';
			?>
			*ssi_examples.php last modified on <?php echo date('m/j/y', filemtime(__FILE__)); ?>
		</span>
			</div>
			</div>
		</div>
	</div></div>
	<div id="footer_section"><div class="frame" style="height: 50px;">
		<div class="smalltext"><a href="http://www.simplemachines.org/" title="Free Forum Software" target="_blank" class="new_win">SMF &copy; 2006&ndash;2009, Simple Machines LLC</a></div>
	</div></div>
	</body>
</html>