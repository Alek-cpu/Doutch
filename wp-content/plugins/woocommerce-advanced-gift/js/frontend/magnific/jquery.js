jQuery(function(){
	//jQuery('.popup1_open').click(function (e) {
	jQuery('#popup1').popup();  
	alert('asd');
	jQuery("body").append("<div class='bt-quickview-popup'><div id='popup1'>aaaa</div></div>");	
	jQuery('.popup1_open').live('click',function(e){		
				e.preventDefault();

		
		var pdata = {
					action: 'bt_quickview_js_response',
					pw_gift_add: jQuery(this).data('id'),
				}
		//alert(jQuery(this).data('id'));
		jQuery.ajax ({
			type: 'POST',
			url: 'http://localhost/woocommerce_woo_gift/wp-admin/admin-ajax.php',
			data: pdata,
			success: function (resp) {
			//	jQuery("#popup1").html(jQuery(this).data('id'));
			//alert(resp);
				jQuery("#popup1").html(resp);
				
				// window.location.href = '" . $cart_page_id . "';
			}
		});
	});
	
});