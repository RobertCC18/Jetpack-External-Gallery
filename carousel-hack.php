<?php
class CarouselWithoutJetpack {
    
    /**
    * Constructor
    */
    function __construct() {

        // Plugin Details
        $this->plugin               = new stdClass;
        $this->plugin->name 		= 'jetpack-external-url-gallery'; // Plugin Folder
        $this->plugin->folder       = plugin_dir_path( __FILE__ );
        $this->plugin->url          = plugin_dir_url( __FILE__ );
        
        // Include class.jetpack-options.php
        // Ignore if Jetpack or another plugin has already done this
            require_once($this->plugin->folder.'/ext_url_carousel/class.jetpack-options.php');
        
        // Include No_Jetpack_Carousel
        // Ignore if Jetpack or another plugin has already done this

            require_once($this->plugin->folder.'/ext_url_carousel/jetpack-carousel.php');
    
        
        add_action('wp_enqueue_scripts', array(&$this, 'frontendScriptsAndCSS'));
        add_action('plugins_loaded', array(&$this, 'loadLanguageFiles'));
    }
    
    /**
    * Enqueue jQuery Spin
    */
    function frontendScriptsAndCSS() {
        wp_register_script( 'spin', plugins_url( 'ext_url_carousel/spin.js', __FILE__ ), false );
        wp_register_script( 'jquery.spin', plugins_url( 'ext_url_carousel/jquery.spin.js', __FILE__ ) , array( 'jquery', 'spin' ) );
    }

    /**
    * Load translations
    */
    function loadLanguageFiles() {
        load_plugin_textdomain('ext_url_carousel', false, basename( dirname( __FILE__ ) ) . '/languages' );		
    }
}

new CarouselWithoutJetpack;

