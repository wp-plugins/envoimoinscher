<?php
/**
 * Admin View: Carriers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
		<br/><input type="submit" class="button-primary" name="refresh" value="<?php _e( 'Reload carriers from API', 'envoimoinscher' ); ?>">
		<?php _e( 'By clicking on this link, you ensure the carrier list is up to date.', 'envoimoinscher' ); ?>
		
		<br/><br/><input type="submit" class="button-primary" name="flush" value="<?php _e( 'Flush offers cache', 'envoimoinscher' ); ?>">
		<?php _e( 'Flush your offers cache if you change your active carriers in order for new carriers to be taken into account.', 'envoimoinscher' ); ?>
		
		<?php if ( $current_section == "advanced_carriers" ) : ?>
		
		<?php $carrier_family = 2; ?>
		<h3><?php printf( __( 'Advanced Carriers', 'envoimoinscher' ) ); ?></h3>
		<p><?php printf( __( 'These carriers require dimensional weight. You must define dimensions per weight bracket for your parcels in tab "Weight options". Indicate dimensions that match the best your packages. Defaults dimensions grid may not be accurate and lead to wrong declaration to the carriers and complementary invoices.', 'envoimoinscher' ) ); ?></p>
		
		<?php else : ?>
		
		<?php $carrier_family = 1; ?>
		<h3><?php printf( __( 'Simple Carriers', 'envoimoinscher' ) ); ?></h3>
		<p><?php printf( __( 'These carriers allow you to send your parcel without dimensional weight. The weight is only used to calculate the shipping cost within the maximum size allowed by each carrier.', 'envoimoinscher' ) ); ?></p>	
		
		<?php endIf; ?>

		<table class="emc_carrier_list <?php echo $current_section; ?> wc_input_table widefat">
			<thead>
				<tr>

					<th><?php _e( 'Offers', 'envoimoinscher' ); ?></th>

					<th><?php _e( 'Description', 'envoimoinscher' ); ?></th>

					<th><?php _e( 'Status', 'envoimoinscher' ); ?></th>
					
					<th><?php _e( 'Edition', 'envoimoinscher' ); ?></th>

				</tr>
			</thead>
			<tbody>
				<?php foreach ( $carrier_list as $carrier ) { 
					if( intval($carrier->srv_family) != $carrier_family) continue;
					
					$carrier_code = $carrier->ope_code.'_'.$carrier->srv_code;

					if( in_array( $carrier_code , $active_carriers) ) {
						$carrier->status = 1;
					}
					else {
						$carrier->status = 0;
					}
				?>
					<tr id="<?php echo $carrier_code; ?>" class="<?php echo ( 1 == $carrier->status ? 'active' : 'inactive' ); ?>">
						<td class="offers">
							<div class="name"><?php echo $carrier->srv_name_bo; ?></div> 				
						</td>

						<td class="description"><?php echo $carrier->srv_description_bo; ?></td>

						<td class="status">
							<span>
								<input type="checkbox" name="offers[]" value="<?php echo $carrier_code; ?>" id="" <?php if ( 1 == $carrier->status ) { echo 'checked="checked"'; } ?> />
							</span>
						</td>
						
						<td class="status" >
							<?php if ( 1 == $carrier->status ) { ?>
								<a target="_blank" href="<?php echo admin_url('admin.php?page=wc-settings&tab=shipping&section='.strtolower($carrier_code))?>">
									<?php _e( 'Edit', 'envoimoinscher' ); ?>
								</a>
							<?php } else {?>
							-
							<?php } ?>
						</td>						
					</tr>
				<?php	}	?>
			</tbody>
		</table>
