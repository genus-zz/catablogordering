<?php
/*
Plugin Name: Catablog Ordering
Plugin URI: https://github.com/genus/catablogordering
Description: Add Catablog Plugin Support for Making Purchase Orders
Version: 0.1.3
Author: diego2k
Author URI: http://www.estudiogenus.com/

Copyright 2012  Diego Coppari (email: diego2k@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
*/


// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) 
{
    echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
    exit;
}
                
require('CataBlogOrdering.class.php');
require('ArrayToTextTable.class.php');

load_plugin_textdomain('catablogcart', false, dirname( plugin_basename( __FILE__ ) ) . '/lang');

add_action('admin_menu', array('CataBlogCart', 'admin_settings_menu') );

add_shortcode('catablogcart', array('CataBlogCart', 'showCart'));

wp_enqueue_style('catablog-cart-css', plugins_url( 'css/catablog-cart.css', __FILE__ ), false, '1','all');

add_action('init', array('CataBlogCart', 'processEvent'), 10);

