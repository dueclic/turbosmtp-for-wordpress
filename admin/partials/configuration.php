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


$send_method = $send_options['is_smtp'] ? 'smtp' : 'api';

if (isset($_GET['send_method']) && in_array($_GET['send_method'], array('smtp', 'api'))) {
    $send_method = sanitize_text_field($_GET['send_method']);
}

if (isset($_REQUEST['error'])) {
    $error = sanitize_text_field($_REQUEST['error']);
    ?>
    <div class="notice notice-error is-dismissible">
        <p>
            <?php
            if ($error == "invalid_request") {
                esc_html_e('Invalid request', 'turbosmtp');
            } else if ($error == "invalid_sender_email") {
                esc_html_e('Sender email address is invalid', 'turbosmtp');
            } else if ($error == "invalid_smtp_server") {
                esc_html_e("SMTP Server is not valid.", 'turbosmtp');
            } else if ($error == "sender_name_empty") {
                esc_html_e("Sender name must be not empty", 'turbosmtp');
            } else if ($error == "invalid_smtp_credentials") {
                esc_html_e("Your API key cannot send via SMTP. In the turboSMTP dashboard, give it the \"SMTP Sending\" permission, or use the API sending method.", 'turbosmtp');
            } else if ($error == "smtp_connection_failed") {
                esc_html_e("Could not connect to the SMTP server. Check the server, port and SSL settings, and that your hosting allows outgoing connections on that port.", 'turbosmtp');
            }
            ?>
        </p>
    </div>
    <?php
}

