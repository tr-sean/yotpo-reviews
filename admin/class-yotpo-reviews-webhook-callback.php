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

if ( isset( $_GET['type'] ) ) :

    require_once $_SERVER["DOCUMENT_ROOT"] . '/wp-load.php';
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-yotpo-reviews-webhook-functions.php';

    $run_webhooks = new Yotpo_Reviews_Webhook_Functions();

    if ( $_GET['type'] == 'wc_webhook' ) :

        // Run order import webhook
        $data = file_get_contents("php://input");
        if ( $data & !empty( $data ) ) :
            $run_wc = $run_webhooks->execute_wc_webhook($data);
        endif;

    elseif ( $_GET['type'] == 'yp_webhook' ) :

        // Run review import webhook
        $run_yp = $run_webhooks->execute_yp_webhook('webhook');

    endif;

else :

    die('Cannot access directly.');

endif;
