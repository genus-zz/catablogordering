<?php
/**
 * CataBlog Basic Shopping Cart
 *
 * @package CataBlog
 * @author diego2k [at] gmail.com
 */
 
class CataBlogCart
{

    public static function processEvent()
    {

		$cb_options = get_option('catablog-options',array());

		if ( !count($cb_options) && !isset($cb_options['public_post_slug']) ) return false;

		$catablog_item_page = strpos($_SERVER['REQUEST_URI'], $cb_options['public_post_slug']) !== false ||
							  strpos($_SERVER['REQUEST_URI'], $cb_options['public_tax_slug'])  !== false ||
							  strpos($_SERVER['REQUEST_URI'], 'catablog-items') !== false ||
							  strpos($_SERVER['REQUEST_URI'], 'catablog-terms') !== false;
			      	    
	    if ( ($catablog_item_page && isset( $_REQUEST['cmd'] )) || isset($_REQUEST['catablogcartprocess']) )
	    {
		    $post_vars = array_map('stripslashes_deep', $_REQUEST);
		    $post_vars = array_map('trim', $post_vars);
			
			$redirect = true;
			$redirect_id = home_url() . '?page_id='.get_option('catablogcart_pageid',0);
			
			$command = $post_vars['cmd'];
			
			do_action('catablogcart_before_event', $command);
		    
			switch( $post_vars['cmd'] )
		    {
		        case '_cart':
		            CataBlogCart::addToCart($post_vars);
                    break;
                    
                case '_empty':
                    CataBlogCart::emptyCart();
		            break;
		            
		        case '_remove':
		            CataBlogCart::removeFromCart($post_vars);
		            break;		            

		        case '_checkout':
		            CataBlogCart::checkOut($post_vars);
		            break;
					
				default:
					$redirect = false;
		    }
			
			do_action('catablogcart_after_event', $command, $redirect);
			
			$redirect    = apply_filters('catablogcart_redirect',$redirect);
			
			$redirect_id = apply_filters('catablogcart_redirect_id',$redirect_id);
			
			if($redirect)
			{
				wp_redirect($redirect_id);
				exit();			
			}
			
	    }
	 
    }
    

    public static function addToCart($post_vars = null)
    {
        
        if ( !is_array($post_vars) ) return false;

        session_start();
        
        if ( !isset($_SESSION['cart']) ) 
        {
            session_destroy();
            session_start();
            $_SESSION['cart']['seed'] = uniqid();
        } 
        
        $item_info = array();
     
        // Parse item information Only add the one that begins with item_
        foreach($post_vars as $post_item_key => $post_item_value)
        {
            if( strstr($post_item_key,'item_') !== false )
            {
               $item_info[$post_item_key] = $post_item_value;
            }
        }

		$item_info['quantity'] = $post_vars['quantity'];
		
		// Checks Whenever or not add Prices
		$hidePrices  = (get_option('catablogcart_hideprices', '') != '');
		
        if(!$hidePrices)
        {
            // Add Amount Qty and calculates Total
	        $item_info['amount']   = $post_vars['amount'];    
			$item_info['total']    = $post_vars['amount'] * $post_vars['quantity'];
        }
        
		$item_info = apply_filters('catablogcart_add_item',$item_info);
		
        $_SESSION['cart']['items'][] = $item_info;

        return true;
    }
    
    
    public static function removeFromCart($post_vars = null)
    {
        if ( !is_array($post_vars) ) return false;

        session_start();
        
        if ( !isset($_SESSION['cart']) ) return false;
        
        $itm = $post_vars['item_order'];
        
        unset($_SESSION['cart']['items'][$itm]);
        
        if ( count($_SESSION['cart']['items']) < 1 )         
            unset($_SESSION['cart']['items']);
        
        return true;
        
    }
        
