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
class Yotpo_Reviews_Admin {

	/**
	 * The ID of this plugin.

     *
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
	 * @param    string    $plugin_name    The name of this plugin.
	 * @param    string    $version    	   The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}



	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/yotpo-reviews-admin.css', array(), $this->version, 'all' );
	}



	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/yotpo-reviews-admin.js', array( 'jquery' ), $this->version, false );
	}



    /**
     * Adds the settings page.
     *
     * @since    1.0.0
     */
    public function add_plugin_admin_menu() {
        add_submenu_page('options-general.php', 'Settings', 'Yotpo Reviews', 'activate_plugins', $this->plugin_name, array($this, 'display_plugin_setup_page') ); // Link to Permissions
    }



    /**
     * Renders the settings page (HTML & junk).
     *
     * @since    1.0.0
     */
    public function display_plugin_setup_page() {
        include_once( 'partials/yotpo-reviews-admin-display.php' );
    }



    /**
     * Registers plugin settings
     *
     * Stored in wp_options
     *
     * @since    1.0.0
     */
    public function register_plugin_settings(){
        register_setting('yotpo_reviews_settings', 'yotpo_reviews_settings', array( $this, 'sanitize_settings' ) );
    }



    /**
     * Sanitize fields and store WC API keys
     *
     * @since     1.0.0
     * @param     array    $settings    The settings from the plugin form.
     * @return    array    $settings    Sanitized settings
     */
    public function sanitize_settings( $settings ) {
        $settings['yotpo_app_key']      = sanitize_text_field( $settings['yotpo_app_key'] );
        $settings['product_identifier'] = sanitize_text_field( $settings['product_identifier'] );
        $settings['wc_consumer_key']    = sanitize_text_field( $settings['wc_consumer_key'] );
        $settings['wc_consumer_secret'] = sanitize_text_field( $settings['wc_consumer_secret'] );
        $err                            = false;

        if ( ! isset( $settings['product_identifier'] ) ) :
            $settings['product_identifier'] = 'product_sku';  // always set checkboxes if they dont exist
        endif;

        if ( $err ) :
            add_settings_error(
                'pses1',
                'pses1',
                implode( '<br>', $err ),
                'error'
            );

            // return $this->options;
        endif;

        // If keys are not blank write to wp-config.php
        if ( ( $settings['wc_consumer_key'] !== '' && $settings['wc_consumer_secret'] !== '' ) || $settings['yotpo_secret_key'] !== '' ) :

        	// Determine what consumer key to use
        	$wc_key = '';
        	if ( $settings['wc_consumer_key'] !== '' ) :
        		$wc_key = $settings['wc_consumer_key'];
        	elseif ( defined( 'WC_CK' ) && WC_CK !== '' ) :
        		$wc_key = WC_CK;
        	endif;

        	// Determine what consumer secret to use
        	$wc_secret = '';
        	if ( $settings['wc_consumer_secret'] !== '' ) :
        		$wc_secret = $settings['wc_consumer_secret'];
        	elseif ( defined( 'WC_SK' ) && WC_SK !== '' ) :
        		$wc_secret = WC_SK;
        	endif;

        	// Determine what yotpo secret to use
        	$yp_secret = '';
        	if ( $settings['yotpo_secret_key'] !== '' ) :
        		$yp_secret = $settings['yotpo_secret_key'];
        	elseif ( defined( 'YP_CK' ) && YP_CK !== '' ) :
        		$yp_secret = YP_CK;
        	endif;

        	// Write to wp-config
        	$this->wp_config_add_directive( $wc_key, $wc_secret, $yp_secret ); // Write to wp-config

            // Create/update the Yotpo webhook
            if ( $yp_secret ) :
                $create_webhook = new Yotpo_Reviews_Webhook_Functions();
                $create_webhook->create_yotpo_webhook();
            endif;

            // Return it empty to be store in DB.
            $settings['wc_consumer_key'] = 'Stored';
            $settings['wc_consumer_secret'] = 'Stored';
            $settings['yotpo_secret_key'] = 'Stored';

        endif;

        return $settings;
    }



    /**
     * Adds WC API keys to wp-config
     *
     * @since     1.0.0
     * @access    private
     * @param     string    $key
     * @param     string    $secret
     */
    private function wp_config_add_directive( $wc_key, $wc_secret, $yp_secret ) {

        // Get file path of wp-config
        $config_path = $_SERVER['DOCUMENT_ROOT'] . '/wp-config.php';

        // Get contents of file
        $config_data = file_get_contents( $config_path );
        if ( $config_data === false ) return;

        // Update file with keys
        $new_config_data = $this->wp_config_remove_from_content( $config_data );
        $new_config_data = preg_replace(
            '~<\?(php)?~',
            "\\0\r\n" . $this->wp_config_addon( $wc_key, $wc_secret, $yp_secret ),
            $new_config_data,
            1
       	);

        // See if current data is different and update file if needed
        if ( $new_config_data != $config_data ) :
            file_put_contents( $config_path, $new_config_data );
        endif;
    }