if (isset($_REQUEST['success'])) {
    ?>
    <div class="notice notice-success is-dismissible">
        <p><?php esc_html_e("Options saved succesfully!", 'turbosmtp'); ?></p>
    </div>
    <?php
}
?>
<div class="wrap">
    <hr class="wp-header-end">
    <div class="tswp-main-container">
        <div class="tswp-main-account">
            <div class="tswp-forms-logo">
                <img src="<?php echo plugins_url("/admin/img/ts-logo.svg", TURBOSMTP_BASE_PATH); ?>" alt="turboSMTP">
                <div class="tswp-account-status">
                    <div><?php _e("Account connected", "turbosmtp"); ?></div>
                    <div class="tswp-account-connected" aria-hidden="true"></div>
                </div>
            </div>
            <div class="tswp-account-info">
                <span class="flex-grow-1 truncate"
                      title="<?php echo esc_attr($user_config["email"]); ?>"><strong><?php echo esc_html($user_config["email"]); ?></strong></span>
                <span>
                    <a href="#" role="button" class="tswp-account-disconnect" title="<?php esc_attr_e("Disconnect account", "turbosmtp"); ?>" aria-label="<?php esc_attr_e("Disconnect account", "turbosmtp"); ?>"
                       data-action="<?php echo wp_nonce_url(admin_url('admin-ajax.php?action=turbosmtp_disconnect_account'), 'turbosmtp_disconnect_account', 'turbosmtp_disconnect_account_nonce'); ?>"
                       id="turbosmtp_disconnect">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" aria-hidden="true" focusable="false"><path
                                    d="M280 24c0-13.3-10.7-24-24-24s-24 10.7-24 24l0 240c0 13.3 10.7 24 24 24s24-10.7 24-24l0-240zM134.2 107.3c10.7-7.9 12.9-22.9 5.1-33.6s-22.9-12.9-33.6-5.1C46.5 112.3 8 182.7 8 262C8 394.6 115.5 502 248 502s240-107.5 240-240c0-79.3-38.5-149.7-97.8-193.3c-10.7-7.9-25.7-5.6-33.6 5.1s-5.6 25.7 5.1 33.6c47.5 35 78.2 91.2 78.2 154.7c0 106-86 192-192 192S56 368 56 262c0-63.4 30.7-119.7 78.2-154.7z"></path></svg>
                    </a>
                </span>
            </div>
        </div>
        <div class="tswp-main-forms">
            <h1><?php _e('Welcome to turboSMTP', "turbosmtp"); ?></h1>
            <p><?php _e('Enhance your WordPress email performance by integrating turboSMTP, a professional SMTP service. This ensures reliable delivery and enables advanced tracking and detailed reporting, giving you insights into your email outreach. Set up and configure your turboSMTP account details in this section.', 'turbosmtp'); ?></p>
            <div class="tswp-form card">
                <h2><?php _e('Settings', "turbosmtp"); ?></h2>
                <p><?php _e('Define your sender details here to control how your WordPress emails are sent. Select your desired connection method, either the turboSMTP API or manual SMTP settings, to tailor the plugin to your needs.', "turbosmtp"); ?></p>

                <form method="post" action="<?php echo admin_url("admin-post.php"); ?>">

                    <input type="hidden" name="action" value="turbosmtp_save_send_options">

                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="ts_auth_email_from">
                                    <?php _e("Sender name", "turbosmtp"); ?>
                                </label>
                            </th>
                            <td><input type="text" id="ts_auth_email_from" name="ts_auth_email_from"
                                       value="<?php echo esc_attr($send_options["fromname"]); ?>" class="regular-text "/></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="ts_auth_email">
                                    <?php _e("Sender email address", "turbosmtp"); ?>
                                </label>
                            </th>
                            <td><input type="email" id="ts_auth_email" name="ts_auth_email"
                                       value="<?php echo esc_attr($send_options["from"]); ?>" class="regular-text"/></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="send_method">
                                    <?php _e("Sending Method", "turbosmtp"); ?>
                                </label>
                            </th>
                            <td>
                                <select id="send_method" name="ts_send_method">
                                    <option value="smtp" <?php selected($send_method, 'smtp'); ?>><?php _e("SMTP", "turbosmtp"); ?></option>
                                    <option value="api" <?php selected($send_method, 'api'); ?>>API</option>
                                </select>
                            </td>
                        </tr>
                    </table>
                    <div id="smtp_settings">
                        <hr class="tswp-hr-separator-half">
                        <h3>
                            <?php _e("turboSMTP SMTP parameters", "turbosmtp"); ?>
                        </h3>
                        <p><?php _e('SMTP authentication uses your connected API key: the Consumer Key is the username and the Consumer Secret is the password. The key needs the "SMTP Sending" permission, which is checked when you save.', "turbosmtp"); ?></p>
                    <table class="form-table">
                            <tr valign="top">
                                <th scope="row"><label for="ts_smtp_host">
                                        <?php _e("SMTP Server", "turbosmtp"); ?>
                                    </label>
                                </th>
                                <td>

                                    <select id="ts_smtp_host" name="ts_smtp_host" class="regular-text">
                                        <?php
                                        foreach ($turbosmtp_hosts as $host => $label):
                                            ?>
                                            <option <?php selected($host, $send_options['host']); ?>
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
                                <th scope="row"><?php _e("SSL", "turbosmtp"); ?></th>
                                <td>
                                    <fieldset>
                                        <legend class="screen-reader-text"><?php _e("SSL", "turbosmtp"); ?></legend>
                                        <p>
                                            <input id="turboSMTP_mail_smtpsecure_none" name="ts_smtp_smtpsecure" type="radio"
                                                   value="" <?php checked($send_options["smtpsecure"], ''); ?> />
                                            <label for="turboSMTP_mail_smtpsecure_none">
                                                <?php _e("No encryption (non-SSL)", "turbosmtp"); ?>
                                            </label>
                                        </p>
                                        <p>
                                            <input id="turboSMTP_mail_smtpsecure_ssl" name="ts_smtp_smtpsecure" type="radio"
                                                   value="ssl" <?php checked($send_options["smtpsecure"], 'ssl'); ?> />
                                            <label for="turboSMTP_mail_smtpsecure_ssl">
                                                <?php _e("Use SSL", "turbosmtp"); ?>
                                            </label>
                                        </p>
                                    </fieldset>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row"><label for="ts_smtp_mailport">
                                        <?php _e("SMTP Port", "turbosmtp"); ?>
                                    </label>
                                </th>
                                <td><input type="text" inputmode="numeric" id="ts_smtp_mailport" name="ts_smtp_mailport"
                                           aria-describedby="ts_smtp_mailport_description"
                                           value="<?php echo esc_attr($send_options["port"]); ?>" />
                                    <div class="description" id="ts_smtp_mailport_description" style="margin-top: .5em">
                                        <small>
                                            <?php _e("Use port 25, 587 or 2525 for non SSL encrypted connections, 465 or 25025 for SSL encrypted connections", "turbosmtp"); ?>
                                        </small>
                                    </div>
                                </td>
                            </tr>

                        </table>
                    </div>
                    <p class="submit">
                        <input type="submit" name="submit" id="submit" class="button button-primary"
                               value="<?php _e("Save changes", "turbosmtp"); ?>">
                    </p>
                    <?php
                    wp_nonce_field('turbosmtp_save_send_options', 'turbosmtp_nonce');
                    ?>

                </form>

            </div>

            <div class="tswp-main-forms">
               <div class="tswp-form card">
                   <h2><?php _e('Send test email', "turbosmtp"); ?></h2>
                   <p><?php _e('Ensure your setup is correct! Enter a recipient email below to send a test email and verify that your turboSMTP settings are working smoothly.', "turbosmtp"); ?></p>

                   <form method="POST" action="">
                       <input type="hidden" name="action" value="turbosmtp_send_test_email">
                       <table class="form-table">
                           <tr valign="top">
                               <th scope="row"><label for="ts_mail_to">
                                       <?php _e("Recipient", "turbosmtp"); ?>
                                   </label>
                               </th>
                               <td><input type="email" id="ts_mail_to" name="ts_mail_to" class="regular-text"
                                          autocomplete="email" aria-describedby="ts_mail_to_description"
                                          value="<?php echo esc_attr($current_user->user_email); ?>" />

                                   <div class="description" id="ts_mail_to_description" style="margin-top: .5em">
                                       <small>
                                           <?php _e("Write your email address and click on \"Send email\"", "turbosmtp"); ?>
                                       </small>
                                   </div>

                               </td>
                           </tr>
                       </table>


                       <p class="submit tswp-email-submit">
                           <?php
                           wp_nonce_field('turbosmtp_send_test_email', 'turbosmtp_send_test_email_nonce');
                           ?>
                           <button id="turbosmtp_send_test_email" class="button button-primary">
                               <?php _e("Send email", "turbosmtp"); ?>
                           </button>
                            <span id="turbosmtp-email-result" role="status" aria-live="polite"></span>
                       </p>

                   </form>
               </div>
            </div>

        </div>

    </div>
</div>
