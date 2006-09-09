<?php
// Version: 2.0 Alpha; ModerationCenter

// Important! Before editing these language files please read the text at the topic of index.spanish.php.

//!!! Untranslated - Whole file needs translation
$txt['moderation_center'] = 'Moderation Center';
$txt['mc_main'] = 'Main';
$txt['mc_posts'] = 'Posts';
$txt['mc_groups'] = 'Groups';

$txt['mc_reported_posts'] = 'Reported Posts';
$txt['mc_view_groups'] = 'View Groups';

$txt['mc_description'] = 'This is your &quot;Moderation Center&quot;. From here you can perform all the moderation actions assigned to yourself by the Administrator. This home page contains a summary of all the latest happenings in your community. You can personalize the layout by clicking <a href="{$scripturl}?action=moderate;sa=personalize">here</a>.';
$txt['mc_group_requests'] = 'Group Requests';
$txt['mc_unapproved_posts'] = 'Unapproved Posts';
$txt['mc_unapproved_attachments'] = 'Unapproved Attachments';
$txt['mc_watched_users'] = 'Watched Users';
$txt['mc_watched_topics'] = 'Watched Topics';
$txt['mc_scratch_board'] = 'Moderator Scratch Board';
$txt['mc_latest_news'] = 'Simple Machines Latest News';
$txt['mc_recent_reports'] = 'Recent Topic Reports';

$txt['mc_cannot_connect_sm'] = 'You are unable to connect to simplemachines.org\'s latest news file.';

$txt['mc_recent_reports_none'] = 'There are no outstanding reports';
$txt['mc_watched_users_none'] = 'You do not currently have any watches in place.';
$txt['mc_group_requests_none'] = 'There are no open requests for group membership.';

$txt['mc_groupr_by'] = 'by';

$txt['mc_reported_posts_desc'] = 'Here you can review all the post reports raised by members of the community.';
$txt['mc_reportedp_open'] = 'Active Reports';
$txt['mc_reportedp_closed'] = 'Old Reports';
$txt['mc_reportedp_by'] = 'by';
$txt['mc_reportedp_reported_by'] = 'Reported By';
$txt['mc_reportedp_last_reported'] = 'Last Reported';
$txt['mc_reportedp_none_found'] = 'No Reports Found';

$txt['mc_reportedp_close'] = 'Close';
$txt['mc_reportedp_open'] = 'Open';
$txt['mc_reportedp_ignore'] = 'Ignore';
$txt['mc_reportedp_unignore'] = 'Un-Ignore';
// Do not use numeric entries in the below string.
$txt['mc_reportedp_ignore_confirm'] = 'Are you sure you wish to ignore further reports about this message?\\n\\nThis will turn off further reports for all moderators of the forum.';
$txt['mc_reportedp_close_selected'] = 'Close Selected';

$txt['mc_groupr_group'] = 'Group';
$txt['mc_groupr_member'] = 'Member';
$txt['mc_groupr_reason'] = 'Reason';
$txt['mc_groupr_none_found'] = 'There are currently no outstanding group requests';
$txt['mc_groupr_submit'] = 'Submit';
$txt['mc_groupr_reason_desc'] = 'Reason to reject %s\'s request to join &quot;%s&quot;';
$txt['mc_groups_reason_title'] = 'Reasons for Rejection';
$txt['with_selected'] = 'With Selected';
$txt['mc_groupr_approve'] = 'Approve Request';
$txt['mc_groupr_reject'] = 'Reject Request (No Reason)';
$txt['mc_groupr_reject_w_reason'] = 'Reject Request with Reason';
// Do not use numeric entries in the below string.
$txt['mc_groupr_warning'] = 'Are you sure you wish to do this?';

$txt['mc_group_email_sub_approve'] = 'Group Membership Approval';
$txt['mc_group_email_sub_reject'] = 'Group Membership Rejection';
// The below emails are sent for group request outcomes. %1$s is membername, %2$s is group name, %3$s is the reason for rejection in the case of rejection with a manual reason.
$txt['mc_group_email_request_reject'] = '%1$s,\\n\\nWe\'re sorry to notify you that your application to join the &quot;%2$s&quot; group at ' . $context['forum_name'] . " has been rejected.\n\n" . $txt[130];
$txt['mc_group_email_request_reject_reason'] = '%1$s,\\n\\nWe\'re sorry to notify you that your application to join the &quot;%2$s&quot; group at ' . $context['forum_name'] . " has been rejected.\n\nThis is due to the following reason:\n\n" . '%3$s\\n\\n' . $txt[130];
$txt['mc_group_email_request_approve'] = '%1$s,\\n\\nWe\'re pleased to notify you that your application to join the &quot;%2$s&quot; group at ' . $context['forum_name'] . " has been accepted, and your account has been updated to include this new membergroup.\n\n" . $txt[130];

