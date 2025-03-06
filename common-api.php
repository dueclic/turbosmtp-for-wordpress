<?php

/**
 * @return bool
 */
function turbosmtp_migration_has_done() {

	$auth_options      = get_option( "ts_auth_options" );
	$plugin_setup_done = get_option( 'ts_migration_done' );

	return ! ( $auth_options &&
	           isset( $auth_options['op_ts_validapi'] ) &&
	           (bool) $auth_options['op_ts_validapi'] &&
	           isset( $auth_options['op_ts_email'] ) &&
	           $auth_options['op_ts_email'] &&
	           ! $plugin_setup_done );

}

function turbosmtp_get_label() {
	$site_name = get_bloginfo();

	return apply_filters( 'turbosmtp_get_label',
		$site_name . ' (WordPress Plugin)',
		$site_name
	);
}

function turbosmtp_validapi() {
	$auth_options = get_option( "ts_auth_options" );

	return isset( $auth_options['valid_api'] ) && (bool) $auth_options['valid_api'];
}

function turbosmtp_analytics_filter_options() {
	return apply_filters( 'turbosmtp_analytics_filter_options', array(
		'clicks'       => 'CLICK',
		'unsubscribes' => 'UNSUB',
		'spam'         => 'REPORT',
		'drop'         => 'SYSFAIL',
		'queued'       => array( 'NEW', 'DEFER' ),
		'opens'        => array( 'OPEN', 'CLICK', 'UNSUB', 'REPORT' ),
		'delivered'    => array( 'SUCCESS', 'OPEN', 'CLICK', 'UNSUB', 'REPORT' ),
		'bounce'       => 'FAIL'
	) );
}

function turbosmtp_get_status_by_filter(
	$filter
) {
	$options = turbosmtp_analytics_filter_options();

	return $options[ $filter ] ?? false;
}

function turbosmtp_get_icon( $item ) {

	$status = $item['status'];

	$analyticsfilterOptions = turbosmtp_analytics_filter_options();


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

	if ( $statusFound !== null ) {
		return '<span class="events events-' . $statusFound . '">' . $status_i18n[ $statusFound ] . '</span>';
	}

	return '<span></span>';

}

function turbosmtp_is_admin_page() {

	$screen = get_current_screen();

	$turbo_admin_pages = array(
		"toplevel_page_turbosmtp_config",
		"turbosmtp_page_turbosmtp_config",
		"toplevel_page_turbosmtp_migration",
		"turbosmtp_page_turbosmtp_stats",
		"turbosmtp_page_turbosmtp_api_keys"
	);

	if ( $screen != null && in_array( $screen->id, $turbo_admin_pages ) ) {
		return $screen;
	}

	return null;
}


function turbosmtp_valid_hosts() {
	$hosts = array(
		"pro.eu.turbo-smtp.com" => __( "European", "turbostmp" ),
		"pro.turbo-smtp.com"    => __( "Not european", "turbostmp" ),
	);

	return $hosts;
}

function turbosmtp_implode( $glue, $pieces ) {
	if ( is_array( $pieces ) ) {
		return implode( $glue, $pieces );
	}

	return $pieces;
}

function turbosmtp_get_header_content_type(
	$headers
) {
	if ( ! is_array( $headers ) ) {
		$tempheaders = explode( "\n", str_replace( "\r\n", "\n", $headers ) );
	} else {
		$tempheaders = $headers;
	}

	if ( ! empty( $tempheaders ) ) {
		foreach ( (array) $tempheaders as $header ) {
			if ( ! str_contains( $header, ':' ) ) {
				continue;
			}
			list( $name, $content ) = explode( ':', trim( $header ), 2 );

			if ( strtolower( $name ) == 'content-type' ) {
				return $content;
			}
		}
	}

	return 'text/plain';

}
