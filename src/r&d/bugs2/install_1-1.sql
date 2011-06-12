#### ATTENTION: You don't need to run or use this file!  The install.php script does everything for you!

#
# Table structure for table `attachments`
#

CREATE TABLE {$db_prefix}attachments (
  ID_ATTACH int(10) unsigned NOT NULL auto_increment,
  ID_THUMB int(10) unsigned NOT NULL default '0',
  ID_MSG int(10) unsigned NOT NULL default '0',
  ID_MEMBER mediumint(8) unsigned NOT NULL default '0',
  attachmentType tinyint(3) unsigned NOT NULL default '0',
  filename tinytext NOT NULL,
  file_hash varchar(40) NOT NULL default '',
  size int(10) unsigned NOT NULL default '0',
  downloads mediumint(8) unsigned NOT NULL default '0',
  width mediumint(8) unsigned NOT NULL default '0',
  height mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY (ID_ATTACH),
  UNIQUE ID_MEMBER (ID_MEMBER, ID_ATTACH),
  KEY ID_MSG (ID_MSG)
) ENGINE=MyISAM;

#
# Table structure for table `ban_groups`
#

CREATE TABLE {$db_prefix}ban_groups (
  ID_BAN_GROUP mediumint(8) unsigned NOT NULL auto_increment,
  name varchar(20) NOT NULL default '',
  ban_time int(10) unsigned NOT NULL default '0',
  expire_time int(10) unsigned,
  cannot_access tinyint(3) unsigned NOT NULL default '0',
  cannot_register tinyint(3) unsigned NOT NULL default '0',
  cannot_post tinyint(3) unsigned NOT NULL default '0',
  cannot_login tinyint(3) unsigned NOT NULL default '0',
  reason tinytext NOT NULL,
  notes text NOT NULL,
  PRIMARY KEY (ID_BAN_GROUP)
) ENGINE=MyISAM;

#
# Table structure for table `ban_items`
#

CREATE TABLE {$db_prefix}ban_items (
  ID_BAN mediumint(8) unsigned NOT NULL auto_increment,
  ID_BAN_GROUP smallint(5) unsigned NOT NULL default '0',
  ip_low1 tinyint(3) unsigned NOT NULL default '0',
  ip_high1 tinyint(3) unsigned NOT NULL default '0',
  ip_low2 tinyint(3) unsigned NOT NULL default '0',
  ip_high2 tinyint(3) unsigned NOT NULL default '0',
  ip_low3 tinyint(3) unsigned NOT NULL default '0',
  ip_high3 tinyint(3) unsigned NOT NULL default '0',
  ip_low4 tinyint(3) unsigned NOT NULL default '0',
  ip_high4 tinyint(3) unsigned NOT NULL default '0',
  hostname tinytext NOT NULL,
  email_address tinytext NOT NULL,
  ID_MEMBER mediumint(8) unsigned NOT NULL default '0',
  hits mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY (ID_BAN),
  KEY ID_BAN_GROUP (ID_BAN_GROUP)
) ENGINE=MyISAM;

#
# Table structure for table `board_permissions`
#

CREATE TABLE {$db_prefix}board_permissions (
  ID_GROUP smallint(5) NOT NULL default '0',
  ID_BOARD smallint(5) unsigned NOT NULL default '0',
  permission varchar(30) NOT NULL default '',
  addDeny tinyint(4) NOT NULL default '1',
  PRIMARY KEY (ID_GROUP, ID_BOARD, permission)
) ENGINE=MyISAM;

#
# Dumping data for table `board_permissions`
#

INSERT INTO {$db_prefix}board_permissions
	(ID_GROUP, ID_BOARD, permission)
VALUES (-1, 0, 'poll_view'),
	(0, 0, 'remove_own'),
	(0, 0, 'lock_own'),
	(0, 0, 'mark_any_notify'),
	(0, 0, 'mark_notify'),
	(0, 0, 'modify_own'),
	(0, 0, 'poll_add_own'),
	(0, 0, 'poll_edit_own'),
	(0, 0, 'poll_lock_own'),
	(0, 0, 'poll_post'),
	(0, 0, 'poll_view'),
	(0, 0, 'poll_vote'),
	(0, 0, 'post_attachment'),
	(0, 0, 'post_new'),
	(0, 0, 'post_reply_any'),
	(0, 0, 'post_reply_own'),
	(0, 0, 'delete_own'),
	(0, 0, 'report_any'),
	(0, 0, 'send_topic'),
	(0, 0, 'view_attachments'),
	(2, 0, 'moderate_board'),
	(2, 0, 'post_new'),
	(2, 0, 'post_reply_own'),
	(2, 0, 'post_reply_any'),
	(2, 0, 'poll_post'),
	(2, 0, 'poll_add_any'),
	(2, 0, 'poll_remove_any'),
	(2, 0, 'poll_view'),
	(2, 0, 'poll_vote'),
	(2, 0, 'poll_edit_any'),
	(2, 0, 'report_any'),
	(2, 0, 'lock_own'),
	(2, 0, 'send_topic'),
	(2, 0, 'mark_any_notify'),
	(2, 0, 'mark_notify'),
	(2, 0, 'delete_own'),
	(2, 0, 'modify_own'),
	(2, 0, 'make_sticky'),
	(2, 0, 'lock_any'),
	(2, 0, 'remove_any'),
	(2, 0, 'move_any'),
	(2, 0, 'merge_any'),
	(2, 0, 'split_any'),
	(2, 0, 'delete_any'),
	(2, 0, 'modify_any'),
	(3, 0, 'moderate_board'),
	(3, 0, 'post_new'),
	(3, 0, 'post_reply_own'),
	(3, 0, 'post_reply_any'),
	(3, 0, 'poll_post'),
	(3, 0, 'poll_add_own'),
	(3, 0, 'poll_remove_any'),
	(3, 0, 'poll_view'),
	(3, 0, 'poll_vote'),
	(3, 0, 'report_any'),
	(3, 0, 'lock_own'),
	(3, 0, 'send_topic'),
	(3, 0, 'mark_any_notify'),
	(3, 0, 'mark_notify'),
	(3, 0, 'delete_own'),
	(3, 0, 'modify_own'),
	(3, 0, 'make_sticky'),
	(3, 0, 'lock_any'),
	(3, 0, 'remove_any'),
	(3, 0, 'move_any'),
	(3, 0, 'merge_any'),
	(3, 0, 'split_any'),
	(3, 0, 'delete_any'),
	(3, 0, 'modify_any');
# --------------------------------------------------------

#
# Table structure for table `boards`
#

