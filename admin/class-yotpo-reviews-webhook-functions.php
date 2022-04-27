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
    public function execute_yp_webhook( $method ) {

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




    /**
     * Creates webhook on WooCommerce
     *
     * This is done so orders can be sent off to Yotpo
     *
     * @since     1.5.0
     * @return    void
     */
    public function create_wc_webhook() {

    	// Let's name this hook
    	$hook_name = 'Yotpo Order Import';

    	// Check to see if webhook exists
    	global $wpdb;
	    $results = $wpdb->get_results( "SELECT name FROM {$wpdb->prefix}wc_webhooks" );
	    foreach( $results as $result ) :
	        if ( strpos($result->name, $hook_name) !== false ) return;
	    endforeach;



        // Get all the keys
        $key = defined('WC_CK') ? WC_CK : define('WC_CK', '');
        $secret = defined('WC_SK') ? WC_SK : define('WC_SK', '');

        $webhook_data = array(
            'name'         => $hook_name,
            'topic'        => 'order.updated',
            'delivery_url' => plugin_dir_url(__FILE__) . 'class-yotpo-reviews-webhook-callback.php?type=wc_webhook'
        );
        $webhook_data = json_encode($webhook_data);


        $curl = curl_init();
        $url = get_bloginfo('url') . '/wp-json/wc/v3/webhooks?consumer_key=' . $key . '&consumer_secret=' . $secret;
        curl_setopt_array($curl, array(
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_USERAGENT      => $_SERVER['HTTP_USER_AGENT'] ?? 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:96.0) Gecko/20100101 Firefox/96.0',
            CURLOPT_REFERER        => get_bloginfo('url'),
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
     * Executes webhook on WooCommerce
     *
     * This is done so orders can be sent off to Yotpo
     *
     * @since     1.5.0
     * @return    void
     */
    public function execute_wc_webhook( $data ) {

    	// Don't continue if data is empty
    	if ( !$data || empty($data) ) return;

    	// Turn order data into array
    	$order = json_decode(stripslashes($data));

    	// Only send off order if completed
    	if ( $order->status !== 'completed' ) return;

    	// Line item
    	foreach( $order->line_items as $item ) :

    		$product = wc_get_product($item->product_id);
    		$sku = $product->get_sku();

    		$line_items[] = array(
    			'external_product_id' => $sku,
    			'quantity' 			  => $item->quantity,
    			'total_price' 		  => $item->total,
    			'subtotal_price' 	  => $item->subtotal
    		);

    	endforeach;

    	// Customer info
    	$customer_id = $order->customer_id ?? $order->id;
    	$customer_billing = $order->billing;
    	$customer_shipping = $order->shipping ?? '';

    	// Put all the order info together
    	$post_fields = array(
    		'order' => array(
			    'external_id' 	 => $order->id,
			    'order_date'  	 => $order->date_created,
			    'total_price' 	 => $order->total,
			    'currency'    	 => 'USD',
			    'payment_method' => $order->payment_method,
			    'customer'		 => array(
			    	'external_id' => $customer_id,
			    	'first_name'  => $customer_billing->first_name,
			    	'last_name'   => $customer_billing->last_name,
			    	'email'  	  => $customer_billing->email,
			    	'phone'  	  => $customer_billing->phone,
			    ),
			    'billing_address' => array(
			    	'address1' 		=> $customer_billing->address_1,
			    	'address2' 		=> $customer_billing->address_2,
			    	'city' 			=> $customer_billing->city,
			    	'company' 		=> $customer_billing->company,
			    	'state' 		=> $customer_billing->state,
			    	'zip' 			=> $customer_billing->postcode,
			    	'province_code' => $customer_billing->state,
			    	'country_code'  => $customer_billing->country,
			    	'phone' 		=> $customer_billing->phone
			    ),
			    'shipping_address' => array(
			    	'address1' 		=> $customer_shipping->address_1,
			    	'address2' 		=> $customer_shipping->address_2,
			    	'city' 			=> $customer_shipping->city,
			    	'company' 		=> $customer_shipping->company,
			    	'state' 		=> $customer_shipping->state,
			    	'zip' 			=> $customer_shipping->postcode,
			    	'province_code' => $customer_shipping->state,
			    	'country_code'  => $customer_shipping->country,
			    ),
			    'line_items' => $line_items
			)
    	);
    	$post_fields = json_encode($post_fields);

    	// Get all the Yotpo keys
        $options = get_option( 'yotpo_reviews_settings' );
        $app_key = $options['yotpo_app_key'] ?? '';
        $auth_token = new Yotpo_Reviews_Import();
        $auth_token = $auth_token->yotpo_auth_token('store');

        // Send order off to Yotpo
        $curl = curl_init();
		$url = 'https://api.yotpo.com/core/v3/stores/' . $app_key . '/orders';
		curl_setopt_array($curl, array(
			CURLOPT_URL 		   => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING 	   => '',
			CURLOPT_MAXREDIRS 	   => 10,
			CURLOPT_TIMEOUT 	   => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST  => 'POST',
			CURLOPT_POSTFIELDS     => $post_fields,
		  	CURLOPT_HTTPHEADER 	   => array(
			    'Accept: application/json',
			    'Content-Type: application/json',
			    'X-Yotpo-Token: ' . $auth_token['access_token']
		  	),
		));

		$response = curl_exec($curl);

		curl_close($curl);
		$response = json_encode($response, true);
		return $response;
    }


}
