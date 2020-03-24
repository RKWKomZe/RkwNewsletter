#
# Table structure for table 'tx_rkwnewsletter_domain_model_newsletter'
#
CREATE TABLE tx_rkwnewsletter_domain_model_newsletter (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	name varchar(255) DEFAULT '' NOT NULL,
	introduction text NOT NULL,
	issue_title varchar(255) DEFAULT '' NOT NULL,
	sender_name varchar(255) DEFAULT '' NOT NULL,
	sender_mail varchar(255) DEFAULT '' NOT NULL,
	reply_name varchar(255) DEFAULT '' NOT NULL,
	reply_mail varchar(255) DEFAULT '' NOT NULL,
	return_path varchar(255) DEFAULT '' NOT NULL,
	priority int(11) DEFAULT '0' NOT NULL,
    type int(11) DEFAULT '0' NOT NULL,
	template varchar(255) DEFAULT '' NOT NULL,
	format tinyint(4) DEFAULT '0' NOT NULL,
	settings_page int(11) DEFAULT '0' NOT NULL,
	rythm tinyint(4) DEFAULT '0' NOT NULL,
	approval varchar(255) DEFAULT '' NOT NULL,
	usergroup varchar(255) DEFAULT '' NOT NULL,
	topic varchar(255) DEFAULT '' NOT NULL,
	issue varchar(255) DEFAULT '' NOT NULL,
	last_sent_tstamp int(11) DEFAULT '0' NOT NULL,
	last_issue_tstamp int(11) DEFAULT '0' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
    KEY language (l10n_parent,sys_language_uid)

);

#
# Table structure for table 'tx_rkwnewsletter_domain_model_topic'
#
CREATE TABLE tx_rkwnewsletter_domain_model_topic (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	name varchar(255) DEFAULT '' NOT NULL,
	short_description varchar(255) DEFAULT '' NOT NULL,
	approval_stage1 varchar(255) DEFAULT '' NOT NULL,
	approval_stage2 varchar(255) DEFAULT '' NOT NULL,
	container_page int(11) unsigned DEFAULT '0' NOT NULL,
	primary_color varchar(255) DEFAULT '' NOT NULL,
	primary_color_editorial varchar(255) DEFAULT '' NOT NULL,
	secondary_color varchar(255) DEFAULT '' NOT NULL,
	secondary_color_editorial varchar(255) DEFAULT '' NOT NULL,
	is_special int(1) unsigned DEFAULT '0' NOT NULL,
	newsletter int(11) unsigned DEFAULT '0' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),
);

#
# Table structure for table 'tx_rkwnewsletter_domain_model_approval'
#
CREATE TABLE tx_rkwnewsletter_domain_model_approval (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	topic int(11) unsigned DEFAULT '0',
	issue int(11) unsigned DEFAULT '0',
	page int(11) unsigned DEFAULT '0',

	allowed_by_user_stage1 varchar(255) DEFAULT '' NOT NULL,
	allowed_by_user_stage2 varchar(255) DEFAULT '' NOT NULL,
	allowed_tstamp_stage1 int(11) unsigned DEFAULT '0',
	allowed_tstamp_stage2 int(11) unsigned DEFAULT '0',
	sent_info_tstamp_stage1 int(11) unsigned DEFAULT '0',
	sent_info_tstamp_stage2 int(11) unsigned DEFAULT '0',
	sent_reminder_tstamp_stage1 int(11) unsigned DEFAULT '0',
	sent_reminder_tstamp_stage2 int(11) unsigned DEFAULT '0',

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),

);


#
# Table structure for table 'tx_rkwnewsletter_domain_model_issue'
#
CREATE TABLE tx_rkwnewsletter_domain_model_issue (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	title varchar(255) DEFAULT '' NOT NULL,
	status tinyint(4) DEFAULT '0' NOT NULL,
	newsletter int(11) unsigned DEFAULT '0',
	pages int(11) unsigned DEFAULT '0',
	approvals int(11) unsigned DEFAULT '0',
	recipients text NOT NULL,
	queue_mail int(11) unsigned DEFAULT '0',

	info_tstamp int(11) unsigned DEFAULT '0',
	reminder_tstamp int(11) unsigned DEFAULT '0',
	release_tstamp int(11) unsigned DEFAULT '0',
	sent_tstamp int(11) unsigned DEFAULT '0',

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),

);


#
# Table structure for table 'fe_users'
#
CREATE TABLE fe_users (
  tx_rkwnewsletter_priority tinyint(4) DEFAULT '0' NOT NULL,
  tx_rkwnewsletter_subscription varchar(255) DEFAULT '' NOT NULL,
  tx_rkwnewsletter_hash varchar(255) DEFAULT '' NOT NULL,
);


#
# Table structure for table 'pages'
#
CREATE TABLE pages (
  tx_rkwnewsletter_newsletter int(11) DEFAULT '0' NOT NULL,
  tx_rkwnewsletter_topic int(11) DEFAULT '0' NOT NULL,
  tx_rkwnewsletter_issue int(11) DEFAULT '0' NOT NULL,

  tx_rkwnewsletter_exclude tinyint(4) unsigned DEFAULT '0' NOT NULL,
  tx_rkwnewsletter_teaser_heading varchar(255) DEFAULT '' NOT NULL,
  tx_rkwnewsletter_teaser_text text NOT NULL,
  tx_rkwnewsletter_teaser_image varchar(255) DEFAULT '' NOT NULL,
  tx_rkwnewsletter_teaser_link varchar(255) DEFAULT '' NOT NULL,
  tx_rkwnewsletter_include_tstamp int(11) DEFAULT '0' NOT NULL,
);

#
# Table structure for table 'pages_language_overlay'
#
CREATE TABLE pages_language_overlay (
  tx_rkwnewsletter_teaser_heading varchar(255) DEFAULT '' NOT NULL,
  tx_rkwnewsletter_teaser_text varchar(255) DEFAULT '' NOT NULL,
  tx_rkwnewsletter_teaser_image varchar(255) DEFAULT '' NOT NULL,
  tx_rkwnewsletter_teaser_link varchar(255) DEFAULT '' NOT NULL,
);

#
# Table structure for table 'tt_content'
#
CREATE TABLE tt_content (

	tx_rkwnewsletter_authors varchar(255) DEFAULT '' NOT NULL,
	tx_rkwnewsletter_is_editorial tinyint(4) DEFAULT '0' NOT NULL,

);

