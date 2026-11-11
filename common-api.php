<?php

/**
 * Removes options left behind by older plugin versions.
 *
 * Before API keys, the plugin stored the account email and password in
 * plain text (ts_auth_options op_ts_*, and ts_send_options email/password
 * for SMTP). Neither is used anymore: authentication only uses the
 * consumer key and secret.
 */
function turbosmtp_cleanup_legacy_options() {

	$auth_options = get_option( 'ts_auth_options' );

	if ( is_array( $auth_options ) ) {
		$legacy_keys = array_filter( array_keys( $auth_options ), function ( $key ) {
			return 0 === strpos( $key, 'op_ts_' );
		} );

		if ( ! empty( $legacy_keys ) ) {
			foreach ( $legacy_keys as $key ) {
				unset( $auth_options[ $key ] );
			}

			if ( empty( $auth_options ) ) {
				delete_option( 'ts_auth_options' );
			} else {
				update_option( 'ts_auth_options', $auth_options );
			}
		}
	}

	$send_options = get_option( 'ts_send_options' );

	if ( is_array( $send_options ) && ( isset( $send_options['email'] ) || isset( $send_options['password'] ) ) ) {
		unset( $send_options['email'], $send_options['password'] );
		update_option( 'ts_send_options', $send_options );
	}

	if ( false !== get_option( 'ts_migration_done' ) ) {
		delete_option( 'ts_migration_done' );
	}

	if ( false !== get_option( 'ts_show_credentials' ) ) {
		delete_option( 'ts_show_credentials' );
	}

}

/**
 * SMTP credentials are the connected API key pair: the Consumer Key is the
 * username and the Consumer Secret is the password. The key needs the
 * SEND_SMTP permission.
 *
 * @return array{username: string, password: string}
 */
function turbosmtp_get_smtp_credentials() {
	$auth_options = get_option( 'ts_auth_options' );

	return array(
		'username' => $auth_options['consumer_key'] ?? '',
		'password' => $auth_options['consumer_secret'] ?? '',
	);
}

function turbosmtp_get_label() {
	$site_name = get_bloginfo();

	return apply_filters( 'turbosmtp_get_label',
		$site_name . ' (WordPress Plugin)',
		$site_name
	);
}

/**
 * The From address wp_mail() uses when nothing else sets one
 * (same logic as wp-includes/pluggable.php).
 *
 * @return string
 */
function turbosmtp_wp_default_from_email() {
	$sitename = wp_parse_url( network_home_url(), PHP_URL_HOST );
	$from     = 'wordpress@';

	if ( null !== $sitename ) {
		if ( 0 === strpos( $sitename, 'www.' ) ) {
			$sitename = substr( $sitename, 4 );
		}

		$from .= $sitename;
	}

	return $from;
}

/**
 * Plain text version of an HTML email body: tags removed, entities decoded
 * (it is not HTML anymore) and runs of blank lines collapsed.
 *
 * @param string $html
 *
 * @return string
 */
function turbosmtp_html_to_text( $html ) {
	$text = preg_replace( '@<head[^>]*?>.*?</head>@si', '', $html );
	$text = wp_strip_all_tags( $text );
	$text = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	$text = preg_replace( "/[ \t]+\n/", "\n", $text );

	return trim( preg_replace( "/\n{3,}/", "\n\n", $text ) );
}

function turbosmtp_validapi() {
	$auth_options = get_option( "ts_auth_options" );

	return isset( $auth_options['valid_api'] ) && (bool) $auth_options['valid_api'];
}

/**
 * Normalizes a consumer key the way the analytics backend stores it: lowercase,
 * the first "@" written as "_at_" and ":" as "_".
 *
 * @param string $consumer_key
 *
 * @return string
 */
function turbosmtp_consumer_key_stream_key( $consumer_key ) {
	$key = strtolower( trim( (string) $consumer_key ) );
	$pos = strpos( $key, '@' );

	if ( false !== $pos ) {
		$key = substr_replace( $key, '_at_', $pos, 1 );
	}

	return str_replace( ':', '_', $key );
}

/**
 * Resolves the consumer_key of an analytics row. Key names are not available
 * here (the consumer keys list refuses API key authentication), so only the
 * key connected to this site can be recognized.
 *
 * @param string $received consumer_key as returned by /analytics.
 *
 * @return array{consumer_key: string, is_own: bool}
 */
function turbosmtp_resolve_consumer_key( $received ) {
	$auth_options = get_option( 'ts_auth_options' );
	$own_key      = $auth_options['consumer_key'] ?? '';
	$is_own       = '' !== $own_key && turbosmtp_consumer_key_stream_key( $own_key ) === strtolower( $received );

	return array(
		'consumer_key' => $is_own ? $own_key : str_replace( '_at_', '@', $received ),
		'is_own'       => $is_own,
	);
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
		"turbosmtp_page_turbosmtp_stats"
	);

	if ( $screen != null && in_array( $screen->id, $turbo_admin_pages ) ) {
		return $screen;
	}

	return null;
}


function turbosmtp_valid_hosts() {
	$hosts = array(
		"pro.eu.turbo-smtp.com" => __( "European", "turbosmtp" ),
		"pro.turbo-smtp.com"    => __( "Not european", "turbosmtp" ),
	);

	return $hosts;
}

function turbosmtp_implode( $glue, $pieces ) {
	if ( is_array( $pieces ) ) {
		return implode( $glue, $pieces );
	}

	return $pieces;
}

/**
 * Extract data from headers
 *
 * @param $headers
 *
 * @return array
 */

function turbosmtp_get_headers_data(
	$headers
) {

	$data = [
		"content-type" => "text/plain",
		"from"         => "",
		"fromname"     => "",
		"cc" => [],
		"bcc" => [],
		"reply-to" => [],
		'headers' => []
	];

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

			$name    = trim( $name );
			$content = trim( $content );

			switch ( strtolower( $name ) ) {
				case 'content-type':

					if ( str_contains( $content, ';' ) ) {
						list( $type ) = explode( ';', $content );
						$data['content-type'] = trim( $type );
					} elseif ( '' !== trim( $content ) ) {
						$data['content-type'] = trim( $content );
					}
					break;

				case 'from':
					$bracket_pos = strpos( $content, '<' );
					if ( false !== $bracket_pos ) {
						// Text before the bracketed email is the "From" name.
						if ( $bracket_pos > 0 ) {
							$from_name        = substr( $content, 0, $bracket_pos );
							$from_name        = str_replace( '"', '', $from_name );
							$from_name        = trim( $from_name );
							$data['fromname'] = $from_name;
						}

						$from_email   = substr( $content, $bracket_pos + 1 );
						$from_email   = str_replace( '>', '', $from_email );
						$from_email   = trim( $from_email );
						$data['from'] = $from_email;

					} elseif ( '' !== trim( $content ) ) {
						$from_email   = trim( $content );
						$data['from'] = $from_email;
					}

					break;

				case 'cc':
					$data['cc'] = array_merge( (array) $data['cc'], explode( ',', $content ) );
					break;
				case 'bcc':
					$data['bcc'] = array_merge( (array) $data['bcc'], explode( ',', $content ) );
					break;
				case 'reply-to':
					$data['reply-to'] = array_merge( (array) $data['reply-to'], explode( ',', $content ) );
					break;
				default:
					$data['headers'][ trim( $name ) ] = trim( $content );
					break;
			}

		}
	}

	return $data;

}
