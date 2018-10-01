<?php // (C) Copyright Bobbing Wide 2017

/*
Plugin Name: wpdb-alt
Plugin URI: https://www.oik-plugins.com/oik-plugins/wpdb-alt
Description: Alternative database
Version: 0.0.0
Author: bobbingwide
Author URI: https://www.oik-plugins.com/author/bobbingwide
Text Domain: wpdb-alt
Domain Path: /languages/
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

    Copyright 2017 Bobbing Wide (email : herb@bobbingwide.com )

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2,
    as published by the Free Software Foundation.

    You may NOT assume that you can use any other version of the GPL.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    The license for this software can likely be found here:
    http://www.gnu.org/licenses/gpl-2.0.html

*/

if ( file_exists( ABSPATH .  "wp-config-alt.php" ) ) {
  require_once( "wp-config-alt.php" );
} else { 

	/** Alternative DB name */
	define('DB_NAME_ALT', 'oikplug_local');

	/** MySQL database username */
	define('DB_USER_ALT', 'oikplug_local');

	/** MySQL database password */
	define('DB_PASSWORD_ALT', 'oikplug_local');

	/** MySQL hostname */
	define('DB_HOST_ALT', 'localhost');

	/** Database Charset to use in creating database tables. */
	define('DB_CHARSET_ALT', 'utf8');

	/** The Database Collate type. Don't change this if in doubt. */
	define('DB_COLLATE_ALT', '');
	/**
	 * WordPress Database Table prefix.
	 *
	 * You can have multiple installations in one database if you give each a unique
	 * prefix. Only numbers, letters, and underscores please!
	 */
	$table_prefix_alt  = 'wp_';

}

global $wpdb_saved;
global $wpdb_alt;


function require_wp_db_alt() {
	global $wpdb_saved;
	global $wpdb_alt;
	$wpdb_saved = null;
	$wpdb_alt = new wpdb( DB_USER_ALT, DB_PASSWORD_ALT, DB_NAME_ALT, DB_HOST_ALT );
	wpdb_alt();
	wp_set_wpdb_vars();
	wpdb_alt( false );
	
	print_r( $wpdb_alt );
}


/** 
 * Toggle the $wpdb instance between normal and alt
 * 
 * @TODO If we were using blog IDs then 1 would be the primary one and any other value would be the alternative
 * 
 * 
 * @param bool $alt 
 */
function wpdb_alt( $alt=true ){
	global $wpdb_saved;
	global $wpdb;
	global $wpdb_alt;
	
	if ( $alt ) {
		if ( $wpdb_saved != $wpdb ) {
			$wpdb_saved = $wpdb;
			$wpdb = $wpdb_alt;
		} else {
			echo "Already switched";
		}
	} else {
		if ( $wpdb == $wpdb_alt ) {
			$wpdb = $wpdb_saved;
		} else {
			echo "Already reset";
		}
	}
	return( $wpdb );
}

function report_wpdb() {
	global $wpdb;
	print_r( $wpdb );
}

report_wpdb();

require_wp_db_alt();


	
	




