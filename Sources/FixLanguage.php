<?php

/* This file is used during the development of SMF 2.0 to keep track of text key changes. It will be deleted
   before distribution and it's only purpose is to ensure people using a non-default language are not left
   with errors. Eventually these key changes will form part of the translator for 2.0.

   Note this file is included from loadLanguage, and will take some processing power I'm afraid. */

// old_key => new_key
$txtChanges = array(
	'Admin' => array(
		'attachment_mode' => 'attachmentEnable',
		'attachment_mode_deactivate' => 'attachmentEnable_deactivate',
		'attachment_mode_enable_all' => 'attachmentEnable_enable_all',
		'attachment_mode_disable_new' => 'attachmentEnable_disable_new',
	),
	'Help' => array(
		'attachmentEnable' => 'attachment_manager_settings',
		'avatar_allow_server_stored' => 'avatar_server_stored',
		'avatar_allow_external_url' => 'avatar_external',
		'avatar_allow_upload' => 'avatar_upload',
	),
	'ManageSmileys' => array(
		'smiley_sets_enable' => 'setting_smiley_sets_enable',
		'smiley_sets_base_url' => 'setting_smileys_url',
		'smiley_sets_base_dir' => 'setting_smileys_dir',
		'smileys_enable' => 'setting_smiley_enable',
		'icons_enable_customized' => 'setting_messageIcons_enable',
		'icons_enable_customized_note' => 'setting_messageIcons_enable_note',
	),
);

function applyTxtFixes()
{
	global $txtChanges, $txt, $helptxt;

	foreach ($txtChanges as $key => $file)
		foreach ($file as $old => $new)
		{
			if ($key == 'Help' && isset($helptxt[$old]))
				$helptxt[$new] = $helptxt[$old];
			elseif (isset($txt[$old]))
				$txt[$new] = $txt[$old];
		}
}

?>