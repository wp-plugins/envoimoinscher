
<style>
.rate-table  td{
	border: 1px solid grey;
	padding: 5px ;
	vertical-align: middle;
}
td.first  {	
	border-top: 2px solid grey;
}
.rate-table  td input{
	text-align: right;
	width: 100px;
	float: left;
}
span.middle-align{
	margin: 5px 0 0 5px;
	display: block;
	float: left;
}  
table.rate-table{
	border-collapse: collapse;
	text-align: center; 
}
tr.delete td, tr.delete{
	border: 0;
}
.hidden{
	display : none;
}
ul.rate-type li, ul.pricing li{
	display: block;
	float: left;
	padding-right: 20px;
	margin:0;
}
ul.rate-type, ul.pricing{
	margin:0;
}
ul.rate-type:after{	
    content:'';
    display:block;
    clear: both;
}
</style>
<?php $weight_scales 	= 	envoimoinscher_model::get_scale_data($this->id, 'weight'); 
			$price_scales 	= 	envoimoinscher_model::get_scale_data($this->id, 'price');
?>
<div class="rate-div">
	<!-- WEIGHT RATE TABLE -->
	<div class="weight-part">
		<table class="rate-table weight">
			<tr class="rate-from">
				<td rowspan=2 >Poids </td>
				<td rowspan=2 >entre <br /><br /> et (>) </td>
				<?php if(!isset($weight_scales[0]['rate_from'])):?>
					<td><input type="text" name="rate_from[]" value="0" id="" /> <span class="middle-align"> kg</span></td>
				<?php endif;
						 foreach ($weight_scales as $weight_scale) : ?>
				<td><input type="text" name="rate_from[]" value="<?php echo $weight_scale['rate_from'] ?>" id="" /> <span class="middle-align"> kg</span></td>
				<?php endforeach ?>
			</tr>
			<tr class="rate-to">
				<?php if(!isset($weight_scales[0]['rate_to'])):?>
					<td><input type="text" name="rate_to[]" value="1" id="" /> <span class="middle-align"> kg</span></td>
				<?php endif;?>
				<?php foreach ($weight_scales as $weight_scale) : ?>
				<td><input type="text" name="rate_to[]" value="<?php echo $weight_scale['rate_to'] ?>" id="" /> <span class="middle-align"> kg</span></td>
				<?php endforeach ?>
			</tr>
			<tr class="price">		
				<td></td>
				<td>Prix</td>
				<?php if(!isset($weight_scales[0]['rate_price'])):?>
					<td><input type="text" name="rate_price[]" value="5" id="" /> <span class="middle-align"> Euros</span></td>
				<?php endif;
				 			foreach ($weight_scales as $weight_scale) : ?>
				<td><input type="text" name="rate_price[]" value="<?php echo $weight_scale['rate_price'] ?>" id="" /> <span class="middle-align"> Euros</span></td>
				<?php endforeach ?>
			</tr>
			<tr class="delete">
				<td></td>
				<td></td>
				<td></td>			
				<?php 
				$i = 0;
				foreach ($weight_scales as $weight_scale) : 
					if($i >= 1){ ?>
	 				<td><span class="delete button-primary" rel="weight" delete="<?php echo $i + 3; ?>">delete</span></td>
				<?php
					}
					$i++;
				endforeach ?>			 
			</tr>
		</table>
		<br />
		<input type="hidden" name="rate_type" value="weight"/>
		<span class="add-rate button-primary" rel="weight">ajouter une tranche</span>
	</div>
	<!-- END -->

	
	<!-- PRICE RATE TABLE -->
	<div class="price-part">
		<table class="rate-table price">
			<tr class="rate-from">
				<td rowspan=2 >Prix </td>
				<td rowspan=2 >entre <br /><br /> et (>) </td>
				<?php if(!isset($price_scales[0]['rate_from'])):?>
					<td><input type="text" name="rate_from[]" value="0" id="" /> <span class="middle-align"> €</span></td>
				<?php endif;
							foreach ($price_scales as $price_scale) : ?>
				<td><input type="text" name="rate_from[]" value="<?php echo $price_scale['rate_from'] ?>" id="" /> <span class="middle-align"> €</span></td>
				<?php endforeach ?>
			</tr>
			<tr class="rate-to">
				<?php if(!isset($price_scales[0]['rate_to'])):?>
					<td><input type="text" name="rate_to[]" value="10" id="" /> <span class="middle-align"> €</span></td>
				<?php endif;
							foreach ($price_scales as $price_scale) : ?>
				<td><input type="text" name="rate_to[]" value="<?php echo $price_scale['rate_to'] ?>" id="" /> <span class="middle-align"> €</span></td>
				<?php endforeach ?>
			</tr>
			<tr class="price">		
				<td></td>
				<td>Prix</td>
				<?php if(!isset($price_scales[0]['rate_price'])):?>
					<td><input type="text" name="rate_price[]" value="5" id="" /> <span class="middle-align"> Euros</span></td>
				<?php endif;
							foreach ($price_scales as $price_scale) : ?>
				<td><input type="text" name="rate_price[]" value="<?php echo $price_scale['rate_price'] ?>" id="" /> <span class="middle-align"> Euros</span></td>
				<?php endforeach ?>
			</tr>
			<tr class="delete">
				<td></td>
				<td></td>
				<td></td>			
				<?php 
				$i = 0;
				foreach ($price_scales as $price_scale) : 
					if($i >= 1){ ?>
	 				<td><span class="delete button-primary" rel="price" delete="<?php echo $i + 3; ?>">delete</span></td>
				<?php
					}
					$i++;
				endforeach ?>
				 
			</tr>
		</table>
		<br />
		<span class="add-rate button-primary" rel="price">ajouter une tranche</span>
		<input type="hidden" name="rate_type" value="price"/>
	</div>
	<!-- END -->
	<input type="hidden" name="rate_carrier" value="<?php echo $this->id ?>"/>
