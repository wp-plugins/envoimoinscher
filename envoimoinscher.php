<?php

if ( ! defined( 'ABSPATH' ) ) { 
	exit; // Exit if accessed directly
}

/**
 * Plugin Name: EnvoiMoinsCher
 * Plugin URI: http://ecommerce.envoimoinscher.com/
 * Description: The EnvoiMoinsCher delivery plugin for WooCommerce connects your site to over 15 carriers and simplifies your shipping process.
 * Version: 1.1.0
 * Author: EnvoiMoinsCher
 * Author URI: http://www.envoimoinscher.com
 * Text Domain: envoimoinscher
 * Domain Path: /languages
 */

/**
 * Check if WooCommerce is active (include network check)
 **/
if ( ! function_exists( 'is_plugin_active_for_network' ) )
	require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) :
	if ( ! is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) :
		return;
	endif;
endif;


if ( ! class_exists('envoimoinscher')){
		
		define('EMC_LOG_FILE','envoimoinscher');
		
		// Setup activation, deactivation and uninstall hooks
		register_activation_hook( __FILE__, array( 'envoimoinscher', 'activate' ) );
		register_deactivation_hook( __FILE__, array( 'envoimoinscher', 'deactivate' ) );
		register_uninstall_hook (__FILE__, array( 'envoimoinscher', 'uninstall' ) );
		
		// Add action to activate plugin on new multisite site if plugin already configured as network activated
		add_action( 'wpmu_new_blog', array( 'envoimoinscher', 'network_activated' ), 10, 6 );
		// Add action to uninstall plugin on multisite site if site is deleted
		add_action( 'wpmu_drop_tables', array( 'envoimoinscher', 'uninstall_multisite_instance' ) );
		
		class envoimoinscher {

			public $version = '1.1.0';
			public $platform = 'woocommerce';
			protected $model = null;
			protected $view  = null;
			private static $emc_errors   = array();
			private static $emc_messages = array();
			
			/**
			 * @var The single instance of the class
			 */
			protected static $_instance = null;
			
			/**
			 * Main envoimoinscher Instance
			 *
			 * Ensures only one instance of envoimoinscher is loaded or can be loaded.
			 * @static
			 * @see EMC()
			 * @return envoimoinscher - Main instance
			 */
			public static function instance() {
				if ( is_null( self::$_instance ) ) {
					self::$_instance = new self();
				}
				return self::$_instance;
			}
	
			/**
			 * __construct function.
			 *
			 * @access public
			 * @return void
			 */
			public function  __construct() {						
				// Define constants
				$this->define_constants();
				
				// Include required files
				add_action( 'woocommerce_init', array( &$this, 'includes' )	);
				
				$this->id = self::getCalledClass();

				$this->method_description = __( 'Shipping plugin: 15 negotiated carriers', 'envoimoinscher' );
				
				// $this->init();
				add_action( 'init', array( 'envoimoinscher_model', 'init_logger' ), 1 );
				add_action( 'init', array( &$this, 'init' ), 2 );
				
				// Add localization
				load_plugin_textdomain('envoimoinscher', FALSE, dirname(plugin_basename(__FILE__)).'/languages/');
			}

			/**
			 * init function.
			 *
			 * @access public
			 * @return void
			 */
			public function init() {
				
				// $this->view  = new envoimoinscher_view();

				// Add shipping methods
				add_filter( 'woocommerce_shipping_methods', array(&$this, 'envoimoinscher_filter_shipping_methods'));
				
				// Adds EMC metabox on order page
				add_action( 'add_meta_boxes_shop_order', array( &$this, 'envoimoinscher_metabox' ) );
				add_action( 'woocommerce_process_shop_order_meta', 'EMC_Meta_Box_Order_Shipping::save', 40, 2 );
				add_action( 'woocommerce_checkout_update_order_meta', array( &$this, 'add_fields_to_order' ) );
				add_action( 'admin_notices', array(&$this, 'admin_notices'));
								
				// Hook pour l'ajout du select avec les points relais
				add_action('woocommerce_after_checkout_form',array(&$this,'load_pickup_point_js'));
	
				// Add ajax callback for pickup points
				if ( is_admin() ) {
					add_action( 'wp_ajax_get_points', array(&$this, 'get_points_callback') );
					add_action( 'wp_ajax_nopriv_get_points', array(&$this, 'get_points_callback') );
				}
				
				// Hook to throw an alert if no relay point selected
				add_action('woocommerce_checkout_process', array(&$this,'hook_checkout_process'));
				// Hook to save relay point
				add_action('woocommerce_checkout_order_processed', array(&$this,'hook_new_order'));
				
				// Change label front office display
				add_filter( 'woocommerce_cart_shipping_method_full_label', array(&$this,'change_shipping_label'), 10, 2 );
				
				// Add single order actions
				add_action( 'woocommerce_order_actions', array( $this, 'add_order_action' ) );
				add_action( 'woocommerce_order_action_send_orders', array( $this, 'process_send_order' ) );
				add_action( 'woocommerce_order_action_download_waybill', array( $this, 'process_download_waybill' ) );
				add_action( 'woocommerce_order_action_download_delivery_waybill', array( $this, 'process_download_delivery_waybill' ) );
				
				// Add front office css
				add_action( 'wp_enqueue_scripts', array(&$this,'add_css') );
        
        // Register & add a new custom status "Shipped" on "order details" and "orders" filter page
        add_action( 'init', array( $this, 'register_shipped_order_status') );
        add_filter( 'wc_order_statuses', array( $this, 'add_shipped_to_order_statuses') );
        
        // Add new bulk actions on "orders" filter page
        add_action('admin_footer-edit.php', array( $this, 'add_bulk_action_options') );
        add_action('load-edit.php', array(&$this, 'emc_bulk_actions'));
				
        //manage actions on "orders" filter page
        add_filter('woocommerce_admin_order_actions', array( $this,'custom_add_orders_actions'), 100 );
				
        // handle push notifications via woocommerce api
        add_action( 'woocommerce_api_envoimoinscher', array(&$this,'api_callback_handler') );
				
				// limit cart shipping costs calculation in FO
				add_filter('woocommerce_cart_ready_to_calc_shipping', array( $this,'limit_cost_calculation') );
				
				// add tracking number in URL
				add_action( 'woocommerce_order_details_after_order_table', array( $this,'add_tracking_in_FO') );
				
				$this->check_version();
			}
			
			/*
			 * Fix for PHP 5.2
			 */
			public static function getCalledClass(){
				
				if ( function_exists('get_called_class') ) {
					return get_called_class();
				}
				else {
					$arr = array(); 
					$arrTraces = debug_backtrace();
					foreach ($arrTraces as $arrTrace){
						 if(!array_key_exists("class", $arrTrace)) continue;
						 if(count($arr)==0) $arr[] = $arrTrace['class'];
						 else if(get_parent_class($arrTrace['class'])==end($arr)) $arr[] = $arrTrace['class'];
					}
					return end($arr);
				}
			}
			
			/*
			 * Define EMC Constants
			 */
			private function define_constants() {
				if (!defined('EMC_PLUGIN_FILE')) define( 'EMC_PLUGIN_FILE', __FILE__ );
				if (!defined('EMC_PLUGIN_BASENAME')) define( 'EMC_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
				if (!defined('EMC_PLATFORM')) define( 'EMC_PLATFORM', $this->platform );
				if (!defined('EMC_VERSION')) define( 'EMC_VERSION', $this->version );
			}

			/**
			 * Include required core files used in admin and on the frontend.
			 */
			public function includes() {
				require_once(WP_PLUGIN_DIR.'/envoimoinscher/envoimoinscher_model.php');
				require_once(WP_PLUGIN_DIR.'/envoimoinscher/envoimoinscher_view.php');
				require_once(WP_PLUGIN_DIR.'/envoimoinscher/includes/envoimoinscher_carrier.php');
				
				if ( is_admin() ) {
					include_once( 'includes/admin/class-emc-admin-menus.php' );
					include_once( 'includes/admin/meta-boxes/class-emc-meta-box-order-shipping.php' );
				}
			}
			
			/**
			 * add css.
			 *
			 * @access public
			 * @return void
			 */
			public function add_css() {
				wp_enqueue_style( 'emc_front_styles', plugins_url( '/assets/css/style.css', EMC_PLUGIN_FILE ), array(), EMC_VERSION );
			}
			

			/* HOOKS */			
			
			/**
			 * function to add description to carrier label display.
			 *
			 * @access public
			 * @param $full_label, $method
			 * @return full_label
			 */
			public function change_shipping_label($full_label, $method){
				
				// make changes only for emc carriers
				if(in_array( $method->id, envoimoinscher_model::get_enabled_shipping_methods(true) ) ) {
					// get carrier settings
					$carrier_settings = get_option('woocommerce_'.$method->id.'_settings');
					
					$full_label .= '<br/><span class="description">'.$carrier_settings['srv_description'].'</span>';
					
					// add delivery date				
					foreach(WC()->cart->get_cart() as $key => $value){
						$cart_id = $key;
					}
					$delivery_date = strtotime( get_option( '_delivery_date_' . $method->id ) );
					$string = get_option( 'EMC_LABEL_DELIVERY_DATE' );
					$string = str_replace( '{DATE}', '<span class="date">' . date_i18n( __( 'l, F jS Y', 'envoimoinscher' ), $delivery_date ) . '</span>', $string );

					if ( $string != ''){
						$full_label .=  '<br/><span class="delivery_date">'.$string.'</span>';
					}
					
					// Add a parcel points list
					if ( ( (stristr(WC()->cart->get_checkout_url(), $_SERVER['REQUEST_URI']) )
								|| (stristr(WC()->cart->get_checkout_url(), $_SERVER['HTTP_REFERER']) && !stristr(WC()->cart->get_cart_url(), $_SERVER['REQUEST_URI']) ) )
								&& in_array($method->id , WC()->session->get('chosen_shipping_methods') ) ) {
						$service = envoimoinscher_model::get_service_by_carrier_code($method->id);
						if ($service->srv_pickup_point) {
							$full_label .=  '<br/><span class="select-parcel" id="parcel_'.$method->id.'">'.__( 'Choose a parcel point', 'envoimoinscher' ).'</span>';
							$full_label .=  '<br/><span>'.__( 'Selected ', 'envoimoinscher' ).' : <span id="emc-parcel-client"></span></span>';
							$full_label .=  '<span id="input_'.$method->id.'"></span>';
						}
					}	
				}
			
				return $full_label;
			}

			/**
			 * is_available function.
			 *
			 * @access public
			 * @param array $package
			 * @return bool
			 */
			public function is_available( $package ) {
				global $woocommerce;
				$is_available = true;

				if ( $this->enabled == 'no' ) {
					$is_available = false;
				} 
				else {

				}

				return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', $is_available, $package );
			}
			
		  /**
			 * Adds single order actions
			 */
			public function add_order_action( $actions ) {
				global $post_id;
				
				if( get_post_meta( $post_id, '_emc_ref', true ) ) {
					
					$actions['send_orders'] = __( 'Re-send using EnvoiMoinsCher', 'envoimoinscher' );
					
					if ( get_post_meta( $post_id, '_label_url', true ) ) {
						$actions['download_waybill'] = __( 'Download waybill', 'envoimoinscher' );
					}
					
					if ( get_post_meta( $post_id, '_label_url', true ) ) {
						$actions['download_delivery_waybill'] = __( 'Download delivery waybill', 'envoimoinscher' );
					}
					
				}
				
				// add send action
				else $actions['send_orders'] = __( 'Send using EnvoiMoinsCher', 'envoimoinscher' );
				
				return $actions;
			}
			
		  /**
			 * Sends a single order
			 */
			public function process_send_order( $order ) {
				
				if ( get_post_meta( $order->id, '_emc_carrier', true ) == '' ) {
					// using add_filter('redirect_post_location', function($loc) {} ) doesn't work in PHP versions below 5.3 so we do this
					$location = add_query_arg( 'emc_notice', 11, get_edit_post_link( $order->id, 'url' ) );
					wp_redirect( $location );
					exit;
				}
				else {
					
					// anti infinite loop back hole system
					remove_action('woocommerce_order_action_send_orders', array( $this, 'process_send_order' ));
					$return = envoimoinscher_model::make_order( $order );
					add_action('woocommerce_order_action_send_orders', array( $this, 'process_send_order' ));
					
					if( is_array($return) ) {
						$location = add_query_arg( 'emc_notice', 1, get_edit_post_link( $order->id, 'url' ) );
						wp_redirect( $location );
						exit;
					}
					else{				
						$location = add_query_arg( 'emc_notice', 14, get_edit_post_link( $order->id, 'url' ) );
						$location = add_query_arg( 'emc_mess', urlencode($return), $location );
						wp_redirect( $location );
						exit;
					}
				}
			}
			
		  /**
			 * Processes "Download waybill" action for orders
			 */
			public function process_download_waybill( $order ) {
				$url = get_post_meta($order->id, '_label_url', true ).'?type=bordereau';
				
				$this->download_labels( $url );
			}
			
		  /**
			 * Processes "Download delivery waybill" action for orders
			 */
			public function process_download_delivery_waybill( $order ) {
				$url = get_post_meta($order->id, '_remise', true ).'?type=remise';
				
				$this->download_labels( $url );
			}
			
			/**
			 * Downloads one or more EnvoiMoinsCher's labels.
			 * @access public
			 * $param $url document url
			 * @return void
			 */
			public function download_labels( $url ) {

				// Send the pdf request
				$options = array(
					CURLOPT_RETURNTRANSFER => 1,
					CURLOPT_URL => $url,
					CURLOPT_HTTPHEADER => array('Authorization: '.base64_encode( get_option('EMC_LOGIN') . ':' . get_option('EMC_PASS') ) ),
					CURLOPT_CAINFO => dirname(__FILE__).'/ca/ca-bundle.crt',
					CURLOPT_SSL_VERIFYPEER => true,
					CURLOPT_SSL_VERIFYHOST => 2
				);

				$req = curl_init();
				curl_setopt_array($req, $options);
				$result = curl_exec($req);
				curl_close($req);

				// We now display the pdf
				header('Content-type: application/pdf');
				
				if ( strpos( $url, 'type=bordereau' ) )
					header('Content-Disposition: attachment; filename="'.__('waybill', 'envoimoinscher').'.pdf"');
				else
					header('Content-Disposition: attachment; filename="'.__('delivery-waybill', 'envoimoinscher').'.pdf"');
				echo $result;
				die();
			}
			
			/**
			 * Metabox admin notices
			 */
			public function admin_notices() {
				global $post_id;
				
				if (isset($_GET['emc_notice'])) {
					$notice = (int) $_GET['emc_notice'];
					switch($notice) {
						// updates starting from 1
						case 1:
							$this->_show_admin_notice( sprintf(__( 'Order passed with the reference %s.', 'envoimoinscher' ), get_post_meta( $post_id, '_emc_ref', true ) ), 'updated' );
							break;
							
						// errors starting from 11
						case 11:
							$this->_show_admin_notice( __( 'Please select an EnvoiMoinsCher carrier.', 'envoimoinscher' ), 'error' );
							break;
						case 14:
							$this->_show_admin_notice( sprintf(__( 'EnvoiMoinsCher API error returned: %s', 'envoimoinscher' ), stripslashes(urldecode($_GET['emc_mess']))) );
							break;
							
						// notices starting from 21
					}
				}

				if (isset($_REQUEST['mass_order'])) {
					if ( $_REQUEST['mass_order'] == 1 ) {
						?>
						<div id="mass_order">
							<div class="ongoing">
								<div class="title"><?php _e('Processing order', 'envoimoinscher'); ?> <span class="processed_id"></span></div>
								<div><?php _e('Successfully sent orders:', 'envoimoinscher'); ?> <span class="success_ids"></span></div>
								<div><?php _e('Orders not sent:', 'envoimoinscher'); ?> <span class="problem_ids"></span></div>
								<div><?php _e('Orders already sent:', 'envoimoinscher'); ?> <span class="already_ids"></span></div>
							</div>
						</div>
						<?php
					}
					else {
						?>
						<div id="mass_order">
							<div class="finished <?php if ( $_REQUEST['already_ids'] != '' || $_REQUEST['problem_ids'] != '' ) { echo 'failure'; } else { echo 'success'; } ?>">
								<div class="title"><?php _e('All orders processed.', 'envoimoinscher'); ?></div>
								<div><?php _e('Successfully sent orders:', 'envoimoinscher'); ?> <span class="success_ids"><?php echo $_REQUEST['success_ids']; ?></span></div>
								<div><?php _e('Orders not sent:', 'envoimoinscher'); ?> <span class="problem_ids"><?php echo $_REQUEST['problem_ids']; ?></span></div>
								<?php if ( $_REQUEST['problem_ids'] != '' ) : ?>
									<div class="warning"><?php _e('Orders not sent may have missing parameters. You should check these orders individually.', 'envoimoinscher'); ?></div>
								<?php endif; ?>
								<div><?php _e('Orders already sent:', 'envoimoinscher'); ?> <span class="already_ids"><?php echo $_REQUEST['already_ids']; ?></span></div>
								<?php if ( $_REQUEST['already_ids'] != '' ) : ?>
									<div class="already"><?php _e('Orders already sent cannot be re-sent using bulk action. You should re-send them individually.', 'envoimoinscher'); ?></div>
								<?php endif; ?>
							</div>
						</div>
						<?php
					}
				}
							
			}
			
			/**
			 * Shows the admin notice for the metabox
			 * @param $message
			 * @param string $type (updated, error or notice)
			 */
			private function _show_admin_notice($message, $type='error') {
				?>
				<div class="<?php esc_attr_e($type); ?> notice-warning">
					<p><?php echo $message; ?></p>
				</div>
				<?php
			}
			
			/**
			 * Add EMC meta box on order page
			 */
			public function envoimoinscher_metabox() {
				foreach ( wc_get_order_types( 'order-meta-boxes' ) as $type ) {
					$order_type_object = get_post_type_object( $type );
					add_meta_box( 'envoimoinscher-order-shipping', __( 'Shipping Options - EnvoiMoinsCher', 'envoimoinscher' ), 'EMC_Meta_Box_Order_Shipping::output', $type, 'normal', 'high' );
				}
			}
			
			/**
			 * Add meta fields to order
			 */
			public function add_fields_to_order( $order_id ) {
				$order = wc_get_order( $order_id );

				envoimoinscher_model::initialize_default_params( $order );
					
				update_post_meta( $order->id, '_pickup_point', '' );				
			}
			
			/**
			 * Loads parcel point js on checkout page if at least one shipping service has parcel points
			**/
			public function load_pickup_point_js( $checkout ) {
				
				$lang = array(
					'Unable to load parcel points' => __( 'Unable to load parcel points', 'envoimoinscher' ),
					'I want this pickup point' => __( 'I want this pickup point', 'envoimoinscher' ),
					'From %1 to %2' => __( 'From %1 to %2', 'envoimoinscher' ),
					' and %1 to %2' => __( ' and %1 to %2', 'envoimoinscher' ),
					'day_1' => __( 'monday', 'envoimoinscher' ),
					'day_2' => __( 'tuesday', 'envoimoinscher' ),
					'day_3' => __( 'wednesday', 'envoimoinscher' ),
					'day_4' => __( 'thursday', 'envoimoinscher' ),
					'day_5' => __( 'friday', 'envoimoinscher' ),
					'day_6' => __( 'saturday', 'envoimoinscher' ),
					'day_7' => __( 'sunday', 'envoimoinscher' )
				);
				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'gmap', '//maps.google.com/maps/api/js?sensor=false' );
				wp_enqueue_script( 'emc_shipping', plugins_url( '/assets/js/shipping.js', EMC_PLUGIN_FILE ), array( 'jquery', 'gmap' ), EMC_VERSION );
				wp_localize_script( 'emc_shipping', 'plugin_url', plugins_url() );
				wp_localize_script( 'emc_shipping', 'lang', $lang );
				wp_localize_script( 'emc_shipping', 'map', envoimoinscher_view::display_google_map_container() );
				wp_localize_script( 'emc_shipping', 'ajaxurl', admin_url( 'admin-ajax.php' ) );
				
				// Add nonce for security
				$ajax_nonce = wp_create_nonce( 'boxtale_emc' );
				wp_localize_script( 'emc_shipping', 'ajax_nonce', $ajax_nonce );
			}
			
			/**
			 * Ajax callback to get pickup points
			 */
			public function get_points_callback(){
				check_ajax_referer( 'boxtale_emc', 'security' );

				echo json_encode($this->get_points($_GET['carrier_code']));
				
				wp_die();
			}
			
			/**
			 * Validate the process form
			**/
			public function hook_checkout_process() {
				global $order_id;

				// Check if the parcel point is needed and if it has been chosen
				if (isset($_POST['shipping_method']))	{
					foreach($_POST['shipping_method'] as $shipping_method) {
						$service = envoimoinscher_model::get_service_by_carrier_code($shipping_method);
						if ($service->srv_pickup_point) {
							if (isset($_POST['_pickup_point'])) {
								update_post_meta( $order_id, '_pickup_point', $_POST['_pickup_point'] );
							}
							else {
								wc_add_notice(__('Please select a parcel point','envoimoinscher'),'error');
							}
							
						}
					}
				}
			}
			
			/**
			 * Add all necessary data on the order creation after the checkout page
			 */
			public function hook_new_order($order_id) {
				if (isset($_POST['shipping_method']))	{
					foreach($_POST['shipping_method'] as $shipping_method) {
						$service = envoimoinscher_model::get_service_by_carrier_code($shipping_method);
						if ( $service != false ) {
							update_post_meta( $order_id, '_emc_carrier', wc_clean( $shipping_method ) );
							if ($service->srv_pickup_point) {
								if (isset($_POST['_pickup_point'])) {
									update_post_meta( $order_id, '_pickup_point', $_POST['_pickup_point'] );
								}							
							}
						}
					}
				}
			}

			
			/**
			 * Activate the module, install it if its tables do not exist
			 */
			public static function activate($network_wide) {
				update_site_option('EMC_NETWORK_ACTIVATED', 'test');
				// multisite activation. See if being activated on the entire network or one site.
				if ( function_exists('is_multisite') && is_multisite() && $network_wide ) {
					global $wpdb;
			 
					// Get this so we can switch back to it later
					$current_blog = $wpdb->blogid;
					// For storing the list of activated blogs
					$activated = array();
			 
					// Get all blogs in the network and activate plugin on each one
					$blog_ids = $wpdb->get_col("SELECT blog_id FROM ".$wpdb->blogs);
					foreach ($blog_ids as $blog_id) {
						switch_to_blog($blog_id);
						self::activate_simple(); // The normal activation function
						$activated[] = $blog_id;
					}
			 
					// Switch back to the current blog
					switch_to_blog($current_blog);
			 
					// Store the array for a later function
					update_site_option('EMC_NETWORK_ACTIVATED', $activated);
				}
				
				// normal activation
				else {
					self::activate_simple();
				}
			}
			
			/**
			 * Activate the module, install it if its tables do not exist
			 */
			public static function activate_simple() {
				require_once(WP_PLUGIN_DIR.'/envoimoinscher/envoimoinscher_model.php');
				
				if (!envoimoinscher_model::is_plugin_installed())
				{
					return envoimoinscher_model::create_database() && 
								 envoimoinscher_model::create_options();
				}
				return true;
			}
			
			/**
			 * Deactivate the module and flush the offers cache
			 */
			public static function deactivate($network_wide) {
				// multisite deactivation. See if being deactivated on the entire network or one site.
				if ( function_exists('is_multisite') && is_multisite() && $network_wide ) {
					global $wpdb;
			 
					// Get this so we can switch back to it later
					$current_blog = $wpdb->blogid;
					// For storing the list of activated blogs
					$activated = array();
			 
					// Get all blogs in the network and activate plugin on each one
					$blog_ids = $wpdb->get_col("SELECT blog_id FROM ".$wpdb->blogs);
					foreach ($blog_ids as $blog_id) {
						switch_to_blog($blog_id);
						self::deactivate_simple(); // The normal activation function
						$activated[] = $blog_id;
					}
			 
					// Switch back to the current blog
					switch_to_blog($current_blog);
			 
					// Store the array for a later function
					update_site_option('EMC_NETWORK_ACTIVATED', $activated);
				}
				
				// normal activation
				else {
					self::deactivate_simple();
				}
			}
			
			/**
			 * Deactivate the module and flush the offers cache
			 */
			public static function deactivate_simple() {
				require_once(WP_PLUGIN_DIR.'/envoimoinscher/envoimoinscher_model.php');
				
				return envoimoinscher_model::flush_rates_cache();
			}
			
			/**
			 * Remove completely the plugin from woocommerce
			 */
			public static function uninstall($network_wide) {
				// multisite deactivation. See if being deactivated on the entire network or one site.
				if ( function_exists('is_multisite') && is_multisite() && $network_wide ) {
					global $wpdb;
			 
					// Get this so we can switch back to it later
					$current_blog = $wpdb->blogid;
			 
					// Get all blogs in the network and activate plugin on each one
					$blog_ids = $wpdb->get_col("SELECT blog_id FROM ".$wpdb->blogs);
					foreach ($blog_ids as $blog_id) {
						switch_to_blog($blog_id);
						self::uninstall_simple(); // The normal activation function
					}
			 
					// Switch back to the current blog
					switch_to_blog($current_blog);
				}
				
				// normal activation
				else {
					self::uninstall_simple();
				}
			}
			
			/**
			 * Remove completely the plugin from woocommerce
			 */
			public static function uninstall_simple() {
				require_once(WP_PLUGIN_DIR.'/envoimoinscher/envoimoinscher_model.php');
				
				return envoimoinscher_model::delete_database();
			}
			
			/**
			 * Runs activation for a plugin on a new site if plugin is already set as network activated on multisite
			 */
			public static function network_activated( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
				if ( is_plugin_active_for_network( 'envoimoinscher/envoimoinscher.php' ) ) {
					switch_to_blog( $blog_id );
					self::activate_simple();
					restore_current_blog();
				}
			}
			
			/**
			 * Runs uninstall for a plugin on a multisite site if site is deleted
			 */
			public static function uninstall_multisite_instance( $tables ) {
				global $wpdb;
				$tables[] = $wpdb->prefix . 'emc_categories';
				$tables[] = $wpdb->prefix . 'emc_dimensions';
				$tables[] = $wpdb->prefix . 'emc_operators';
				$tables[] = $wpdb->prefix . 'emc_services';
				$tables[] = $wpdb->prefix . 'emc_orders';
				$tables[] = $wpdb->prefix . 'emc_scales';
				return $tables;
			}
			
			/**
			 * Hook menu shipping backoffice 
			 */
			public function envoimoinscher_filter_shipping_methods( $methods ) {
				
				$emc_services = envoimoinscher_model::get_emc_active_shipping_methods();

				foreach ( $emc_services as $key => $value ) {
					if ( ! class_exists($value)){
						eval("class $value extends envoimoinscher_carrier{}");
					}
					if ( !in_array( $value, $methods ) ) {
						$methods[] = new $value();
					}
				}

				return $methods;
			}	
			
		  /** 
			 * Register new status "shipped"
			 */
			public static function register_shipped_order_status() {

				register_post_status( 'wc-awaiting-shipment', array(
					'label'                     => _x( 'Awaiting Shipment', 'Order status', 'envoimoinscher' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( 'Awaiting Shipment <span class="count">(%s)</span>', 'Awaiting Shipment <span class="count">(%s)</span>', 'envoimoinscher' )
				) );
				register_post_status( 'wc-shipped', array(
					'label'                     => _x( 'Shipped', 'Order status', 'envoimoinscher' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( 'Shipped <span class="count">(%s)</span>', 'Shipped <span class="count">(%s)</span>', 'envoimoinscher' )
				) );
			}
      
			/**
			 * Add 2 new status "shipped" and "awaiting shipment" to list of WC Order statuses
			 */
			public function add_shipped_to_order_statuses( $order_statuses ) {
				$new_order_statuses = array();
				// add new order status after processing
				foreach ( $order_statuses as $key => $status ) {
					$new_order_statuses[ $key ] = $status;
					if ( 'wc-on-hold' === $key ) {
						$new_order_statuses['wc-awaiting-shipment'] = 'Awaiting Shipment';
						$new_order_statuses['wc-shipped'] = 'Shipped';
					}
				}          
				return $new_order_statuses;
			}
      
			/**
			 * Add extra bulk action options to mark shipment as complete or processing
			 *
			 * Using Javascript until WordPress core fixes: http://core.trac.wordpress.org/ticket/16031
			 */
			public function add_bulk_action_options() {        
				global $post_type;
				if($post_type == 'shop_order') {

					?>
					<script type="text/javascript">
						jQuery(document).ready(function() {
							jQuery('<option>').val('mark_awaiting-shipment').text('<?php echo addslashes(__('Mark awaiting shipment', 'envoimoinscher'));?>').appendTo("select[name='action']");
							jQuery('<option>').val('mark_awaiting-shipment').text('<?php echo addslashes(__('Mark awaiting shipment', 'envoimoinscher'));?>').appendTo("select[name='action2']");

							jQuery('<option>').val('mark_shipped').text('<?php echo addslashes(__('Mark shipped', 'envoimoinscher'));?>').appendTo("select[name='action']");
							jQuery('<option>').val('mark_shipped').text('<?php echo addslashes(__('Mark shipped', 'envoimoinscher'));?>').appendTo("select[name='action2']");
							
							jQuery('<option>').val('mass_order').text('<?php echo addslashes(__('Send using EnvoiMoinsCher', 'envoimoinscher'));?>').appendTo("select[name='action']");
							jQuery('<option>').val('mass_order').text('<?php echo addslashes(__('Send using EnvoiMoinsCher', 'envoimoinscher'));?>').appendTo("select[name='action2']");
							
							jQuery('<option>').val('waybills').text('<?php echo addslashes(__('Download all waybills', 'envoimoinscher'));?>').appendTo("select[name='action']");
							jQuery('<option>').val('waybills').text('<?php echo addslashes(__('Download all waybills', 'envoimoinscher'));?>').appendTo("select[name='action2']");
							
							jQuery('<option>').val('remises').text('<?php echo addslashes(__('Download all delivery waybills', 'envoimoinscher'));?>').appendTo("select[name='action']");
							jQuery('<option>').val('remises').text('<?php echo addslashes(__('Download all delivery waybills', 'envoimoinscher'));?>').appendTo("select[name='action2']");
						});
					</script>
					<?php
				}
			}
			
			/**
			 * Defines bulk actions
			 */
			function emc_bulk_actions() {

				global $typenow;
				$post_type = $typenow;

				if($post_type == 'shop_order') {

					// check if we're in one of emc bulk actions
					$wp_list_table = _get_list_table( 'WP_Posts_List_Table' );
					$action = $wp_list_table->current_action();
					$allowed_actions = array("waybills", "remises", "mass_order");
					if(!in_array($action, $allowed_actions)) return;

					// check if admin panel
					check_admin_referer('bulk-posts');
					
					// get ids
					if(isset($_REQUEST['post'])) {
						$post_ids = array_map('intval', $_REQUEST['post']);
					}

					// if no id end there
					if(empty($post_ids)) return;

					// get return page
					$sendback = remove_query_arg( array('exported', 'untrashed', 'deleted', 'ids'), wp_get_referer() );
					if ( ! $sendback )
						$sendback = admin_url( "edit.php?post_type=$post_type" );
					$pagenum = $wp_list_table->get_pagenum();
					$sendback = add_query_arg( 'paged', $pagenum, $sendback );

					// execute action
					switch($action) {
						case 'waybills':		
							$references = array();
							foreach( $post_ids as $value ) {
								array_push($references, get_post_meta( $value, '_emc_ref', true) );
							}
							$envoi = implode(',', $references);
							
							if ( get_option('EMC_ENV') == 'test' ) $base_url = 'http://test.envoimoinscher.com/documents';
							else $base_url = 'http://documents.envoimoinscher.com/documents';
							$url = $base_url.'?type=bordereau&envoi='.$envoi;
							$this->download_labels( $url );
							break;
						
						case 'remises':
							$references = array();
							foreach( $post_ids as $value ) {
								array_push($references, get_post_meta( $value, '_emc_ref', true) );
							}
							$envoi = implode(',', $references);

							if ( get_option('EMC_ENV') == 'test' ) $base_url = 'http://test.envoimoinscher.com/documents';
							else $base_url = 'http://documents.envoimoinscher.com/documents';
							$url = $base_url.'?type=remise&envoi='.$envoi;
							$this->download_labels( $url );

							break;
						
						case 'mass_order':
							$loc = remove_query_arg( array( 'mass_order', 'success_ids', 'problem_ids', 'already_ids' ), $sendback );
							$loc = add_query_arg('order_ids', implode(',', $post_ids), $loc);
							$loc = add_query_arg( 'mass_order', 1, $loc );
							header('location: '.$loc);
							exit;
							
							break;
							
						default: return;
					}

					$sendback = remove_query_arg( array('action', 'action2', 'tags_input', 'post_author', 'comment_status', 'ping_status', '_status',  'post', 'bulk_edit', 'post_view'), $sendback );
					
					// redirect
					wp_redirect($sendback);
					exit();
				}	 
			}
    
      
			/**
			 * Output custom columns action
			 * @param  array $actions
			 * @return array $new_actions
			 */      
			public function custom_add_orders_actions( $actions )  {

				global $post, $the_order;
				
				$new_actions = array();
				
				if ( empty( $the_order ) || $the_order->id != $post->ID ) {
					$the_order = wc_get_order( $post->ID );
				}
				
				if ( $the_order->has_status( array( 'pending', 'on-hold' ) ) ) {
					$new_actions['processing'] = array(
						'url'       => wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_mark_order_status&status=processing&order_id=' . $post->ID ), 'woocommerce-mark-order-status' ),
						'name'      => __( 'Processing', 'woocommerce' ),
						'action'    => "processing"
					);
				}
				
				if ( $the_order->has_status( array( 'pending', 'on-hold', 'processing' ) ) ) {
					$new_actions['awaiting-shipment'] = array(
						'url'       => wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_mark_order_status&status=awaiting-shipment&order_id=' . $post->ID ), 'woocommerce-mark-order-status' ),
						'name'      => __( 'Awaiting Shipment', 'envoimoinscher' ),
						'action'    => "awaiting-shipment"
					);
				}
				
				if ( $the_order->has_status( array( 'pending', 'on-hold', 'processing', 'awaiting-shipment' ) ) ) {
					$new_actions['shipped'] = array(
						'url'       => wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_mark_order_status&status=shipped&order_id=' . $post->ID ), 'woocommerce-mark-order-status' ),
						'name'      => __( 'Shipped', 'envoimoinscher' ),
						'action'    => "shipped"
					);
				}
				
				if ( $the_order->has_status( array( 'pending', 'on-hold', 'processing', 'awaiting-shipment', 'shipped' ) ) ) {
					$new_actions['complete'] = array(
						'url'       => wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_mark_order_status&status=completed&order_id=' . $post->ID ), 'woocommerce-mark-order-status' ),
						'name'      => __( 'Complete', 'woocommerce' ),
						'action'    => "complete"
					);
				}
				
				$new_actions['view'] = $actions['view'];
				
				return $new_actions;
			
			}
          
			
			/**
			 * Get a list of parcel points for the given carrier code
			 * The adress used is the actual recipient address
			 *
			 * @return list of parcel points for this carrier
			 */
			public function get_points($carrier_code) {
				$address = envoimoinscher_model::get_recipient_address();
				return envoimoinscher_model::get_pickup_points($carrier_code,$address['ville'],$address['code_postal'],$address['pays']);
			}
			
			/**
			 * name : check_version()
			 *
			 * aim : check the version of the EMC module for the user for the woocommerce Module.
			 * 		 Update the client module if It is not up-to-date 
			 * 
			 * @access public
			 * @return void
			 */	
			public function check_version(){	
				$emc_user_version = get_option( 'EMC_VERSION' ); 
											
				if(version_compare($this->version, $emc_user_version) > 0){
					
					$dh = opendir(WP_PLUGIN_DIR.'/envoimoinscher/upgrade');
					$upgrade_success = true;

					// look for upgrade files
					while ($upgrade_success && false !== ($filename = readdir($dh))) {
						if ($filename != '.' && $filename != '..' && substr($filename, -4) == '.php') {
							
							// get the upgrade version
							$filename_no_ext = explode('-',substr($filename, 0, -4));
							$file_upgrade_version = $filename_no_ext[1];
							
							// do we need this upgrade ?
							if (version_compare($file_upgrade_version, $emc_user_version) > 0 && version_compare($file_upgrade_version, $this->version) <= 0) {
															
								// apply the upgrade 
								include_once(WP_PLUGIN_DIR.'/envoimoinscher/upgrade/'.$filename);
								eval('$upgrade_success = upgrade_'.str_replace('.','_',$file_upgrade_version).'();');
								
								if ($upgrade_success) {
									$emc_user_version = $file_upgrade_version;
									update_option( 'EMC_VERSION', $emc_user_version );
								}
								else {
									envoimoinscher_model::log('Fatal error while upgrading the module to version  ' . $file_upgrade_version.', actual version is '.$emc_user_version);
								}
							}
						}
					}

					// if all upgrades worked, we change the stored version to the module's version
					if ($upgrade_success) {
						update_option( 'EMC_VERSION', $this->version );
						envoimoinscher_model::log('Upgraded the module to version '.$this->version);
					}
				}
			}
			
			/**
			 * Get push notifications via woocommerce api
			 */	
		  public function api_callback_handler() {
				$query_string = $_SERVER['QUERY_STRING'];
					
				if ( stristr( $query_string, 'type=status' ) )
					envoimoinscher_model::insert_documents_links( $query_string );
				elseif( stristr( $query_string, 'type=tracking' ))
					envoimoinscher_model::insert_tracking_informations( $query_string );
			}
			
			/**
			 * Call to api only when city, country and postcode are complete
			 */	
			public function limit_cost_calculation( ) {
				if ( ( (stristr(WC()->cart->get_checkout_url(), $_SERVER['REQUEST_URI']) ||  (stristr(WC()->cart->get_checkout_url(), $_SERVER['HTTP_REFERER'] ) ) ) ) ) {
					if ( ! WC()->customer->has_calculated_shipping() ) {
						if ( ! WC()->customer->get_shipping_country() || ! WC()->customer->get_shipping_city() || ! WC()->customer->get_shipping_postcode() )
							return false;
					}
				}
			
				return true;
			}
			
			/**
			 * Get push notifications via woocommerce api
			 */	
		  public function add_tracking_in_FO ($order) {
				if( get_post_meta( $order->id, '_emc_carrier', true ) != '' ){
					$carrier_code = get_post_meta( $order->id, '_emc_carrier', true );
					$carrier_settings = get_option('woocommerce_'.$carrier_code.'_settings');
					
					if ( get_post_meta( $order->id, '_carrier_ref', true ) ) {				
						if( isset( $carrier_settings['carrier_tracking_url'] ) && $carrier_settings['carrier_tracking_url'] != '' ) {
							echo '<div class="carrier_ref">';
							$link = str_replace ( '@', get_post_meta( $order->id, '_carrier_ref', true ), $carrier_settings['carrier_tracking_url'] );
							$text = sprintf(__( 'Your carrier tracking reference is %s.', 'envoimoinscher' ), '<a href="' . $link . '" target="_blank">' . get_post_meta( $order->id, '_carrier_ref', true ) . '</a>' );
							echo $text . '</div>'; 
						}
					}				
				}
			}

		}/* end of class */
}
//end not exist class envoimoinscher


if (class_exists('envoimoinscher')){

	$inst_envoimoinscher = new envoimoinscher();

}

/**
 * Returns the main instance of envoimoinscher.
 * @return envoimoinscher
 */
function EMC() {
	return envoimoinscher::instance();
}