    public static function showCart()
    {
		@session_start();

		$hidePrices  = (get_option('catablogcart_hideprices', '') != '');
		
        $sent = false;
        $message = '';
        if ( !isset($_SESSION['cart']) ) 
            $empty = true;        
        else
            if ( !is_array($_SESSION['cart']['items']) ) 
				$empty = true;
		
        if ($empty) 
        {    
			return '<div class="catablog-cart-message cart-empty">'. __('Cart is empty','catablogcart') .'</div>';
        }
		
		if (isset($_SESSION['cart']['submit']))
		{
			if ($_SESSION['cart']['submit'] == true)
			{
				$sent = true;
				if ( CataBlogCart::emptyCart() )
					return '<div class="catablog-cart-message order-sent">'. __('Your order was sent, you will receive an email notifying you that your order has been sent.','catablogcart') .'</div>';
			}
			else
			{
				$message = __('Unable to precess your order, please report this issue using contact form','catablogcart');
			}
		}

		$cart = $_SESSION['cart']['items'];

		ob_start();
		
		?>
		<div class="catablog-cart">

			<div class="catablog-cart-message"><?php echo $message; ?></div>
			
			<table class="catablog-cart-table">
				<thead>
					<tr>
						<td><?php _e('Code','catablogcart') ?></td>
						<td><?php _e('Description','catablogcart') ?></td>
						<td><?php _e('Qty','catablogcart') ?></td>
						<?php if(!$hidePrices) { ?>
						<td><?php _e('Price','catablogcart') ?></td>
						<td><?php _e('Total','catablogcart') ?></td>
						<?php } ?>
						<td>&nbsp;</td>
					</tr>
				</thead>
				
				<tbody>
					<?php foreach($cart as $order => $item) { ?>
					<tr>
						<td><?php echo $item['item_number']; ?></td>
						<td><?php echo $item['item_name'];   ?></td>
						<td><?php echo $item['quantity'];    ?></td>
						<?php if(!$hidePrices) : ?>
						<td align="right"><?php echo number_format($item['amount'],2); ?></td>
						<td align="right"><?php echo number_format($item['total'],2);  ?></td>
						<?php endif; ?>
						<td><a class="catablog-cart-action cart-remove" href="?catablog-items&cmd=_remove&item_order=<?php echo $order; ?>">x</a></td>
					</tr>
					<?php $total = $total + $item['total']; } ?>
				</tbody>
				
				<?php if(!$hidePrices) : ?>
				<tfoot>
					<tr>
						<td colspan="4"></td>
						<td class="catablog-cart-tabletotal" align="right"><?php echo number_format($total,2); ?></td>
						<td>&nbsp;</td>
					</tr>
				</tfoot>
				<?php endif; ?>
			</table>               
		   
			<a class="catablog-cart-action cart-empty-cart" href="?catablog-items&cmd=_empty"><?php _e('Empty Cart','catablogcart'); ?></a>
			
			<h2><?php _e('Order Now','catablogcart'); ?></h2>
			
			<form method="POST" action="?catablog-items" class="catablog-cart-form" >

				<?php do_action('catablogcart_before_form_fields'); ?>
			
				<span class="catablog-checkout-row">
					<label for="email"><?php _e('E-Mail','catablogcart'); ?></label>
					<input type="email" pattern="[^ @]*@[^ @]*" size="60" id="email" name="email" required="true" />
				</span>
			
				<span class="catablog-checkout-row">
					<label for="firstlast"><?php _e('Name','catablogcart'); ?></label>
					<input type="text" size="60" id="username" name="firstlast" required="true" />
				</span>
								
				<span class="catablog-checkout-row">
					<label for="phone"><?php _e('Phone','catablogcart'); ?></label>
					<input type="text" size="60" id="phone" name="phone" required="true" />
				</span>
				
				<span class="catablog-checkout-row">
					<label for="address"><?php _e('Address','catablogcart'); ?></label>
					<input type="text" size="60" id="address" name="address" />
				</span>
				
				<span class="catablog-checkout-row">
					<label for="note"><?php _e('Note','catablogcart'); ?></label>
					<textarea type="text" size="60" id="note" name="note"></textarea>
				</span>
				
				<?php do_action('catablogcart_after_form_fields'); ?>
				
				<span class="catablog-checkout-row">
					<center>
						<input type="hidden" name="cmd" value="_checkout" />
						<input type="hidden" name="formseed" value="<?php echo $_SESSION['cart']['seed']; ?>" />
						<input type="submit" name="submit" value="<?php _e('CONFIRM','catablogcart'); ?>" />
					</center>
				</span>
			
			</form>
			
		
		</div>
		<?php
		
		$data = ob_get_contents(); 
		ob_end_clean();
		
		return $data;
		
    }
    