</div>
<script>
	// function to show or hide rate elements 
	function showHideTable(rate_checked, type_rate_checked){
		jQuery("div.weight-part,div.price-part").hide();
		jQuery("div.weight-part :input, div.price-part :input").attr("disabled", true);
		// rate is not selected
		if(rate_checked == "1" ){
			// weight or rate table is hidden
			jQuery("div."+type_rate_checked+"-part").hide();
			// all div is hidden
			jQuery(".rate-div").fadeOut();
			jQuery(".rate-type").fadeOut();
			jQuery(".rate-type").next("p.description").fadeOut();
		}
		else{
			// weight or rate table is shown
			jQuery("div."+type_rate_checked+"-part").show();
			jQuery("div."+type_rate_checked+"-part :input").attr("disabled", false);
			// all div is shown
			jQuery(".rate-div").fadeIn(1200);
			// button display
			jQuery(".rate-type").fadeIn(1200);
			jQuery(".rate-type").next("p.description").fadeIn(1200);
		}
	}
	
	// 2 => rate 1=> real price
	var rate_checked;
	rate_checked = jQuery('input[name="<?php echo $this->plugin_id.$this->id ;?>_pricing"]:checked').attr("value");
	// weight Or price
	var type_rate_checked;
	type_rate_checked = jQuery('input[name="<?php echo $this->plugin_id.$this->id ;?>_rate_type"]:checked').attr("value");
	
	showHideTable(rate_checked, type_rate_checked);

	
	// function to add column to rate table
	jQuery(document).delegate(".add-rate","click",function(){
		var rate_selected = jQuery(this).attr("rel");
		// Add of Scale value from 
		var rate_from_element = jQuery(".rate-table."+rate_selected+" .rate-from td").last().clone();
		var rate_from_val 		= parseFloat(jQuery(".rate-table."+rate_selected+" .rate-to td").last().find("input").val());
		rate_from_element.find("input").attr("value", rate_from_val);
		jQuery(".rate-table."+rate_selected+" .rate-from").append("<td>"+rate_from_element.html()+"</td>");
		
		// Add of Scale value to
		var rate_to_element 	= jQuery(".rate-table."+rate_selected+" .rate-to td").last().clone();
		var rate_to_val 			= parseFloat(rate_from_val) + parseFloat(0.1);
		rate_to_element.find("input").attr("value", rate_to_val.toFixed(1));
		jQuery(".rate-table."+rate_selected+" .rate-to").append("<td>"+rate_to_element.html()+"</td>");
		
		//Add of Scale value price
		var rate_to_element 	= jQuery(".rate-table."+rate_selected+" .price td").last().clone();
		var rate_to_val 			= jQuery(".rate-table."+rate_selected+" .price td").last().find("input").val();
		rate_to_element.find("input").attr("value", rate_to_val);
		jQuery(".rate-table."+rate_selected+" .price").append("<td>"+rate_to_element.html()+"</td>");

		var j = jQuery(".rate-table."+rate_selected+" .rate-from td").last().index() + 1;
		//add a delete button
		jQuery(".rate-table."+rate_selected+" tr.delete").append("<td><span class='delete button-primary' rel='"+rate_selected+"' delete='"+j+"'>delete</span></td>");
	});
	
	//function to delete created tab columns
	jQuery(document).delegate(".delete.button-primary","click",function(){
		var listItem = document.getElementById("tr.delete td");
		var n = jQuery(this).parent().index(listItem);
		var o = n - 2;

		jQuery(".rate-table."+jQuery(this).attr("rel")+" tr.rate-to").find('td:eq('+(o)+')').remove();
		jQuery(".rate-table."+jQuery(this).attr("rel")+" tr.rate-from").find('td:eq('+n+')').remove();
		jQuery(".rate-table."+jQuery(this).attr("rel")+" tr.price").find('td:eq('+n+')').remove();
		jQuery(".rate-table."+jQuery(this).attr("rel")+" tr.delete").find('td:eq('+n+')').remove();
	});
	
	// function to make event on pricing type input
	jQuery(document).delegate(".pricing","click",function(){		
		var checked_value = jQuery(this).find('input:checked').attr("value");
		rate_checked = checked_value;
		showHideTable(checked_value, type_rate_checked);
	});

	// function to make event on rate type input
	jQuery(document).delegate(".rate-type","click",function(){		
		var checked_value = jQuery(this).find('input:checked').attr("value");
		type_rate_checked = checked_value;
		showHideTable(checked_value, type_rate_checked);
	});
</script>