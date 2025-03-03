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
 */

?>


<div class="wrap turbo-about" id="demo-client">

    <div class="register register-center">
        <div class="box">

            <div class="turbo-promo">
                <a href="https://dashboard.serversmtp.com/web/whmcs-redirect?to=clientarea.php">
                    <img src="<?php echo plugins_url( "admin/img/turbo_upgrade.png", TURBOSMTP_BASE_PATH ); ?>">
                </a>
            </div>

            <h3>
				<?php _e( "Upgrade your plan to unlock statistics", "turbosmtp" ); ?>
            </h3>
            <p>
                <span class="ui-icon-alert"></span> <?php _e( "You are currently on a free trial plan. Please upgrade to view the full dashboard.", "turbosmtp" ); ?>
            </p>
            <p class="submit" style="text-align:center;">
                <a class="std" href="https://dashboard.serversmtp.com/web/whmcs-redirect?to=clientarea.php">
					<?php _e( "Upgrade plan", "turbosmtp" ); ?>
                </a>
            </p>
        </div>
    </div>
</div>

