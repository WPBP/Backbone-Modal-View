<?php
/**
 * @package   Backbone-Modal-View
 * @author    Mte90
 * @license   GPL-3.0+
 * @link      http://mte90.net
 * @copyright 2016 GPL
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add on a button a Backbone Modal View with a list
 */
class BB_Modal_View {

	/**
	 * Construct the class parameter
	 * 
	 * @param array $args Parameters of class.
	 * @return void
	 */
	function __construct( $args = array() ) {
		$defaults = array(
			'id' => 'test',
			'hook' => 'admin_notices',
			'label' => __( 'Open Modal' ),
			'data' => array( 'rand' => rand() ),
			'ajax' => '',
			'echo_button' => true
		);
		$this->args = wp_parse_args( $args, $defaults );
		if ( $this->args[ 'echo_button' ] ) {
			add_action( $this->args[ 'hook' ], array( $this, 'btn_modal_echo' ) );
		} else {
			add_action( $this->args[ 'hook' ], array( $this, 'btn_modal' ) );
		}
		add_action( 'admin_head', array( $this, 'append_resource_modal' ) );
		add_action( 'admin_footer', array( $this, 'append_modal' ) );
	}

	/**
	 * Assign tax to users
	 *
	 * @param string $value The button to show.
	 * @param string $column_name The id of the column.
	 * @param string $user_id The user ID.
	 */
	public function btn_modal() {
		$data = '';
		foreach ( $this->args[ 'data' ] as $key => $value ) {
			$data .= 'data-' . str_replace( ' ', '-', $key ) . '="' . $value . '" ';
		}
		$value = '<a href="#" class="button bb-modal-button modal-' . $this->args[ 'id' ] . '" data-id="' . $this->args[ 'id' ] . '" data-ajax="' . $this->args[ 'ajax' ] . '" ' . $data . '>' . $this->args[ 'label' ] . '</a>';
		return $value;
	}

	public function btn_modal_echo() {
		echo $this->btn_modal();
	}

	/**
	 * Append the modal
	 */
	public function append_resource_modal() {
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'wp-backbone' );
		//wp_enqueue_script( 'bb-modal-view', plugins_url( '/assets/js/public.js', dirname( __FILE__ ) ), array( 'jquery', 'wp-backbone' ) );
		wp_enqueue_script( 'bb-modal-view', 'https://boilerplate.dev/wp-content/plugins/Backbone-Modal-View/assets/js/public.js', array( 'jquery', 'wp-backbone' ) );
	}

	/**
	 * 
	 * Based on find_posts
	 * 
	 * @param type $found_action
	 */
	public function append_modal( $found_action = '' ) {
		?>
		<style>
			#bb-modal-view-close {
				width: 36px;
				height: 36px;
				position: absolute;
				top: 0px;
				right: 0px;
				cursor: pointer;
				text-align: center;
				color: #666;
			}
			#bb-modal-view-close::before {
				font: 400 20px/36px dashicons;
				vertical-align: top;
				content: "";
			}
			#bb-modal-view-close:hover {
				color: #00A0D2;
			}
		</style>
		<div id="bb-modal-view-<?php echo $this->args[ 'id' ]; ?>" class="bb-modal-view" style="display: none;">
			<div class="bb-modal-view-head">
				<?php _e( 'Task' ); ?>
				<div id="bb-modal-view-close"></div>
			</div>
			<div class="bb-modal-view-inside">
				<div class="bb-modal-view-search">
					<?php if ( $found_action ) { ?>
						<input type="hidden" name="found_action" value="<?php echo esc_attr( $found_action ); ?>" />
					<?php } ?>
					<input type="hidden" name="affected" id="affected" value="" />
					<?php wp_nonce_field( '#' . $this->args[ 'id' ], '_ajax_nonce', false ); ?>
					<label class="screen-reader-text" for="#<?php echo $this->args[ 'id' ]; ?>-input"><?php _e( 'Search' ); ?></label>
					<input type="text" id="<?php echo $this->args[ 'id' ]; ?>-input" name="ps" value="" autocomplete="off" />
					<span class="spinner"></span>
					<input type="button" id="<?php echo $this->args[ 'id' ]; ?>-search" value="<?php esc_attr_e( 'Search' ); ?>" class="button" />
					<div class="clear"></div>
				</div>
				<div id="bb-modal-view-response"></div>
			</div>
			<div class="bb-modal-view-buttons">
				<?php submit_button( __( 'Select' ), 'button-primary alignright', $this->args[ 'id' ] . '-submit', false ); ?>
				<div class="clear"></div>
			</div>
		</div>
		<?php
	}

	/**
	 * Ajax handler for querying posts for the Find Users modal.
	 *
	 * @see window.findPosts
	 *
	 * @since 3.1.0
	 */
	public function wp_ajax_find_tax() {
		$plugin = DaTask::get_instance();
		$taxs = get_terms( 'task-team', array(
			'orderby' => 'count',
			'hide_empty' => 0,
			'name__like' => wp_unslash( $_POST[ 'ps' ] )
				) );
		$user_taxs = explode( ', ', get_user_meta( wp_unslash( $_POST[ 'user' ] ), $plugin->get_fields( 'category_to_do' ), true ) );

		if ( !$taxs ) {
			wp_send_json_error( __( 'No items found.' ) );
		}

		$html = '<table class="widefat"><thead><tr><th class="found-radio"><br /></th><th>' . __( 'Name' ) . '</th></tr></thead><tbody>';
		$alt = '';
		foreach ( $taxs as $tax ) {
			$checked = '';
			foreach ( $user_taxs as $key => $user_tax ) {
				if ( $user_tax === $tax->slug ) {
					$checked = ' checked="checked"';
					unset( $user_taxs[ $key ] );
				}
			}
			$alt = ( 'alternate' == $alt ) ? '' : 'alternate';

			$html .= '<tr class="' . trim( 'found-tax-task ' . $alt ) . '"><td class="found-checkbox"><input type="checkbox" id="found-' . $tax->slug . '" name="found_tax_task" value="' . esc_attr( $tax->slug ) . '"' . $checked . '></td>';
			$html .= '<td><label for="found-' . $tax->slug . '">' . esc_html( $tax->name ) . '</label></td></tr>' . "\n\n";
		}

		$html .= '</tbody></table>';

		wp_send_json_success( $html );
	}

}
