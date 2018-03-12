<?php
	//global $wpdb;
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	//table prefix
	$table_prefix = "bd_ppt_";

	// create tables
	$sqldb = array();

	$sqldb[] = "CREATE TABLE {$table_prefix}packages (
				  id int(5) unsigned NOT NULL AUTO_INCREMENT,
				  icon varchar(50) NOT NULL,
				  title varchar(50) NOT NULL,
				  subtitle varchar(255) DEFAULT NULL,
				  price_text varchar(50) NOT NULL,
				  is_recommended int(1) DEFAULT 0,
				  show_order int(5) NOT NULL,
				  created int(10) unsigned NOT NULL,
				  updated int(10) unsigned NOT NULL,
				  PRIMARY KEY  (id)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";	

	$sqldb[] = "CREATE TABLE {$table_prefix}package_features (
				  id int(5) unsigned NOT NULL AUTO_INCREMENT,
				  package_id int(5) unsigned NOT NULL,
				  name varchar(100) DEFAULT NULL,
				  category int(3) unsigned NOT NULL,
				  created int(10) unsigned NOT NULL,
				  updated int(10) unsigned NOT NULL,
				  PRIMARY KEY  (id)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

	$sqldb[] = "CREATE TABLE {$table_prefix}addons (
				  id int(5) unsigned NOT NULL AUTO_INCREMENT,
				  name varchar(100) DEFAULT NULL,
				  created int(10) unsigned NOT NULL,
				  updated int(10) unsigned NOT NULL,
				  PRIMARY KEY  (id)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

	$sqldb[] = "CREATE TABLE {$table_prefix}addon_features (
				  id int(5) unsigned NOT NULL AUTO_INCREMENT,
				  addon_id int(5) unsigned NOT NULL,
				  name varchar(100) DEFAULT NULL,
				  price_text varchar(50) NOT NULL,
				  description longtext DEFAULT NULL,
				  created int(10) unsigned NOT NULL,
				  updated int(10) unsigned NOT NULL,
				  PRIMARY KEY  (id)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

	foreach ($sqldb as $tabledb)
	{
		dbDelta( $tabledb );
	}