<?php
/*
Plugin Name: Jetpack External Images For Gallery Extension
Plugin URI: http://robbotdev.com
Description: Allows external images to be added using the [gallery] shortcode
Version: 2.0.0
Author: Robert Crawford
Author URI: http://robbotdev.com
Text Domain: robbot
Domain Path: /languages
*/
 include_once(ABSPATH.'wp-admin/includes/plugin.php');
 require_once(ABSPATH.'wp-content/plugins/jetpack/modules/tiled-gallery.php');
 require_once( plugin_dir_path( __FILE__ ) . 'carousel-hack.php'); // get carousel init class
 add_action( 'plugins_loaded', 'wppizza_extend_custom_vars');

 function wppizza_extend_custom_vars(){
    
     if (!class_exists( 'Jetpack_Tiled_Gallery' )) { return;}
     require_once( 'guts-core.php' );
     $gallery_jge = new RBD_Jetpack_Gallery();
    // remove_filter( 'post_gallery', array( $gallery, 'gallery_shortcode' ), 1001, 2 ); // not needed (will depricate in next version)
     add_filter( 'post_gallery', array( $gallery_jge, 'gallery_shortcode_rbd' ), 10, 2 ); // hook into jetpack with custom class
     add_filter( 'jp_carousel_force_enable', function( $input ) { return true; } ); // force jetpack to use custom carousel
     add_filter( 'jce_remove_attachment_comments', function( $input ) { return true; } ); // remove comments for external images (cause they got no comments)
 }



 