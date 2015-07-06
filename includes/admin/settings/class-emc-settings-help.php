<?php
/**
 * EMC Help page
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'emc_settings_help' ) ) :

/**
 * emc_settings_help
 */
class emc_settings_help extends WC_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->id    = 'help';
		$this->label = __( 'Help', 'envoimoinscher' );
		
		add_filter( 'emc_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
	}
	
 /**
	* Override output
	*/
	public function output() {
		// Hide the save button
		$GLOBALS['hide_save_button'] = true;
		
		include_once 'faq.php';
    include_once dirname ( dirname (__FILE__) ) . '/views/html-admin-help.php';
	}
	
}

endif;
