<?php

/**
 * The webhook callback function of the plugin.
 *
 * @link       https://www.seanrsullivan.com
 * @since      1.0.0
 *
 * @package    Yotpo_Reviews
 * @subpackage Yotpo_Reviews/admin
 */

require_once $_SERVER["DOCUMENT_ROOT"] . '/wp-load.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-yotpo-reviews-webhook-functions.php';

$run_webhook = new Yotpo_Reviews_Webhook_Functions();
$run = $run_webhook->execute_webhook('webhook');
