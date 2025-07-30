/**
 * Merchant Pre Orders.
 * 
 */

'use strict';

var merchant = merchant || {};

merchant.modules = merchant.modules || {};

(function($) {
	
	merchant.modules.preOrders = {
		
		init: function() {
			
			var self = this;
			
			var cachedAddToCartText;
			
			$(document).on('show_variation', '.variations_form', function ( event, variation ) {
				
				var $product = $(this).closest('.product');
				
				var $button  = $product.find('.single_add_to_cart_button:not(.merchant_buy_now_button)');
				var $form    = $product.find('.variations_form');
				var $dateTxt = $product.find('.merchant-pre-orders-date');
				
				if ( ! cachedAddToCartText ) {
					cachedAddToCartText = $button.html();
				}
				
				if ( $dateTxt.length ) {
					$dateTxt.remove();
				}
				
				$product.removeClass('merchant-pre-ordered-product');
				if( variation.is_pre_order === true ) {
					if ( variation.is_pre_order_date ) {
						$product.addClass('merchant-pre-ordered-product');
						if(variation.pre_order_placement === 'before'){
							$product.find('.variations_form.cart').before('<div class="merchant-pre-orders-date">'+ variation.is_pre_order_date +'</div>');
						} else if(variation.pre_order_placement === 'after') {
							$product.find('.variations_form.cart').after('<div class="merchant-pre-orders-date">'+ variation.is_pre_order_date +'</div>');
						}
					}
					$button.html(window.merchant.setting.pre_orders_add_button_title);
				} else {
					$button.html(cachedAddToCartText);
				}
				
			});
			
			$(document).on('click.wc-variation-form', '.reset_variations', function () {
				
				var $product = $(this).closest('.product');
				
				var $dateTxt = $product.find('.merchant-pre-orders-date');
				
				if ( $dateTxt.length ) {
					$dateTxt.remove();
				}
				
				$product.removeClass('merchant-pre-ordered-product');
				
				$product.find('.single_add_to_cart_button:not(.merchant_buy_now_button)').html(cachedAddToCartText);
				
			});
			
		},
		
	};
	
	merchant.modules.preOrders.init();
	
}(jQuery));