CREATE TABLE {$db_prefix}boards (
  ID_BOARD smallint(5) unsigned NOT NULL auto_increment,
  ID_CAT tinyint(4) unsigned NOT NULL default '0',
  childLevel tinyint(4) unsigned NOT NULL default '0',
  ID_PARENT smallint(5) unsigned NOT NULL default '0',
  boardOrder smallint(5) NOT NULL default '0',
  ID_LAST_MSG int(10) unsigned NOT NULL default '0',
  ID_MSG_UPDATED int(10) unsigned NOT NULL default '0',
  memberGroups varchar(255) NOT NULL default '-1,0',
  name tinytext NOT NULL,
  description text NOT NULL,
  numTopics mediumint(8) unsigned NOT NULL default '0',
  numPosts mediumint(8) unsigned NOT NULL default '0',
  countPosts tinyint(4) NOT NULL default '0',
  ID_THEME tinyint(4) unsigned NOT NULL default '0',
  permission_mode tinyint(4) unsigned NOT NULL default '0',
  override_theme tinyint(4) unsigned NOT NULL default '0',
  PRIMARY KEY (ID_BOARD),
  UNIQUE categories (ID_CAT, ID_BOARD),
  KEY ID_PARENT (ID_PARENT),
  KEY ID_MSG_UPDATED (ID_MSG_UPDATED),
  KEY memberGroups (memberGroups(48))
) ENGINE=MyISAM;

#
# Dumping data for table `boards`
#

INSERT INTO {$db_prefix}boards
	(ID_BOARD, ID_CAT, boardOrder, ID_LAST_MSG, ID_MSG_UPDATED, name, description, numTopics, numPosts, memberGroups)
VALUES (1, 1, 1, 1, 1, '{$default_board_name}', '{$default_board_description}', 1, 1, '-1,0');
# --------------------------------------------------------

#
# Table structure for table `calendar`
#

CREATE TABLE {$db_prefix}calendar (
  ID_EVENT smallint(5) unsigned NOT NULL auto_increment,
  startDate date NOT NULL default '0001-01-01',
  endDate date NOT NULL default '0001-01-01',
  ID_BOARD smallint(5) unsigned NOT NULL default '0',
  ID_TOPIC mediumint(8) unsigned NOT NULL default '0',
  title varchar(48) NOT NULL default '',
  ID_MEMBER mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY (ID_EVENT),
  KEY startDate (startDate),
  KEY endDate (endDate),
  KEY topic (ID_TOPIC, ID_MEMBER)
) ENGINE=MyISAM;

#
# Table structure for table `calendar_holidays`
#

CREATE TABLE {$db_prefix}calendar_holidays (
  ID_HOLIDAY smallint(5) unsigned NOT NULL auto_increment,
  eventDate date NOT NULL default '0001-01-01',
  title varchar(30) NOT NULL default '',
  PRIMARY KEY (ID_HOLIDAY),
  KEY eventDate (eventDate)
) ENGINE=MyISAM;

#
# Dumping data for table `calendar_holidays`
#

INSERT INTO {$db_prefix}calendar_holidays
	(title, eventDate)
VALUES ('New Year\'s', '0004-01-01'),
	('Christmas', '0004-12-25'),
	('Valentine\'s Day', '0004-02-14'),
	('St. Patrick\'s Day', '0004-03-17'),
	('April Fools', '0004-04-01'),
	('Earth Day', '0004-04-22'),
	('United Nations Day', '0004-10-24'),
	('Halloween', '0004-10-31'),
	('Mother\'s Day', '2004-05-09'),
	('Mother\'s Day', '2005-05-08'),
	('Mother\'s Day', '2006-05-14'),
	('Mother\'s Day', '2007-05-13'),
	('Mother\'s Day', '2008-05-11'),
	('Mother\'s Day', '2009-05-10'),
	('Mother\'s Day', '2010-05-09'),
	('Mother\'s Day', '2011-05-08'),
	('Mother\'s Day', '2012-05-13'),
	('Mother\'s Day', '2013-05-12'),
	('Mother\'s Day', '2014-05-11'),
	('Mother\'s Day', '2015-05-10'),
	('Mother\'s Day', '2016-05-08'),
	('Mother\'s Day', '2017-05-14'),
	('Mother\'s Day', '2018-05-13'),
	('Mother\'s Day', '2019-05-12'),
	('Mother\'s Day', '2020-05-10'),
	('Father\'s Day', '2004-06-20'),
	('Father\'s Day', '2005-06-19'),
	('Father\'s Day', '2006-06-18'),
	('Father\'s Day', '2007-06-17'),
	('Father\'s Day', '2008-06-15'),
	('Father\'s Day', '2009-06-21'),
	('Father\'s Day', '2010-06-20'),
	('Father\'s Day', '2011-06-19'),
	('Father\'s Day', '2012-06-17'),
	('Father\'s Day', '2013-06-16'),
	('Father\'s Day', '2014-06-15'),
	('Father\'s Day', '2015-06-21'),
	('Father\'s Day', '2016-06-19'),
	('Father\'s Day', '2017-06-18'),
	('Father\'s Day', '2018-06-17'),
	('Father\'s Day', '2019-06-16'),
	('Father\'s Day', '2020-06-21'),
	('Summer Solstice', '2004-06-20'),
	('Summer Solstice', '2005-06-20'),
	('Summer Solstice', '2006-06-21'),
	('Summer Solstice', '2007-06-21'),
	('Summer Solstice', '2008-06-20'),
	('Summer Solstice', '2009-06-20'),
	('Summer Solstice', '2010-06-21'),
	('Summer Solstice', '2011-06-21'),
	('Summer Solstice', '2012-06-20'),
	('Summer Solstice', '2013-06-21'),
	('Summer Solstice', '2014-06-21'),
	('Summer Solstice', '2015-06-21'),
	('Summer Solstice', '2016-06-20'),
	('Summer Solstice', '2017-06-20'),
	('Summer Solstice', '2018-06-21'),
	('Summer Solstice', '2019-06-21'),
	('Summer Solstice', '2020-06-20'),
	('Vernal Equinox', '2004-03-19'),
	('Vernal Equinox', '2005-03-20'),
	('Vernal Equinox', '2006-03-20'),
	('Vernal Equinox', '2007-03-20'),
	('Vernal Equinox', '2008-03-19'),
	('Vernal Equinox', '2009-03-20'),
	('Vernal Equinox', '2010-03-20'),
	('Vernal Equinox', '2011-03-20'),
	('Vernal Equinox', '2012-03-20'),
	('Vernal Equinox', '2013-03-20'),
	('Vernal Equinox', '2014-03-20'),
	('Vernal Equinox', '2015-03-20'),
	('Vernal Equinox', '2016-03-19'),
	('Vernal Equinox', '2017-03-20'),
	('Vernal Equinox', '2018-03-20'),
	('Vernal Equinox', '2019-03-20'),
	('Vernal Equinox', '2020-03-19'),
	('Winter Solstice', '2004-12-21'),
	('Winter Solstice', '2005-12-21'),
	('Winter Solstice', '2006-12-22'),
	('Winter Solstice', '2007-12-22'),
	('Winter Solstice', '2008-12-21'),
	('Winter Solstice', '2009-12-21'),
	('Winter Solstice', '2010-12-21'),
	('Winter Solstice', '2011-12-22'),
	('Winter Solstice', '2012-12-21'),
	('Winter Solstice', '2013-12-21'),
	('Winter Solstice', '2014-12-21'),
	('Winter Solstice', '2015-12-21'),
	('Winter Solstice', '2016-12-21'),
	('Winter Solstice', '2017-12-21'),
	('Winter Solstice', '2018-12-21'),
	('Winter Solstice', '2019-12-21'),
	('Winter Solstice', '2020-12-21'),
	('Autumnal Equinox', '2004-09-22'),
	('Autumnal Equinox', '2005-09-22'),
	('Autumnal Equinox', '2006-09-22'),
	('Autumnal Equinox', '2007-09-23'),
	('Autumnal Equinox', '2008-09-22'),
	('Autumnal Equinox', '2009-09-22'),
	('Autumnal Equinox', '2010-09-22'),
	('Autumnal Equinox', '2011-09-23'),
	('Autumnal Equinox', '2012-09-22'),
	('Autumnal Equinox', '2013-09-22'),
	('Autumnal Equinox', '2014-09-22'),
	('Autumnal Equinox', '2015-09-23'),
	('Autumnal Equinox', '2016-09-22'),
	('Autumnal Equinox', '2017-09-22'),
	('Autumnal Equinox', '2018-09-22'),
	('Autumnal Equinox', '2019-09-23'),
	('Autumnal Equinox', '2020-09-22');

