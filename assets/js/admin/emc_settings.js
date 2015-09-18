jQuery(window).load(function(){
	
	/* Shipping description page */
	jQuery('.fromto').change(function(){
		jQuery('.fromto-'+jQuery(this).val()).attr('selected','selected');
	});
	
	/* Help page */
	jQuery('#emc_help .category_head').click(function() {
		jQuery(this).find('img').attr('src', img_folder + 'arrow_down.png');
		jQuery(this).next('.questions').slideToggle(function() {	
			if(jQuery(this).prev('.category_head').hasClass('closed')){

				jQuery(this).prev('.category_head').removeClass('closed');
				jQuery(this).prev('.category_head').addClass('open');
			}
			else if(jQuery(this).prev('.category_head').hasClass('open')){
				jQuery(this).prev('.category_head').find('img').attr('src', img_folder + 'arrow_right.png');
				jQuery(this).prev('.category_head').removeClass('open');
				jQuery(this).prev('.category_head').addClass('closed');
				jQuery(this).closest('.category').find('.question').removeClass('open');
				jQuery(this).closest('.category').find('.question').addClass('closed');
				jQuery(this).closest('.category').find('.answer').hide();
			}		
		});
	});
	
	jQuery('#emc_help .question').click(function() {	
		jQuery(this).next('.answer').slideToggle();
		
		if(jQuery(this).hasClass('closed')){
			jQuery(this).removeClass('closed');
			jQuery(this).addClass('open');
		}
		else if(jQuery(this).hasClass('open')){
			jQuery(this).removeClass('open');
			jQuery(this).addClass('closed');
		}
	});
});