<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.seanrsullivan.com
 * @since      1.0.0
 *
 * @package    Yotpo_Reviews
 * @subpackage Yotpo_Reviews/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Yotpo_Reviews
 * @subpackage Yotpo_Reviews/admin
 * @author     Sean Sullivan
 */

class Yotpo_Reviews_Webhook_Functions {

    /**
     * Creates webhook on Yotpo
     *
     * @since     1.0.0
     * @return    void
     */
    public function create_yotpo_webhook() {

        // Get all the keys
        $options = get_option( 'yotpo_reviews_settings' );
        $app_key = $options['yotpo_app_key'] ?? '';
        $auth_token = new Yotpo_Reviews_Import();
        $auth_token = $auth_token->yotpo_auth_token();

        $webhook_data = array(
            'url' => plugin_dir_url(__FILE__) . 'class-yotpo-reviews-webhook-callback.php',
            'event_name' => 'review_create'
        );
        $webhook_data = json_encode($webhook_data);

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
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => $webhook_data,
            CURLOPT_HTTPHEADER     => array(
                'Accept: application/json',
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $response = json_decode($response, true);

        return $response;
    }


	/**
     * Webhook execution
     *
     * Executes the webhook callback and inserts a row into the log table.
     *
     * @since     1.0.0
     * @param     string     $method               The method of import done.
     * @param     array      $webhook_response     Response from the webhook.
     * @return    void
     */
    public function execute_webhook( $method ) {

		global $wpdb;

		$create_reviews = new Yotpo_Reviews_Import();
		$create_reviews = $create_reviews->create_reviews();

		list( $data, $total_count, $response ) = $create_reviews;
        date_default_timezone_set('America/New_York');
		$date = date('n/j/y g:i a');

		$created 		 = !empty( $response['create'] ) ? $response['create'] : array(); // Avoid errors
		$deleted 		 = !empty( $response['delete'] ) ? $response['delete'] : array(); // Avoid errors
		$response_count  = count($created); // Only reviews returned
		$deleted_count   = count($deleted); // Deleted reviews
		$duplicate_count = Yotpo_Reviews_Admin::get_duplicate_count($created, 'woocommerce_rest_comment_duplicate'); // Get duplicate count
		$imported_count  = $response_count - $duplicate_count; // How much was actually imported
		$skipped 		 = $total_count - $response_count; // Total reviews retrieved

        // Update methods to be "words"
		$method_type = ucwords( str_replace('_', ' ', $method) );

        // Insert log into database
		$wpdb->query(
		   $wpdb->prepare(
		      "INSERT INTO `wp_yotpo_review_log`
		      ( id, date, total, imported, skipped_exists, skipped_none, deleted, method )
		      VALUES ( %d, %s, %d, %d, %d, %d, %d, %s )",
		      '', $date, $total_count, $imported_count, $duplicate_count, $skipped, $deleted_count, $method_type
		   )
		);

        // If manually done, return the data back to the form result.
		if ( $method == 'manually_run' || $method == 'first_time' ) return $create_reviews;

	}


}