INSERT INTO {$db_prefix}calendar_holidays
	(title, eventDate)
VALUES ('Independence Day', '0004-07-04'),
	('Cinco de Mayo', '0004-05-05'),
	('Flag Day', '0004-06-14'),
	('Veterans Day', '0004-11-11'),
	('Groundhog Day', '0004-02-02'),
	('Thanksgiving', '2004-11-25'),
	('Thanksgiving', '2005-11-24'),
	('Thanksgiving', '2006-11-23'),
	('Thanksgiving', '2007-11-22'),
	('Thanksgiving', '2008-11-27'),
	('Thanksgiving', '2009-11-26'),
	('Thanksgiving', '2010-11-25'),
	('Thanksgiving', '2011-11-24'),
	('Thanksgiving', '2012-11-22'),
	('Thanksgiving', '2013-11-21'),
	('Thanksgiving', '2014-11-20'),
	('Thanksgiving', '2015-11-26'),
	('Thanksgiving', '2016-11-24'),
	('Thanksgiving', '2017-11-23'),
	('Thanksgiving', '2018-11-22'),
	('Thanksgiving', '2019-11-21'),
	('Thanksgiving', '2020-11-26'),
	('Memorial Day', '2004-05-31'),
	('Memorial Day', '2005-05-30'),
	('Memorial Day', '2006-05-29'),
	('Memorial Day', '2007-05-28'),
	('Memorial Day', '2008-05-26'),
	('Memorial Day', '2009-05-25'),
	('Memorial Day', '2010-05-31'),
	('Memorial Day', '2011-05-30'),
	('Memorial Day', '2012-05-28'),
	('Memorial Day', '2013-05-27'),
	('Memorial Day', '2014-05-26'),
	('Memorial Day', '2015-05-25'),
	('Memorial Day', '2016-05-30'),
	('Memorial Day', '2017-05-29'),
	('Memorial Day', '2018-05-28'),
	('Memorial Day', '2019-05-27'),
	('Memorial Day', '2020-05-25'),
	('Labor Day', '2004-09-06'),
	('Labor Day', '2005-09-05'),
	('Labor Day', '2006-09-04'),
	('Labor Day', '2007-09-03'),
	('Labor Day', '2008-09-01'),
	('Labor Day', '2009-09-07'),
	('Labor Day', '2010-09-06'),
	('Labor Day', '2011-09-05'),
	('Labor Day', '2012-09-03'),
	('Labor Day', '2013-09-09'),
	('Labor Day', '2014-09-08'),
	('Labor Day', '2015-09-07'),
	('Labor Day', '2016-09-05'),
	('Labor Day', '2017-09-04'),
	('Labor Day', '2018-09-03'),
	('Labor Day', '2019-09-09'),
	('Labor Day', '2020-09-07'),
	('D-Day', '0004-06-06');
# --------------------------------------------------------

#
# Table structure for table `categories`
#

CREATE TABLE {$db_prefix}categories (
  ID_CAT tinyint(4) unsigned NOT NULL auto_increment,
  catOrder tinyint(4) NOT NULL default '0',
  name tinytext NOT NULL,
  canCollapse tinyint(1) NOT NULL default '1',
  PRIMARY KEY (ID_CAT)
) ENGINE=MyISAM;

#
# Dumping data for table `categories`
#

INSERT INTO {$db_prefix}categories
VALUES (1, 0, '{$default_category_name}', 1);
# --------------------------------------------------------

#
# Table structure for table `collapsed_categories`
#

CREATE TABLE {$db_prefix}collapsed_categories (
  ID_CAT tinyint(4) unsigned NOT NULL default '0',
  ID_MEMBER mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY (ID_CAT, ID_MEMBER)
) ENGINE=MyISAM;

#
# Table structure for table `log_actions`
#

CREATE TABLE {$db_prefix}log_actions (
  ID_ACTION int(10) unsigned NOT NULL auto_increment,
  logTime int(10) unsigned NOT NULL default '0',
  ID_MEMBER mediumint(8) unsigned NOT NULL default '0',
  ip char(16) NOT NULL default '                ',
  action varchar(30) NOT NULL default '',
  extra text NOT NULL,
  PRIMARY KEY (ID_ACTION),
  KEY logTime (logTime),
  KEY ID_MEMBER (ID_MEMBER)
) ENGINE=MyISAM;

#
# Table structure for table `log_activity`
#

CREATE TABLE {$db_prefix}log_activity (
  date date NOT NULL default '0001-01-01',
  hits mediumint(8) unsigned NOT NULL default '0',
  topics smallint(5) unsigned NOT NULL default '0',
  posts smallint(5) unsigned NOT NULL default '0',
  registers smallint(5) unsigned NOT NULL default '0',
  mostOn smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY (date),
  KEY hits (hits),
  KEY mostOn (mostOn)
) ENGINE=MyISAM;

