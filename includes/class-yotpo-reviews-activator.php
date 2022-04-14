<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.seanrsullivan.com
 * @since      1.0.0
 *
 * @package    Yotpo_Reviews
 * @subpackage Yotpo_Reviews/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Yotpo_Reviews
 * @subpackage Yotpo_Reviews/includes
 * @author     Sean Sullivan
 */
class Yotpo_Reviews_Activator {

	/**
	 * Global Table Name (Import Logs)
	 *
	 * @since     1.0.0
	 * @return    string    New table name
	 */
	public static function register_logs_table() {
	    return 'wp_yotpo_review_log';
	}



	/**
	 * Creates the import log table
	 *
	 * @since     1.0.0
	 * @return    void
	 */
	public static function create_logs_table(){

		global $wpdb;
		global $charset_collate;

	    // Call this manually as we may have missed the init hook
	    $db_table = Yotpo_Reviews_Activator::register_logs_table();

	    $cols = array();
	    $cols[] = 'id INT(5) UNSIGNED NULL DEFAULT NULL AUTO_INCREMENT';
	    $cols[] = 'date VARCHAR(255)';
	    $cols[] = 'total INT(3)';
	    $cols[] = 'imported INT(3)';
	    $cols[] = 'skipped_exists INT(3)';
	    $cols[] = 'skipped_none INT(3)';
	    $cols[] = 'deleted INT(3)';
	    $cols[] = 'method VARCHAR(255)';

	    $create_cols = join(",", $cols);

	    // Log Table
	    $sql_create_user_table = "CREATE TABLE IF NOT EXISTS $db_table (
	        $create_cols,
	        PRIMARY KEY  (id)
	        ) $charset_collate; ";
	    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	    dbDelta($sql_create_user_table);
	}

}
