<?php

	/**
	* The AJAX callback function of the plugin.
	*
	* @link       https://www.seanrsullivan.com
	* @since      2.0.0
	*
	* @package    Yotpo_Reviews
	* @subpackage Yotpo_Reviews/admin
	*/

	// Load WP and required classes
	require_once $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php'; // Load WP functions as needed
	include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-yotpo-reviews-crons.php'; // Load the Cron class
	include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-yotpo-reviews-admin.php'; // Load the Admin class


	// Check for the type of AJAX call
	if ( isset( $_GET['run_type'] ) ) : // If run type query

		$ajax_call           = $_GET['run_type'];
		$yotpo_reviews_crons = new Yotpo_Reviews_Crons();
		$ajax_return         = $yotpo_reviews_crons->schedule_cron( $ajax_call );

	elseif ( isset( $_GET['table'] ) ) : // If clearing log table

		$ajax_call   = $_GET['table'];
		$ajax_return = Yotpo_Reviews_Admin::clear_logs_table();

	endif;

	// Put callback info into an array
	$ajax_info = array(
		'ajax_msg'  => $ajax_return,
		'ajax_type' => $ajax_call
	);

	// Make it JSON to send back to the AJAX call
	echo json_encode( $ajax_info );




