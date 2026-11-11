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
 * @var $begin string
 * @var $end string
 */


?>
<div class="tswp-stats">
    <h1 class="screen-reader-text"><?php esc_html_e( "turboSMTP Report", "turbosmtp" ); ?></h1>
    <div class="error-tooltip" aria-hidden="true"></div>

    <div class="ts-loading other-infos-loading" role="status">

        <div class="ts-spinner">
            <div class="rect1"></div>
            <div class="rect2"></div>
            <div class="rect3"></div>
            <div class="rect4"></div>
            <div class="rect5"></div>
        </div>
        <h3><?php _e( "Loading analytics data...", "turbosmtp" ); ?></h3>

    </div>

    <div class="wrap" id="paid-client" style="display:none;">

        <div class="actions bulkactions tswp-stats-top">

            <form method="post" action="" class="change_date">

                <label for="ts_date_range" class="screen-reader-text"><?php esc_html_e( "Date range", "turbosmtp" ); ?></label>
                <input id="ts_date_range" name="from_date" value="" type="text" autocomplete="off">

                <?php

                foreach ( $_GET as $key => $value ) {

                    $key   = sanitize_text_field( $key );
                    $value = sanitize_text_field( $value );

                    if ( 'from' != $key && 'to' != $key ) {
                        echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" />';
                    }
                }

                ?>
                <span class="dashicons dashicons-calendar-alt" aria-hidden="true"></span>

            </form>
            <div>
                <?php _e( "Need more statistics?", "turbosmtp" ); ?> <a href="https://dashboard.serversmtp.com/analytics/overview" target="_blank" rel="noopener"><?php _e( "Check turboSMTP dashboard", "turbosmtp" ); ?><span class="screen-reader-text"> <?php esc_html_e( "(opens in a new tab)", "turbosmtp" ); ?></span></a>.
            </div>

        </div>

        <div class="other-infos">

            <div class="other-infos-header">
                <div class="other-infos-toggle">
                    <button type="button" aria-expanded="true" aria-controls="turbo-stat-chart-wrap"
                            aria-label="<?php esc_attr_e( "Hide chart", "turbosmtp" ); ?>"><span class="icon-arrow-down" aria-hidden="true"></span></button>
                </div>
                <h2><span class="icon-stat" aria-hidden="true"></span> <?php esc_html_e("Line Chart", "turbosmtp"); ?></h2>
            </div>

            <div id="turbo-stat-chart-wrap" class="tswp-chart-wrap">
                <canvas id="turbo-stat-chart" role="img"
                        aria-label="<?php esc_attr_e( "Line chart of email events per day in the selected period. The totals are listed below the chart.", "turbosmtp" ); ?>"></canvas>
            </div>

        </div>

        <div class="notice notice-error other-infos-noresults" role="status">
            <p>
                <?php _e( "No results found for your temporal choice.", "turbosmtp" ); ?>
            </p>
        </div>

        <div class="other-infos-columns" role="group" aria-label="<?php esc_attr_e( "Filter the email history by event", "turbosmtp" ); ?>">
            <div class="total-email active" data-ts-filter="all" role="button" tabindex="0" aria-pressed="true">

                <div class="panel">

                    <div class="heading">
                        <h3><?php _e( "total sent emails", "turbosmtp" ); ?></h3>
                    </div>

                    <div class="body">
                        <h4></h4>
                    </div>

                    <div class="foot">
                        <p></p>
                    </div>

                </div>

            </div>
            <div class="delivered" data-ts-filter="delivered" role="button" tabindex="0" aria-pressed="false">
                <div class="panel">

                    <div class="heading">
                        <h3><?php _e( "delivered emails", "turbosmtp" ); ?></h3>
                    </div>

                    <div class="body">
                        <h4></h4>
                    </div>

                    <div class="foot">
                        <p></p>
                    </div>

                </div>
            </div>
            <div class="opens" data-ts-filter="opens" role="button" tabindex="0" aria-pressed="false">
                <div class="panel">

                    <div class="heading">
                        <h3><?php _e( "opened emails", "turbosmtp" ); ?></h3>
                    </div>

                    <div class="body">
                        <h4></h4>
                    </div>

                    <div class="foot">
                        <p></p>
                    </div>

                </div>
            </div>
            <div class="clicks" data-ts-filter="clicks" role="button" tabindex="0" aria-pressed="false">
                <div class="panel">

                    <div class="heading">
                        <h3><?php _e( "clicked emails", "turbosmtp" ); ?></h3>
                    </div>

                    <div class="body">
                        <h4></h4>
                    </div>

                    <div class="foot">
                        <p></p>
                    </div>

                </div>
            </div>
            <div class="bounce" data-ts-filter="bounce" role="button" tabindex="0" aria-pressed="false">
                <div class="panel">

                    <div class="heading">
                        <h3><?php _e( "bounces", "turbosmtp" ); ?></h3>
                    </div>

                    <div class="body">
                        <h4></h4>
                    </div>

                    <div class="foot">
                        <p></p>
                    </div>

                </div>
            </div>
            <div class="other-stats">
                <div class="unsubscribes" data-ts-filter="unsubscribes" role="button" tabindex="0" aria-pressed="false">
                    <div class="heading">
                        <h3><?php _e( "unsubscribes", "turbosmtp" ); ?></h3>
                    </div>
                    <div class="body">
                        <p></p>
                    </div>
                </div>
                <div class="spam" data-ts-filter="spam" role="button" tabindex="0" aria-pressed="false">
                    <div class="heading">
                        <h3><?php _e( "spam reports", "turbosmtp" ); ?></h3>
                    </div>
                    <div class="body">
                        <p></p>
                    </div>
                </div>
                <div class="queued" data-ts-filter="queued" role="button" tabindex="0" aria-pressed="false">
                    <div class="heading">
                        <h3><?php _e( "queued", "turbosmtp" ); ?></h3>
                    </div>
                    <div class="body">
                        <p></p>
                    </div>
                </div>
                <div class="drop" data-ts-filter="drop" role="button" tabindex="0" aria-pressed="false">
                    <div class="heading">
                        <h3><?php _e( "drop", "turbosmtp" ); ?></h3>
                    </div>
                    <div class="body">
                        <p></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="ts-loading history-email-loading" role="status">

            <div class="ts-spinner">
                <div class="rect1"></div>
                <div class="rect2"></div>
                <div class="rect3"></div>
                <div class="rect4"></div>
                <div class="rect5"></div>
            </div>
            <h3><?php _e( "Loading emails history...", "turbosmtp" ); ?></h3>

        </div>

        <div class="history-email">

            <form id="email-sent-list" class="history-step" method="get">

                <div class="ts-history-table-loading">
                    <div class="ts-spinner">
                        <div class="rect1"></div>
                        <div class="rect2"></div>
                        <div class="rect3"></div>
                        <div class="rect4"></div>
                        <div class="rect5"></div>
                    </div>
                </div>

                <?php
                $page_value = isset( $_REQUEST['page'] ) ? sanitize_text_field( $_REQUEST['page'] ) : '';
                ?>

                <input type="hidden" name="page" value="<?php echo esc_attr( $page_value ); ?>"/>

                <div id="ts-history-table" style="">
                    <?php
                    $wp_list_table = new Turbosmtp_Messages_List_Table(
                        $this->api,
                        $begin,
                        $end,
                        apply_filters( 'turbosmtp_stats_per_page', 10 ),
                        "all"
                    );
                    $wp_list_table->prepare_items();
                    $wp_list_table->display();

                    ?>
                </div>

            </form>
        </div>

    </div>
</div>
