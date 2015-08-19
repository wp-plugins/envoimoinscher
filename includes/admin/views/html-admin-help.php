<?php

/**
 * Admin View: Offers simulator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !isset ($faq) || empty($faq) ) {
	exit; // Exit if $faq is not defined
}

?>

<div id="emc_help" class="wrap woocommerce">
	
	<h3><?php _e('Frequently asked questions', 'envoimoinscher'); ?></h3>

  <?php 
		foreach( $faq as $category ) {
			echo '<div class="category">';
			echo '<div class="category_head closed"><img src="' . plugins_url( 'assets/img/arrow_right.png', EMC_PLUGIN_FILE) . '" class="img_switch" alt="click to switch">';
			echo '<h3 class="category_name">' . $category['category'] . '</h3></div>';
			
			echo '<div class="questions">';			
			foreach ( $category['questions'] as $item ) {
				if ( isset($item['comment']) ) echo '<div class="comment">' . $item['comment'] . '</div>';
				echo '<div class="question closed">' . $item['question'] . '</div>';
				echo '<div class="answer">' . $item['answer'] . '</div>';
			}
			echo '</div></div>';
		}
	?>
</div>
