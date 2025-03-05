<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.dueclic.com
 * @since      4.9.0
 *
 * @package    Turbosmtp
 * @subpackage Turbosmtp/admin/partials
 *
 * @var $current_user WP_User
 * @var $turbosmtp_hosts array
 * @var $send_options array
 * @var $user_config array
 */

?>


<div class="wrap">

	<?php
	if ( isset( $_REQUEST['error'] ) ) {
		$error = sanitize_text_field( $_REQUEST['error'] );
		?>
        <div class="notice notice-error is-dismissible">
            <p>
				<?php
				if ( $error == "invalid_request" ) {
					esc_html_e( 'Invalid request', 'turbosmtp' );
				} else if ( $error == "invalid_sender_email" ) {
					esc_html_e( 'Sender email address is invalid', 'turbosmtp' );
				} else if ( $error == "invalid_smtp_email" ) {
					esc_html_e( 'SMTP Email is invalid', 'turbosmtp' );
				} else if ( $error == "invalid_smtp_server" ) {
					esc_html_e( "SMTP Server is not valid.", 'turbosmtp' );
				} else if ( $error == "sender_name_empty" ) {
					esc_html_e( "Sender name must be not empty", 'turbosmtp' );
				}
				?>
            </p>
        </div>
		<?php
	}

	if ( isset( $_REQUEST['success'] ) ) {
		?>
        <p class="turbosmtp-success">
			<?php esc_html_e( "Options saved succesfully!", 'turbosmtp' ); ?>
        </p>
		<?php
	}

	$send_method = $send_options['is_smtp'] ? 'smtp' : 'api';
	if ( isset( $_GET['send_method'] ) && in_array( $_GET['send_method'], array( 'smtp', 'api' ) ) ) {
		$send_method = sanitize_text_field( $_GET['send_method'] );
	}

	?>


    <div class="login">

        <div class="ts-pull-right">
            <p><?php _e( "Welcome", "turbosmtp" ); ?> <?php echo $user_config["email"]; ?> <a href="#" data-action="<?php echo wp_nonce_url(admin_url('admin-ajax.php?action=turbosmtp_disconnect_account'), 'turbosmtp_disconnect_account', 'turbosmtp_disconnect_account_nonce'); ?>" id="turbosmtp_disconnect"><?php esc_html_e("Disconnect", "turbosmtp"); ?></a></p>
        </div>

        <h2><?php _e( "Settings", "turbosmtp" ); ?> turboSMTP</h2>

        <div class="container-white">

            <h3><?php _e( "Sender email configuration", "turbosmtp" ); ?></h3>

            <form method="post" action="<?php echo admin_url( "admin-post.php" ); ?>">

                <input type="hidden" name="action" value="turbosmtp_save_send_options">

                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><label for="ts_smtp_host">
								<?php _e( "Sending Method", "turbosmtp" ); ?>
                            </label>
                        </th>
                        <td>
                            <select id="send_method" name="ts_send_method">
                                <option value="smtp" <?php selected( $send_method, 'smtp' ); ?>><?php _e( "SMTP", "turbosmtp" ); ?></option>
                                <option value="api" <?php selected( $send_method, 'api' ); ?>>API</option>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="ts_auth_email_from">
								<?php _e( "Sender name", "turbosmtp" ); ?>
                            </label>
                        </th>
                        <td><input type="text" id="ts_auth_email_from" name="ts_auth_email_from"
                                   value="<?php echo $send_options["fromname"]; ?>" size="43"
                                   style="width:272px;height:24px;"/></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="ts_auth_email">
								<?php _e( "Sender email address", "turbosmtp" ); ?>
                            </label>
                        </th>
                        <td><input type="text" id="ts_auth_email" name="ts_auth_email"
                                   value="<?php echo $send_options["from"]; ?>" size="43"
                                   style="width:272px;height:24px;"/></td>
                    </tr>
                </table>
                <div id="smtp_settings">
                    <h3>
						<?php _e( "turboSMTP parameters", "turbosmtp" ); ?>
                    </h3>
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row"><label for="ts_smtp_email">
									<?php _e( "Email", "turbosmtp" ); ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" id="ts_smtp_email" name="ts_smtp_email"
                                       value="<?php echo $send_options["email"]; ?>"/>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label for="ts_smtp_password">
									<?php _e( "Password", "turbosmtp" ); ?>
                                </label>
                            </th>
                            <td>

								<?php
								if ( ! defined( "TURBOSMTP_SMTP_PASSWORD" ) ):
									?>


                                    <input type="password" id="ts_smtp_password" name="ts_smtp_password"
                                           value=""/> <span class="description">
                                <?php _e( "Password is stored in clear in WordPress database, consider to define it in wp-config.php AS TURBOSMTP_SMTP_PASSWORD", "turbosmtp" ); ?>
							</span>


								<?php
								else:
									?>
									<?php
									_e( "Safely stored in wp-config.php", "turbosmtp" );
									?>
								<?php
								endif;
								?>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><label for="ts_smtp_host">
									<?php _e( "SMTP Server", "turbosmtp" ); ?>
                                </label>
                            </th>
                            <td>

                                <select name="ts_smtp_host">
									<?php
									foreach ( $turbosmtp_hosts as $host => $label ):
										?>
                                        <option <?php selected( $host, $send_options['host'] ); ?>
                                                value="<?php echo $host; ?>">
											<?php echo $label; ?> (<?php echo $host; ?>)
                                        </option>
									<?php
									endforeach;
									?>
                                </select>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label for="ts_smtp_mailport">
									<?php _e( "SMTP Port", "turbosmtp" ); ?>
                                </label>
                            </th>
                            <td><input type="text" id="ts_smtp_mailport" name="ts_smtp_mailport"
                                       value="<?php echo $send_options["port"]; ?>" size="43"
                                       style="width:50px;height:24px;"/>
                                <br>
                                <span class="description">
                                <?php _e( "Use port 25, 587 or 2525 for non encrypted connections, 465 or 25025 for encrypted connections", "turbosmtp" ); ?>
							</span></td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><?php _e( "SSL", "turbosmtp" ); ?>
                            </th>
                            <td>
                                <p>
                                    <input id="turboSMTP_mail_smtpsecure_none" name="ts_smtp_smtpsecure" type="radio"
                                           value=""<?php if ( $send_options["smtpsecure"] == '' ) { ?> checked="checked"<?php } ?> />
                                    <label for="ts_smtp_smtpsecure">
										<?php _e( "No encryption (non-SSL)", "turbosmtp" ); ?>
                                    </label>
                                </p>
                                <p>
                                    <input id="turboSMTP_mail_smtpsecure_ssl" name="ts_smtp_smtpsecure" type="radio"
                                           value="ssl"<?php if ( $send_options["smtpsecure"] == 'ssl' ) { ?> checked="checked"<?php } ?> />
                                    <label for="turboSMTP_mail_smtpsecure_ssl">
										<?php _e( "Use SSL", "turbosmtp" ); ?>
                                    </label>
                                </p>
                            </td>
                        </tr>

                    </table>
                </div>
                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="std"
                           value="<?php _e( "Save changes", "turbosmtp" ); ?>">
                </p>
				<?php
				wp_nonce_field( 'turbosmtp_save_send_options', 'turbosmtp_nonce' );
				?>

            </form>

        </div>

        <div class="container-white">

            <h3><?php _e( "Send test email", "turbosmtp" ); ?></h3>

            <form method="POST" action="">
                <input type="hidden" name="action" value="turbosmtp_send_test_email">
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><label for="ts_mail_to">
								<?php _e( "Recipient", "turbosmtp" ); ?>
                            </label>
                        </th>
                        <td><input type="text" id="ts_mail_to" name="ts_mail_to" value="<?php echo esc_html($current_user->user_email); ?>" size="43"
                                   style="width:272px;height:24px;"/>
                            <br>
                            <span class="description">
                                <?php _e( "Write your email address and click on \"Send test\"", "turbosmtp" ); ?>
							</span></td>
                    </tr>
                </table>
                <p class="submit">
					<?php
					wp_nonce_field( 'turbosmtp_send_test_email', 'turbosmtp_send_test_email_nonce' );
					?>
                    <button id="turbosmtp_send_test_email" class="std">
                        <?php _e( "Send test", "turbosmtp" ); ?>
                    </button>
                </p>
            </form>

            <p id="turbosmtp-email-result"></p>

        </div>

    </div>
</div>
