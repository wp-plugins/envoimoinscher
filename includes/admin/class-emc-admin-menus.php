<?php
/**
 * Setup menus in emc admin.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'emc_admin_menus' ) ) :

/**
 * WC_Admin_Menus Class
 */
class emc_admin_menus {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		// Add menus
		add_action( 'admin_menu', array( $this, 'emc_menu' ), 30 );
		
		// Add styles
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
		
		// Add ajax callbacks
		add_action( 'wp_ajax_format_price', array(&$this, 'format_price_callback') );
		add_action( 'wp_ajax_check_labels', array(&$this, 'check_labels_callback') );
		add_action( 'wp_ajax_mass_order', array(&$this, 'mass_order_callback') );
	}
	
	/**
	 * Enqueue styles
	 */
	public function admin_styles( $hook ) {
		global $wp_scripts;

		$screen = get_current_screen();

		if ( in_array( $screen->id, wc_get_screen_ids() ) ) {
			// Admin styles for WC pages only
			wp_enqueue_style( 'emc_admin_styles', plugins_url( '/assets/css/admin.css', EMC_PLUGIN_FILE ), array(), EMC_VERSION );

			if( 'post.php' == $hook ){
				wp_enqueue_script( 'emc_admin_post_script', plugins_url( '/assets/js/admin/emc_admin_post.js', EMC_PLUGIN_FILE ),  array( 'jquery' ), EMC_VERSION );	
				
				// Add nonce for security
				$ajax_nonce = wp_create_nonce( 'boxtale_emc' );
				wp_localize_script( 'emc_admin_post_script', 'ajax_nonce', $ajax_nonce );
			}
			
			if( 'edit.php' == $hook ){
				wp_enqueue_script( 'emc_admin_edit_script', plugins_url( '/assets/js/admin/emc_admin_edit.js', EMC_PLUGIN_FILE ),  array( 'jquery' ), EMC_VERSION );
				$redirect_url = remove_query_arg( array('order_ids', 'mass_order', 'success_ids', 'problem_ids', 'already_ids' ), wp_get_referer() );;
				$redirect_url = add_query_arg( 'mass_order', 0, $redirect_url);;
				wp_localize_script( 'emc_admin_edit_script', 'redirect_url', $redirect_url );
				
				// Add nonce for security
				$ajax_nonce = wp_create_nonce( 'boxtale_emc' );
				wp_localize_script( 'emc_admin_edit_script', 'ajax_nonce', $ajax_nonce );
			}
		}
	}
	
	/**
	 * Ajax callback to format price
	 */
	public function format_price_callback(){
		check_ajax_referer( 'boxtale_emc', 'security' );

		if( isset($_POST['value']) ){
			if( isset($_POST['tax']) && $_POST['tax'] == "notax" ) {
				ob_clean();
				echo wc_price( $_POST['value'], array('ex_tax_label'=>true) );
			}
		}
		wp_die();
	}
	
	/**
	 * Ajax callback to check documents
	 */
	public function check_labels_callback(){	
		check_ajax_referer( 'boxtale_emc', 'security' );
		
		$result = array();
		
		if( isset($_POST['order_id']) ){
			if ( get_post_meta( $_POST['order_id'], '_label_url' ) ) $result['label_url'] = get_post_meta( $_POST['order_id'], '_label_url', true );
			if ( get_post_meta( $_POST['order_id'], '_remise' ) ) $result['remise'] = get_post_meta( $_POST['order_id'], '_remise', true );
			if ( get_post_meta( $_POST['order_id'], '_manifest' ) ) $result['manifest'] = get_post_meta( $_POST['order_id'], '_manifest', true );
			if ( get_post_meta( $_POST['order_id'], '_connote' ) ) $result['connote'] = get_post_meta( $_POST['order_id'], '_connote', true );
			if ( get_post_meta( $_POST['order_id'], '_proforma' ) ) $result['proforma'] = get_post_meta( $_POST['order_id'], '_proforma', true );
			if ( get_post_meta( $_POST['order_id'], '_b13a' ) ) $result['b13a'] = get_post_meta( $_POST['order_id'], '_b13a', true );
		}
		
		if ( !empty($result) ) {
			echo json_encode($result);
		}
		wp_die();		
	}
	
	/**
	 * Ajax callback for mass order
	 */
	public function mass_order_callback(){
		check_ajax_referer( 'boxtale_emc', 'security' );

		if( isset($_REQUEST['order_id']) ){
			// orders already sent are not to be re-sent
			if( get_post_meta( $_REQUEST['order_id'], '_emc_ref', true ) ) {
				echo json_encode(array("status"=>"already"));
				exit;
			}
			
			$order = wc_get_order( $_REQUEST['order_id'] );
			$result = envoimoinscher_model::make_order($order);

			if( is_array($result) ) {
				echo json_encode(array("status"=>"success"));
			}
			else{
				echo json_encode(array("status"=>"error"));
			}
		}
		wp_die();
	}
	
	/**
	 * Add menu item
	 */
	public function emc_menu() {
		$emc_page = add_submenu_page( 'woocommerce', __( 'EnvoiMoinsCher', 'envoimoinscher' ),  __( 'EnvoiMoinsCher', 'envoimoinscher' ) , 'manage_woocommerce', 'envoimoinscher-settings', array( $this, 'settings_page' ) );
	}
	
	/**
	 * Init the settings page
	 */
	public function settings_page() {
	
		// You'll be able to delete this condition when emc-install will be working
		if ( ! class_exists( 'emc_admin_settings' ) ) {
			include dirname(__FILE__) .'/class-emc-admin-settings.php';
			new emc_admin_settings();
		}
	}
	
}

endif;

return new emc_admin_menus();
