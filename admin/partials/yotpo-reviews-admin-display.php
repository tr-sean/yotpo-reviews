<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.seanrsullivan.com
 * @since      1.0.0
 *
 * @package    Yotpo_Reviews
 * @subpackage Yotpo_Reviews/admin/partials
 */


    // Functions to run from secondary forms
    if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) :

        // Clear Yotpo Cache
        if ( $_POST['ypr_action'] == 'clear_cache' ) :

            $clear_cache = new Yotpo_Reviews_Import();
            $cache_response = $clear_cache->yotpo_clear_cache();

            if ( $cache_response == 200 ) :
                $success = 'Cache successfully cleared.';
            else :
                $error = 'An error has occurred, please try again.';
            endif;

        // Manually run import
        elseif ( $_POST['ypr_action'] == 'manually_run' || $_POST['ypr_action'] == 'first_time' ) :

            // Import->create the reviews
            $create_reviews = new Yotpo_Reviews_Webhook_Functions();
            $create_reviews = $create_reviews->execute_yp_webhook($_POST['ypr_action']);

            // List out responses from function
            list( $data, $total_count, $response ) = $create_reviews;

            // Get the proper counts.
            $created         = !empty( $response['create'] ) ? $response['create'] : array(); // Avoid errors
            $deleted         = !empty( $response['delete'] ) ? $response['delete'] : array(); // Avoid errors
            $response_count  = count($created); // Only reviews returned
            $deleted_count   = count($deleted); // Deleted reviews
            $duplicate_count = Yotpo_Reviews_Admin::get_duplicate_count($created, 'woocommerce_rest_comment_duplicate'); // Get duplicate count
            $imported_count  = $response_count - $duplicate_count; // How much was actually imported
            $skipped         = $total_count - $response_count; // Total reviews retrieved

            // echo $response_count . ' -> response<br>';
            // echo $duplicate_count . ' -> dupe<br>';
            // echo $total_count . ' -> total<br>';
            // echo $imported_count . ' -> imported<br>';
            // echo $skipped . ' -> skipped<br>';

            // Display success or error messages.
            if ( !empty( $data ) ) :
                $success = '';
                if ( $imported_count > 0 ) $success .= 'Successfully imported ' . $imported_count . ' reviews. ';
                if ( $duplicate_count > 0 ) $success .= $duplicate_count . ' reviews were skipped since they already exist. ';
                $success .= $skipped . ' were skipped becuase the parent product does not exist.';
            else :
                $success = 'Task successfully completed. No reviews were imported.';
            endif;

            $success .= $deleted_count > 0 ? '<p>' . $deleted_count . ' reviews were deleted.</p>' : '';

            // Determine what page to import from Yotpo.
            $page = isset( $_POST['page'] ) ? $_POST['page'] : '';
            if ( $total_count == 100 && $page == '' ) :
                echo '<input type="hidden" name="page" value="2" form="first_time">';
                $success .= '<p>Only 100 reviews can be imported at a time. To retrieve any additional reviews, please run the import again. (End of run 1)</p>';
            elseif ( $total_count == 100 && $page == '2' ) :
                echo '<input type="hidden" name="page" value="3" form="first_time">';
                $success .= '<p>Only 100 reviews can be imported at a time. To retrieve any additional reviews, please run the import again. (End of run 2)</p>';
            elseif ( $total_count == 100 && $page == '3' ) :
                echo '<input type="hidden" name="page" value="4" form="first_time">';
                $success .= '<p>Only 100 reviews can be imported at a time. To retrieve any additional reviews, please run the import again. (End of run 3)</p>';
            elseif ( $total_count == 100 && $page == '4' ) :
                echo '<input type="hidden" name="page" value="5" form="first_time">';
                $success .= '<p>Only 100 reviews can be imported at a time. To retrieve any additional reviews, please run the import again. (End of run 4)</p>';
            elseif ( $total_count == 100 && $page == '5' ) :
                echo '<input type="hidden" name="page" value="6" form="first_time">';
                $success .= '<p>Only 100 reviews can be imported at a time. To retrieve any additional reviews, please run the import again. (End of run 5)</p>';
            endif;

        endif;
?>

    <div class="notice notice-<?php echo $success ? 'success' : 'error'; ?> is-dismissible">
        <?php $msg = $success ?? $error; ?>
        <p><?php _e( $msg, 'yotpo-reviews' ); ?></p>
    </div>