    public static function checkOut($post_vars)
    {
    
        @session_start();


        if ( !isset($_SESSION['cart']) ) 
            return false;        
        else
            if ( !is_array($_SESSION['cart']['items']) ) 
                return false;
        
        if( $_SESSION['cart']['seed'] != $post_vars['formseed'] )
            return false;

		$standard_subject = __('ORDER','catablogcart') . ' ' . get_option('blogname');	
		$standard_from    = get_option('admin_email');
		$standard_order   = '%EMAIL% %NAME% %PHONE% %ADDRESS% %NOTE% %ORDER%';
		
        $to       = $post_vars['email'];
        $from     = get_option('catablogcart_emailfrom',    $standard_from);
		$fromName = get_bloginfo('name');
        $subject  = get_option('catablogcart_emailsubject', $standard_subject);      
        $isHtml   = (get_option('catablogcart_emailhtml', '') != '') ? 'html' : 'plain';
		$cart     = $_SESSION['cart']['items']; 

		$to       = apply_filters('catablogcart_email_order_to',      $to);
		$from     = apply_filters('catablogcart_email_order_from',    $from);
		$fromName = apply_filters('catablogcart_email_order_fromname',$fromName);
		$subject  = apply_filters('catablogcart_email_order_subject', $subject);
		$cart     = apply_filters('catablogcart_email_order_items',   $cart);
	
		$order = '';	

		if($isHtml)
		{
			// Introducing HTML email send

			$order .= '<table width="100%" border="1">';

			foreach($cart as $key => $item)
			{
				// Make Titles
				if($key == 0) 
				{
					$order .= '<tr>';
					foreach( array_keys($item) as $title )
						$order .= '<th>'.str_replace('item_','',$title).'</th>';
					$order .= '</tr>';
				}
				
				// Content
				$order .= '<tr>';
				foreach($item as $value)
				{
					// Fix Empty Value Cells
					if ($value == '') 
						$value = '&nbsp;';
					
					// Right Align Numbers
					if( is_numeric($value) )
						$order .= "<td align='right'>$value</td>";
					
					// Default Format
					else
						$order .= "<td>$value</td>";
				}
				$order .= '</tr>';				
			}
			$order .= '</table>';
		}		
		else
		{
			// Introducing ArrayToTextTable to render text table
			$order = new ArrayToTextTable($cart);
			$order->showHeaders(true);
			$order = $order->render(true);
		}		

		$order = apply_filters('catablogcart_email_order_table', $order);

        $headers = "From: $fromName <$from>"                   . PHP_EOL .
                   "To: $to"                                   . PHP_EOL .
                   "BCC: $from"                                . PHP_EOL .
                   "MIME-Version 1.0"                          . PHP_EOL .
                   "Content-type: text/$isHtml; charset=utf-8" . PHP_EOL .
		           "X-Mailer: PHP-" . phpversion()             . PHP_EOL ;

		$upload_dir = wp_upload_dir();		
		$template = $upload_dir['basedir'] . "/catablog/templates/order.htm";
		
		if( is_file($template) )
			$message = file_get_contents($template);
		else
			$message = get_option('catablogcart_emailtemplate', $standard_order);
               
        $message = str_replace('%EMAIL%',   $post_vars['email'],     $message );
        $message = str_replace('%NAME%',    $post_vars['firstlast'], $message );
        $message = str_replace('%PHONE%',   $post_vars['phone'],     $message );
        $message = str_replace('%ADDRESS%', $post_vars['address'],   $message );
        $message = str_replace('%NOTE%',    $post_vars['note'],      $message );
        $message = str_replace('%ORDER%',   $order,                  $message );

		$message = apply_filters('catablogcart_email_body', $message, $post_vars);
		
        $ok = wp_mail( $to, $subject, $message, $headers );

		$_SESSION['cart']['submit'] = $ok;

		return $ok;

    }

    public static function emptyCart()
    {
        @session_start();
        if ( isset($_SESSION['cart']) ) 
		{
            unset($_SESSION['cart']['items']);
			@session_destroy();
        }       
        return true;
        
    }