#
# Table structure for table `log_banned`
#

CREATE TABLE {$db_prefix}log_banned (
  ID_BAN_LOG mediumint(8) unsigned NOT NULL auto_increment,
  ID_MEMBER mediumint(8) unsigned NOT NULL default '0',
  ip char(16) NOT NULL default '                ',
  email tinytext NOT NULL,
  logTime int(10) unsigned NOT NULL default '0',
  PRIMARY KEY (ID_BAN_LOG),
  KEY logTime (logTime)
) ENGINE=MyISAM;

#
# Table structure for table `log_boards`
#

CREATE TABLE {$db_prefix}log_boards (
  ID_MEMBER mediumint(8) unsigned NOT NULL default '0',
  ID_BOARD smallint(5) unsigned NOT NULL default '0',
  ID_MSG int(10) unsigned NOT NULL default '0',
  PRIMARY KEY (ID_MEMBER, ID_BOARD)
) ENGINE=MyISAM;

#
# Table structure for table `log_errors`
#

CREATE TABLE {$db_prefix}log_errors (
  ID_ERROR mediumint(8) unsigned NOT NULL auto_increment,
  logTime int(10) unsigned NOT NULL default '0',
  ID_MEMBER mediumint(8) unsigned NOT NULL default '0',
  ip char(16) NOT NULL default '                ',
  url text NOT NULL,
  message text NOT NULL,
  session char(32) NOT NULL default '                                ',
  PRIMARY KEY (ID_ERROR),
  KEY logTime (logTime),
  KEY ID_MEMBER (ID_MEMBER),
  KEY ip (ip(16))
) ENGINE=MyISAM;

#
# Table structure for table `log_floodcontrol`
#

CREATE TABLE {$db_prefix}log_floodcontrol (
  ip char(16) NOT NULL default '                ',
  logTime int(10) unsigned NOT NULL default '0',
  PRIMARY KEY (ip(16))
) ENGINE=MyISAM;

#
# Table structure for table `log_karma`
#

CREATE TABLE {$db_prefix}log_karma (
  ID_TARGET mediumint(8) unsigned NOT NULL default '0',
  ID_EXECUTOR mediumint(8) unsigned NOT NULL default '0',
  logTime int(10) unsigned NOT NULL default '0',
  action tinyint(4) NOT NULL default '0',
  PRIMARY KEY (ID_TARGET, ID_EXECUTOR),
  KEY logTime (logTime)
) ENGINE=MyISAM;

#
# Table structure for table `log_mark_read`
#

CREATE TABLE {$db_prefix}log_mark_read (
  ID_MEMBER mediumint(8) unsigned NOT NULL default '0',
  ID_BOARD smallint(5) unsigned NOT NULL default '0',
  ID_MSG int(10) unsigned NOT NULL default '0',
  PRIMARY KEY (ID_MEMBER, ID_BOARD)
) ENGINE=MyISAM;

#
# Table structure for table `log_notify`
#

CREATE TABLE {$db_prefix}log_notify (
  ID_MEMBER mediumint(8) unsigned NOT NULL default '0',
  ID_TOPIC mediumint(8) unsigned NOT NULL default '0',
  ID_BOARD smallint(5) unsigned NOT NULL default '0',
  sent tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY (ID_MEMBER, ID_TOPIC, ID_BOARD)
) ENGINE=MyISAM;

#
# Table structure for table `log_online`
#

CREATE TABLE {$db_prefix}log_online (
  session varchar(32) NOT NULL default '',
  logTime timestamp(14) /*!40102 NOT NULL default CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP */,
  ID_MEMBER mediumint(8) unsigned NOT NULL default '0',
  ip int(10) unsigned NOT NULL default '0',
  url text NOT NULL,
  PRIMARY KEY (session),
  KEY logTime (logTime),
  KEY ID_MEMBER (ID_MEMBER)
) ENGINE=MyISAM;

#
# Table structure for table `log_polls`
#

CREATE TABLE {$db_prefix}log_polls (
  ID_POLL mediumint(8) unsigned NOT NULL default '0',
  ID_MEMBER mediumint(8) unsigned NOT NULL default '0',
  ID_CHOICE tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY (ID_POLL, ID_MEMBER, ID_CHOICE)
) ENGINE=MyISAM;

#
# Table structure for table `log_search_messages`
#

CREATE TABLE {$db_prefix}log_search_messages (
  ID_SEARCH tinyint(3) unsigned NOT NULL default '0',
  ID_MSG int(10) unsigned NOT NULL default '0',
  PRIMARY KEY (ID_SEARCH, ID_MSG)
) ENGINE=MyISAM;

#
# Table structure for table `log_search_results`
#

CREATE TABLE {$db_prefix}log_search_results (
  ID_SEARCH tinyint(3) unsigned NOT NULL default '0',
  ID_TOPIC mediumint(8) unsigned NOT NULL default '0',
  ID_MSG int(10) unsigned NOT NULL default '0',
  relevance smallint(5) unsigned NOT NULL default '0',
  num_matches smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY (ID_SEARCH, ID_TOPIC)
) ENGINE=MyISAM;

#
# Table structure for table `log_search_subjects`
#

CREATE TABLE {$db_prefix}log_search_subjects (
  word varchar(20) NOT NULL default '',
  ID_TOPIC mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY (word, ID_TOPIC),
  KEY ID_TOPIC (ID_TOPIC)
) ENGINE=MyISAM;

#
# Table structure for table `log_search_topics`
#

CREATE TABLE {$db_prefix}log_search_topics (
  ID_SEARCH tinyint(3) unsigned NOT NULL default '0',
  ID_TOPIC mediumint(9) NOT NULL default '0',
  PRIMARY KEY (ID_SEARCH, ID_TOPIC)
) ENGINE=MyISAM;

#
# Table structure for table `log_topics`
#

CREATE TABLE {$db_prefix}log_topics (
  ID_MEMBER mediumint(8) unsigned NOT NULL default '0',
  ID_TOPIC mediumint(8) unsigned NOT NULL default '0',
  ID_MSG int(10) unsigned NOT NULL default '0',
  PRIMARY KEY (ID_MEMBER, ID_TOPIC),
  KEY ID_TOPIC (ID_TOPIC)
) ENGINE=MyISAM;

#
# Table structure for table `membergroups`
#

CREATE TABLE {$db_prefix}membergroups (
  ID_GROUP smallint(5) unsigned NOT NULL auto_increment,
  groupName varchar(80) NOT NULL default '',
  onlineColor varchar(20) NOT NULL default '',
  minPosts mediumint(9) NOT NULL default '-1',
  maxMessages smallint(5) unsigned NOT NULL default '0',
  stars tinytext NOT NULL,
  PRIMARY KEY (ID_GROUP),
  KEY minPosts (minPosts)
) ENGINE=MyISAM;