<?php endif; ?>

<style>#wpfooter { position: relative; }</style>

<div id="up-wrap"></div>
    <h1>Yotpo Reviews</h1>

    <?php $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'settings'; ?>

    <div id="poststuff">
    	<div id="post-body" class="metabox-holder columns-2">
    		<div id="post-body-content">

                <h2 class="nav-tab-wrapper" style="padding: 0;">
                    <a href="?page=yotpo-reviews&tab=settings" class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>">Settings</a>
                    <a href="?page=yotpo-reviews&tab=logs" class="nav-tab <?php echo $active_tab == 'logs' ? 'nav-tab-active' : ''; ?>">Logs</a>
                    <a href="?page=yotpo-reviews&tab=info" class="nav-tab <?php echo $active_tab == 'info' ? 'nav-tab-active' : ''; ?>">Info</a>
                </h2>

                <?php if( $active_tab == 'settings' ) : // Settings Tab ?>

                    <h3>Settings</h3>
                    <p>Update keys and plugin settings.</p>
                    <p><strong>Please note:</strong> Once your keys are added, they are stored in wp-config.php and <em>not</em> in the WP database.</p>


        			<form action="options.php" method="post" class="yotpo-reviews-admin-form">

        			    <?php
        			        settings_fields( 'yotpo_reviews_settings' );
        			        do_settings_sections( __FILE__ );
        			        $options = get_option( 'yotpo_reviews_settings' );
        			    ?>

        			    <p class="description">All fields are required.</p>

        			    <table class="form-table" role="presentation">

        			        <tr>
        			            <th>
        			                <label for="yotpo_app_key" class="form-label">Yotpo App Key</label>
        			            </th>
        			            <td>
        			                <input id="yotpo_app_key" name="yotpo_reviews_settings[yotpo_app_key]" type="text" class="regular-text" value="<?php echo $options['yotpo_app_key'] ?? ''; ?>">
        			            </td>
        			        </tr>
        			        <tr>
        			            <th>
        			                <label for="yotpo_app_key" class="form-label">Yotpo Secret Key</label>
        			            </th>
        			            <td>
        			                <input id="yotpo_app_key" name="yotpo_reviews_settings[yotpo_secret_key]" type="password" class="regular-text" placeholder="" value="<?php echo YP_SK ?? ''; ?>"><span style="padding-left: 15px"><?php echo $options['yotpo_secret_key'] == 'Stored' ? 'Secret key is stored. Add new to update.' : ''; ?></span>
        			                <p class="description">You can find these keys under the <a href="https://settings.yotpo.com/#/general_settings" target="_blank">general settings</a> in the Yotpo dashboard for the specific store.</p>
        			            </td>
        			        </tr>

        			        <tr>
        			            <th>
        			                <label class="form-label">Product Identifier</label>
        			            </th>
        			            <?php
        			            	$identifier = $options['product_identifier'];

        			            	if ( $identifier ) :
        			            		$checked_sku = $identifier == 'product_sku' ? 'checked' : 'disabled';
        			            		$checked_id = $identifier == 'product_id' ? 'checked' : 'disabled';
        			            	endif;
        			            ?>
        			            <td>
        			                <label>
        			                    <input class="identifiers" name="yotpo_reviews_settings[product_identifier]" value="product_sku" type="radio" <?php echo $checked_sku ?? ''; ?> required>
        			                    Product SKU
        			                </label><br>
        			                <label>
        			                    <input class="identifiers" name="yotpo_reviews_settings[product_identifier]" value="product_id" type="radio" <?php echo $checked_id ?? ''; ?>>
        			                    Product ID
        			                </label>
        			                <?php if ( $identifier ) : ?>
        			                <p class="description">Since changing this option will disconnect all current reviews, this is now disabled. If this needs updating, <a href="" class="enable-radios">click here to disable</a>.</p>
        			                <?php endif; ?>
        			            </td>
        			        </tr>

                            <tr>
                                <th>
                                    <label class="form-label">Import Orders?</label>
                                    <p class="description">If you intend on using Yotpo's automated emails, then you'll need to activate this option.</p>
                                </th>
                                <?php
                                    $orders = $options['order_import'];

                                    if ( $orders ) :
                                        $checked_yes = $orders == 'yes' ? 'checked' : '';
                                        $checked_no = $orders == 'no' ? 'checked' : '';
                                    endif;
                                ?>
                                <td>
                                    <label>
                                        <input class="orders" name="yotpo_reviews_settings[order_import]" value="yes" type="radio" <?php echo $checked_yes ?? ''; ?> required>
                                        Yes
                                    </label><br>
                                    <label>
                                        <input class="orders" name="yotpo_reviews_settings[order_import]" value="no" type="radio" <?php echo $checked_no ?? ''; ?>>
                                        No
                                    </label>
                                </td>
                            </tr>

        			        <tr>
        			            <th colspan="2">
        			                <h2>WooCommerce API Info</h2>
        			                <p class="description">You can set the API up from the <a href="<?php bloginfo('url'); ?>/wp-admin/admin.php?page=wc-settings&tab=advanced&section=keys" target="_blank">WooCommerce settings page</a>.</p>
        			            </th>
        			        </tr>


        			        <tr>
        			            <th><label for="wc_consumer_key" class="form-label">Consumer Key</label></th>
        			            <td><input id="wc_consumer_key" name="yotpo_reviews_settings[wc_consumer_key]" type="text" class="regular-text" value="<?php echo WC_CK; ?>"><span style="padding-left: 15px"><?php echo $options['wc_consumer_key'] == 'Stored' ? 'Consumer key is stored. Add new to update.' : ''; ?></span></td>
        			        </tr>

        			        <tr>
        			            <th><label for="wc_consumer_key" class="form-label">Consumer Secret</label></th>
        			            <td><input id="wc_consumer_secret" name="yotpo_reviews_settings[wc_consumer_secret]" type="password" class="regular-text" value="<?php echo WC_SK; ?>"><span style="padding-left: 15px"><?php echo $options['wc_consumer_secret'] == 'Stored' ? 'Secret key is stored. Add new to update.' : ''; ?></span></td>
        			        </tr>

        			        <?php do_settings_fields('yotpo_reviews_settings', 'default') ?>

        			    </table>

        			    <?php
        			        do_settings_sections('yotpo_reviews_settings');
        			        submit_button();
        			    ?>

        			</form>


                    <script type="text/javascript">
                        var radios = document.querySelectorAll('.identifiers'),
                            enable = document.querySelector('.enable-radios');

                        enable.addEventListener('click', function(e) {
                            e.preventDefault();
                            for(var i = 0; i < radios.length; i++) {
                                radios[i].disabled = false;
                            }
                        });
                    </script>

                <?php elseif( $active_tab == 'logs' ) : // Logs Tab ?>

                    <h3>Logs</h3>
                    <p>See when the import has been run. If you like, you can clear the data from the table by <a href="?page=yotpo-reviews&tab=logs&table=clear">clicking here</a>.</p>

                    <?php
                        global $wpdb;
                        $wpdb->show_errors();
                        $id = 'id';
                        $sql = $wpdb->prepare("SELECT * FROM `wp_yotpo_review_log` ORDER BY %s ASC", $id);
                        $logs = $wpdb->get_results( $sql , ARRAY_A );

                        // Delete data from table
                        if ( isset( $_GET['table'] ) && $_GET['table'] == 'clear' ) :
                            $wpdb->query("TRUNCATE TABLE `wp_yotpo_review_log`");
                            echo '
                                <div class="notice notice-success is-dismissible">
                                    <p>Table has been cleared. You may need to refresh page to see it as empty.</p>
                                </div>';
                        endif;
                    ?>


                    <table class="widefat fixed" cellspacing="0">
                        <thead>
                            <tr>
                                <th id="columnname" class="manage-column column-columnname" scope="col">Date</th>
                                <th id="columnname" class="manage-column column-columnname" scope="col">Total Reviews</th>
                                <th id="columnname" class="manage-column column-columnname" scope="col">Actually Imported</th>
                                <th id="columnname" class="manage-column column-columnname" scope="col">Skipped<br>(Exists)</th>
                                <th id="columnname" class="manage-column column-columnname" scope="col">Skipped<br>(No product)</th>
                                <th id="columnname" class="manage-column column-columnname" scope="col">Deleted</th>
                                <th id="columnname" class="manage-column column-columnname" scope="col">Import Method</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 0; foreach ($logs as $log) : ?>
                            <tr <?php echo $i % 2 == 0 ? 'class="alternate"' : ''; ?>>
                                <td class="column-columnname" title="<?php echo date('l F j, Y \a\t g:i a', strtotime($log['date'])); ?>"><?php echo $log['date']; ?></td>
                                <td class="column-columnname"><?php echo $log['total']; ?></td>
                                <td class="column-columnname"><?php echo $log['imported']; ?></td>
                                <td class="column-columnname"><?php echo $log['skipped_exists']; ?></td>
                                <td class="column-columnname"><?php echo $log['skipped_none']; ?></td>
                                <td class="column-columnname"><?php echo $log['deleted']; ?></td>
                                <td class="column-columnname"><?php echo $log['method']; ?></td>
                            </tr>
                            <?php $i ++; endforeach; ?>
                        </tbody>
                    </table>

                <?php elseif( $active_tab == 'info' ) : // Info tab ?>

                    <h3>Plugin Info</h3>

                    <h3>Initial Setup</h3>
                    <ol>
                        <li>Input your Yotpo app ID and secret key. <a href="https://support.yotpo.com/en/article/finding-your-yotpo-app-key-and-secret-key" target="_blank">How?</a></li>
                        <li>Select how you would like your products identified.</li>
                        <li>Input your WooCommerce client key and secret key. <a href="https://woocommerce.com/document/woocommerce-rest-api/" target="_blank">How?</a></li>
                        <li>Your keys are not stored in a database, but rather stored in the wp-config.php file for safe keeping.</li>
                    </ol>

                    <h3>Using The Plugin</h3>

                    <p>There are several ways to utilize this plugin. After inputting all of the needed keys, you will need to do a "First Time Import" if you have existing reviews on Yotpo.</p>

                    <p>From there you can go in and manually get reviews from the past 24 hours, by clicking the "Run Manual Import" button.</p>

                    <p>For the more automated process, each time a review is posted, a webhook from Yotpo is fired which will automatically add any reviews from the previous 24 hours. This webhook is created when you save or update your Yotpo keys.</p>

                    <p>I do suggest going in every now-and-then and manually grabbing reviews just to make sure you have what you need.</p>

                    <p>If you have reviews that are published then “rejected”, the next time an import is run, they will be removed from the WooCommerce reviews.</p>

                    <h3>Helper Functions</h3>
                    <p>Here are some helper functions you can use in your theme that come along with this plugin:</p>

                    <p>
                        <strong>Review Stars</strong><br>
                        To display a more accurate "bottomline" rating, you can use the function below. Place it where you want to display a product's rating.
                    </p>
                    <code>&lt;?php echo Yotpo_Reviews_Public::wc_ratings( $product->get_id() ); ?&gt;</code>


                    <h3>Plugin Deactivation</h3>
                    <p>When the plugin is deactivated, to avoid any unwanted review imports, the webhook from Yotpo is deleted. Unfortunately, there is no option to pause the webhook. So, if you reactivate the plugin again, you need to resave the main settings in order to create the webhook once again.</p>

                <?php endif; ?>
    		</div>

    		<div id="postbox-container-1">
    			<div id="side-sortables" class="meta-box-sortables ui-sortable">

    				<!-- Clear Cache -->
    				<form action="" method="post" id="clear_cache" class="postbox">
    					<input type="hidden" name="ypr_action" value="clear_cache">
    					<div class="postbox-header"><h2 class="hndle ui-sortable-handle">Clear Review Cache</h2></div>
    					<div class="inside">
    						<p>You can manually clear the cache from the Yotpo API.</p>
    						<input type="submit" value="Clear Cache" class="button">
    					</div>
    				</form>

    				<!-- Manually Run Import -->
    				<form action="" method="post" id="manually_run" class="postbox">
    					<input type="hidden" name="ypr_action" value="manually_run">
    					<div class="postbox-header"><h2 class="hndle ui-sortable-handle">Manually Run Import</h2></div>
    					<div class="inside">
    						<p>You can manually run the import.</p>
    						<input type="submit" value="Run Manual Import" class="button">
    					</div>
    				</form>

    				<!-- First Time Run -->
    				<form action="" method="post" id="first_time" class="postbox">
    					<input type="hidden" name="ypr_action" value="first_time">
    					<div class="postbox-header"><h2 class="hndle ui-sortable-handle">First Time Import</h2></div>
    					<div class="inside">
    						<p>In order to gather any reviews that have been posted prior to this install, you will need to run a first time import.</p>
    						<input type="submit" value="Run First Time Import" class="button">
    					</div>
    				</form>
    			</div>
    		</div>
    	</div>

    	<br class="clear">
    </div>

</div>


