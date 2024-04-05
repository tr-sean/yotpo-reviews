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
 * The import-specific functionality of the plugin.
 *
 * @package    Yotpo_Reviews
 * @subpackage Yotpo_Reviews/admin
 * @author     Sean Sullivan
 */
class Yotpo_Reviews_Import {

	/**
     * Get Yotpo Auth Token
     *
     * @since     1.0.0
     * @param     string.  $type        The type of Yotpo API (UGC or Core). Added in 1.5.0.
     * @return    array    $response    The array of the token.
     */
	public function yotpo_auth_token( $type = 'ugc') {

		$options    = get_option( 'yotpo_reviews_settings' );
		$app_key    = $options['yotpo_app_key'] ?? '';
		$secret_key = defined('YP_SK') ? YP_SK : define('YP_SK', '');

        if ( !$app_key || !$secret_key ) return;

        // Determine which auth route to take.
        if ( $type == 'ugc' ) :

        	$url = 'https://api.yotpo.com/oauth/token';
        	$post_fields = array(
				'client_id' 	=> $app_key,
				'client_secret' => $secret_key,
				'grant_type' 	=> 'client_credentials'
			);

		elseif ( $type == 'store' ) :

			$url         = 'https://api.yotpo.com/core/v3/stores/' . $app_key . '/access_tokens';
			$post_fields = array( 'secret' => $secret_key );

		endif;

		$post_fields = json_encode($post_fields);


		$curl = curl_init();

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
			CURLOPT_HTTPHEADER => array(
			    'Accept: application/json',
			    'Content-Type: application/json',
			),
		));

		$response = curl_exec($curl);
		$err      = curl_error($curl);
        curl_close($curl);

        if ($err) :
            return 'cURL Error #: ' . $err;
        else :
            $response = json_decode($response, true);
            return $response;
        endif;

    }





    /**
     * Get Yotpo Reviews
     *
     * @since     2.0.0     Added $method argument and ability to automatically loop through API pages.
     * @since     1.0.0
     * @return    string    $method      The type of import.
     * @return    array     $response    The array of the review.
     */
    public function yotpo_get_reviews( $method = '' ) {

    	// Get all the keys
		$options    = get_option( 'yotpo_reviews_settings' );
		$app_key    = $options['yotpo_app_key'] ?? '';
		$auth_token = $this->yotpo_auth_token();

        // If keys are blank, exit.
        if ( !$app_key || empty($auth_token) ) return;

		// Get the reviews
        if ( $method == 'first_time' ) :

			// Resets
			$page = 1;
			$data = [];

			set_time_limit(0);

			// Loop through all pages
			while ( true ) :

				// Make HTTP request to the API
				$response = $this->yotpo_get_reviews_curl( $app_key, $auth_token['access_token'], $page );

				// Check if API response is valid
				if (!$response) :
					error_log('Error: Failed to fetch data from API'); // Handle error
					break;
				endif;

				// Check if there is data in the response
				if ( empty( $response['reviews'] ) ) break; // No more data, break out of the loop

				// Append data from this page to the main data array
				$data = array_merge( $data, $response['reviews'] );

				// Move to the next page
				$page++;

			endwhile;

			return $data;

		else :

			$response = $this->yotpo_get_reviews_curl( $app_key, $auth_token['access_token'], 1, date('Ymd',strtotime('-1 days')) );
			return $response['reviews'];

		endif;

    }





    /**
     * Helper function for Yotpo API calls
     *
     * @since     2.0.0
	 * @param     string    $app_key     The Yotpo app key.
	 * @param     string    $auth_token  The Yotpo auth token.
	 * @param     int       $page        The page number.
	 * @param     string    $since_date  The date to start from.
     * @return    array     $response    The array of the review.
     */
    public function yotpo_get_reviews_curl( $app_key, $auth_token, $page, $since_date = '') {

		$url = 'https://api.yotpo.com/v1/apps/' . $app_key . '/reviews?since_date=' . $since_date . '&count=100&page=' . $page . '&deleted=true&utoken=' . $auth_token;
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'GET',
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json',
                'Content-Type: application/json'
            ],
        ]);

        $response = curl_exec($curl);
        $err      = curl_error($curl);
        curl_close($curl);

		if ($err) :
            return 'cURL Error #: ' . $err;
        else :
            $response = json_decode($response, true);
            return $response;
        endif;
	}





    /**
     * Clear Yotpo Cache
     *
     * @since     1.0.0
     * @return    array    $response    The array of the response.
     */
    public function yotpo_clear_cache() {

    	// Get all the keys
     $options    = get_option( 'yotpo_reviews_settings' );
     $app_key    = $options['yotpo_app_key'] ?? '';
     $auth_token = $this->yotpo_auth_token();

        $curl = curl_init();
		$url = 'https://api.yotpo.com/v1/widget/' . $app_key . '/widgets/clear_widgets_cache?utoken=' . $auth_token['access_token'];
		curl_setopt_array($curl, array(
			CURLOPT_URL 		   => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING 	   => '',
			CURLOPT_MAXREDIRS 	   => 10,
			CURLOPT_TIMEOUT 	   => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST  => 'GET',
		));

		$response = curl_exec($curl);
		$err      = curl_error($curl);
        curl_close($curl);

        if ($err) :
            return 'cURL Error #: ' . $err;
        else :
            $response = json_decode($response, true);
            return $response['status']['code'];
        endif;

	}




    /**
     * Import reviews to WooCommerce
     *
     * @since     2.0.0     Added in automatic batch processing.
     * @since     1.0.0
	 * @return    string    $method      The type of import.
     * @return    array     $response    The array of the response including batch data, response count and WC response.
     */
	public function create_reviews( $method ) {

		global $wpdb;

		// Get the reviews and settings
		$reviews = $this->yotpo_get_reviews( $method );
		$options = get_option( 'yotpo_reviews_settings' );

		// Count the amount of reviews retrieved
		$total_count = count( $reviews );

		error_log( print_r($reviews, true) );

		// Loop through all reviews and batch them by 100
		$batches = array_chunk($reviews, 100); $i = 1;
		foreach ( $batches as $batch ) :

			$data = []; $deleted_data = [];
			foreach ( $batch as $review ) :
				if ( isset( $review['sku'] ) && $review['sku'] ) :

					if ( $review['sku'] == 'yotpo_site_reviews' ) continue;

					// Determine what identifier to use
					if ( $options['product_identifier'] == 'product_sku' ) :
						$prod_id = wc_get_product_id_by_sku( $review['sku'] );
					else :
						$prod_id = $review['sku'];
					endif;


					// If product doesn't exist, skip.
					if ( $prod_id && $prod_id !== 0 ) :

						$comment_id = $wpdb->get_var( $wpdb->prepare('SELECT comment_ID FROM `wp_comments` WHERE comment_content = %s', $review['content'] ) );

						// If review marked as deleted, do so.
						if ( $review['deleted'] == 1 ) :

							// Get comment ID by review
							$deleted_data[] = $comment_id ?? '';

						// Send off approved reviews
						elseif ( !$comment_id ) :

							// Put together review data
							$review_data = array(
								'product_id'     => $prod_id,
								'review'         => $review['content'],
								'reviewer'       => $review['name'],
								'reviewer_email' => $review['email'],
								'rating'         => $review['score'],
								'verified'       => $review['reviewer_type'] == 'verified_buyer' ? true : false
							);

							$data[] = $review_data;

						endif;

					endif;
				endif;

			endforeach;

			// Get data, and encode to JSON
			$review_data       = array( 'create' => $data, 'delete' => $deleted_data );
			$final_review_data = json_encode($review_data);

			// Exit if empty
			if ( empty( $review_data['create'] ) && empty( $review_data['delete'] ) ) return array($data, $total_count, $review_data);

			$curl = curl_init();
			$url  = get_bloginfo('url') . '/wp-json/wc/v3/products/reviews/batch' . '?consumer_key=' . WC_CK . '&consumer_secret=' . WC_SK;
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
			    CURLOPT_POSTFIELDS     => $final_review_data,
			    CURLOPT_HTTPHEADER     => array(
			        'Content-Type: application/json'
			    ),
			));
			$response = curl_exec($curl);
			curl_close($curl);
			$response = json_decode($response, true);

			// Add the titles to the reviews
			$this->add_review_title();

			// BUG FIX: This calls the clear_transients method which resets all averages and arrays.
			foreach ( $reviews as $review ) : if ( isset( $review['sku'] ) && $review['sku'] ) :

			    // Determine what identifier to use
			    if ( $options['product_identifier'] == 'product_sku' ) :
			        $prod_id = wc_get_product_id_by_sku( $review['sku'] );
			    else :
			        $prod_id = $review['sku'];
			    endif;

			    // Clears and refreshes the comment count for the specific product.
			    do_action( 'wp_update_comment_count', $prod_id);

			endif; endforeach;

			// Return the data to display in the table
			return array($data, $total_count, $response);

		$i ++; endforeach;
	}





    /**
     * Add review title
     *
     * @since     1.0.0
     * @return    void
     */
	public function add_review_title() {

		global $wpdb;

		// Get reviews
		$reviews = $this->yotpo_get_reviews();

		// Add title to review
		foreach ( $reviews as $review ) :
			$comment_id = $wpdb->get_var( $wpdb->prepare('SELECT comment_ID FROM `wp_comments` WHERE comment_content = %s', $review['content'] ) );
			update_comment_meta( $comment_id, 'comment_title', $review['title'] );
		endforeach;

	}

}
