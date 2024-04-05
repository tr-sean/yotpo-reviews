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
	 * Remove the scheduled import event
	 *
	 * @since    2.0.0
	 * @return   void
	 */
	public static function remove_scheduled_event() {

        wp_clear_scheduled_hook('yotpo_review_cron', array( 'scheduled' ));

	}

}
