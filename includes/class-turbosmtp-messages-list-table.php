<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Turbosmtp_Messages_List_Table extends WP_List_Table {

	private $total_items;

	private $per_page;

	private $from;
	private $end;
	private $filter;

	/**
	 * @var Turbosmtp_Api $api
	 */
	private $api;
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


		?>
		<table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
			<thead>
			<tr>
				<?php $this->print_column_headers(); ?>
			</tr>
			</thead>

			<tbody id="the-list">
			<?php $this->display_rows_or_placeholder(); ?>
			</tbody>

		</table>
		<?php
		$this->display_tablenav( 'top' );
	}

	function get_ts_data( $page_number ) {

		$data = array();

		$ts_data = $this->api->get_analytics( [
			'from' => $this->from,
			'to' => $this->end,
			'status' => $this->filter,
			'page' =>  $this->get_pagenum()	,
			'limit' => 10,
		]  );

		$ts_emails = $ts_data['results'];

		foreach ( $ts_emails as $email ) {

			$data[] = array(

				"subject"      => ( strlen( $email['subject'] ) > 40 ? substr( $email['subject'], 0, 40 ) . "..." : $email['subject'] ),
				"subject_comp" => $email['subject'],
				"from"         => $email['sender'],
				"to"           => $email['recipient'],
				"datetime"     => $email['send_time'],
				"status"       => $email['status']
			);

		}

		$this->total_items = $ts_data['count'];

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
		$hidden                = array( );
		$sortable              = array();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$current_page = $this->get_pagenum();
		$this->items  = $this->get_ts_data( $current_page );

		$this->set_pagination_args( array(
			'total_items' => (int) $this->total_items,
			'per_page'    => $this->per_page,
		) );

	}

	function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'status':
				return turbosmtp_get_icon($item);
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
		return turbosmtp_get_icon($item) ;
	}

	/**
	 * @param Turbosmtp_Api $api
	 * @param $from
	 * @param $end
	 * @param int $per_page
	 * @param bool $filter
	 */

	public function __construct( $api, $from, $end, $per_page = 10, $filter = false ) {

		$this->api = $api;

		parent::__construct( array(
			'singular' => __( 'sent email', 'turbosmtp' ),
			'plural'   => __( 'sent emails', 'turbosmtp' ),
			'ajax'     => true,
		) );

		$this->from  = $from;
		$this->end    = $end;
		$this->filter = $filter;
		$this->per_page = $per_page;

	}

}
