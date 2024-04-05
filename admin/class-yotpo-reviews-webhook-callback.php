<?php

/**
 * The webhook callback function of the plugin.
 *
 * @link       https://www.seanrsullivan.com
 * @since      2.0.0     Remove YP webhook call
 * @since      1.0.0
 *
 * @package    Yotpo_Reviews
 * @subpackage Yotpo_Reviews/admin
 */

// Load WP and required classes
require_once $_SERVER["DOCUMENT_ROOT"] . '/wp-load.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-yotpo-reviews-webhook-functions.php';

$run_webhooks = new Yotpo_Reviews_Webhook_Functions();

if ( isset( $_GET['type'] ) && $_GET['type'] == 'wc_webhook' ) :

	// Run order import webhook
	$data   = file_get_contents("php://input");
	$run_wc = $run_webhooks->execute_wc_webhook($data);

endif;

