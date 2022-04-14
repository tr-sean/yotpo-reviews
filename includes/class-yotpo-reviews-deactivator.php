<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://www.trinityroad.com
 * @since      1.0.0
 *
 * @package    Yotpo_Reviews
 * @subpackage Yotpo_Reviews/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Yotpo_Reviews
 * @subpackage Yotpo_Reviews/includes
 * @author     Sean Sullivan <ssullivan@trinityroad.com>
 */
class Yotpo_Reviews_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function get_yotpo_webhook() {

        // Get all the keys
        $options = get_option( 'yotpo_reviews_settings' );
        $app_key = $options['yotpo_app_key'] ?? '';
        $auth_token = new Yotpo_Reviews_Import();
        $auth_token = $auth_token->yotpo_auth_token();

        $curl = curl_init();
        $url = 'https://api.yotpo.com/apps/' . $app_key . '/webhooks?utoken=' . $auth_token['access_token'];
        curl_setopt_array($curl, array(
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'GET',
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $webhooks = $response['response']['webhooks'];
        $webhook_url = YPR_URL . '/admin/class-yotpo-reviews-webhook-callback.php';
        $key = array_search($webhook_url, array_column($response['response']['webhooks'], 'url'));

        echo $webhooks[$key]['id'];

	}

}
