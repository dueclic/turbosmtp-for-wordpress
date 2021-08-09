<?php

	function TurboSMTP_Menu() {

		add_menu_page(__("turboSMTP integration and configuration", "turbosmtp"), "turboSMTP", "manage_options", "ts-dash", "TSConfiguration_Page", plugins_url('icons/ts_icon.png',__FILE__ ), 80);

		add_submenu_page("ts-dash", __("turboSMTP integration and configuration", "turbosmtp"), __("Configuration", "turbosmtp"), "manage_options", "ts-dash", "TSConfiguration_Page");

        if (turbosmtp_validapi()) {

	        add_submenu_page("ts-dash", __("Report", "turbosmtp"), __("Report", "turbosmtp"), "manage_options", "ts-stats", "TSStats");
	        add_submenu_page("ts-dash", __("Logout", "turbosmtp"), __("Logout", "turbosmtp"), "manage_options", "ts-logout", "TSLogout");

        }

	}

	function TSConfiguration_Page() {

		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You don\'t have the right permission to view this page!', 'turbosmtp' ) );
		}

		$ts_nonce = wp_create_nonce('ts_nonce');
		global $ts_options;
		global $ts_send_options;

		require("turbo-config-section.php");

		do_action('ts_change_footer_copyright');

	}

	function TSStats() {

		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You don\'t have the right permission to view this page!', 'turbosmtp'  ) );
		}

		require("turbo-stats-section.php");


		do_action('ts_change_footer_copyright');

	}

	function TSLogout() {

		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You don\'t have the right permission to view this page!', 'turbosmtp' ) );
		}

		$ts_nonce = wp_create_nonce('ts_nonce');

		require("turbo-logout-section.php");

		do_action('ts_change_footer_copyright');

	}

	function get_stats_chart() {

		global $ts_options;


		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

			try {

				$api = new TurboApiStats($ts_options["op_ts_email"], $ts_options["op_ts_password"]);

				$start_date = sanitize_text_field($_POST['start_date']);
				$end_date = sanitize_text_field($_POST['end_date']);

				print json_encode($api->getStats($start_date, $end_date));

			}

			catch (Exception $e) {
				print $e->getMessage();
			}

		}
		die();

	}
