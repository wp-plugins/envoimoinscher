<?php

/**
 * Admin View: Offers simulator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<div class="wrap woocommerce">
  <table class="emc_simulator_list  wc_input_table widefat">
    <thead>
      <tr>
        <th><?php printf( __( 'Offer', 'envoimoinscher' ) ); ?></th>
        <th><?php printf( __( 'Carrier', 'envoimoinscher' ) ); ?></th>
        <th><?php printf( __( 'Price ET', 'envoimoinscher' ) ); ?></th>
        <th><?php printf( __( 'Price ATI', 'envoimoinscher' ) ); ?></th>
        <th><?php printf( __( 'Description', 'envoimoinscher' ) ); ?></th>
      </tr>
    </thead>
    <tbody>
    <?php foreach($offers as $o => $offre) { ?>
        <tr>
          <td class="center label"><b><?php echo $offre['service']['label'];?></b></td>
          <td class="center"><?php echo $offre['operator']['label'];?></td>
          <td class="center"><?php echo $offre['price']['tax-exclusive'];?> <?php  echo $offre['price']['currency'] == 'EUR' ? '€' : $offre['price']['currency']; ?></td>
          <td class="center"><?php echo $offre['price']['tax-inclusive'];?> <?php  echo $offre['price']['currency'] == 'EUR' ? '€' : $offre['price']['currency']; ?></td>
          <td class="hide_links"><?php echo implode('<br /> - ', $offre['characteristics']); ?></td>
        </tr>
    <?php } ?>
    </tbody>
  </table>
</div>
