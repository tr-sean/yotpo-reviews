<?php

	// Activate WP Cron to check for new reviews
	if ( $_SERVER['REQUEST_METHOD'] == 'GET' && ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] == true ) ) :

		$start_cron = new Yotpo_Reviews_Crons();
		$start_cron->schedule_cron('scheduled', 'daily');

	endif;

?>


<h2 class="yp-style">Settings</h2>
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
			<th colspan="2">
				<h3 class="yp-style">Yotpo API Info</h3>
				<p class="description">You can find these keys under the <a href="https://settings.yotpo.com/#/general_settings" target="_blank">general settings</a> in the Yotpo dashboard for the specific store.</p>
			</th>
		</tr>
		<tr>
			<th><label for="yotpo_app_key" class="form-label">Yotpo App Key</label></th>
			<td><input id="yotpo_app_key" name="yotpo_reviews_settings[yotpo_app_key]" type="text" class="regular-text" value="<?php echo $options['yotpo_app_key'] ?? ''; ?>"></td>
		</tr>
		<tr>
			<th><label for="yotpo_app_key" class="form-label">Yotpo Secret Key</label></th>
			<td><input id="yotpo_app_key" name="yotpo_reviews_settings[yotpo_secret_key]" type="password" class="regular-text" placeholder="" value="<?php echo defined('YP_SK') ? YP_SK : define('YP_SK', ''); ?>"><span style="padding-left: 15px"><?php echo $options['yotpo_secret_key'] == 'Stored' ? 'Secret key is stored. Add new to update.' : ''; ?></span></td>
		</tr>

		<tr>
			<?php
				$identifier = $options['product_identifier'];

				if ( $identifier ) :
					$checked_sku = $identifier == 'product_sku' ? 'checked' : 'disabled';
					$checked_id  = $identifier == 'product_id' ? 'checked' : 'disabled';
				endif;
			?>

			<th>
				<label class="form-label">Product Identifier</label>
				<?php if ( $identifier ) : ?>
				<p class="description">Since changing this option will disconnect all current reviews, this is now disabled. If this needs updating, <a href="" class="enable-radios">click here to disable</a>.</p>
				<?php endif; ?>
			</th>
			<td>
				<label>
					<input class="identifiers" name="yotpo_reviews_settings[product_identifier]" value="product_sku" type="radio" <?php echo $checked_sku ?? ''; ?> required>
					Product SKU
				</label><br>
				<label>
					<input class="identifiers" name="yotpo_reviews_settings[product_identifier]" value="product_id" type="radio" <?php echo $checked_id ?? ''; ?>>
					Product ID
				</label>
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
					$checked_no  = $orders == 'no' ? 'checked' : '';
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
				<h3 class="yp-style">WooCommerce API Info</h3>
				<p class="description">You can set the API up from the <a href="<?php bloginfo('url'); ?>/wp-admin/admin.php?page=wc-settings&tab=advanced&section=keys" target="_blank">WooCommerce settings page</a>.</p>
			</th>
		</tr>
		<tr>
			<th><label for="wc_consumer_key" class="form-label">Consumer Key</label></th>
			<td><input id="wc_consumer_key" name="yotpo_reviews_settings[wc_consumer_key]" type="text" class="regular-text" value="<?php echo defined('WC_CK') ? WC_CK : define('WC_CK', ''); ?>"><span style="padding-left: 15px"><?php echo $options['wc_consumer_key'] == 'Stored' ? 'Consumer key is stored. Add new to update.' : ''; ?></span></td>
		</tr>
		<tr>
			<th><label for="wc_consumer_key" class="form-label">Consumer Secret</label></th>
			<td><input id="wc_consumer_secret" name="yotpo_reviews_settings[wc_consumer_secret]" type="password" class="regular-text" value="<?php echo defined('WC_SK') ? WC_SK : define('WC_SK', ''); ?>"><span style="padding-left: 15px"><?php echo $options['wc_consumer_secret'] == 'Stored' ? 'Secret key is stored. Add new to update.' : ''; ?></span></td>
		</tr>

		<?php do_settings_fields('yotpo_reviews_settings', 'default') ?>

	</table>

	<?php
		do_settings_sections('yotpo_reviews_settings');
		submit_button();
	?>

</form>