#
# Dumping data for table `membergroups`
#

INSERT INTO {$db_prefix}membergroups
	(ID_GROUP, groupName, onlineColor, minPosts, stars)
VALUES (1, '{$default_administrator_group}', '#FF0000', -1, '5#staradmin.gif'),
	(2, '{$default_global_moderator_group}', '#0000FF', -1, '5#stargmod.gif'),
	(3, '{$default_moderator_group}', '', -1, '5#starmod.gif'),
	(4, '{$default_newbie_group}', '', 0, '1#star.gif'),
	(5, '{$default_junior_group}', '', 50, '2#star.gif'),
	(6, '{$default_full_group}', '', 100, '3#star.gif'),
	(7, '{$default_senior_group}', '', 250, '4#star.gif'),
	(8, '{$default_hero_group}', '', 500, '5#star.gif');
# --------------------------------------------------------

#
# Table structure for table `members`
#

CREATE TABLE {$db_prefix}members (
  ID_MEMBER mediumint(8) unsigned NOT NULL auto_increment,
  memberName varchar(80) NOT NULL default '',
  dateRegistered int(10) unsigned NOT NULL default '0',
  posts mediumint(8) unsigned NOT NULL default '0',
  ID_GROUP smallint(5) unsigned NOT NULL default '0',
  lngfile tinytext NOT NULL,
  lastLogin int(10) unsigned NOT NULL default '0',
  realName tinytext NOT NULL,
  instantMessages smallint(5) NOT NULL default 0,
  unreadMessages smallint(5) NOT NULL default 0,
  buddy_list text NOT NULL,
  pm_ignore_list tinytext NOT NULL,
  messageLabels text NOT NULL,
  passwd varchar(64) NOT NULL default '',
  emailAddress tinytext NOT NULL,
  personalText tinytext NOT NULL,
  gender tinyint(4) unsigned NOT NULL default '0',
  birthdate date NOT NULL default '0001-01-01',
  websiteTitle tinytext NOT NULL,
  websiteUrl tinytext NOT NULL,
  location tinytext NOT NULL,
  ICQ tinytext NOT NULL,
  AIM varchar(16) NOT NULL default '',
  YIM varchar(32) NOT NULL default '',
  MSN tinytext NOT NULL,
  hideEmail tinyint(4) NOT NULL default '0',
  showOnline tinyint(4) NOT NULL default '1',
  timeFormat varchar(80) NOT NULL default '',
  signature text NOT NULL,
  timeOffset float NOT NULL default '0',
  avatar tinytext NOT NULL,
  pm_email_notify tinyint(4) NOT NULL default '0',
  karmaBad smallint(5) unsigned NOT NULL default '0',
  karmaGood smallint(5) unsigned NOT NULL default '0',
  usertitle tinytext NOT NULL,
  notifyAnnouncements tinyint(4) NOT NULL default '1',
  notifyOnce tinyint(4) NOT NULL default '1',
  notifySendBody tinyint(4) NOT NULL default '0',
  notifyTypes tinyint(4) NOT NULL default '2',
  memberIP tinytext NOT NULL,
  memberIP2 tinytext NOT NULL,
  secretQuestion tinytext NOT NULL,
  secretAnswer varchar(64) NOT NULL default '',
  ID_THEME tinyint(4) unsigned NOT NULL default '0',
  is_activated tinyint(3) unsigned NOT NULL default '1',
  validation_code varchar(10) NOT NULL default '',
  ID_MSG_LAST_VISIT int(10) unsigned NOT NULL default '0',
  additionalGroups tinytext NOT NULL,
  smileySet varchar(48) NOT NULL default '',
  ID_POST_GROUP smallint(5) unsigned NOT NULL default '0',
  totalTimeLoggedIn int(10) unsigned NOT NULL default '0',
  passwordSalt varchar(5) NOT NULL default '',
  PRIMARY KEY (ID_MEMBER),
  KEY memberName (memberName(30)),
  KEY dateRegistered (dateRegistered),
  KEY ID_GROUP (ID_GROUP),
  KEY birthdate (birthdate),
  KEY posts (posts),
  KEY lastLogin (lastLogin),
  KEY lngfile (lngfile(30)),
  KEY ID_POST_GROUP (ID_POST_GROUP)
) ENGINE=MyISAM;

#
# Table structure for table `message_icons`
#

CREATE TABLE {$db_prefix}message_icons (
  ID_ICON smallint(5) unsigned NOT NULL auto_increment,
  title varchar(80) NOT NULL default '',
  filename varchar(80) NOT NULL default '',
  ID_BOARD mediumint(8) unsigned NOT NULL default 0,
  iconOrder smallint(5) unsigned NOT NULL default 0,
  PRIMARY KEY (ID_ICON),
  KEY ID_BOARD (ID_BOARD)
) ENGINE=MyISAM;

#
# Dumping data for table `message_icons`
#

# // !!! i18n
INSERT INTO {$db_prefix}message_icons
	(filename, title, iconOrder)
VALUES ('xx', 'Standard', '0'),
	('thumbup', 'Thumb Up', '1'),
	('thumbdown', 'Thumb Down', '2'),
	('exclamation', 'Exclamation point', '3'),
	('question', 'Question mark', '4'),
	('lamp', 'Lamp', '5'),
	('smiley', 'Smiley', '6'),
	('angry', 'Angry', '7'),
	('cheesy', 'Cheesy', '8'),
	('grin', 'Grin', '9'),
	('sad', 'Sad', '10'),
	('wink', 'Wink', '11');
# --------------------------------------------------------

#
# Table structure for table `messages`
#

CREATE TABLE {$db_prefix}messages (
  ID_MSG int(10) unsigned NOT NULL auto_increment,
  ID_TOPIC mediumint(8) unsigned NOT NULL default '0',
  ID_BOARD smallint(5) unsigned NOT NULL default '0',
  posterTime int(10) unsigned NOT NULL default '0',
  ID_MEMBER mediumint(8) unsigned NOT NULL default '0',
  ID_MSG_MODIFIED int(10) unsigned NOT NULL default '0',
  subject tinytext NOT NULL,
  posterName tinytext NOT NULL,
  posterEmail tinytext NOT NULL,
  posterIP tinytext NOT NULL,
  smileysEnabled tinyint(4) NOT NULL default '1',
  modifiedTime int(10) unsigned NOT NULL default '0',
  modifiedName tinytext NOT NULL,
  body text NOT NULL,
  icon varchar(16) NOT NULL default 'xx',
  PRIMARY KEY (ID_MSG),
  UNIQUE topic (ID_TOPIC, ID_MSG),
  UNIQUE ID_BOARD (ID_BOARD, ID_MSG),
  UNIQUE ID_MEMBER (ID_MEMBER, ID_MSG),
  KEY ipIndex (posterIP(15), ID_TOPIC),
  KEY participation (ID_MEMBER, ID_TOPIC),
  KEY showPosts (ID_MEMBER, ID_BOARD),
  KEY ID_TOPIC (ID_TOPIC)
) ENGINE=MyISAM;

