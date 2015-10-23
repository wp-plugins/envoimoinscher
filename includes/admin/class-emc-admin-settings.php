<?php
/**
 * EMC Admin Settings Class.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'emc_admin_settings' ) ) :

/**
 * emc_admin_settings
 */
class emc_admin_settings {

	private static $errors   = array();
	private static $messages = array();
	private static $obj_setting = array();
	private $tabs;
	
	public function __construct() {
		self::get_settings_pages();
		
		$this->tabs = apply_filters( 'emc_settings_tabs_array', array() );
		
		$this->output();
	}
	
	/**
	 * Include the settings page classes
	 */
	public static function get_settings_pages() {
		
		if ( ! class_exists( 'WC_Settings_Page' ) ) {
			require_once ( WC()->plugin_path() . '/includes/admin/settings/class-wc-settings-page.php');
		}

		include( 'settings/class-emc-settings-account.php' );
		include( 'settings/class-emc-settings-shipping-description.php' );
		include( 'settings/class-emc-settings-parameters.php' );
		include( 'settings/class-emc-settings-carriers.php' );
		include( 'settings/class-emc-settings-simulator.php' );
		include( 'settings/class-emc-settings-help.php' );

		self::$obj_setting['account'] = new emc_settings_account();
		self::$obj_setting['shipping-description'] = new emc_settings_shipping_description();
		self::$obj_setting['parameters'] = new emc_settings_parameters();
		self::$obj_setting['carriers'] = new emc_settings_carriers();
		self::$obj_setting['simulator'] = new emc_settings_simulator();
		self::$obj_setting['help'] = new emc_settings_help();

		return self::$obj_setting;
	}
	
	/**
	 * Show settings tabs
	 */
	public function output() {
		
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_script( 'woocommerce_admin', WC()->plugin_url() . '/assets/js/admin/woocommerce_admin' . $suffix . '.js', array( 'jquery', 'jquery-blockui', 'jquery-ui-sortable', 'jquery-ui-widget', 'jquery-ui-core', 'jquery-tiptip' ), WC_VERSION );

		wp_enqueue_script( 'woocommerce_settings', WC()->plugin_url() . '/assets/js/admin/settings.min.js', array( 'jquery', 'jquery-ui-datepicker', 'jquery-ui-sortable', 'iris', 'chosen', 'jquery-tiptip' ), WC()->version, true );

		wp_enqueue_script( 'emc_settings', plugins_url( '/assets/js/admin/emc_settings.js', EMC_PLUGIN_FILE ), array( 'jquery' ), EMC_VERSION );
		
		wp_localize_script( 'emc_settings', 'img_folder', plugins_url( '/assets/img/', EMC_PLUGIN_FILE ) );
		
		wp_localize_script( 'woocommerce_settings', 'woocommerce_settings_params', array(
			'i18n_nav_warning' => __( 'The changes you made will be lost if you navigate away from this page.', 'envoimoinscher' )
		) );
		
		wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );
		wp_enqueue_style( 'emc_admin_styles', plugins_url( '/assets/css/admin.css', EMC_PLUGIN_FILE ), array(), EMC_VERSION );
		
		$current_tab = ( empty( $_GET['tab'] ) ) ? 'account' : urldecode( $_GET['tab'] );
		
		// Save settings if data has been posted
		if ( ! empty( $_POST ) && empty($_POST['submit'])) {
			$this->save();
		}
		
		// Add any posted messages
		if ( ! empty( $_GET['wc_error'] ) ) {
			self::add_error( stripslashes( $_GET['wc_error'] ) );
		}

		if ( ! empty( $_GET['wc_message'] ) ) {
			self::add_message( stripslashes( $_GET['wc_message'] ) );
		}

		self::show_messages();
		
		// Get tabs for the settings page
		$tabs = apply_filters( 'emc_settings_tabs_array', array() );
        
    include 'views/html-admin-settings.php';
    
    // Datas are not saved but processed
		if (method_exists(self::$obj_setting[$current_tab], 'additionalOutput')){
			$offers = self::$obj_setting['simulator']->additionalOutput();
			if ( is_array( $offers ) ) {
				$view_file = 'views/html-admin-'.$current_tab.'.php';
				include_once $view_file;
			}
		}
	}
	
	/**
	 * Save the settings
	 */
	public function save() {

		if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'woocommerce-settings' ) ) {
			die( __( 'Action failed. Please refresh the page and retry.', 'envoimoinscher' ) );
		}
		
		$current_tab = ( empty( $_GET['tab'] ) ) ? 'account' : urldecode( $_GET['tab'] );
		
		switch( $current_tab ){
			case 'shipping-description':
				self::$obj_setting['shipping-description']->save();
				break;
				
			case 'parameters':
				self::$obj_setting['parameters']->save();
				break;
				
			case 'carriers':
				self::$obj_setting['carriers']->save();
				break;
				
			case 'simulator':
				self::$obj_setting['simulator']->save();
				break;
				
			case 'help':
				self::$obj_setting['help']->save();
				break;
				
			default:
				self::$obj_setting['account']->save();
				break;
		}
		
		self::add_message( __( 'Your settings have been saved.', 'envoimoinscher' ) );
	}
	
	/**
	 * Add a message
	 * @param string $text
	 */
	public static function add_message( $text ) {
		self::$messages[] = $text;
	}
	
	/**
	 * Add an error
	 * @param string $text
	 */
	public static function add_error( $text ) {
		self::$errors[] = $text;
	}
	
	/**
	 * Output messages + errors
	 * @return string
	 */
	public static function show_messages() {
		if ( sizeof( self::$errors ) > 0 ) {
			foreach ( self::$errors as $error ) {
				echo '<div id="message" class="error fade"><p><strong>' . esc_html( $error ) . '</strong></p></div>';
			}
		} elseif ( sizeof( self::$messages ) > 0 ) {
			foreach ( self::$messages as $message ) {
				echo '<div id="message" class="updated fade"><p><strong>' . esc_html( $message ) . '</strong></p></div>';
			}
		}
	}
	
}

endif;
