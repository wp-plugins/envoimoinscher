<?php
/**
 * Admin View: Weight options
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

		?>
		<h3><?php printf( __( 'Weight Options', 'envoimoinscher' ) ); ?></h3>
		<p><?php printf( __( 'On this page, you can customize the maximum size of your parcel by weight brackets. Dimensions are one of the primary criteria for shipping attribution. Enter most common dimensions to get realistic prices. These options will be used to get advanced carriers rates.', 'envoimoinscher' ) ); ?></p>
		<p><?php printf( __( 'Without customization, the default dimensions provided by EnvoiMoinsCher are used.', 'envoimoinscher' ) ); ?></p>
		
		<table class="<?php echo $current_section; ?> wc_input_table widefat">
			<thead>
				<tr>

					<th>#</th>

					<th><?php _e( 'Weight', 'envoimoinscher' ); ?></th>

					<th><?php _e( 'Max length', 'envoimoinscher' ); ?></th>

					<th><?php _e( 'Max width', 'envoimoinscher' ); ?></th>
					
					<th><?php _e( 'Max height', 'envoimoinscher' ); ?></th>

				</tr>
				
			</thead>
			<tbody>
				<?php	foreach ( $dims as $key => $value ) {	?>
					<tr>
						<td class=""><?php echo $key+1; ?></td>

						<td class="dimension">
							<input type="text" name="weight<?php echo $key+1; ?>" id="weight<?php echo $key+1; ?>" value="<?php echo $value->dim_weight; ?>" class="" /> <span>kg</span>
						</td>

						<td class="dimension">
							<input type="text" name="length<?php echo $key+1; ?>" id="length<?php echo $key+1; ?>" value="<?php echo $value->dim_length; ?>" class="" /> <span>cm</span>
						</td>

						<td class="dimension">
							<input type="text" name="width<?php echo $key+1; ?>" id="width<?php echo $key+1; ?>" value="<?php echo $value->dim_width; ?>" class="" /> <span>cm</span>
						</td>
						
						<td class="dimension">
							<input type="text" name="height<?php echo $key+1; ?>" id="height<?php echo $key+1; ?>" value="<?php echo $value->dim_height; ?>" class="" /> <span>cm</span>
							<input type="hidden" name="id<?php echo $key+1; ?>" id="id<?php echo $key+1; ?>" value="<?php echo $value->dim_id; ?>" />
						</td>
					</tr>
				<?php } ?>
				<input type="hidden" name="countDims" id="countDims" value="<?php echo count($dims); ?>" />
			</tbody>
		</table>