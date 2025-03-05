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
			'sender'         => __( 'From', 'turbosmtp' ),
			'recipient'           => __( 'To', 'turbosmtp' ),
			'send_time'     => __( 'Date / Time', 'turbosmtp' ),
			'error'        => __( 'Error description', 'turbosmtp' ),
		);

		return $columns;

	}
	function display() {
		wp_nonce_field( 'turbosmtp_get_stats_history', 'turbosmtp_get_stats_history_nonce' );


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

	function get_ts_data(  ) {

		return $this->api->get_analytics( [
			'from' => $this->from,
			'to' => $this->end,
			'status' => $this->filter,
			'page' =>  $this->get_pagenum()	,
			'limit' => 10,
		]  );

	}

	function ajax_response() {

		check_ajax_referer( 'turbosmtp_get_stats_history', 'turbosmtp_get_stats_history_nonce' );

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
		$hidden                = array(  "error" );
		$sortable              = array();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$data  = $this->get_ts_data( );

		$this->total_items = $data['count'];
        $this->items = $data['results'];


		$this->set_pagination_args( array(
			'total_items' => (int) $this->total_items,
			'per_page'    => $this->per_page,
		) );

	}

	function column_default( $item, $column_name ) {
        if ($column_name === 'status'){
	        return turbosmtp_get_icon($item);
        }
        if ($column_name === 'send_time'){
	        return date( "d/m/Y H:i", strtotime( $item[ $column_name ] ) );
        }
		return $item[ $column_name ];
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
