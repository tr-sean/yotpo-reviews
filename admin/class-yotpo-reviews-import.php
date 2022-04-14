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
     * @return    array    $response    The array of the token.
     */
	public function yotpo_auth_token() {

    	$options = get_option( 'yotpo_reviews_settings' );
        $app_key = $options['yotpo_app_key'] ?? '';
        $secret_key = defined('YP_SK') ? YP_SK : define('YP_SK', '');

        if ( !$app_key || !$secret_key ) return;

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL 		   => 'https://api.yotpo.com/oauth/token',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING 	   => '',
			CURLOPT_MAXREDIRS 	   => 10,
			CURLOPT_TIMEOUT 	   => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST  => 'POST',
			CURLOPT_POSTFIELDS     => array(
				'client_id' 	=> $app_key,
				'client_secret' => $secret_key,
				'grant_type' 	=> 'client_credentials'
			),
		));

		$response = curl_exec($curl);
        $err = curl_error($curl);
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
     * @since     1.0.0
     * @return    array    $response    The array of the review.
     */
    public function yotpo_get_reviews() {

    	// Get all the keys
    	$options = get_option( 'yotpo_reviews_settings' );
        $app_key = $options['yotpo_app_key'] ?? '';
        $auth_token = $this->yotpo_auth_token();

        // If first time run, get 'em all, otherwise get since yesterday
        $since_date = isset( $_POST['ypr_action'] ) && $_POST['ypr_action'] == 'first_time' ? '' : date('Ymd',strtotime('-1 days'));

        // If import returned 100 results, get the next page.
        $page = isset( $_POST['page'] ) ? $_POST['page'] : '1';

        // If keys are blank, exit.
        if ( !$app_key || empty($auth_token) ) return;

        // Get the reviews
        $url = 'https://api.yotpo.com/v1/apps/' . $app_key . '/reviews?since_date=' . $since_date . '&count=100&page=' . $page . '&deleted=true&utoken=' . $auth_token['access_token'];
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
        $err = curl_error($curl);
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
    	$options = get_option( 'yotpo_reviews_settings' );
        $app_key = $options['yotpo_app_key'] ?? '';
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
        $err = curl_error($curl);
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
     * @since     1.0.0
     * @return    array    $response    The array of the response.
     */
	public function create_reviews() {

		global $wpdb;

		// Get the reviews and settings
		$reviews = $this->yotpo_get_reviews();
		$reviews = $reviews['reviews'];
		$options = get_option( 'yotpo_reviews_settings' );

		// Count the amount of reviews retrieved
		$total_count = count( $reviews );

		// Loop through all reviews
		$data = array(); $deleted_data = array();
		foreach ( $reviews as $review ) :
			if ( $review['sku'] ) :

				// Determine what identifier to use
				if ( $options['product_identifier'] == 'product_sku' ) :
					$prod_id = wc_get_product_id_by_sku( $review['sku'] );
				else :
					$prod_id = $review['sku'];
				endif;

				// echo $prod_id . '<br>';

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

		$review_data = array( 'create' => $data, 'delete' => $deleted_data );
		$final_review_data = json_encode($review_data);

		$curl = curl_init();
		$url = get_bloginfo('url') . '/wp-json/wc/v3/products/reviews/batch' . '?consumer_key=' . WC_CK . '&consumer_secret=' . WC_SK;
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

		return array($data, $total_count, $response);

	}




	/**
     * Add review title
     *
     * @since     1.0.0
     * @return    void
     */
	public function add_review_title() {

		global $wpdb;

		$reviews = $this->yotpo_get_reviews();
		$reviews = $reviews['reviews'];

		foreach ( $reviews as $review ) :
			$comment_id = $wpdb->get_var( $wpdb->prepare('SELECT comment_ID FROM `wp_comments` WHERE comment_content = %s', $review['content'] ) );
			update_comment_meta( $comment_id, 'comment_title', $review['title'] );
		endforeach;

	}

}
