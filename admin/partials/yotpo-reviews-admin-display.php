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
	$msg = $msg_class = '';
    if ( $_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['ypr_action'] == 'clear_cache' ) :

		$clear_cache    = new Yotpo_Reviews_Import();
		$cache_response = $clear_cache->yotpo_clear_cache();

		if ( $cache_response == 200 ) :
			$success = 'Cache successfully cleared.';
		else :
			$error = 'An error has occurred, please try again.';
		endif;

		$msg       = $success ?? $error;
		$msg_class = $success ? 'notice-success' : 'notice-error';
	endif;

?>

<style>#wpfooter { position: relative; }</style>




<div class="loading-overlay">
    <span aria-hidden="true" aria-label="Loading"><?php echo __('Importing Reviews')?></span>
</div>

<div class="notice <?php echo $msg_class; ?> is-dismissible" <?php echo $msg ? '' : 'style="display:none;"'; ?>>
	<p class="notice__msg"><?php _e( $msg, 'yotpo-reviews' ); ?></p>
</div>




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

			<?php
				if( $active_tab == 'settings' ) : // Settings Tab

					include( 'yotpo-reviews-admin-display-settings.php' );

				elseif( $active_tab == 'logs' ) : // Logs Tab

					include( 'yotpo-reviews-admin-display-logs.php' );

				elseif( $active_tab == 'info' ) : // Info tab

					include( 'yotpo-reviews-admin-display-info.php' );

				endif;
			?>
		</div>

		<?php include( 'yotpo-reviews-admin-display-actions.php' ); ?>

	</div>

	<br class="clear">
</div>


