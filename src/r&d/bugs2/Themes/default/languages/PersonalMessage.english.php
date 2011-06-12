<?php
// Version: 1.1; PersonalMessage

$txt[143] = 'Personal Messages Index';
$txt[148] = 'Send message';
$txt[150] = 'To';
$txt[1502] = 'Bcc';
$txt[316] = 'Inbox';
$txt[320] = 'Outbox';
$txt[321] = 'New Message';
$txt[411] = 'Delete Messages';
// Don't translate "PMBOX" in this string.
$txt[412] = 'Delete all messages in your PMBOX';
$txt[413] = 'Are you sure you want to delete all messages?';
$txt[535] = 'Recipient';
// Don't translate the word "SUBJECT" here, as it is used to format the message - use numeric entities as well.
$txt[561] = 'New Personal Message: SUBJECT';
// Don't translate SENDER or MESSAGE in this language string; they are replaced with the corresponding text - use numeric entities too.
$txt[562] = 'You have just been sent a personal message by SENDER on ' . $context['forum_name'] . '.' . "\n\n" . 'IMPORTANT: Remember, this is just a notification. Please do not reply to this email.' . "\n\n" . 'The message they sent you was:' . "\n\n" . 'MESSAGE';
$txt[748] = '(multiple recipients: \'name1, name2\')';
// Use numeric entities in the below string.
$txt['instant_reply'] = 'Reply to this Personal Message here:';

$txt['smf249'] = 'Are you sure you want to delete all selected personal messages?';

$txt['sent_to'] = 'Sent to';
$txt['reply_to_all'] = 'Reply to All';

$txt['pm_capacity'] = 'Capacity';
$txt['pm_currently_using'] = '%s messages, %s%% full.';

$txt['pm_error_user_not_found'] = 'Unable to find member \'%s\'.';
$txt['pm_error_ignored_by_user'] = 'User \'%s\' has blocked your personal message.';
$txt['pm_error_data_limit_reached'] = 'PM could not be sent to \'%s\' as their inbox is full!';
$txt['pm_successfully_sent'] = 'PM successfully sent to \'%s\'.';
$txt['pm_too_many_recipients'] = 'You may not send personal messages to more than %d recipient(s) at once.';
$txt['pm_too_many_per_hour'] = 'You have exceeded the limit of %d personal messages per hour.';
$txt['pm_send_report'] = 'Send report';
$txt['pm_save_outbox'] = 'Save a copy in my outbox';
$txt['pm_undisclosed_recipients'] = 'Undisclosed recipients';

$txt['pm_read'] = 'Read';
$txt['pm_replied'] = 'Replied To';

// Message Pruning.
$txt['pm_prune'] = 'Prune Messages';
$txt['pm_prune_desc1'] = 'Delete all personal messages older than';
$txt['pm_prune_desc2'] = 'days.';
$txt['pm_prune_warning'] = 'Are you sure you wish to prune your personal messages?';

// Actions Drop Down.
$txt['pm_actions_title'] = 'Further Actions';
$txt['pm_actions_delete_selected'] = 'Delete Selected';
$txt['pm_actions_filter_by_label'] = 'Filter By Label';
$txt['pm_actions_go'] = 'Go';

// Manage Labels Screen.
$txt['pm_apply'] = 'Apply';
$txt['pm_manage_labels'] = 'Manage Labels';
$txt['pm_labels_delete'] = 'Are you sure you wish to delete the selected labels?';
$txt['pm_labels_desc'] = 'From here you can add, edit and delete the labels used in your personal message center.';
$txt['pm_label_add_new'] = 'Add New Label';
$txt['pm_label_name'] = 'Label Name';
$txt['pm_labels_no_exist'] = 'You currently have no labels setup!';

// Labeling Drop Down.
$txt['pm_current_label'] = 'Label';
$txt['pm_msg_label_title'] = 'Label Message';
$txt['pm_msg_label_apply'] = 'Add Label';
$txt['pm_msg_label_remove'] = 'Remove Label';
$txt['pm_msg_label_inbox'] = 'Inbox';
$txt['pm_sel_label_title'] = 'Label Selected';
$txt['labels_too_many'] = 'Sorry, %s messages already had the maximum amount of labels allowed!';

// Sidebar Headings.
$txt['pm_labels'] = 'Labels';
$txt['pm_messages'] = 'Messages';
$txt['pm_preferences'] = 'Preferences';

$txt['pm_is_replied_to'] = 'You have forwarded or responded to this message.';

// Reporting messages.
$txt['pm_report_to_admin'] = 'Report To Admin';
$txt['pm_report_title'] = 'Report Personal Message';
$txt['pm_report_desc'] = 'From this page you can report the personal message you received to the admin team of the forum. Please be sure to include a description of why you are reporting the message, as this will be sent along with the contents of the original message.';
$txt['pm_report_admins'] = 'Administrator to send report to';
$txt['pm_report_all_admins'] = 'Send to all forum administrators';
$txt['pm_report_reason'] = 'Reason why you are reporting this message';
$txt['pm_report_message'] = 'Report Message';

// Important - The following strings should use numeric entities.
$txt['pm_report_pm_subject'] = '[REPORT] ';
// In the below string, do not translate "{REPORTER}" or "{SENDER}".
$txt['pm_report_pm_user_sent'] = '{REPORTER} has reported the below personal message, sent by {SENDER}, for the following reason:';
$txt['pm_report_pm_other_recipients'] = 'Other recipients of the message include:';
$txt['pm_report_pm_hidden'] = '%d hidden recipient(s)';
$txt['pm_report_pm_unedited_below'] = 'Below are the original contents of the personal message which was reported:';
$txt['pm_report_pm_sent'] = 'Sent:';

$txt['pm_report_done'] = 'Thank you for submitting this report. You should hear back from the admin team shortly';
$txt['pm_report_return'] = 'Return to the inbox';

$txt['pm_search_title'] = 'Search Personal Messages';
$txt['pm_search_bar_title'] = 'Search Messages';
$txt['pm_search_text'] = 'Search for';
$txt['pm_search_go'] = 'Search';
$txt['pm_search_advanced'] = 'Advanced search';
$txt['pm_search_user'] = 'by user';
$txt['pm_search_match_all'] = 'Match all words';
$txt['pm_search_match_any'] = 'Match any words';
$txt['pm_search_options'] = 'Options';
$txt['pm_search_post_age'] = 'Age';
$txt['pm_search_show_complete'] = 'Show full message in results.';
$txt['pm_search_subject_only'] = 'Search by subject and author only.';
$txt['pm_search_between'] = 'Between';
$txt['pm_search_between_and'] = 'and';
$txt['pm_search_between_days'] = 'days';
$txt['pm_search_order'] = 'Order results by';
$txt['pm_search_choose_label'] = 'Choose labels to search by, or search all';

$txt['pm_search_results'] = 'Search Results';
$txt['pm_search_none_found'] = 'No Messages Found';

$txt['pm_search_orderby_relevant_first'] = 'Most relevant first';
$txt['pm_search_orderby_recent_first'] = 'Most recent first';
$txt['pm_search_orderby_old_first'] = 'Oldest first';

$txt['pm_visual_verification_label'] = 'Verification';
$txt['pm_visual_verification_desc'] = 'Please enter the code in the image above to send this pm.';
$txt['pm_visual_verification_listen'] = 'Listen to the Letters';

?>