$txt['mc_unapproved_attachments_none_found'] = 'No unapproved attachments found!';
$txt['mc_unapproved_replies_none_found'] = 'No unapproved posts found!';
$txt['mc_unapproved_topics_none_found'] = 'No unapproved topics found!';
$txt['mc_unapproved_posts_desc'] = 'From here you can approve or delete any posts awaiting moderation.';
$txt['mc_unapproved_replies'] = 'Replies';
$txt['mc_unapproved_topics'] = 'Topics';
$txt['mc_unapproved_by'] = 'by';
$txt['mc_unapproved_sure'] = 'Are you sure you want to do this?';
$txt['mc_unapproved_attach_name'] = 'Attachment Name';
$txt['mc_unapproved_attach_size'] = 'Filesize';
$txt['mc_unapproved_attach_poster'] = 'Poster';
$txt['mc_viewmodreport'] = 'Moderation Report for %1$s by %2$s';
$txt['mc_modreport_summary'] = 'There have been %1$d report(s) concerning this post.  The last report was %2$s.';
$txt['mc_modreport_whoreported_title'] = 'Members who have reported this posts';
$txt['mc_modreport_whoreported_data'] = 'Reported by %1$s on %2$s.  They left the following message:';
$txt['mc_modreport_modactions'] = 'Actions taken by other moderators';

$txt['modlog_view'] = 'Moderation Log';
$txt['modlog_date'] = 'Date';
$txt['modlog_member'] = 'Member';
$txt['modlog_position'] = 'Position';
$txt['modlog_action'] = 'Action';
$txt['modlog_ip'] = 'IP';
$txt['modlog_search_result'] = 'Search Results';
$txt['modlog_total_entries'] = 'Total Entries';
$txt['modlog_ac_approved_topic'] = 'Approved Topic';
$txt['modlog_ac_approved'] = 'Approved';
$txt['modlog_ac_banned'] = 'Banned';
$txt['modlog_ac_locked'] = 'Locked';
$txt['modlog_ac_stickied'] = 'Stickied';
$txt['modlog_ac_deleted'] = 'Deleted';
$txt['modlog_ac_deleted_member'] = 'Deleted Member';
$txt['modlog_ac_removed'] = 'Removed';
$txt['modlog_ac_modified'] = 'Modified';
$txt['modlog_ac_merged'] = 'Merged';
$txt['modlog_ac_split'] = 'Split';
$txt['modlog_ac_moved'] = 'Moved';
$txt['modlog_ac_profile'] = 'Profile Edit';
$txt['modlog_ac_pruned'] = 'Pruned Board';
$txt['modlog_ac_news'] = 'Edited News';
$txt['modlog_enter_comment'] = 'Enter Moderation Comment';
$txt['modlog_moderation_log'] = 'Moderation Log';
$txt['modlog_moderation_log_desc'] = 'Below is a list of all the moderation actions that have been carried out by moderators of the forum.<br /><b>Please note:</b> Entries cannot be removed from this log until they are at least twenty-four hours old.';
$txt['modlog_no_entries_found'] = 'No entries found';
$txt['modlog_remove'] = 'Remove';
$txt['modlog_removeall'] = 'Remove All';
$txt['modlog_go'] = 'Go';
$txt['modlog_add'] = 'Add';
$txt['modlog_search'] = 'Quick Search';
$txt['modlog_by'] = 'By';

$txt['modlog_view'] = 'Log de moderaci&oacute;n';
$txt['modlog_date'] = 'Fecha';
$txt['modlog_member'] = 'Usuario';
$txt['modlog_position'] = 'Grupo';
$txt['modlog_action'] = 'Acci&oacute;n';
$txt['modlog_ip'] = 'IP';
$txt['modlog_search_result'] = 'Resultados de la b&uacute;squeda';
$txt['modlog_total_entries'] = 'Total de entradas';
// Untranslated!
$txt['modlog_ac_approved_topic'] = 'Approved Topic';
$txt['modlog_ac_approved'] = 'Approved';
$txt['modlog_ac_banned'] = 'Acceso restringido';
$txt['modlog_ac_locked'] = 'Bloqueado';
$txt['modlog_ac_stickied'] = 'Fijado';
$txt['modlog_ac_deleted'] = 'Borrado';
$txt['modlog_ac_deleted_member'] = 'Usuario Borrado';
$txt['modlog_ac_removed'] = 'Eliminado';
$txt['modlog_ac_modified'] = 'Modificado';
$txt['modlog_ac_merged'] = 'Combinado';
$txt['modlog_ac_split'] = 'Dividido';
$txt['modlog_ac_moved'] = 'Movido';
$txt['modlog_ac_profile'] = 'Editar Perfil';
$txt['modlog_ac_pruned'] = 'Foro Podado';
$txt['modlog_ac_news'] = 'Noticias Editadas';
$txt['modlog_enter_comment'] = 'Introduce el comentario de moderaci&oacute;n';
$txt['modlog_moderation_log'] = 'Log de moderaci&oacute;n';
$txt['modlog_moderation_log_desc'] = 'Debajo est&aacute; la lista de todas las acciones de moderaci&oacute;n que han sido realizadas por moderadores del foro.<br /><b>Please note:</b> Entries cannot be removed from this log until they are at least 24 hours old.';
$txt['modlog_no_entries_found'] = 'No se encontraron entradas';
$txt['modlog_remove'] = 'Eliminar';
$txt['modlog_removeall'] = 'Eliminar todos';
$txt['modlog_go'] = 'Ir';
$txt['modlog_add'] = 'Agregar';
$txt['modlog_search'] = 'B&uacute;squeda r&aacute;pida';
$txt['modlog_by'] = 'Por';

?>