#
# Dumping data for table `messages`
#

INSERT INTO {$db_prefix}messages
	(ID_MSG, ID_MSG_MODIFIED, ID_TOPIC, ID_BOARD, posterTime, subject, posterName, posterEmail, posterIP, modifiedName, body, icon)
VALUES (1, 1, 1, 1, UNIX_TIMESTAMP(), '{$default_topic_subject}', 'Simple Machines', 'info@simplemachines.org', '127.0.0.1', '', '{$default_topic_message}', 'xx');
# --------------------------------------------------------

#
# Table structure for table `moderators`
#

CREATE TABLE {$db_prefix}moderators (
  ID_BOARD smallint(5) unsigned NOT NULL default '0',
  ID_MEMBER mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY (ID_BOARD, ID_MEMBER)
) ENGINE=MyISAM;

#
# Table structure for table `package_servers`
#

CREATE TABLE {$db_prefix}package_servers (
  ID_SERVER smallint(5) unsigned NOT NULL auto_increment,
  name tinytext NOT NULL,
  url tinytext NOT NULL,
  PRIMARY KEY (ID_SERVER)
) ENGINE=MyISAM;

#
# Dumping data for table `package_servers`
#

INSERT INTO {$db_prefix}package_servers
	(name, url)
VALUES ('Simple Machines Third-party Mod Site', 'http://mods.simplemachines.org');
# --------------------------------------------------------

#
# Table structure for table `permissions`
#

CREATE TABLE {$db_prefix}permissions (
  ID_GROUP smallint(5) NOT NULL default '0',
  permission varchar(30) NOT NULL default '',
  addDeny tinyint(4) NOT NULL default '1',
  PRIMARY KEY (ID_GROUP, permission)
) ENGINE=MyISAM;

#
# Dumping data for table `permissions`
#

INSERT INTO {$db_prefix}permissions
	(ID_GROUP, permission)
VALUES (-1, 'search_posts'),
	(-1, 'calendar_view'),
	(-1, 'view_stats'),
	(-1, 'profile_view_any'),
	(0, 'view_mlist'),
	(0, 'search_posts'),
	(0, 'profile_view_own'),
	(0, 'profile_view_any'),
	(0, 'pm_read'),
	(0, 'pm_send'),
	(0, 'calendar_view'),
	(0, 'view_stats'),
	(0, 'who_view'),
	(0, 'profile_identity_own'),
	(0, 'profile_extra_own'),
	(0, 'profile_remove_own'),
	(0, 'profile_server_avatar'),
	(0, 'profile_upload_avatar'),
	(0, 'profile_remote_avatar'),
	(0, 'karma_edit'),
	(2, 'view_mlist'),
	(2, 'search_posts'),
	(2, 'profile_view_own'),
	(2, 'profile_view_any'),
	(2, 'pm_read'),
	(2, 'pm_send'),
	(2, 'calendar_view'),
	(2, 'view_stats'),
	(2, 'who_view'),
	(2, 'profile_identity_own'),
	(2, 'profile_extra_own'),
	(2, 'profile_remove_own'),
	(2, 'profile_server_avatar'),
	(2, 'profile_upload_avatar'),
	(2, 'profile_remote_avatar'),
	(2, 'profile_title_own'),
	(2, 'calendar_post'),
	(2, 'calendar_edit_any'),
	(2, 'karma_edit');
# --------------------------------------------------------

#
# Table structure for table `personal_messages`
#

CREATE TABLE {$db_prefix}personal_messages (
  ID_PM int(10) unsigned NOT NULL auto_increment,
  ID_MEMBER_FROM mediumint(8) unsigned NOT NULL default '0',
  deletedBySender tinyint(3) unsigned NOT NULL default '0',
  fromName tinytext NOT NULL,
  msgtime int(10) unsigned NOT NULL default '0',
  subject tinytext NOT NULL,
  body text NOT NULL,
  PRIMARY KEY (ID_PM),
  KEY ID_MEMBER (ID_MEMBER_FROM, deletedBySender),
  KEY msgtime (msgtime)
) ENGINE=MyISAM;

#
# Table structure for table `pm_recipients`
#

CREATE TABLE {$db_prefix}pm_recipients (
  ID_PM int(10) unsigned NOT NULL default '0',
  ID_MEMBER mediumint(8) unsigned NOT NULL default '0',
  labels varchar(60) NOT NULL default '-1',
  bcc tinyint(3) unsigned NOT NULL default '0',
  is_read tinyint(3) unsigned NOT NULL default '0',
  deleted tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY (ID_PM, ID_MEMBER),
  UNIQUE ID_MEMBER (ID_MEMBER, deleted, ID_PM)
) ENGINE=MyISAM;

#
# Table structure for table `polls`
#

CREATE TABLE {$db_prefix}polls (
  ID_POLL mediumint(8) unsigned NOT NULL auto_increment,
  question tinytext NOT NULL,
  votingLocked tinyint(1) NOT NULL default '0',
  maxVotes tinyint(3) unsigned NOT NULL default '1',
  expireTime int(10) unsigned NOT NULL default '0',
  hideResults tinyint(3) unsigned NOT NULL default '0',
  changeVote tinyint(3) unsigned NOT NULL default '0',
  ID_MEMBER mediumint(8) unsigned NOT NULL default '0',
  posterName tinytext NOT NULL,
  PRIMARY KEY (ID_POLL)
) ENGINE=MyISAM;

#
# Table structure for table `poll_choices`
#

CREATE TABLE {$db_prefix}poll_choices (
  ID_POLL mediumint(8) unsigned NOT NULL default '0',
  ID_CHOICE tinyint(3) unsigned NOT NULL default '0',
  label tinytext NOT NULL,
  votes smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY (ID_POLL, ID_CHOICE)
) ENGINE=MyISAM;

#
# Table structure for table `settings`
#

CREATE TABLE {$db_prefix}settings (
  variable tinytext NOT NULL,
  value text NOT NULL,
  PRIMARY KEY (variable(30))
) ENGINE=MyISAM;

#
# Dumping data for table `settings`
#

INSERT INTO {$db_prefix}settings
	(variable, value)
