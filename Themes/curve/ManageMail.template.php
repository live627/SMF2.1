<?php
// Version: 2.0 RC1; ManageMail

function template_browse()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
	<div id="manage_mail">
		<h3 class="catbg"><span class="left"></span><span class="right"></span>
			', $txt['mailqueue_stats'], '
		</h3>
		<div class="windowbg">
			<span class="topslice"><span></span></span>
				<div class="content">
					<dl>
						<dt>', $txt['mailqueue_size'], '</dt>
						<dd>', $context['mail_queue_size'], '</dd>
						<dt>', $txt['mailqueue_oldest'], '</dt>
						<dd>', $context['oldest_mail'], '</dd>
					</dl>
				</div>
			<span class="botslice"><span></span></span>
		</div>
	</div>';

	template_show_list('mail_queue');
}

?>