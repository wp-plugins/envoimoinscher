<?php
/**
 * EMC Carriers Settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'emc_settings_carriers' ) ) :

/**
 * emc_settings_carriers
 */
class emc_settings_carriers extends WC_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->id    = 'carriers';
		$this->label = __( 'Carriers', 'envoimoinscher' );

		add_filter( 'emc_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'woocommerce_sections_' . $this->id, array( $this, 'output_sections' ) );
		add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
	}
	
		/**
	 * Add this page to settings
	 */
	public function add_settings_page( $pages ) {
		$pages[ $this->id ] = $this->label;

		return $pages;
	}
	
	/**
	 * Get sections
	 *
	 * @return array
	 */
	public function get_sections() {

		$sections = array(
			'' => __( 'Simple Carriers', 'envoimoinscher' ),
			'advanced_carriers' => __( 'Advanced Carriers', 'envoimoinscher' ),
			'weight_options' => __( 'Weight Options', 'envoimoinscher' ),
		);

		return $sections;
	}
	
	/**
	 * Output the settings
	 */
	public function output() {
		$current_section = empty( $_REQUEST['section'] ) ? '' : sanitize_title( $_REQUEST['section'] );

		if ( $current_section == 'weight_options' ){
			$this->output_weight_options();
		}
		else {
			$this->output_carrier_list( );
		}
	}

	/**
	 * Save settings
	 */
	public function save() {
		$current_section = empty( $_REQUEST['section'] ) ? '' : sanitize_title( $_REQUEST['section'] );
		
		if ( $current_section == 'weight_options' ){	
			$this->save_weight_options();
		} else {
			$this->save_carrier_list();
		}
	}
	
	/**
	 * Output weight options
	 */
	public function output_weight_options() {
		$current_section = empty( $_REQUEST['section'] ) ? '' : sanitize_title( $_REQUEST['section'] );
		
		$dims = envoimoinscher_model::get_dimensions();

		include( dirname ( dirname( __FILE__ ) ) . '/views/html-admin-carriers-weight-options.php');
	}
	
	/**
	 * Save weight options
	 */
	public function save_weight_options() {
		if ( empty( $_POST ) ) {
			return false;
		}
		else {
			global $wpdb;
			
			$from_weight = 0; // Initialize
			//update dimensions
			for ($i = 1; $i <= $_POST['countDims']; $i++)
			{
				$data = array(
					'dim_length'			=> $wpdb->prepare('%d', $_POST['length'.$i]),
					'dim_width'			  => $wpdb->prepare('%d', $_POST['width'.$i]),
					'dim_height'			=> $wpdb->prepare('%d', $_POST['height'.$i]),
					'dim_weight_from'	=> $wpdb->prepare('%f', $from_weight),
					'dim_weight'			=> $wpdb->prepare('%f', $_POST['weight'.$i]),
				);
				$from_weight = $data['dim_weight'];
				envoimoinscher_model::updateDimensions($data, $wpdb->prepare('%d', $_POST['id'.$i]));
			}
		}
	}
	
	/**
	 * Output carrier list
	 */
	public function output_carrier_list( ) {
		$current_section = empty( $_REQUEST['section'] ) ? '' : sanitize_title( $_REQUEST['section'] );
		
		$carrier_list = envoimoinscher_model::get_services();

		$active_carriers = envoimoinscher_model::get_emc_active_shipping_methods();
		
		include( dirname ( dirname( __FILE__ ) ) . '/views/html-admin-carriers-carrier-list.php');
	}
	
	/**
	 * Save carriers
	 */
	public function save_carrier_list() {
		if ( empty( $_POST ) ) {
			return false;
		}
		elseif ( isset( $_POST['refresh'] ) ) {
			$return = envoimoinscher_model::load_carrier_list_api(false);
			if($return === true) emc_admin_settings::add_message( __( 'Your carrier list has been successfully updated.', 'envoimoinscher' ) );
			else emc_admin_settings::add_error( $return );
		}
		elseif ( isset( $_POST['flush'] ) ) {
			$return = envoimoinscher_model::flush_rates_cache();

			if($return !== false) emc_admin_settings::add_message( __( 'Your cache has been successfully flushed.', 'envoimoinscher' ) );
			else emc_admin_settings::add_error( __( 'An error occurred while flushing your cache.', 'envoimoinscher' ) );
		}
		else {
			// save carriers
			$current_section = empty( $_REQUEST['section'] ) ? '' : sanitize_title( $_REQUEST['section'] );
			$emc_services = get_option( 'EMC_SERVICES' , array ( 1 => array(), 2 => array() ) );
			$posted_services = array();
			if ( isset ( $_POST['offers'] ) && !empty ( $_POST['offers'] ) ) {
				foreach ( $_POST['offers'] as $key => $value ) {
					array_push ( $posted_services, $value );
				}
			}
			
			if ( $current_section == 'advanced_carriers' ) {
				$emc_services[2] = $posted_services;
			}
			else {
				$emc_services[1] = $posted_services;
			}

			update_option( 'EMC_SERVICES', $emc_services );
			
			// set default values for automatically activating service
			foreach( $posted_services as $value ){
				if ( get_option( 'woocommerce_' . $value . '_settings' ) ){
					$option = get_option( 'woocommerce_' . $value . '_settings' );
					$option['enabled'] = 'yes';
					update_option( 'woocommerce_' . $value . '_settings',  $option );
				}
				else{
					$service = envoimoinscher_model::get_service_by_carrier_code($value);
					
					if( $service->srv_dropoff_point == 1 ) {
						$default_PP = '';
					}
					else{
						$default_PP = 'POST';
					}
		
					$option = array(
						'enabled' => 'yes',
						'srv_name' => $service->ope_name. ' (' . $service->srv_name_bo . ')',
						'srv_description' => $service->srv_description,
						'pricing' => 1,
						'default_dropoff_point' => $default_PP,
						'carrier_tracking_url' =>'',
					);
					update_option( 'woocommerce_' . $value . '_settings', $option );
				}
			}
		}
	}
	
	/**
	 * Output sections
	 */
	public function output_sections() {
		$current_section = empty( $_REQUEST['section'] ) ? '' : sanitize_title( $_REQUEST['section'] );

		$sections = $this->get_sections();

		if ( empty( $sections ) ) {
			return;
		}

		echo '<ul class="subsubsub">';

		$array_keys = array_keys( $sections );

		foreach ( $sections as $id => $label ) {
			echo '<li><a href="' . admin_url( 'admin.php?page=envoimoinscher-settings&tab=' . $this->id . '&section=' . sanitize_title( $id ) ) . '" class="' . ( $current_section == $id ? 'current' : '' ) . '">' . $label . '</a> ' . ( end( $array_keys ) == $id ? '' : '|' ) . ' </li>';
		}

		echo '</ul><br class="clear" />';
	}
	
}

endif;