VALUES ('smfVersion', '{$smf_version}'),
	('news', '{$default_news}'),
	('compactTopicPagesContiguous', '5'),
	('compactTopicPagesEnable', '1'),
	('enableStickyTopics', '1'),
	('todayMod', '1'),
	('karmaMode', '0'),
	('karmaTimeRestrictAdmins', '1'),
	('enablePreviousNext', '1'),
	('pollMode', '1'),
	('enableVBStyleLogin', '1'),
	('enableCompressedOutput', '{$enableCompressedOutput}'),
	('karmaWaitTime', '1'),
	('karmaMinPosts', '0'),
	('karmaLabel', '{$default_karmaLabel}'),
	('karmaSmiteLabel', '{$default_karmaSmiteLabel}'),
	('karmaApplaudLabel', '{$default_karmaApplaudLabel}'),
	('attachmentSizeLimit', '128'),
	('attachmentPostLimit', '192'),
	('attachmentNumPerPostLimit', '4'),
	('attachmentDirSizeLimit', '10240'),
	('attachmentUploadDir', '{$boarddir}/attachments'),
	('attachmentExtensions', 'doc,gif,jpg,mpg,pdf,png,txt,zip'),
	('attachmentCheckExtensions', '0'),
	('attachmentShowImages', '1'),
	('attachmentEnable', '1'),
	('attachmentEncryptFilenames', '1'),
	('attachmentThumbnails', '1'),
	('attachmentThumbWidth', '150'),
	('attachmentThumbHeight', '150'),
	('censorIgnoreCase', '1'),
	('mostOnline', '1'),
	('mostOnlineToday', '1'),
	('mostDate', UNIX_TIMESTAMP()),
	('allow_disableAnnounce', '1'),
	('trackStats', '1'),
	('userLanguage', '1'),
	('titlesEnable', '1'),
	('topicSummaryPosts', '15'),
	('enableErrorLogging', '1'),
	('max_image_width', '0'),
	('max_image_height', '0'),
	('onlineEnable', '0'),
	('cal_holidaycolor', '000080'),
	('cal_bdaycolor', '920AC4'),
	('cal_eventcolor', '078907'),
	('cal_enabled', '0'),
	('cal_maxyear', '2010'),
	('cal_minyear', '2004'),
	('cal_daysaslink', '0'),
	('cal_defaultboard', ''),
	('cal_showeventsonindex', '0'),
	('cal_showbdaysonindex', '0'),
	('cal_showholidaysonindex', '0'),
	('cal_showeventsoncalendar', '1'),
	('cal_showbdaysoncalendar', '1'),
	('cal_showholidaysoncalendar', '1'),
	('cal_showweeknum', '0'),
	('cal_maxspan', '7'),
	('smtp_host', ''),
	('smtp_port', '25'),
	('smtp_username', ''),
	('smtp_password', ''),
	('mail_type', '0'),
	('timeLoadPageEnable', '0'),
	('totalTopics', '1'),
	('totalMessages', '1'),
	('simpleSearch', '0'),
	('censor_vulgar', ''),
	('censor_proper', ''),
	('enablePostHTML', '0'),
	('theme_allow', '1'),
	('theme_default', '1'),
	('theme_guests', '1'),
	('enableEmbeddedFlash', '0'),
	('xmlnews_enable', '1'),
	('xmlnews_maxlen', '255'),
	('hotTopicPosts', '15'),
	('hotTopicVeryPosts', '25'),
	('registration_method', '0'),
	('send_validation_onChange', '0'),
	('send_welcomeEmail', '1'),
	('allow_editDisplayName', '1'),
	('allow_hideOnline', '1'),
	('allow_hideEmail', '1'),
	('guest_hideContacts', '0'),
	('spamWaitTime', '5'),
	('pm_spam_settings', '10,5,20'),
	('reserveWord', '0'),
	('reserveCase', '1'),
	('reserveUser', '1'),
	('reserveName', '1'),
	('reserveNames', '{$default_reserved_names}'),
	('autoLinkUrls', '1'),
	('banLastUpdated', '0'),
	('smileys_dir', '{$boarddir}/Smileys'),
	('smileys_url', '{$boardurl}/Smileys'),
	('avatar_directory', '{$boarddir}/avatars'),
	('avatar_url', '{$boardurl}/avatars'),
	('avatar_max_height_external', '65'),
	('avatar_max_width_external', '65'),
	('avatar_action_too_large', 'option_html_resize'),
	('avatar_max_height_upload', '65'),
	('avatar_max_width_upload', '65'),
	('avatar_resize_upload', '1'),
	('avatar_download_png', '1'),
	('failed_login_threshold', '3'),
	('oldTopicDays', '120'),
	('edit_wait_time', '90'),
	('edit_disable_time', '0'),
	('autoFixDatabase', '1'),
	('allow_guestAccess', '1'),
	('time_format', '{$default_time_format}'),
	('number_format', '1234.00'),
	('enableBBC', '1'),
	('max_messageLength', '20000'),
	('max_signatureLength', '300'),
	('autoOptDatabase', '7'),
	('autoOptMaxOnline', '0'),
	('autoOptLastOpt', '0'),
	('defaultMaxMessages', '15'),
	('defaultMaxTopics', '20'),
	('defaultMaxMembers', '30'),
	('enableParticipation', '1'),
	('recycle_enable', '0'),
	('recycle_board', '0'),
	('maxMsgID', '1'),
	('enableAllMessages', '0'),
	('fixLongWords', '0'),
	('knownThemes', '1,2,3'),
	('who_enabled', '1'),
	('time_offset', '0'),
	('cookieTime', '60'),
	('lastActive', '15'),
	('smiley_sets_known', 'default,classic'),
	('smiley_sets_names', '{$default_smileyset_name}\n{$default_classic_smileyset_name}'),
	('smiley_sets_default', 'default'),
	('cal_days_for_index', '7'),
	('requireAgreement', '1'),
	('unapprovedMembers', '0'),
	('default_personalText', ''),
	('package_make_backups', '1'),
	('databaseSession_enable', '{$databaseSession_enable}'),
	('databaseSession_loose', '1'),
	('databaseSession_lifetime', '2880'),
	('search_cache_size', '50'),
	('search_results_per_page', '30'),
	('search_weight_frequency', '30'),
	('search_weight_age', '25'),
	('search_weight_length', '20'),
	('search_weight_subject', '15'),
	('search_weight_first_message', '10'),
	('search_max_results', '1200'),
	('permission_enable_deny', '0'),
	('permission_enable_postgroups', '0'),
	('permission_enable_by_board', '0');
# --------------------------------------------------------

#
# Table structure for table `sessions`
#

