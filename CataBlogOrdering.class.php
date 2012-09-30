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
			
		$catablog_item_page = strpos($_SERVER['REQUEST_URI'], $cb_options['public_post_slug']) !== false;
			      	    

	    if ( ($catablog_item_page && isset( $_REQUEST['cmd'] )) || isset($_REQUEST['catablogcartprocess']) )
	    {
		    $post_vars = array_map('stripslashes_deep', $_REQUEST);
		    $post_vars = array_map('trim', $post_vars);
			
			$redirect_id = '?page_id='.get_option('catablogcart_pageid',0);
		
		    switch( $post_vars['cmd'] )
		    {
		        case '_cart':
		            CataBlogCart::addToCart($post_vars);
		            header('Location: index.php'.$redirect_id,true);
		            exit();
                    break;
                    
                case '_empty':
                    CataBlogCart::emptyCart();
		            header('Location: index.php'.$redirect_id,true);
		            exit();
		            break;
		            
		        case '_remove':
		            CataBlogCart::removeFromCart($post_vars);
		            header('Location: index.php'.$redirect_id,true);
		            exit();
		            break;		            

		        case '_checkout':
		            CataBlogCart::checkOut($post_vars);
		            header('Location: index.php'.$redirect_id,true);
		            exit();
		            break;

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

        // Add Amount and Qty
        $item_info['amount']   = $post_vars['amount'];
        $item_info['quantity'] = $post_vars['quantity'];
        
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

        $sent = false;
        $message = '';
        if ( !isset($_SESSION['cart']) ) 
            $empty = true;        
        else
            if ( !is_array($_SESSION['cart']['items']) ) 
                $empty = true;
        
        if ($empty) 
        {        
            echo __('Cart is empty','catablogcart');
            return true;
        }
        else
        {
            if (isset($_SESSION['cart']['submit']))
            {
            	if ($_SESSION['cart']['submit'] == true)
            	{
            		$sent = true;
            		$message = __('Your order was sent, you will receive an email notifying you that your order has been sent.','catablogcart');
            	}
            	else
            	{
            		$message = __('Unable to precess your order, please report this issue using contact form','catablogcart');
            	}
            }

			$cart = $_SESSION['cart']['items'];            
			?>
		
            <div class="catablog-cart">

                <div class="catablog-cart-message"><?php echo $message; ?></div>
                
                <?php if(!$sent) { ?>
                
                <table class="catablog-cart-table">
                    <thead>
                        <tr>
                            <td><?php _e('Code','catablogcart') ?></td>
                            <td><?php _e('Description','catablogcart') ?></td>
                            <td><?php _e('Qty','catablogcart') ?></td>
                            <td><?php _e('Price','catablogcart') ?></td>
                            <td>&nbsp;</td>
                        </tr>
                    </thead>
                    
                    <tbody>
                        <?php foreach($cart as $order => $item) { ?>
                        <tr>
                            <td><?php echo $item['item_number']; ?></td>
                            <td><?php echo $item['item_name'];   ?></td>
                            <td><?php echo $item['quantity'];    ?></td>
                            <td align="right"><?php echo $item['amount']; ?></td>
                            <td><a class="catablog-cart-action" href="index.php?catablog-items&cmd=_remove&item_order=<?php echo $order; ?>">x</a></td>
                        </tr>
                        <?php $total = $total + $item['amount']; } ?>
                    </tbody>
                    
                    <tfoot>
                        <tr>
                            <td colspan="3"></td>
                            <td class="catablog-cart-tabletotal" align="right"><?php printf('%0.2f',$total); ?></td>
                            <td>&nbsp;</td>
                        </tr>
                    </tfoot>                    
                </table>               
               
                <a class="catablog-cart-action" href="index.php?catablog-items&cmd=_empty"><?php _e('Empty Cart','catablogcart'); ?></a>
                
                <h2><?php _e('Order Now','catablogcart'); ?></h2>
                
                <form method="POST" action="index.php?catablog-items&cmd=_checkout" class="catablog-cart-form" >

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
                    
                    <span class="catablog-checkout-row">
                        <center>
                            <input type="hidden" name="formseed" value="<?php echo $_SESSION['cart']['seed']; ?>" />
                            <input type="submit" name="submit" value="<?php _e('CONFIRM','catablogcart'); ?>" />
                        <center>
                    </span>
                
                </form>
                
                <?php } ?>
                
            </div> 
                       
        <?php
        }
        
        if ($sent == true) CataBlogCart::emptyCart();
        
        return true;
    }
    
    public static function checkOut($post_vars)
    {
    
        session_start();  
              
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
		
        $to      = $post_vars['email'];
        $from    = get_option('catablogcart_emailfrom',    $standard_from);
        $subject = get_option('catablogcart_emailsubject', $standard_subject);
        
		$order   = "";
        foreach($_SESSION['cart']['items'] as $order => $item) 
        {
            $order .= implode($item,"\t\t") . "\n";
        }       
        $dir = WP_CONTENT_DIR . "/plugins/catablog/templates/cart";
        
        $headers = "From: $from"                             . PHP_EOL .
                   "To: $to"                                 . PHP_EOL .
                   "BCC: $from"                              . PHP_EOL .
                   "MIME-Version 1.0"                        . PHP_EOL .
                   "Content-type: text/plain; charset=utf-8" . PHP_EOL .
                   "X-Mailer: PHP-" . phpversion()           . PHP_EOL ;
        
        $message = get_option('catablogcart_emailtemplate', $standard_order);
               
        $message = str_replace('%EMAIL%',   $post_vars['email'],     $message );
        $message = str_replace('%NAME%',    $post_vars['firstlast'], $message );
        $message = str_replace('%PHONE%',   $post_vars['phone'],     $message );
        $message = str_replace('%ADDRESS%', $post_vars['address'],   $message );
        $message = str_replace('%NOTE%',    $post_vars['note'],      $message );
        $message = str_replace('%ORDER%',   $order,                  $message );
        
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

