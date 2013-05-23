#
# Table structure for table 'fe_users'
#
CREATE TABLE fe_users (
	mailhash varchar(60) DEFAULT '',
	gtc tinyint(4) unsigned DEFAULT '0' NOT NULL,
	mailchimp_groups varchar(400) DEFAULT '',
	newsletters tinyint(4) unsigned DEFAULT '0' NOT NULL,
);

#
# Table structure for table 'fe_groups'
#
CREATE TABLE fe_groups (
	show_in_frontend tinyint(4) unsigned DEFAULT '0' NOT NULL,
);

/*
If you want to use it with direct_mail categories you need this as well...

CREATE TABLE fe_users (
	module_sys_dmail_newsletter tinyint(4) unsigned DEFAULT '0' NOT NULL,
	module_sys_dmail_html tinyint(4) unsigned DEFAULT '0' NOT NULL,
);

#
# Table structure for table 'sys_dmail_category'
#
CREATE TABLE sys_dmail_category (
	description varchar(1000) DEFAULT '' NOT NULL,
);
 */