CREATE TABLE {$db_prefix}sessions (
  session_id char(32) NOT NULL,
  last_update int(10) unsigned NOT NULL,
  data text NOT NULL,
  PRIMARY KEY (session_id)
) ENGINE=MyISAM;

#
# Table structure for table `smileys`
#

CREATE TABLE {$db_prefix}smileys (
  ID_SMILEY smallint(5) unsigned NOT NULL auto_increment,
  code varchar(30) NOT NULL default '',
  filename varchar(48) NOT NULL default '',
  description varchar(80) NOT NULL default '',
  smileyRow tinyint(4) unsigned NOT NULL default '0',
  smileyOrder smallint(5) unsigned NOT NULL default '0',
  hidden tinyint(4) unsigned NOT NULL default '0',
  PRIMARY KEY (ID_SMILEY)
) ENGINE=MyISAM;

#
# Dumping data for table `smileys`
#

INSERT INTO {$db_prefix}smileys
	(code, filename, description, smileyOrder, hidden)
VALUES (':)', 'smiley.gif', '{$default_smiley_smiley}', 0, 0),
	(';)', 'wink.gif', '{$default_wink_smiley}', 1, 0),
	(':D', 'cheesy.gif', '{$default_cheesy_smiley}', 2, 0),
	(';D', 'grin.gif', '{$default_grin_smiley}', 3, 0),
	('>:(', 'angry.gif', '{$default_angry_smiley}', 4, 0),
	(':(', 'sad.gif', '{$default_sad_smiley}', 5, 0),
	(':o', 'shocked.gif', '{$default_shocked_smiley}', 6, 0),
	('8)', 'cool.gif', '{$default_cool_smiley}', 7, 0),
	('???', 'huh.gif', '{$default_huh_smiley}', 8, 0),
	('::)', 'rolleyes.gif', '{$default_roll_eyes_smiley}', 9, 0),
	(':P', 'tongue.gif', '{$default_tongue_smiley}', 10, 0),
	(':-[', 'embarrassed.gif', '{$default_embarrassed_smiley}', 11, 0),
	(':-X', 'lipsrsealed.gif', '{$default_lips_sealed_smiley}', 12, 0),
	(':-\\', 'undecided.gif', '{$default_undecided_smiley}', 13, 0),
	(':-*', 'kiss.gif', '{$default_kiss_smiley}', 14, 0),
	(':\'(', 'cry.gif', '{$default_cry_smiley}', 15, 0),
	('>:D', 'evil.gif', '{$default_evil_smiley}', 16, 1),
	('^-^', 'azn.gif', '{$default_azn_smiley}', 17, 1),
	('O0', 'afro.gif', '{$default_afro_smiley}', 18, 1);
# --------------------------------------------------------

#
# Table structure for table `themes`
#

CREATE TABLE {$db_prefix}themes (
  ID_MEMBER mediumint(8) NOT NULL default '0',
  ID_THEME tinyint(4) unsigned NOT NULL default '1',
  variable tinytext NOT NULL,
  value text NOT NULL,
  PRIMARY KEY (ID_THEME, ID_MEMBER, variable(30)),
  KEY ID_MEMBER (ID_MEMBER)
) ENGINE=MyISAM;

#
# Dumping data for table `themes`
#

INSERT INTO {$db_prefix}themes
	(ID_THEME, variable, value)
VALUES (1, 'name', '{$default_theme_name}'),
	(1, 'theme_url', '{$boardurl}/Themes/default'),
	(1, 'images_url', '{$boardurl}/Themes/default/images'),
	(1, 'theme_dir', '{$boarddir}/Themes/default'),
	(1, 'show_bbc', '1'),
	(1, 'show_latest_member', '1'),
	(1, 'show_modify', '1'),
	(1, 'show_user_images', '1'),
	(1, 'show_blurb', '1'),
	(1, 'show_gender', '0'),
	(1, 'show_newsfader', '0'),
	(1, 'number_recent_posts', '0'),
	(1, 'show_member_bar', '1'),
	(1, 'linktree_link', '1'),
	(1, 'show_profile_buttons', '1'),
	(1, 'show_mark_read', '1'),
	(1, 'show_sp1_info', '1'),
	(1, 'linktree_inline', '0'),
	(1, 'show_board_desc', '1'),
	(1, 'newsfader_time', '5000'),
	(1, 'allow_no_censored', '0'),
	(1, 'additional_options_collapsable', '1'),
	(1, 'use_image_buttons', '1'),
	(1, 'enable_news', '1'),
	(2, 'name', '{$default_classic_theme_name}'),
	(2, 'theme_url', '{$boardurl}/Themes/classic'),
	(2, 'images_url', '{$boardurl}/Themes/classic/images'),
	(2, 'theme_dir', '{$boarddir}/Themes/classic'),
	(3, 'name', '{$default_babylon_theme_name}'),
	(3, 'theme_url', '{$boardurl}/Themes/babylon'),
	(3, 'images_url', '{$boardurl}/Themes/babylon/images'),
	(3, 'theme_dir', '{$boarddir}/Themes/babylon');
# --------------------------------------------------------

#
# Table structure for table `topics`
#

CREATE TABLE {$db_prefix}topics (
  ID_TOPIC mediumint(8) unsigned NOT NULL auto_increment,
  isSticky tinyint(4) NOT NULL default '0',
  ID_BOARD smallint(5) unsigned NOT NULL default '0',
  ID_FIRST_MSG int(10) unsigned NOT NULL default '0',
  ID_LAST_MSG int(10) unsigned NOT NULL default '0',
  ID_MEMBER_STARTED mediumint(8) unsigned NOT NULL default '0',
  ID_MEMBER_UPDATED mediumint(8) unsigned NOT NULL default '0',
  ID_POLL mediumint(8) unsigned NOT NULL default '0',
  numReplies int(10) unsigned NOT NULL default '0',
  numViews int(10) unsigned NOT NULL default '0',
  locked tinyint(4) NOT NULL default '0',
  PRIMARY KEY (ID_TOPIC),
  UNIQUE lastMessage (ID_LAST_MSG, ID_BOARD),
  UNIQUE firstMessage (ID_FIRST_MSG, ID_BOARD),
  UNIQUE poll (ID_POLL, ID_TOPIC),
  KEY isSticky (isSticky),
  KEY ID_BOARD (ID_BOARD)
) ENGINE=MyISAM;

#
# Dumping data for table `topics`
#

INSERT INTO {$db_prefix}topics
	(ID_TOPIC, ID_BOARD, ID_FIRST_MSG, ID_LAST_MSG, ID_MEMBER_STARTED, ID_MEMBER_UPDATED)
VALUES (1, 1, 1, 1, 0, 0);
# --------------------------------------------------------