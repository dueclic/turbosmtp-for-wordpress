<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

function get_icon( $item ) {

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

class TS_Ajax_List_Table extends WP_List_Table {

	private $begin;
	private $end;
	private $total_items;
	private $filter;
	private $csv_download;

	function getCSVDownload() {
		return $this->csv_download;
	}

	function get_columns() {

		$columns = array(
			'cb'           => '',
			'subject'      => __( 'Subject', 'turbosmtp' ),
			'subject_comp' => '',
			'from'         => __( 'From', 'turbosmtp' ),
			'to'           => __( 'To', 'turbosmtp' ),
			'datetime'     => __( 'Date / Time', 'turbosmtp' ),
			'error'        => __( 'Error description', 'turbosmtp' ),
		);

		return $columns;

	}

	function display() {
		wp_nonce_field( 'ajax-custom-list-nonce', '_ajax_custom_list_nonce' );

		/**
		 *
		 * Fix for Wordpress 4.3.8
		 *
		 */

		/*$singular = $this->_args['singular'];
		$this->screen->render_screen_reader_content( 'heading_list' );*/

		?>
        <table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
            <thead>
            <tr>
				<?php $this->print_column_headers(); ?>
            </tr>
            </thead>

            <tbody id="the-list"<?php
			/*if ( $singular ) {
				echo " data-wp-lists='list:$singular'";
			}*/ ?>>
			<?php $this->display_rows_or_placeholder(); ?>
            </tbody>

        </table>
		<?php
		$this->display_tablenav( 'top' );
	}

	function get_ts_data( $page_number ) {

		global $ts_options;

		$data = array();

		$api     = new TurboApiStats( $ts_options["op_ts_email"], $ts_options["op_ts_password"] );
		$ts_data = $api->getEmails( $this->begin, $this->end, $this->filter, $this->get_pagenum() );

		if ( isset( $ts_data["is_free"] ) ) {
			return $ts_data;
		}

		$ts_emails = $ts_data['emails'];

		foreach ( $ts_emails as $email ) {

			array_push( $data, array(

				"subject"      => ( strlen( $email['subject'] ) > 40 ? substr( $email['subject'], 0, 40 ) . "..." : $email['subject'] ),
				"subject_comp" => $email['subject'],
				"from"         => $email['sender'],
				"to"           => $email['recipient'],
				"datetime"     => $email['sendTime'],
				"status"       => $email['status'],
				"opened"       => isset( $email['opened'] ) ? $email['opened'] : false,
				"spam"         => isset( $email['spam'] ) ? $email['spam'] : false,
				"unsub"        => isset( $email['unsub'] ) ? $email['unsub'] : false,
				"clicked"      => isset( $email['clicked'] ) ? $email['clicked'] : false,
				"error"        => $email['error'],

			) );

		}

		$this->total_items = $ts_data['total'];

		return $data;

	}

	function ajax_response() {

		check_ajax_referer( 'ajax-custom-list-nonce', '_ajax_custom_list_nonce' );

		$this->prepare_items();

		extract( $this->_args );
		extract( $this->_pagination_args, EXTR_SKIP );

		ob_start();
		if ( ! empty( $_REQUEST['no_placeholder'] ) ) {
			$this->display_rows();
		} else {
			$this->display_rows_or_placeholder();
		}
		$rows = ob_get_clean();

		ob_start();
		$this->print_column_headers();
		$headers = ob_get_clean();

		ob_start();
		$this->pagination( 'top' );
		$pagination_top = ob_get_clean();

		ob_start();
		$this->pagination( 'bottom' );
		$pagination_bottom = ob_get_clean();

		$response                         = array( 'rows' => $rows );
		$response['pagination']['top']    = $pagination_top;
		$response['pagination']['bottom'] = $pagination_bottom;
		$response['column_headers']       = $headers;

		if ( isset( $total_items ) ) {
			$response['total_items_i18n'] = sprintf( _n( '1 item', '%s items', $total_items ), number_format_i18n( $total_items ) );
		}

		if ( isset( $total_pages ) ) {
			$response['total_pages']      = $total_pages;
			$response['total_pages_i18n'] = number_format_i18n( $total_pages );
		}

		die( wp_json_encode( $response ) );

	}

	function prepare_items() {
		$columns               = $this->get_columns();
		$hidden                = array( "spam", "unsub", "clicked", "status", "opened", "subject_comp", "error" );
		$sortable              = array();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$current_page = $this->get_pagenum();
		$this->items  = $this->get_ts_data( $current_page );

		$per_page = 10;

		$this->set_pagination_args( array(
			'total_items' => (int) $this->total_items,
			'per_page'    => $per_page,
		) );

	}

	function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'status':
				return get_icon( $item );
			case 'subject':
			case 'from':
			case 'to':
				return $item[ $column_name ];
			case 'datetime':
				return date( "d/m/Y H:i", strtotime( $item[ $column_name ] ) );
			default:
				return $item[ $column_name ];
		}
	}

	function column_cb( $item ) {
		return get_icon( $item );
	}

	public function __construct( $begin, $end, $filter = false ) {

		parent::__construct( array(
			'singular' => __( 'sent email', 'turbosmtp' ),
			'plural'   => __( 'sent emails', 'turbosmtp' ),
			'ajax'     => true,
		) );

		$this->begin  = date( "Y-m-d H:i:s", strtotime( $begin ) );
		$this->end    = $end . " 23:59:59";
		$this->filter = $filter;

	}

}

?>
