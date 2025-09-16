import { test, expect } from '@playwright/test';
const domain = 'https://demo.athemes.com/';
const modulesDemoUrls = [
	{
		id: 'product-bundles',
		selector: '.mppb-wrap',
		name: 'Product Bundles',
		url: domain + 'merchant/product/earth-deep-pore-minimizing-cleansing/?scrollto=.mppb-wrap'
	},
	{
		id: 'frequently-bought-together',
		selector: '.merchant-frequently-bought-together',
		name: 'Frequently Bought Together',
		url: domain + 'merchant/product/deep-sweep-2-bha-pore-cleaning-toner-with-moringa/?scrollto=.merchant-frequently-bought-together'
	},
	{
		id: 'bulk-discounts',
		selector: '.merchant-volume-discounts',
		name: 'Bulk Discounts',
		url: domain + 'merchant/product/eternal-sunset-collection-lip-and-cheek-set-with-jojoba-oil/'
	},
	{
		id: 'buy-x-get-y',
		selector: '.merchant-bogo',
		name: 'Buy X Get Y',
		url: domain + 'merchant/product/vinopure-pore-purifying-gel-cleanser-salicylic-acid-combination/'
	},
	{
		id: 'waitlist',
		selector: '.merchant-wait-list-container',
		name: 'Waitlist',
		url: domain + 'merchant/product/rare-earth-deep-pore-minimizing-cleansing-clay-mask/'
	},
	{
		id: 'free-gifts',
		selector: '.merchant-free-gifts-widget-button',
		name: 'Free Gifts',
		url: domain + 'merchant/shop/?display=free-gifts'
	},
	{
		id: 'free-shipping-bar',
		selector: '.product .entry-summary .merchant-freespb-wrapper',
		name: 'Free Shipping Bar',
		url: domain + 'merchant/product/facial-cream/?add-to-cart=95&display=free-shipping-bar'
	},
	// {
	// 	id: 'storewide-sale',
	// 	selector: '.merchant-storewide-sale',
	// 	name: 'Storewide Sale',
	// 	url: domain + 'merchant/shop/?display=storewide-sale'
	// }
	{
		id: 'spending-goal',
		selector: '.merchant-spending-goal-widget',
		name: 'Spending Goal',
		url: domain + 'merchant/shop/?display=spending-goal&add-to-cart=95'
	},
	{
		id: 'pre-order',
		type: 'contains-text',
		selector: '.product .entry-summary .single_add_to_cart_button',
		text: 'Pre-Order',
		name: 'Pre-Order',
		url: domain + 'merchant/product/ultra-facial-moisturizing-cream-with-squalane-combination/?display=pre-order'
	},
	{
		id: 'sticky-add-to-cart',
		selector: '.merchant-sticky-add-to-cart-wrapper',
		name: 'Sticky Add to Cart',
		url: domain + 'merchant/product/facial-treatment-essence-pitera-essence/?display=sticky-add-to-cart&scrollto=.product_title'
	},
	{
		id: 'added-to-cart-popup',
		selector: '.merchant-added-to-cart-popup .popup',
		name: 'Added to Cart Popup',
		url: domain + 'merchant/shop/?display=added-to-cart-popup&tclick=.post-95%20.add_to_cart_button'
	},
	{
		id: 'countdown-timer',
		selector: '.merchant-countdown-timer',
		name: 'Countdown Timer',
		url: domain + 'merchant/product/deep-sweep-2-bha-pore-cleaning-toner-with-moringa/?display=countdown-timer'
	},
	{
		id: 'shopify-checkout',
		selector: '.merchant-pro-sc-layout-shopify',
		name: 'Shopify Checkout',
		url: domain + 'merchant/checkout/?display=shopify-checkout&add-to-cart=95'
	},
	{
		id: 'one-step-checkout',
		selector: '.merchant-pro-sc-layout-one-step',
		name: 'One Step Checkout',
		url: domain + 'merchant/checkout/?display=one-step-checkout&add-to-cart=95'
	},
	{
		id: 'multi-step-checkout',
		selector: '.merchant-pro-multi-step-wrapper',
		name: 'Multi Step Checkout',
		url: domain + 'merchant/checkout/?display=multi-step-checkout&add-to-cart=95'
	},
	{
		id: 'stock-scarcity',
		selector: '.merchant-stock-scarcity',
		name: 'Stock Scarcity',
		url: domain + 'merchant/product/mini-radiant-creamy-concealer-and-blush-customizable-set/'
	},
	{
		id: 'recently-viewed-products',
		selector: '.merchant-recently-viewed-products',
		name: 'Recently Viewed Products',
		url: domain + 'merchant/product/facial-treatment-essence-pitera-essence/?scrollto=.merchant-recently-viewed-products-section&ck=woocommerce_recently_viewed&ckv=450%7C452%7C42&reloadpage=1'
	},
	{
		id: 'buy-now',
		selector: '.product .entry-summary .merchant-buy-now-button',
		name: 'Buy Now',
		url: domain + 'merchant/product/watermelon-glow-hyaluronic-clay-pore-tight-facial-mask/?display=buy-now'
	},
	{
		id: 'product-labels',
		selector: '.merchant-product-labels',
		name: 'Product Labels',
		url: domain + 'merchant/shop/?display=product-labels'
	},
	{
		id: 'quick-view',
		selector: '.merchant-quick-view-button',
		name: 'Quick View',
		url: domain + 'merchant/shop/?display=quick-view&scrollto=.woocommerce-sorting-wrapper'
	},
	{
		id: 'side-cart',
		selector: '.merchant-side-cart',
		name: 'Side Cart',
		url: domain + 'merchant/shop/?tclick=.site-header-cart%20a.cart-contents&add-to-cart=95'
	},
	{
		id: 'cart-reserved-timer',
		selector: '.merchant-cart-reserved-timer',
		name: 'Cart Reserved Timer',
		url: domain + 'merchant/cart/?reloadpage=1&add-to-cart=93'
	},
	// {
	// 	id: 'cart-count-favicon',
	// 	selector: '.merchant-cart-count-favicon',
	// 	name: 'Cart Count Favicon',
	// 	url: domain + 'merchant/shop/?add-to-cart=95'
	// }
	// {
	// 	id: 'inactive-tab-message',
	// 	selector: '.merchant-inactive-tab-message',
	// 	name: 'Inactive Tab Message',
	// 	url: domain + 'merchant/shop/'
	// },
	{
		id: 'advanced-reviews',
		selector: '.merchant-adv-reviews',
		name: 'Advanced Reviews',
		url: domain + 'merchant/product/deep-sweep-2-bha-pore-cleaning-toner-with-moringa/?display=advanced-reviews&scrollto=.merchant-adv-reviews'
	},
	{
		id: 'recent-sales-notifications',
		selector: '.merchant-recent-sales-notifications-widget',
		name: 'Recent Sales Notifications',
		url: domain + 'merchant/product/variable-facial-with-sizes/'
	},
	{
		id: 'reasons-to-buy-list',
		selector: '.merchant-reasons-list',
		name: 'Reasons to Buy List',
		url: domain + 'merchant/product/variable-facial-with-sizes/?display=reasons-to-buy'
	},
	{
		id: 'quick-social-links',
		selector: '.merchant-quick-social-links',
		name: 'Quick Social Links',
		url: domain + 'merchant/shop/?display=quick-social-links'
	},
	{
		id: 'brand-image',
		selector: '.merchant-product-brand-image',
		name: 'Brand Image',
		url: domain + 'merchant/product/pore-cleansing-minimizing/?display=brand-image'
	},
	{
		id: 'trust-badges',
		selector: '.merchant-trust-badges',
		name: 'Trust Badges',
		url: domain + 'merchant/product/earth-deep-pore-minimizing-cleansing/?display=trust-badges&scrollto=.merchant-trust-badges-images&scrollto-offset=300'
	},
	{
		id: 'payment-logos',
		selector: '.merchant-payment-logos',
		name: 'Payment Logos',
		url: domain + 'merchant/product/watermelon-glow-hyaluronic-clay-pore-tight-facial-mask/?display=payment-logos&scrollto=.merchant-payment-logos&scrollto-offset=200'
	},
	{
		id: 'wishlist',
		selector: '.merchant-wishlist-button',
		name: 'Wishlist',
		url: domain + 'merchant/shop/?display=wishlist'
	},
	{
		id: 'variation-swatches',
		selector: '.merchant-variations-wrapper',
		name: 'Variation Swatches',
		url: domain + 'merchant/product/variable-facial-cream-rare/'
	},
	{
		id: 'google-address-autocomplete',
		selector: '.pac-container',
		name: 'Google Address Autocomplete',
		url: domain + 'merchant/checkout/?display=one-step-checkout&add-to-cart=95&focusto=.merchant-pro-sc-field-address_1&focustotext=New%20York'
	},
	{
		id: 'size-chart',
		selector: '.merchant-product-size-chart-modal-inner',
		name: 'Size Chart',
		url: domain + 'merchant/product/variable-facial-with-sizes/?display=size-chart&tclick=.merchant-product-size-chart%20a'
	},
	{
		id: 'product-video',
		selector: '.merchant-product-video',
		name: 'Product Video',
		url: domain + 'merchant/product/facial-cream/'
	},
	{
		id: 'product-audio',
		selector: '.merchant-product-audio',
		name: 'Product Audio',
		url: domain + 'merchant/product/mini-eternal-sunset/'
	},
	{
		id: 'login-popup',
		selector: '.merchant-login-popup-button',
		name: 'Login Popup',
		url: domain + 'merchant/login-popup/'
	},
	{
		id: 'product-navigation-links',
		selector: '.merchant-product-navigation',
		name: 'Product Navigation Links',
		url: domain + 'merchant/product/variable-facial-with-sizes/?display=product-navigation'
	},
	{
		id: 'real-time-search',
		selector: '.merchant-ajax-search-wrapper',
		name: 'Real Time Search',
		url: domain + 'merchant/?s=rare&post_type=product&tclickv=a.header-search'
	},
	{
		id: 'clear-cart',
		selector: '.merchant-clear-cart-button',
		name: 'Clear Cart',
		url: domain + 'merchant/cart/?add-to-cart=95&display=clear-cart'
	},
	{
		id: 'animated-add-to-cart',
		type: 'contains-css-animation-name',
		selector: '.single_add_to_cart_button',
		animationName: 'merchant-swing',
		name: 'Animated Add to Cart',
		url: domain + 'merchant/product/watermelon-glow-hyaluronic-clay-pore-tight-facial-mask/?display=animated-add-to-cart'
	},
	{
		id: 'add-to-cart-text',
		type: 'contains-text',
		selector: '.single_add_to_cart_button',
		text: 'Custom Add To Cart',
		name: 'Add to Cart Text',
		url: domain + 'merchant/product/variable-facial-with-sizes/?display=add-to-cart-text'
	},
	{
		id: 'auto-external-links',
		type: 'contains-text',
		selector: '.product .entry-summary a',
		text: 'External Link',
		name: 'Auto External Links',
		url: domain + 'merchant/product/mini-radiant-creamy-concealer-and-blush-customizable-set/'
	},
	{
		id: 'scroll-to-top-button',
		selector: '.merchant-scroll-to-top-button',
		name: 'Scroll to Top Button',
		url: domain + 'merchant/?scrollto=.bhfb-footer'
	},
	{
		id: 'agree-to-terms-checkbox',
		selector: '.woocommerce-terms-and-conditions-checkbox-text',
		name: 'Agree to Terms Checkbox',
		url: domain + 'merchant/checkout/?scrollto=.merchant-pro-sc-payment-methods&add-to-cart=95'
	},
	{
		id: 'cookie-banner',
		selector: '.merchant-cookie-banner',
		name: 'Cookie Banner',
		url: domain + 'merchant/shop/?display=cookie-banner&rmcookie=merchant_cookie_banner'
	}
];

for(const module of modulesDemoUrls) {
	test(`Module ${module.name} is visible`, async ({ page, browserName }) => {
		await page.goto(module.url);

		if ( typeof module.type !== 'undefined' &&  module.type === 'contains-text' ) {
			await expect(page.locator(module.selector).nth(0)).toContainText(module.text);
		} else if ( typeof module.type !== 'undefined' &&  module.type === 'contains-css-animation-name' ) {
			const element = page.locator(module.selector).nth(0);

			await element.hover();
			const hasHoverAnimation = await element.evaluate((el, animationName) => {
				const styles = window.getComputedStyle(el);
				return styles.animationName.includes(animationName);
			}, module.animationName);
			await expect(hasHoverAnimation).toBeTruthy();
		} else {
			await expect(page.locator(module.selector).nth(0)).toBeVisible();
		}
	});
}