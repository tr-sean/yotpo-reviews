<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.trinityroad.com
 * @since      1.0.0
 *
 * @package    Yotpo_Reviews
 * @subpackage Yotpo_Reviews/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Yotpo_Reviews
 * @subpackage Yotpo_Reviews/public
 * @author     Sean Sullivan <ssullivan@trinityroad.com>
 */
class Yotpo_Reviews_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string    $plugin_name    The name of the plugin.
	 * @param    string    $version        The version of this plugin.
	 */
	public function __construct( $plugin_name = '', $version = '') {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/yotpo-reviews-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/yotpo-reviews-public.js', array( 'jquery' ), $this->version, false );

	}






    /**
     * Helper function to load a WooCommerce template or template part file from the
     *   active theme or a plugin folder.
     *
     * @since    1.0.0
     * @param	 string    $template_name
     * @return	 string	   $file    Path to template file.
     */
    public function load_wc_template_file( $template_name ) {
        // Check theme folder first
        $file = get_stylesheet_directory() . '/woocommerce/' . $template_name;
        if ( @file_exists( $file ) ) return $file;

        // Now check plugin folder
        $file = untrailingslashit( plugin_dir_path( __FILE__ ) )  . '/templates/' . $template_name;
        if ( @file_exists( $file ) ) return $file;
    }



    /**
     * Cache the template name
     *
     * @since    1.0.0
     * @param	 array     $templates
     * @param	 string    $template_name
     * @return   array     $templates    Updated templates with the custom templates cached.
     */
    public function get_template_name( $templates, $template_name ){
        // Capture/cache the $template_name which is a file name like single-product.php
        wp_cache_set( 'my_wc_main_template', $template_name ); // cache the template name
        return $templates;
    }



    /**
     * Clear template name cache
     *
     * @since    1.0.0
     * @param    string    $template
     * @return	 string    $template
     */
    public function clear_template_cache( $template ) {
        if ( $template_name = wp_cache_get( 'my_wc_main_template' ) ) :
            wp_cache_delete( 'my_wc_main_template' ); // delete the cache
            if ( $file = $this->load_wc_template_file( $template_name ) ) return $file;
        endif;
        return $template;
    }



    /**
     * Override the template parts
     *
     * @since    1.0.0
     * @param    string    		  $template
     * @param    string    		  $slug
     * @param    string    		  $name
     * @return	 string|string    $file|$template    The proper file template to use.
     */
    public function override_template_parts( $template, $slug, $name ){
        $file = $this->load_wc_template_file( "{$slug}-{$name}.php" );
        return $file ? $file : $template;
    }



    /**
     * Display the custom template
     *
     * @since    1.0.0
     * @param    string    		  $template
     * @param    string    		  $template_name
     * @return	 string|string    $file|$template    The proper file template to use.
     */
    public function display_custom_template( $template, $template_name ){
        $file = $this->load_wc_template_file( $template_name );
        return $file ? $file : $template;
    }




    /**
     * Main template override
     *
     * Special funcionality needed to override main comment template.
     *
     * @since    1.0.0
     * @param    string    $template
     * @return	 string	   $template
     */
    public function override_reviews_template( $template ) {
        global $woocommerce;
        $template_path = $this->load_wc_template_file( 'single-product-reviews.php' );
        if ( get_post_type() == 'product' && file_exists( $template_path ) ) return $template_path;

        return $woocommerce->comments_template_loader($template);
    }




    /**
     * Post the review to Yotpo
     *
     * @since    1.0.0
     * @return	 void
     */
    public function post_yotpo_review() {

    	// Get all the keys
    	$options = get_option( 'yotpo_reviews_settings' );
        $app_key = $options['yotpo_app_key'] ?? '';

        // Get array of results
    	$review = $_POST['review'];

	    // Gather form data
	    $post_fields = array(
	        'appkey'              => $app_key,
	        'domain'              => get_bloginfo('url'),
	        'sku'                 => $_POST['product_id'],
	        'product_title'       => $_POST['product_title'],
	        'product_url'         => $_POST['product_url'],
	        'product_image_url'   => $_POST['product_image'],
	        'display_name'        => $review['name'],
	        'email'               => $review['email'],
	        'review_content'      => $review['content'],
	        'review_title'        => $review['subject'],
	        'review_score'        => $review['rating']
	    );
	    $post_fields = json_encode($post_fields);

	    $curl = curl_init();
	    curl_setopt_array($curl, array(
	        CURLOPT_URL            => 'https://api.yotpo.com/v1/widget/reviews',
	        CURLOPT_RETURNTRANSFER => true,
	        CURLOPT_ENCODING       => '',
	        CURLOPT_MAXREDIRS      => 10,
	        CURLOPT_TIMEOUT        => 0,
	        CURLOPT_FOLLOWLOCATION => true,
	        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
	        CURLOPT_CUSTOMREQUEST  => 'POST',
	        CURLOPT_POSTFIELDS     => $post_fields,
	        CURLOPT_HTTPHEADER     => array( 'Content-Type: application/json' ),
	    ));

	    $response = curl_exec($curl);
	    curl_close($curl);
	    $response = json_decode( $response, true );

	    $result = $response['code'] == 200 ? 'posted' : 'failed';
	    header( 'Location: ' . $_POST['product_url'] . '?review=' . $result );
	}




    /**
     * Display star ratings
     *
     * @since     1.0.0
     * @param     integer    $prod_id
     * @return    string     $stars    The HTML for the review stars
     */
    public static function wc_ratings( $prod_id ) {
        $product_info = wc_get_product( $prod_id );
        $rating = $product_info->get_average_rating();
        $count = $product_info->get_review_count();
        $rating_class = $rating == 0 && $count == 0 ? 'no-reviews' : '';

        $stars = '<span class="stars ' . $rating_class . '" style="--rating: ' . $rating . '" aria-label="Rating of this product is ' . round( $rating, 2 ) . ' out of 5." title="Rating of this product is ' . round( $rating, 2 ) . ' out of 5."></span>';

        if ( is_product() ) :
            $add_an_s = $count > 1 ? 's' : '';
            $review_link_text = $count == 0 ? 'Be the first to leave a review.' : $count . ' review' . $add_an_s;
            $stars .= '<a href="#reviews" class="review-link">' . $review_link_text . '</a>';
        endif;

        return $stars;
    }

}
