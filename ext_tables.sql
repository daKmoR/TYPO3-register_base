#
# Table structure for table 'fe_users'
#
CREATE TABLE fe_users (
	mailhash varchar(60) DEFAULT '',
	gtc tinyint(4) unsigned DEFAULT '0' NOT NULL,
	module_sys_dmail_newsletter tinyint(4) unsigned DEFAULT '0' NOT NULL,
	module_sys_dmail_html tinyint(4) unsigned DEFAULT '0' NOT NULL,
);

#
# Table structure for table 'sys_dmail_category'
#
CREATE TABLE sys_dmail_category (
	description varchar(1000) DEFAULT '' NOT NULL,
);