=== Catablog Ordering - Add Catablog Support for Making Purchase Orders ===
Contributors: diego2k
Tags: plugin, admin, product, sales, orders, purchase, store, cart, ecommerce, Spanish
Requires at least: catablog plugin in order to work
Tested up to: wordpress: 3
Stable tag: trunk

== Description ==

I use CataBlog for make product catalog and i needed a way for let people 
to make a purchase order of product and send it by email, so here it is.
This is a work in progress so feel free to contact me and ask me for updates.

== Installation ==

1) Copy the catablogordering folder to your wordpress plugin folder.

2) Activate the plugin and a new option will come up for settings.

3) Modify the Catablog STORE template form, and get ride of the action attribute of the form tag and add a hidden input named "cmd" with a "_cart" value.

	<form method='post' >
	  <input type='hidden' name='cmd' value='_cart'> <!-- This tell plugin to add to cart -->
	  <input type='hidden' name='item_name' value='%TITLE-TEXT%'>
	  <input type='hidden' name='item_number' value='%PRODUCT-CODE%'>
	  <input type='hidden' name='amount' value='%PRICE%'>
	  <input type='hidden' name='quantity' value='1'>
	  <input type='hidden' name='add' value='1'>
	  <input type='submit' value='ADD TO CART'>
	</form>

4) If you want additional data to be sent on the e-mail add form's fields with  "item_" prefix in the name attribute.

	  <input type='text' name='item_weight'>


== Frequently Asked Questions == 

= Hey dude, where is my CART? =
You must create a new page with the [catablogcart] shortcode.

= Where is the add to cart button? =
This plugin use the %BUY-NOW-BUTTON% of the template, but the %BUY-NOW-BUTTON% only appears when item got a price, that is a limitation of Catablog plugin, there is no way to force the button to showup. A workaround of this is to put a 1 in price for each item you want a add to cart.

= The process don't redirect me to the CART page! =
Check out the option of the plugin you must select what page to redirect to.

= Where can i find you? =
You can find me on facebook/diego2k

= This is cool how can i help? =
Make donation, make translations, improve it, spread the word!

== 0.1.2 ==
Small bugfix to support catablog slug change 

== 0.1.1 ==
Improved Documentation

== 0.1 == 
First Public Release
