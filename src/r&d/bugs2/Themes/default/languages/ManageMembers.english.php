<?php
// Version: 1.1; ManageMembers

$txt['membergroups_title'] = 'Manage Membergroups';
$txt['membergroups_description'] = 'Membergroups are groups of members that have similar permission settings, appearance, or access rights. Some membergroups are based on the amount of posts a user has made. You can assign someone to a membergroup by selecting their profile and changing their account settings.';
$txt['membergroups_modify'] = 'Modify';

$txt['membergroups_add_group'] = 'Add group';
$txt['membergroups_regular'] = 'Regular groups';
$txt['membergroups_post'] = 'Post count based groups';

$txt['membergroups_new_group'] = 'Add Membergroup';
$txt['membergroups_group_name'] = 'Membergroup name';
$txt['membergroups_new_board'] = 'Visible Boards';
$txt['membergroups_new_board_desc'] = 'Boards the membergroup can see';
$txt['membergroups_new_board_post_groups'] = '<em>Note: normally, post groups don\'t need access because the group the member is in will give them access.</em>';
$txt['membergroups_new_as_type'] = 'by type';
$txt['membergroups_new_as_copy'] = 'based off of';
$txt['membergroups_new_copy_none'] = '(none)';
$txt['membergroups_can_edit_later'] = 'You can edit them later.';

$txt['membergroups_edit_group'] = 'Edit Membergroup';
$txt['membergroups_edit_name'] = 'Group name';
$txt['membergroups_edit_post_group'] = 'This group is based off posts';
$txt['membergroups_min_posts'] = 'Required posts';
$txt['membergroups_online_color'] = 'Color in online list';
$txt['membergroups_star_count'] = 'Number of star images';
$txt['membergroups_star_image'] = 'Star image filename';
$txt['membergroups_star_image_note'] = 'you can use $language for the language of the user';
$txt['membergroups_max_messages'] = 'Max personal messages';
$txt['membergroups_max_messages_note'] = '0 = unlimited';
$txt['membergroups_edit_save'] = 'Save';
$txt['membergroups_delete'] = 'Delete';
$txt['membergroups_confirm_delete'] = 'Are you sure you want to delete this group?!';

$txt['membergroups_members_title'] = 'Showing all members from group';
$txt['membergroups_members_no_members'] = 'This group is currently empty';
$txt['membergroups_members_add_title'] = 'Add a member to this group';
$txt['membergroups_members_add_desc'] = 'List of Members to Add';
$txt['membergroups_members_add'] = 'Add Members';
$txt['membergroups_members_remove'] = 'Remove from Group';

$txt['membergroups_postgroups'] = 'Post groups';

$txt['membergroups_edit_groups'] = 'Edit Membergroups';
$txt['membergroups_settings'] = 'Membergroup Settings';
$txt['groups_manage_membergroups'] = 'Groups allowed to change membergroups';
$txt['membergroups_settings_submit'] = 'Save';
$txt['membergroups_select_permission_type'] = 'Select permission profile';
$txt['membergroups_images_url'] = '{theme URL}/images/';
$txt['membergroups_select_visible_boards'] = 'Show boards';

$txt['admin_browse_approve'] = 'Members whose accounts are awaiting approval';
$txt['admin_browse_approve_desc'] = 'From here you can manage all members who are waiting to have their accounts approved.';
$txt['admin_browse_activate'] = 'Members whose accounts are awaiting activation';
$txt['admin_browse_activate_desc'] = 'This screen lists all the members who have still not activated their accounts at your forum.';
$txt['admin_browse_awaiting_approval'] = 'Awaiting Approval <span style="font-weight: normal">(%d)</span>';
$txt['admin_browse_awaiting_activate'] = 'Awaiting Activation <span style="font-weight: normal">(%d)</span>';

$txt['admin_browse_username'] = 'Username';
$txt['admin_browse_email'] = 'Email Address';
$txt['admin_browse_ip'] = 'IP Address';
$txt['admin_browse_registered'] = 'Registered';
$txt['admin_browse_id'] = 'ID';
$txt['admin_browse_with_selected'] = 'With Selected';
$txt['admin_browse_no_members_approval'] = 'No members currently await approval.';
$txt['admin_browse_no_members_activate'] = 'No members currently have not activated their accounts.';

// Don't use entities in the below strings, except the main ones. (lt, gt, quot.)
$txt['admin_browse_warn'] = 'all selected members?';
$txt['admin_browse_outstanding_warn'] = 'all affected members?';
$txt['admin_browse_w_approve'] = 'Approve';
$txt['admin_browse_w_activate'] = 'Activate';
$txt['admin_browse_w_delete'] = 'Delete';
$txt['admin_browse_w_reject'] = 'Reject';
$txt['admin_browse_w_remind'] = 'Remind';
$txt['admin_browse_w_approve_deletion'] = 'Approve (Delete Accounts)';
$txt['admin_browse_w_email'] = 'and send email';
$txt['admin_browse_w_approve_require_activate'] = 'Approve and Require Activation';

$txt['admin_browse_filter_by'] = 'Filter By';
$txt['admin_browse_filter_show'] = 'Displaying';
$txt['admin_browse_filter_type_0'] = 'Unactivated New Accounts';
$txt['admin_browse_filter_type_2'] = 'Unactivated Email Changes';
$txt['admin_browse_filter_type_3'] = 'Unapproved New Accounts';
$txt['admin_browse_filter_type_4'] = 'Unapproved Account Deletions';
$txt['admin_browse_filter_type_5'] = 'Unapproved "Under Age" Accounts';

$txt['admin_browse_outstanding'] = 'Outstanding Members';
$txt['admin_browse_outstanding_days_1'] = 'With all members who registered longer than';
$txt['admin_browse_outstanding_days_2'] = 'days ago';
$txt['admin_browse_outstanding_perform'] = 'Perform the following action';
$txt['admin_browse_outstanding_go'] = 'Perform Action';

// Use numeric entities in the below nine strings.
$txt['admin_approve_reject'] = 'Registration Rejected';
$txt['admin_approve_reject_desc'] = 'Regrettably, your application to join ' . $context['forum_name'] . ' has been rejected.';
$txt['admin_approve_delete'] = 'Account Deleted';
$txt['admin_approve_delete_desc'] = 'Your account on ' . $context['forum_name'] . ' has been deleted.  This may be because you never activated your account, in which case you should be able to register again.';
$txt['admin_approve_remind'] = 'Registration Reminder';
$txt['admin_approve_remind_desc'] = 'You still have not activated your account at';
$txt['admin_approve_remind_desc2'] = 'Please click the link below to activate your account:';
$txt['admin_approve_accept_desc'] = 'Your account has been activated manually by the admin and you can now login and post.';
$txt['admin_approve_require_activation'] = 'Your account on ' . $context['forum_name'] . ' has been approved by the forum administrator, and must now be activated before you can begin posting.';

?>