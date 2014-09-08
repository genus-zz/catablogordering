=== Catablog Ordering - Add Catablog Support for Making Purchase Orders ===
Contributors: diego2k
Tags: plugin, admin, product, sales, orders, purchase, store, cart, ecommerce, Spanish
Requires at least: 3.5
Tested up to: 3.9
Stable tag: 0.2

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

5) List of Hooks and Filters

	With this version i added some new filters and action hooks:
	
	ACTION:
		* catablogcart_before_event($command)
		This action is performed before add/remove/empty cart functions are executed, the command indicates wich one will be executed.
			
		* catablogcart_after_event($command, $redirect)
		Same as before but it raised after process and the redirect var indicates if there will be a redirect to cart page.

		* catablogcart_before_form_fields		
		This action is executed before the html order form tag is rendered so it's a good place to add some input tags that you want to be submitted
	
		* catablogcart_after_form_fields
		This action is executed after the html order form tag is rendered so it's a good place to add some input tags that you want to be submitted
	
	FILTERS:
		* catablogcart_redirect
		Indicates if there will be a redirect after a add/remove/empty command is executed.
		
		* catablogcart_redirect_id
		Indicates the page_id for wp_redirect function.
	
		* catablogcart_add_item($item_info)
	
		Email Related Filters:
		
			* catablogcart_email_order_to($to)
		
			* catablogcart_email_order_from($from)
		
			* catablogcart_email_order_fromname($fromname)
		
			* catablogcart_email_order_subject($subject)
		
			* catablogcart_email_order_items($items)
		
			* catablogcart_email_order_table($order_table)

			* catablogcart_email_body($email_body, $post_vars)
			
		
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

== 0.2 ==
Some improvements on how the code works
Added some hooks and filters
Removed the annoying 1 

== 0.1.4 ==
Added Option to Hide Prices and Totals
Added Option to send HTML e-mails
Fixed template location to be same as catablog's upload folder
Removed item_ prefix on e-mail table titles

== 0.1.3 ==
Fixed problem with translation
Fixed bug that only emails one item from cart
Added support for custom order template

== 0.1.2 ==
Small bugfix to support catablog slug change 

== 0.1.1 ==
Improved Documentation

== 0.1 == 
First Public Release
