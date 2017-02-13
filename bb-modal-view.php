<?php
/**
 * @package   Backbone-Modal-View
 * @author    Mte90
 * @copyright 2016 GPL
 * @license   GPL-3.0+
 * @link      http://mte90.net
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
			'input' => 'checkbox', //or radio
			'label' => __( 'Open Modal' ),
			'data' => array( 'rand' => rand() ),
			'ajax' => array( $this, 'ajax_posts' ),
			'ajax_on_select' => array( $this, 'ajax_posts_selected' ),
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

		$ajax = $this->args[ 'ajax' ];
		if ( is_array( $ajax ) ) {
			$ajax = $ajax[ 1 ];
		}
		add_action( 'wp_ajax_' . $ajax, $this->args[ 'ajax' ] );
		$this->args[ 'ajax' ] = $ajax;

		$ajax_on_select = $this->args[ 'ajax_on_select' ];
		if ( is_array( $ajax_on_select ) ) {
			$ajax_on_select = $ajax_on_select[ 1 ];
		}
		add_action( 'wp_ajax_' . $ajax_on_select, $this->args[ 'ajax_on_select' ] );
		$this->args[ 'ajax_on_select' ] = $ajax_on_select;
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
		$value = '<a href="#" class="button bb-modal-button modal-' . $this->args[ 'id' ] . '" data-id="' . $this->args[ 'id' ] . '" data-ajax="' . $this->args[ 'ajax' ] . '" data-ajax-on-select="' . $this->args[ 'ajax_on_select' ] . '" ' . $data . '>' . $this->args[ 'label' ] . '</a>';
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
		// TODO use the right path
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
		<div id="bb-modal-view-<?php echo $this->args[ 'id' ]; ?>" class="find-box bb-modal-view" style="display: none;">
			<div class="find-box-head bb-modal-view-head">
				<?php _e( 'Task' ); ?>
				<div id="bb-modal-view-close"></div>
			</div>
			<div class="find-box-inside bb-modal-view-inside">
				<div class="find-box-search bb-modal-view-search">
					<?php if ( $found_action ) { ?>
						<input type="hidden" name="found_action" value="<?php echo esc_attr( $found_action ); ?>" />
					<?php } ?>
					<input type="hidden" name="affected" id="affected" value="" />
					<?php wp_nonce_field( '#' . $this->args[ 'id' ], '_ajax_nonce', false ); ?>
					<label class="screen-reader-text" for="#bb-modal-view-input"><?php _e( 'Search' ); ?></label>
					<input type="text" id="bb-modal-view-input" name="ps" value="" autocomplete="off" />
					<span class="spinner"></span>
					<input type="button" id="bb-modal-view-search" value="<?php esc_attr_e( 'Search' ); ?>" class="button" />
					<div class="clear"></div>
				</div>
				<div id="bb-modal-view-response"></div>
			</div>
			<div class="find-box-buttons bb-modal-view-buttons">
				<?php submit_button( __( 'Select' ), 'button-primary alignright', 'bb-modal-view-submit', false ); ?>
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
	public function ajax_posts() {
		$query = new WP_Query( array( 'post_type' => 'post', 's' => wp_unslash( $_POST[ 'ps' ] ) ) );

		if ( !$query->posts ) {
			wp_send_json_error( __( 'No items found.' ) );
		}
		$user_posts = explode( ', ', get_user_meta( get_current_user_id(), 'bb-modal-view', true ) );

		$html = '<table class="widefat"><thead><tr><th class="found-checkbox"><br /></th><th>' . __( 'Name' ) . '</th></tr></thead><tbody>';
		$alt = '';
		$checked = '';
		foreach ( $query->posts as $post ) {
			$checked = '';
			foreach ( $user_posts as $key => $posts ) {
				if ( $posts === ( string ) $post->ID ) {
					$checked = ' checked="checked"';
					unset( $user_posts[ $key ] );
				}
			}
			$alt = ( 'alternate' == $alt ) ? '' : 'alternate';

			$html .= '<tr class="' . trim( 'bb-modal-view-item ' . $alt ) . '"><td class="found-' . $this->args[ 'input' ] . '"><input type="' . $this->args[ 'input' ] . '" id="found-' . $post->ID . '" name="ajax_posts" value="' . esc_attr( $post->ID ) . '"' . $checked . '></td>';
			$html .= '<td><label for="found-' . $post->ID . '">' . esc_html( $post->post_title ) . '</label></td></tr>' . "\n\n";
		}

		$html .= '</tbody></table>';

		wp_send_json_success( $html );
	}

	/**
	 * Add taxonomy to the user
	 */
	public function ajax_posts_selected() {
		update_user_meta( get_current_user_id(), 'bb-modal-view', wp_unslash( $_POST[ 'check' ] ) );

		wp_send_json_success();
	}

}
