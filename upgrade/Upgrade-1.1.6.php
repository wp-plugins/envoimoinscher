<?php
/**
 * Upgrade to module version 1.1.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function upgrade_1_1_6() {
	
	add_option('EMC_carrier_display', 'no');
	
	return true;
}