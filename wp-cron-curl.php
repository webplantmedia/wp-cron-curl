<?php
/*
Plugin Name: WP Cron Curl
Plugin URI: http://angiemakes.com/
Description: Cron Curl For Symbiostock
Author: Chris Baldelomar
Author URI: http://webplantmedia.com/
Version: 1.0
License: GPLv2 or later
*/


function wcc_add_every_minute( $schedules ) {
 
    $schedules['every_minute'] = array(
            'interval'  => 60,
            'display'   => __( 'Every Minute', 'wp-cron-curl' )
    );
     
    return $schedules;
}
add_filter( 'cron_schedules', 'wcc_add_every_minute' );

function wcc_activation() {
    if ( ! wp_next_scheduled ( 'wcc_symbiostock_processor_event' )) {
		wp_schedule_event(time(), 'every_minute', 'wcc_symbiostock_processor_event');
    }
}
register_activation_hook( __FILE__, 'wcc_activation' );

function wcc_do_symbiostock_processor() {
	wcc_remote_post( 'https://stockshop.angiemakes.com/?c=1&ss_c=e5f112c6238b6d99e22b' );
}
add_action( 'wcc_symbiostock_processor_event', 'wcc_do_symbiostock_processor' );

function wcc_deactivation() {
	wp_clear_scheduled_hook( 'wcc_symbiostock_processor_event' );
}
register_deactivation_hook( __FILE__, 'wcc_deactivation' );

function wcc_remote_post( $url ) {   
	$args = array(
		'timeout' => 15,
	);
	$request = wp_remote_post( $url, $args );

	if ( is_wp_error($request) ) {
		return false;
	} else {
		$res = maybe_unserialize( wp_remote_retrieve_body( $request ) );
		if ( ! is_object( $res ) && ! is_array( $res ) )
			return false;
	}

	return true;
}
