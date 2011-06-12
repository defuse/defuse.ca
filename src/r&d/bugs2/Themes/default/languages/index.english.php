<?php
// Version: 1.1.13; index

global $forum_copyright, $forum_version, $webmaster_email;

// Locale (strftime, pspell_new) and spelling. (pspell_new, can be left as '' normally.)
// For more information see:
//   - http://www.php.net/function.pspell-new
//   - http://www.php.net/function.setlocale
// Again, SPELLING SHOULD BE '' 99% OF THE TIME!!  Please read this!
$txt['lang_locale'] = 'en_US';
$txt['lang_dictionary'] = 'en';
$txt['lang_spelling'] = 'american';

// Character set and right to left?
$txt['lang_character_set'] = 'ISO-8859-1';
$txt['lang_rtl'] = false;

$txt['days'] = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
$txt['days_short'] = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');
// Months must start with 1 => 'January'. (or translated, of course.)
$txt['months'] = array(1 => 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
$txt['months_titles'] = array(1 => 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
$txt['months_short'] = array(1 => 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');

$txt['newmessages0'] = 'is new';
$txt['newmessages1'] = 'are new';
$txt['newmessages3'] = 'New';
$txt['newmessages4'] = ',';

$txt[2] = 'Admin';

$txt[10] = 'Save';

$txt[17] = 'Modify';
$txt[18] = $context['forum_name'] . ' - Index';
$txt[19] = 'Members';
$txt[20] = 'Board name';
$txt[21] = 'Posts';
$txt[22] = 'Last post';

$txt[24] = '(No subject)';
$txt[26] = 'Posts';
$txt[27] = 'View Profile';
$txt[28] = 'Guest';
$txt[29] = 'Author';
$txt[30] = 'on';
$txt[31] = 'Remove';
$txt[33] = 'Start new topic';

$txt[34] = 'Login';
// Use numeric entities in the below string.
$txt[35] = 'Username';
$txt[36] = 'Password';

$txt[40] = 'That username does not exist.';

$txt[62] = 'Board Moderator';
$txt[63] = 'Remove Topic';
$txt[64] = 'Topics';
$txt[66] = 'Modify message';
$txt[68] = 'Name';
$txt[69] = 'Email';
$txt[70] = 'Subject';
$txt[72] = 'Message';

$txt[79] = 'Profile';

$txt[81] = 'Choose password';
$txt[82] = 'Verify password';
$txt[87] = 'Position';

$txt[92] = 'View the profile of';
$txt[94] = 'Total';
$txt[95] = 'Posts';
$txt[96] = 'Website';
$txt[97] = 'Register';

$txt[101] = 'Message Index';
$txt[102] = 'News';
$txt[103] = 'Home';

$txt[104] = 'Lock/Unlock Topic';
$txt[105] = 'Post';
$txt[106] = 'An Error Has Occurred!';
$txt[107] = 'at';
$txt[108] = 'Logout';
$txt[109] = 'Started by';
$txt[110] = 'Replies';
$txt[111] = 'Last post';
$txt[114] = 'Administration Login';
// Use numeric entities in the below string.
$txt[118] = 'Topic';
$txt[119] = 'Help';
$txt[121] = 'Remove message';
$txt[125] = 'Notify';
$txt[126] = 'Do you want a notification email if someone replies to this topic?';
// Use numeric entities in the below string.
$txt[130] = "Regards,\nThe " . $context['forum_name'] . ' Team.';
$txt[131] = 'Notify of replies';
$txt[132] = 'Move Topic';
$txt[133] = 'Move to';
$txt[139] = 'Pages';
$txt[140] = 'Users active in past ' . $modSettings['lastActive'] . ' minutes';
$txt[144] = 'Personal Messages';
$txt[145] = 'Reply with quote';
$txt[146] = 'Reply';

$txt[151] = 'No messages...';
$txt[152] = 'you have';
$txt[153] = 'messages';
$txt[154] = 'Remove this message';

$txt[158] = 'Users Online';
$txt[159] = 'Personal Message';
$txt[160] = 'Jump to';
$txt[161] = 'go';
$txt[162] = 'Are you sure you want to remove this topic?';
$txt[163] = 'Yes';
$txt[164] = 'No';

$txt[166] = 'Search Results';
$txt[167] = 'End of results';
$txt[170] = 'Sorry, no matches were found';
$txt[176] = 'on';

$txt[182] = 'Search';
$txt[190] = 'All';

$txt[193] = 'Back';
$txt[194] = 'Password reminder';
$txt[195] = 'Topic started by';
$txt[196] = 'Title';
$txt[197] = 'Post by';
$txt[200] = 'Searchable list of all registered members.';
$txt[201] = 'Please welcome';
$txt[208] = 'Administration Center';
$txt[211] = 'Last Edit';
$txt[212] = 'Would you like to deactivate notification on this topic?';

$txt[214] = 'Recent Posts';

$txt[227] = 'Location';
$txt[231] = 'Gender';
$txt[233] = 'Date Registered';

$txt[234] = 'View the most recent posts on the forum.';
$txt[235] = 'is the most recently updated topic';

$txt[238] = 'Male';
$txt[239] = 'Female';

$txt[240] = 'Invalid character used in Username.';

$txt['welcome_guest'] = 'Welcome, <b>' . $txt[28] . '</b>. Please <a href="' . $scripturl . '?action=login">login</a> or <a href="' . $scripturl . '?action=register">register</a>.';
$txt['welcome_guest_activate'] = '<br />Did you miss your <a href="' . $scripturl . '?action=activate">activation email?</a>';
$txt['hello_member'] = 'Hey,';
// Use numeric entities in the below string.
$txt['hello_guest'] = 'Welcome,';
$txt[247] = 'Hey,';
$txt[248] = 'Welcome,';
$txt[249] = 'Please';
$txt[250] = 'Back';
$txt[251] = 'Please select a destination';

// Escape any single quotes in here twice.. 'it\'s' -> 'it\\\'s'.
$txt[279] = 'Posted by';

$txt[287] = 'Smiley';
$txt[288] = 'Angry';
$txt[289] = 'Cheesy';
$txt[290] = 'Laugh';
$txt[291] = 'Sad';
$txt[292] = 'Wink';
$txt[293] = 'Grin';
$txt[294] = 'Shocked';
$txt[295] = 'Cool';
$txt[296] = 'Huh';
$txt[450] = 'Roll Eyes';
$txt[451] = 'Tongue';
$txt[526] = 'Embarrassed';
$txt[527] = 'Lips sealed';
$txt[528] = 'Undecided';
$txt[529] = 'Kiss';
$txt[530] = 'Cry';

$txt[298] = 'Moderator';
$txt[299] = 'Moderators';

$txt[300] = 'Mark Topics as Read for this Board';
$txt[301] = 'Views';
$txt[302] = 'New';

$txt[303] = 'View all members';
$txt[305] = 'View';
$txt[307] = 'Email';

$txt[308] = 'Viewing Members';
$txt[309] = 'of';
$txt[310] = 'total members';
$txt[311] = 'to';
$txt[315] = 'Forgot your password?';

$txt[317] = 'Date';
// Use numeric entities in the below string.
$txt[318] = 'From';
$txt[319] = 'Subject';
$txt[322] = 'Check for new messages';
$txt[324] = 'To';

$txt[330] = 'Topics';
$txt[331] = 'Members';
$txt[332] = 'Members List';
$txt[333] = 'New Posts';
$txt[334] = 'No New Posts';

$txt['sendtopic_send'] = 'Send';

$txt[371] = 'Time Offset';
$txt[377] = 'or';

$txt[398] = 'Sorry, no matches were found';

$txt[418] = 'Notification';

$txt[430] = 'Sorry %s, you are banned from using this forum!';

$txt[452] = 'Mark ALL messages as read';

$txt[454] = 'Hot Topic (More than ' . $modSettings['hotTopicPosts'] . ' replies)';
$txt[455] = 'Very Hot Topic (More than ' . $modSettings['hotTopicVeryPosts'] . ' replies)';
$txt[456] = 'Locked Topic';
$txt[457] = 'Normal Topic';
$txt['participation_caption'] = 'Topic you have posted in';

$txt[462] = 'GO';

$txt[465] = 'Print';
$txt[467] = 'Profile';
$txt[468] = 'Topic Summary';
$txt[470] = 'N/A';
$txt[471] = 'message';
$txt[473] = 'This name is already in use by another member.';

$txt[488] = 'Total Members';
$txt[489] = 'Total Posts';
$txt[490] = 'Total Topics';

$txt[497] = 'Minutes to stay logged in';

$txt[507] = 'Preview';
$txt[508] = 'Always stay logged in';

$txt[511] = 'Logged';
// Use numeric entities in the below string.
$txt[512] = 'IP';

$txt[513] = 'ICQ';
$txt[515] = 'WWW';

$txt[525] = 'by';

$txt[578] = 'hours';
$txt[579] = 'days';

$txt[581] = ', our newest member.';

$txt[582] = 'Search for';

$txt[603] = 'AIM';
// In this string, please use +'s for spaces.
$txt['aim_default_message'] = 'Hi.+Are+you+there?';
$txt[604] = 'YIM';

$txt[616] = 'Remember, this forum is in \'Maintenance Mode\'.';

$txt[641] = 'Read';
$txt[642] = 'times';

$txt[645] = 'Forum Stats';
$txt[656] = 'Latest Member';
$txt[658] = 'Total Categories';
$txt[659] = 'Latest Post';

$txt[660] = 'You\'ve got';
$txt[661] = 'Click';
$txt[662] = 'here';
$txt[663] = 'to view them.';

$txt[665] = 'Total Boards';

$txt[668] = 'Print Page';

$txt[679] = 'This must be a valid email address.';

$txt[683] = 'I am a geek!!';
$txt[685] = $context['forum_name'] . ' - Info Center';

$txt[707] = 'Send this topic';

$txt['sendtopic_title'] = 'Send the topic &quot;%s&quot; to a friend.';
// Use numeric entities in the below three strings.
$txt['sendtopic_dear'] = 'Dear %s,';
$txt['sendtopic_this_topic'] = 'I want you to check out "%s" on ' . $context['forum_name'] . '.  To view it, please click this link';
$txt['sendtopic_thanks'] = 'Thanks';
$txt['sendtopic_sender_name'] = 'Your name';
$txt['sendtopic_sender_email'] = 'Your email address';
$txt['sendtopic_receiver_name'] = 'Recipient\'s name';
$txt['sendtopic_receiver_email'] = 'Recipient\'s email address';
$txt['sendtopic_comment'] = 'Add a comment';
// Use numeric entities in the below string.
$txt['sendtopic2'] = 'A comment has also been added regarding this topic';

$txt[721] = 'Hide email address from public?';

$txt[737] = 'Check all';

// Use numeric entities in the below string.
$txt[1001] = 'Database Error';
$txt[1002] = 'Please try again.  If you come back to this error screen, report the error to an administrator.';
$txt[1003] = 'File';
$txt[1004] = 'Line';
// Use numeric entities in the below string.
$txt[1005] = 'SMF has detected and automatically tried to repair an error in your database.  If you continue to have problems, or continue to receive these emails, please contact your host.';
$txt['database_error_versions'] = '<b>Note:</b> It appears that your database <em>may</em> require an upgrade. Your forum\'s files are currently at version ' . $forum_version . ', while your database is at version ' . $modSettings['smfVersion'] . '. The above error might possibly go away if you execute the latest version of upgrade.php.';
$txt['template_parse_error'] = 'Template Parse Error!';
$txt['template_parse_error_message'] = 'It seems something has gone sour on the forum with the template system.  This problem should only be temporary, so please come back later and try again.  If you continue to see this message, please contact the administrator.<br /><br />You can also try <a href="javascript:location.reload();">refreshing this page</a>.';
$txt['template_parse_error_details'] = 'There was a problem loading the <tt><b>%1$s</b></tt> template or language file.  Please check the syntax and try again - remember, single quotes (<tt>\'</tt>) often have to be escaped with a slash (<tt>\\</tt>).  To see more specific error information from PHP, try <a href="' . $boardurl . '%1$s">accessing the file directly</a>.<br /><br />You may want to try to <a href="javascript:location.reload();">refresh this page</a> or <a href="' . $scripturl . '?theme=1">use the default theme</a>.';

$txt['smf10'] = '<b>Today</b> at ';
$txt['smf10b'] = '<b>Yesterday</b> at ';
$txt['smf20'] = 'Post new poll';
$txt['smf21'] = 'Question';
$txt['smf23'] = 'Submit Vote';
$txt['smf24'] = 'Total Voters';
$txt['smf25'] = 'shortcuts: hit alt+s to submit/post or alt+p to preview';
$txt['smf29'] = 'View results';
$txt['smf30'] = 'Lock Voting';
$txt['smf30b'] = 'Unlock Voting';
$txt['smf39'] = 'Edit Poll';
$txt['smf43'] = 'Poll';
$txt['smf47'] = '1 Day';
$txt['smf48'] = '1 Week';
$txt['smf49'] = '1 Month';
$txt['smf50'] = 'Forever';
$txt['smf52'] = 'Login with username, password and session length';
$txt['smf53'] = '1 Hour';
$txt['smf56'] = 'MOVED';
$txt['smf57'] = 'Please enter a brief description as to<br />why this topic is being moved.';
$txt['smf60'] = 'Sorry, you don\'t have enough posts to modify karma - you need at least ';
$txt['smf62'] = 'Sorry, you can\'t repeat a karma action without waiting ';
$txt['smf82'] = 'Board';
$txt['smf88'] = 'in';
$txt['smf96'] = 'Sticky Topic';

$txt['smf138'] = 'Delete';

$txt['smf199'] = 'Your Personal Messages';

$txt['smf211'] = 'KB';

$txt['smf223'] = '[More Stats]';

// Use numeric entities in the below three strings.
$txt['smf238'] = 'Code';
$txt['smf239'] = 'Quote from';
$txt['smf240'] = 'Quote';

$txt['smf251'] = 'Split Topic';
$txt['smf252'] = 'Merge Topics';
$txt['smf254'] = 'Subject For New Topic';
$txt['smf255'] = 'Only split this post.';
$txt['smf256'] = 'Split topic after and including this post.';
$txt['smf257'] = 'Select posts to split.';
$txt['smf258'] = 'New Topic';
$txt['smf259'] = 'Topic successfully split into two topics.';
$txt['smf260'] = 'Origin Topic';
$txt['smf261'] = 'Please select which posts you wish to split.';
$txt['smf264'] = 'Topics successfully merged.';
$txt['smf265'] = 'Newly Merged Topic';
$txt['smf266'] = 'Topic to be merged';
$txt['smf267'] = 'Target board';
$txt['smf269'] = 'Target topic';
$txt['smf274'] = 'Are you sure you want to merge';
$txt['smf275'] = 'with';
$txt['smf276'] = 'This function will merge the messages of two topics into one topic. The messages will be sorted according to the time of posting. Therefore the earliest posted message will be the first message of the merged topic.';

$txt['smf277'] = 'Set topic sticky';
$txt['smf278'] = 'Set topic non-sticky';
$txt['smf279'] = 'Lock topic';
$txt['smf280'] = 'Unlock topic';

$txt['smf298'] = 'Advanced search';

$txt['smf299'] = 'MAJOR SECURITY RISK:';
$txt['smf300'] = 'You have not removed ';

$txt['smf301'] = 'Page created in ';
$txt['smf302'] = ' seconds with ';
$txt['smf302b'] = ' queries.';

$txt['smf315'] = 'Use this function to inform the moderators and administrators of an abusive or wrongly posted message.<br /><i>Please note that your email address will be revealed to the moderators if you use this.</i>';

$txt['online2'] = 'Online';
$txt['online3'] = 'Offline';
$txt['online4'] = 'Personal Message (Online)';
$txt['online5'] = 'Personal Message (Offline)';
$txt['online8'] = 'Status';

$txt['topbottom4'] = 'Go Up';
$txt['topbottom5'] = 'Go Down';

$forum_copyright = '<a href="http://www.simplemachines.org/" title="Simple Machines Forum" target="_blank">Powered by ' . $forum_version . '</a> |
<a href="http://www.simplemachines.org/about/copyright.php" title="Free Forum Software" target="_blank">SMF &copy; 2006-2011, Simple Machines LLC</a>';

$txt['calendar3'] = 'Birthdays:';
$txt['calendar4'] = 'Events:';
$txt['calendar3b'] = 'Upcoming Birthdays:';
$txt['calendar4b'] = 'Upcoming Events:';
// Prompt for holidays in the calendar, leave blank to just display the holiday's name.
$txt['calendar5'] = '';
$txt['calendar9'] = 'Month:';
$txt['calendar10'] = 'Year:';
$txt['calendar11'] = 'Day:';
$txt['calendar12'] = 'Event Title:';
$txt['calendar13'] = 'Post In:';
$txt['calendar20'] = 'Edit Event';
$txt['calendar21'] = 'Delete this event?';
$txt['calendar22'] = 'Delete Event';
$txt['calendar23'] = 'Post Event';
$txt['calendar24'] = 'Calendar';
$txt['calendar37'] = 'Link to Calendar';
$txt['calendar43'] = 'Link Event';
$txt['calendar47'] = 'Upcoming Calendar';
$txt['calendar47b'] = 'Today\'s Calendar';
$txt['calendar51'] = 'Week';
$txt['calendar54'] = 'Number of Days:';
$txt['calendar_how_edit'] = 'how do you edit these events?';
$txt['calendar_link_event'] = 'Link Event To Post:';
$txt['calendar_confirm_delete'] = 'Are you sure you want to delete this event?';
$txt['calendar_linked_events'] = 'Linked Events';

$txt['moveTopic1'] = 'Post a redirection topic';
$txt['moveTopic2'] = 'Change the topic\'s subject';
$txt['moveTopic3'] = 'New subject';
$txt['moveTopic4'] = 'Change every message\'s subject';

$txt['theme_template_error'] = 'Unable to load the \'%s\' template.';
$txt['theme_language_error'] = 'Unable to load the \'%s\' language file.';

$txt['parent_boards'] = 'Child Boards';

$txt['smtp_no_connect'] = 'Could not connect to SMTP host';
$txt['smtp_port_ssl'] = 'SMTP port setting incorrect; it should be 465 for SSL servers.';
$txt['smtp_bad_response'] = 'Couldn\'t get mail server response codes';
$txt['smtp_error'] = 'Ran into problems sending Mail. Error: ';
$txt['mail_send_unable'] = 'Unable to send mail to the email address \'%s\'';

$txt['mlist_search'] = 'Search for members';
$txt['mlist_search2'] = 'Search again';
$txt['mlist_search_email'] = 'Search by email address';
$txt['mlist_search_messenger'] = 'Search by messenger nickname';
$txt['mlist_search_group'] = 'Search by position';
$txt['mlist_search_name'] = 'Search by name';
$txt['mlist_search_website'] = 'Search by website';
$txt['mlist_search_results'] = 'Search results for';

$txt['attach_downloaded'] = 'downloaded';
$txt['attach_viewed'] = 'viewed';
$txt['attach_times'] = 'times';

$txt['MSN'] = 'MSN';

$txt['settings'] = 'Settings';
$txt['never'] = 'Never';
$txt['more'] = 'more';

$txt['hostname'] = 'Hostname';
$txt['you_are_post_banned'] = 'Sorry %s, you are banned from posting or sending personal messages on this forum.';
$txt['ban_reason'] = 'Reason';

$txt['tables_optimized'] = 'Database tables optimized';

$txt['add_poll'] = 'Add poll';
$txt['poll_options6'] = 'You may only select up to %s options.';
$txt['poll_remove'] = 'Remove Poll';
$txt['poll_remove_warn'] = 'Are you sure you want to remove this poll from the topic?';
$txt['poll_results_expire'] = 'Results will be shown when voting has closed';
$txt['poll_expires_on'] = 'Voting closes';
$txt['poll_expired_on'] = 'Voting closed';
$txt['poll_change_vote'] = 'Remove Vote';
$txt['poll_return_vote'] = 'Voting options';

$txt['quick_mod_remove'] = 'Remove selected';
$txt['quick_mod_lock'] = 'Lock selected';
$txt['quick_mod_sticky'] = 'Sticky selected';
$txt['quick_mod_move'] = 'Move selected to';
$txt['quick_mod_merge'] = 'Merge selected';
$txt['quick_mod_markread'] = 'Mark selected read';
$txt['quick_mod_go'] = 'Go!';
$txt['quickmod_confirm'] = 'Are you sure you want to do this?';

$txt['spell_check'] = 'Spell Check';

$txt['quick_reply_1'] = 'Quick Reply';
$txt['quick_reply_2'] = 'With a <i>Quick-Reply</i> you can use bulletin board code and smileys as you would in a normal post, but much more conveniently.';
$txt['quick_reply_warning'] = 'Warning: this topic is currently locked!<br />Only admins and moderators can reply.';

$txt['notification_enable_board'] = 'Are you sure you wish to enable notification of new topics for this board?';
$txt['notification_disable_board'] = 'Are you sure you wish to disable notification of new topics for this board?';
$txt['notification_enable_topic'] = 'Are you sure you wish to enable notification of new replies for this topic?';
$txt['notification_disable_topic'] = 'Are you sure you wish to disable notification of new replies for this topic?';

$txt['rtm1'] = 'Report to moderator';

$txt['unread_topics_visit'] = 'Recent Unread Topics';
$txt['unread_topics_visit_none'] = 'No unread topics found since your last visit.  <a href="' . $scripturl . '?action=unread;all">Click here to try all unread topics</a>.';
$txt['unread_topics_all'] = 'All Unread Topics';
$txt['unread_replies'] = 'Updated Topics';

$txt['who_title'] = 'Who\'s Online';
$txt['who_and'] = ' and ';
$txt['who_viewing_topic'] = ' are viewing this topic.';
$txt['who_viewing_board'] = ' are viewing this board.';
$txt['who_member'] = 'Member';

$txt['powered_by_php'] = 'Powered by PHP';
$txt['powered_by_mysql'] = 'Powered by MySQL';
$txt['valid_html'] = 'Valid HTML 4.01!';
$txt['valid_xhtml'] = 'Valid XHTML 1.0!';
$txt['valid_css'] = 'Valid CSS!';

$txt['guest'] = 'Guest';
$txt['guests'] = 'Guests';
$txt['user'] = 'User';
$txt['users'] = 'Users';
$txt['hidden'] = 'Hidden';
$txt['buddy'] = 'Buddy';
$txt['buddies'] = 'Buddies';
$txt['most_online_ever'] = 'Most Online Ever';
$txt['most_online_today'] = 'Most Online Today';

$txt['merge_select_target_board'] = 'Select the target board of the merged topic';
$txt['merge_select_poll'] = 'Select which poll the merged topic should have';
$txt['merge_topic_list'] = 'Select topics to be merged';
$txt['merge_select_subject'] = 'Select subject of merged topic';
$txt['merge_custom_subject'] = 'Custom subject';
$txt['merge_enforce_subject'] = 'Change the subject of all the messages';
$txt['merge_include_notifications'] = 'Include notifications?';
$txt['merge_check'] = 'Merge?';
$txt['merge_no_poll'] = 'No poll';

$txt['response_prefix'] = 'Re: ';
$txt['current_icon'] = 'Current Icon';

$txt['smileys_current'] = 'Current Smiley Set';
$txt['smileys_none'] = 'No Smileys';
$txt['smileys_forum_board_default'] = 'Forum/Board Default';

$txt['search_results'] = 'Search Results';
$txt['search_no_results'] = 'No results found';

$txt['totalTimeLogged1'] = 'Total time logged in: ';
$txt['totalTimeLogged2'] = ' days, ';
$txt['totalTimeLogged3'] = ' hours and ';
$txt['totalTimeLogged4'] = ' minutes.';
$txt['totalTimeLogged5'] = 'd ';
$txt['totalTimeLogged6'] = 'h ';
$txt['totalTimeLogged7'] = 'm';

$txt['approve_thereis'] = 'There is';
$txt['approve_thereare'] = 'There are';
$txt['approve_member'] = 'one member';
$txt['approve_members'] = 'members';
$txt['approve_members_waiting'] = 'awaiting approval.';

$txt['notifyboard_turnon'] = 'Do you want a notification email when someone posts a new topic in this board?';
$txt['notifyboard_turnoff'] = 'Are you sure you do not want to receive new topic notifications for this board?';

$txt['activate_code'] = 'Your activation code is';

$txt['find_members'] = 'Find Members';
$txt['find_username'] = 'Name, username, or email address';
$txt['find_buddies'] = 'Show Buddies Only?';
$txt['find_wildcards'] = 'Allowed Wildcards: *, ?';
$txt['find_no_results'] = 'No results found';
$txt['find_results'] = 'Results';
$txt['find_close'] = 'Close';

$txt['unread_since_visit'] = 'Show unread posts since last visit.';
$txt['show_unread_replies'] = 'Show new replies to your posts.';

$txt['change_color'] = 'Change Color';

$txt['quickmod_delete_selected'] = 'Delete Selected';

// In this string, don't use entities. (&amp;, etc.)
$txt['show_personal_messages'] = 'You have received one or more new personal messages.\\nView them now (in a new window)?';

$txt['previous_next_back'] = '&laquo; previous';
$txt['previous_next_forward'] = 'next &raquo;';

$txt['movetopic_auto_board'] = '[BOARD]';
$txt['movetopic_auto_topic'] = '[TOPIC LINK]';
$txt['movetopic_default'] = 'This topic has been moved to ' . $txt['movetopic_auto_board'] . ".\n\n" . $txt['movetopic_auto_topic'];

$txt['upshrink_description'] = 'Shrink or expand the header.';

$txt['mark_unread'] = 'Mark unread';

$txt['ssi_not_direct'] = 'Please don\'t access SSI.php by URL directly; you may want to use the path (%s) or add ?ssi_function=something.';
$txt['ssi_session_broken'] = 'SSI.php was unable to load a session!  This may cause problems with logout and other functions - please make sure SSI.php is included before *anything* else in all your scripts!';

// Escape any single quotes in here twice.. 'it\'s' -> 'it\\\'s'.
$txt['preview_title'] = 'Preview post';
$txt['preview_fetch'] = 'Fetching preview...';
$txt['preview_new'] = 'New message';
$txt['error_while_submitting'] = 'The following error or errors occurred while posting this message:';

$txt['split_selected_posts'] = 'Selected posts';
$txt['split_selected_posts_desc'] = 'The posts below will form a new topic after splitting.';
$txt['split_reset_selection'] = 'reset selection';

$txt['modify_cancel'] = 'Cancel';
$txt['mark_read_short'] = 'Mark Read';

$txt['pm_short'] = 'My Messages';
$txt['hello_member_ndt'] = 'Hello';

$txt['ajax_in_progress'] = 'Loading...';

?>