    /**
     * Returns string Addon required for plugin in wp-config
     *
     * @since     1.0.0
     * @access    private
     * @param     string    $key
     * @param     string    $secret
     */
    private function wp_config_addon( $wc_key, $wc_secret, $yp_secret ) {
        return "/** Save WC/Yotpo API */\r\n" .
            "define('WC_CK', '" .  $wc_key  . "'); // Consumer Key\r\n" .
            "define('WC_SK', '" .  $wc_secret  . "'); // Consumer Secret\r\n" .
            "define('YP_SK', '" .  $yp_secret  . "'); // Yotpo Secret\r\n";
    }




    /**
     * Disables WP Keys
     *
     * @since     1.0.0
     * @access    private
     * @param     string    $config_data    wp-config.php content
     * @return    string
     */
    private function wp_config_remove_from_content( $config_data ) {
        $config_data = preg_replace(
            "~\\/\\*\\* Save WC/Yotpo API \\*\\*?\\/.*?\\/\\/ Consumer Secret(\r\n)*~s",
            '', $config_data );
        $config_data = preg_replace(
            "~(\\/\\/\\s*)?define\\s*\\(\\s*['\"]?WC_CK['\"]?\\s*,.*?\\)\\s*;+\\r?\\n?~is",
            '', $config_data );
        $config_data = preg_replace(
            "~(\\/\\/\\s*)?define\\s*\\(\\s*['\"]?WC_SK['\"]?\\s*,.*?\\)\\s*;+\\r?\\n?~is",
            '', $config_data );
        $config_data = preg_replace(
            "~(\\/\\/\\s*)?define\\s*\\(\\s*['\"]?YP_SK['\"]?\\s*,.*?\\)\\s*;+\\r?\\n?~is",
            '', $config_data );

        return $config_data;
    }




    /**
     * Add the title to our admin area, for editing, etc
     *
     * @since     1.0.0
     * @return    void
     */
    public function add_comment_title_meta_box() {
        add_meta_box( 'comment-title', __( 'Review Title' ), array( $this, 'add_comment_title_meta_box_html'), 'comment', 'normal', 'high' );
    }


    /**
     * HTML to display comment title in column
     *
     * @since     1.0.0
     * @param     array    $comment
     * @return    void
     */
    public function add_comment_title_meta_box_html( $comment ) {
        $title = get_comment_meta( $comment->comment_ID, 'comment_title', true );
        wp_nonce_field( 'comment_update', 'comment_update', false );
?>
        <p>
            <label for="comment_title"><?php _e( 'Review Title' ); ?></label>
            <input type="text" name="comment_title" value="<?php echo esc_attr( $title ); ?>" class="widefat" />
        </p>
<?php
    }


    /**
     * Save our comment (from the admin area)
     *
     * @since     1.0.0
     * @param     string    $comment_id
     * @return    void
     */
    public function add_comment_title_admin_save( $comment_id ) {
        if( ! isset( $_POST['comment_update'] ) || ! wp_verify_nonce( $_POST['comment_update'], 'comment_update' ) ) return;
        if( isset( $_POST['comment_title'] ) )
            update_comment_meta( $comment_id, 'comment_title', esc_attr( $_POST['comment_title'] ) );
    }


    /**
     * Add headline to the comment text
     *
     * @since     1.0.0
     * @param     string    $text
     * @param     array     $comment
     * @return    string	$text		Updated text
     */
    public function add_comment_title_to_text( $text, $comment ) {
        if( is_admin() ) return $text;
        if( $title = get_comment_meta( $comment->comment_ID, 'comment_title', true ) ) :
            $title = '<h3>' . esc_attr( $title ) . '</h3>';
            $text = $title . $text;
        endif;
        return $text;
    }


    /**
     * Put title in comments list table
     *
     * @since     1.0.0
     * @return    void
     */
    public function comment_title_load() {
        $screen = get_current_screen();
        add_filter("manage_{$screen->id}_columns", array( $this, 'comment_title_add_columns') );
    }


    /**
     * Add headline to the comment text
     *
     * @since     1.0.0
     * @param     array     $cols     Current column titles
     * @return    array		$cols	  Updated text
     */
    public function comment_title_add_columns($cols) {
        $cols['title'] = __('Review Title', 'yotpo-reviews');
        return $cols;
    }


    /**
     * Display the comment title in the column
     *
     * @since     1.0.0
     * @param     string     $col
     * @param     string     $comment_id
     * @return    void
     */
    public function comment_title_column_cb($col, $comment_id) {
        switch($col) {
            case 'title':
                if ($t = get_comment_meta($comment_id, 'comment_title', true)) :
                    echo esc_html($t);
                else :
                    esc_html_e('No Title', 'yotpo-reviews');
                endif;
            break;
        }
    }




    /**
     * Get count of duplicate reviews on import
     *
     * @since     1.0.0
     * @param     array      $response     The array to search through
     * @param     string     $value        The value to search for
     * @return    string     $results      The count of the search results
     */
    public static function get_duplicate_count($response, $value) {
    	$value = trim(strtolower($value));
	    $results = [];

	    foreach ( $response as $array ) :
	        $keyword_found = false;

	        if ( isset( $array['error'] ) ) :
		        foreach ( $array['error'] as $keyword ) :
		        	if ( !is_array($keyword) ) :
			        	if ( $keyword_found ) continue;
			            $keyword = trim( strtolower($keyword) );
			            if ($keyword == $value) $results[] = $array;
			        endif;
		        endforeach;
		    endif;
	    endforeach;

	    return count( $results );
	}

}
