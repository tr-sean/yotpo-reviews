<?php

/**
*  Functions to set up scheduled events for review imports.
*
* @link       https://www.seanrsullivan.com
* @since      2.0.0
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
class Yotpo_Reviews_Crons {


    /**
     * Schedule the events
     *
     * Executes the webhook callback and inserts a row into the log table.
     *
     * @since     2.0.0
     * @param     string     $run_type     The run type of the call.
     * @param     string     $run_time     The scheduled run time of the event.
     * @return    void
     */
	public function schedule_cron( $run_type = '', $run_time = '') {

		// If no run time, run immediately
		if ( !$run_time ) :

			wp_schedule_single_event( time(), 'yotpo_review_cron', array( $run_type ) );

			return ucwords( str_replace('_', ' ', $run_type) ) . ' import has been run. Page will refresh in 3 seconds. If it does not, you can <a href=""click here</a> to refresh.';

		else :

			// If scheduled event exists, return
			if ( wp_next_scheduled( 'yotpo_review_cron' ) ) return;

			wp_schedule_event( time() + 60, $run_time, 'yotpo_review_cron', array( 'scheduled' ) );

		endif;

	}

}
