<?php

/**
 * @return bool
 */
function turbosmtp_migration_has_done() {

	$auth_options      = get_option( "ts_auth_options" );
	$plugin_setup_done = get_option( 'ts_migration_done' );

	return ! ( $auth_options &&
	           isset($auth_options['op_ts_validapi']) &&
	           (bool)$auth_options['op_ts_validapi'] &&
	           isset($auth_options['op_ts_email']) &&
	           $auth_options['op_ts_email'] &&
	           ! $plugin_setup_done );

}

function turbosmtp_get_label(){
	return apply_filters('turbosmtp_get_label', 'turboSMTP (WordPress Plugin)');
}

function turbosmtp_validapi() {
	$auth_options      = get_option( "ts_auth_options" );
	return isset( $auth_options['valid_api'] ) && (bool)$auth_options['valid_api'];
}

function turbosmtp_get_icon( $item ) {

	$status = $item['status'];

	$analyticsfilterOptions = array(
		'clicks'       => 'CLICK',
		'unsubscribes' => 'UNSUB',
		'spam'         => 'REPORT',
		'drop'         => 'SYSFAIL',
		'queued'       => array( 'NEW', 'DEFER' ),
		'opens'        => array( 'OPEN', 'CLICK', 'UNSUB', 'REPORT' ),
		'delivered'    => array( 'SUCCESS', 'OPEN', 'CLICK', 'UNSUB', 'REPORT' ),
		'bounce'       => 'FAIL'
	);


	$statusFound = null;

	foreach ( $analyticsfilterOptions as $key => $statuses ) {
		if ( ! is_array( $statuses ) ) {
			$statuses = array( $statuses );
		}

		if ( in_array( $status, $statuses ) ) {
			$statusFound = $key;
			break;
		}
	}

	$status_i18n = array(
		"queued"       => __( "Queue", "turbosmtp" ),
		"delivered"    => __( "Delivered", "turbosmtp" ),
		"bounce"       => __( "Bounced", "turbosmtp" ),
		"opens"        => __( "Opened", "turbosmtp" ),
		"clicks"       => __( "Click", "turbosmtp" ),
		"unsubscribes" => __( "Unsubscribes", "turbosmtp" ),
		"drop"         => __( "Dropped", "turbosmtp" ),
		"spam"         => __( "Spam", "turbosmtp" ),
		"all"          => __( "Total", "turbosmtp" )
	);

	if ($statusFound !== null){
		return '<span class="events events-' . $statusFound . '">'.$status_i18n[$statusFound].'</span>';
	}

	return '<span></span>';



}
