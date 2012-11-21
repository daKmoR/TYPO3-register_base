#
# Table structure for table 'fe_users'
#
CREATE TABLE fe_users (
	mailhash varchar(60) DEFAULT '',
	gtc tinyint(4) unsigned DEFAULT '0' NOT NULL,
);