	// ADMIN AREA
	public static function admin_settings_menu() 
	{
		add_submenu_page(	'options-general.php',   
							__('Catablog Cart Options','catablogcart'),  
							__('Catablog Cart Options','catablogcart'), 
							'administrator', 
							'catablogcart' , 
							array('CataBlogCart','admin_settings_page') 
						);
						
		add_action( 'admin_init', array('CataBlogCart','admin_settings_register') );
	}

	public static function admin_settings_register() 
	{
		register_setting('catablogcart-group', 'catablogcart_pageid'       );
		register_setting('catablogcart-group', 'catablogcart_emailfrom'    );
		register_setting('catablogcart-group', 'catablogcart_emailsubject' );
		register_setting('catablogcart-group', 'catablogcart_emailtemplate');
		register_setting('catablogcart-group', 'catablogcart_emailhtml'    );
		register_setting('catablogcart-group', 'catablogcart_hideprices'   );
	}
    
    public static function admin_settings_page()
    {
    ?>

		<div class="wrap">
		<h2><?php _e('Catablog Cart Options','catablogcart'); ?></h2>

		<form method="post" action="options.php">
			<?php settings_fields( 'catablogcart-group' ); ?>
			<table class="form-table">
				<tr valign="top">
				<th scope="row"><?php _e('Cart Page ID','catablogcart'); ?></th>
				<td>
					<?php wp_dropdown_pages( array('name' => 'catablogcart_pageid', 'selected' => get_option('catablogcart_pageid',0) ) ); ?>
					<small><?php _e('Create a page with the <strong>[catablogcart]</strong> short code','catablogcart'); ?></small>
				</td>
				</tr>
				 
				<tr valign="top">
				<th scope="row"><?php _e('Order E-mail From','catablogcart'); ?></th>
				<td><input type="text" name="catablogcart_emailfrom" value="<?php echo get_option('catablogcart_emailfrom'); ?>" /></td>
				</tr>
				
				<tr valign="top">
				<th scope="row"><?php _e('Order E-mail Subject','catablogcart'); ?></th>
				<td><input type="text" size="50" name="catablogcart_emailsubject" value="<?php echo get_option('catablogcart_emailsubject'); ?>" /></td>
				</tr>

				<tr valign="top">
				<th scope="row"><?php _e('Order E-Mail Template','catablogcart'); ?></th>
				<td>
					<textarea cols="60" rows="10" name="catablogcart_emailtemplate"><?php echo get_option('catablogcart_emailtemplate'); ?></textarea><br/>
					<small><?php _e('Special variables %ORDER%, %EMAIL%, %NAME%, %PHONE%, %ADDRESS%, %NOTE%','catablogcart'); ?></small>				
				</td>
				</tr>
				
				<tr valign="top">
				<th scope="row"><?php _e('Send HTML E-mail','catablogcart'); ?></th>
				<td>
				    <input type="checkbox" name="catablogcart_emailhtml" value="yes" <?php echo get_option('catablogcart_emailhtml','') != '' ? 'checked="checked"' : ''; ?>" />
				    <small><?php _e('If checked the email will be sent as HTML, otherwise it will be plain text','catablogcart'); ?></small>
				</td>
				</tr>

				<tr valign="top">
				<th scope="row"><?php _e('Hide Prices','catablogcart'); ?></th>
				<td>
				    <input type="checkbox" name="catablogcart_hideprices" value="yes" <?php echo get_option('catablogcart_hideprices','') != '' ? 'checked="checked"' : ''; ?>" />
				    <small><?php _e('Enable this option to hide prices and totals','catablogcart'); ?></small>
				</td>
				</tr>
							
				<tr>
				  <td colspan="2" align="right" style="text-align: right;">
					<small>If you enjoy this plugin please donate!</small>
					<br />
					<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
					  <input type="hidden" name="cmd" value="_s-xclick">
					  <input type="hidden" name="hosted_button_id" value="C2PZ5SY3T8PYN">
					  <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
					  <img alt="" border="0" src="https://www.paypalobjects.com/es_XC/i/scr/pixel.gif" width="1" height="1">
					  </form>
				  </td>
				</tr>
			</table>
		
			<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes'); ?>" />
			</p>

		</form>

		</div>
		
	<?php
    }

}

