jQuery(document).ready(function ($) {
	//jQuery(".btn-add-gift-button").live("click",function(e){
	jQuery(document).on("click",".btn-add-gift-button",function(e){		
		jQuery(".gift_cart_ajax").show();
		
		e.preventDefault();
		
		var pdata = {
					action: pw_wc_gift_adv_ajax.action_add_gift,
					pw_add_gift: jQuery(this).data("id"),
				}
		$.ajax ({
			type: "POST",
			url: pw_wc_gift_adv_ajax.ajaxurl,
			data: pdata,
			success: function (resp) {
			    //alert(resp);
				window.location.href = pw_wc_gift_adv_ajax.cart_page_id;
			}
		});
	}); 	
	
	//jQuery(".btn-select-gift-button").live("click",function(e){
	jQuery(document).on("click",".btn-select-gift-button",function(e){
		
		
		e.preventDefault();
	
		var pdata = {
					action: pw_wc_gift_adv_ajax.action_show_variation,
					pw_gift_variable: jQuery(this).data("id"),
					pw_gift_rule_id: jQuery(this).data("rule-id"),
				}
		$.ajax ({
			type: "POST",
			url: pw_wc_gift_adv_ajax.ajaxurl,
			data: pdata,
			success: function (resp) {
			   // alert();
				$(".pw-gifts").html(resp);				
				chanegLayout();
				jQuery(".pw-cover").css('visibility','visible');
				jQuery(".pw_gift_popup").css('visibility','visible');
				//window.location.href = pw_wc_gift_adv_ajax.cart_page_id;
			}
		});
		  
	}); 
	
	$(".pw_gift_pagination_num").click(function(e){
		e.preventDefault();
		var page=$(this).attr("data-page-id");
		$("."+page).siblings(".pw_gift_pagination_div").removeClass("pw-gift-active");
		$("."+page).addClass("pw-gift-active");
	});	
	

	jQuery('.pw-cover,.pw_gift_popup_close').on('click',function(){
		jQuery('.pw_gift_popup').css('visibility','hidden');
		jQuery('.pw-cover').css('visibility','hidden');
	});	
});

jQuery(window).on('resize', function(){
	chanegLayout();
});
function chanegLayout() {
  jQuery('.pw_gift_popup').css({
    position: 'fixed',
    left: (jQuery(window).width() - jQuery('.pw_gift_popup').outerWidth()) / 2,
    top: (jQuery(window).height() - jQuery('.pw_gift_popup').outerHeight()) / 2
  });

}

