<?php

class RBD_Jetpack_Gallery extends Jetpack_Tiled_Gallery { 
   private static $talaveras = array( 'rectangular', 'square', 'circle', 'rectangle', 'columns' );
   
   public function set_atts_rbd( $atts ) {
    global $post;

    $this->atts = shortcode_atts( array(
        'ext_url'    => '',
        'order'      => 'ASC',
        'orderby'    => 'menu_order ID',
        'id'         => isset( $post->ID ) ? $post->ID : 0,
        'include'    => '',
        'exclude'    => '',
        'type'       => '',
        'grayscale'  => false,
        'link'       => '',
        'columns'	 => 3
    ), $atts, 'gallery' );

    $this->atts['id'] = (int) $this->atts['id'];
    $this->float = is_rtl() ? 'right' : 'left';

    // Default to rectangular is tiled galleries are checked
    if ( $this->tiles_enabled() && ( ! $this->atts['type'] || 'default' == $this->atts['type'] ) ) {
        /** This filter is already documented in functions.gallery.php */
        $this->atts['type'] = apply_filters( 'jetpack_default_gallery_type', 'rectangular' );
    }

    if ( !$this->atts['orderby'] ) {
        $this->atts['orderby'] = sanitize_sql_orderby( $this->atts['orderby'] );
        if ( !$this->atts['orderby'] )
            $this->atts['orderby'] = 'menu_order ID';
    }

    if ( 'rand' == strtolower( $this->atts['order'] ) ) {
        $this->atts['orderby'] = 'rand';
    }

    // We shouldn't have more than 20 columns.
    if ( ! is_numeric( $this->atts['columns'] ) || 20 < $this->atts['columns'] ) {
        $this->atts['columns'] = 3;
    }
}


    public function get_attachments_rbd() {
        extract( $this->atts );
        $atts_test = $this->atts;
        if ($atts_test['ext_url'] != null) {
            $urls = $atts_test['ext_url'];
            $urls = explode(',', $urls);
        }
        else {
            $urls = '';
        }
        if ( !empty( $include ) ) {
            $include = preg_replace( '/[^0-9,]+/', '', $include );
            $_attachments = get_posts( array('include' => $include, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );
    
            $attachments = array();
            foreach ( $_attachments as $key => $val ) {
                $attachments[$val->ID] = $_attachments[$key];
            }
        } elseif ( 0 == $id ) {
            // Should NEVER Happen but infinite_scroll_load_other_plugins_scripts means it does
            // Querying with post_parent == 0 can generate stupidly memcache sets on sites with 10000's of unattached attachments as get_children puts every post in the cache.
            // TODO Fix this properly
            $attachments = array();
        } elseif ( !empty( $exclude ) ) {
            $exclude = preg_replace( '/[^0-9,]+/', '', $exclude );
            $attachments = get_children( array('post_parent' => $id, 'exclude' => $exclude, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );
        } else {
            $attachments = get_children( array('post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby ) );
        }
        if (is_array($urls)) { // check if multiple images attached

        foreach ($urls as $url) { // run array push for multiple pics
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $array = array("ID" => $url,
            "post_author" => '',
            "post_date" => '',
            "post_date_gmt" => '',
            "post_content" => '',
            "post_title" => '',
            "post_excerpt" => '', 
            "post_status" => '',
            "comment_status" => '',
            "ping_status" => '',
            "post_password" => '',
            "post_name" => 'Listing',
            "to_ping" => '',
            "pinged" => '',
            "guid" => 'listing',
            "post_type" => 'attachment',
            "post_mime_type" => 'image/jpeg',
            "comment_count" => 0,
            "filter" => 'raw');
            $push = (object) $array; 
            array_push($attachments, $push); // setup post array for external image injection
        }
    }
    else {
        $urls = filter_var($urls, FILTER_SANITIZE_URL);
        $array = array("ID" => $urls,
        "post_author" => '',
        "post_date" => '',
        "post_date_gmt" => '',
        "post_content" => '',
        "post_title" => '',
        "post_excerpt" => '', 
        "post_status" => '',
        "comment_status" => '',
        "ping_status" => '',
        "post_password" => '',
        "post_name" => 'Listing',
        "to_ping" => '',
        "pinged" => '',
        "guid" => 'listing',
        "post_type" => 'attachment',
        "post_mime_type" => 'image/jpeg',
        "comment_count" => 0,
        "filter" => 'raw');
        $push = (object) $array; 
        array_push($attachments, $push); // setup post array for external image injection
    }
        
        return $attachments;
    }
    
    public function gallery_shortcode_rbd( $val, $atts ) {
        
            if ( ! empty( $val ) ) // something else is overriding post_gallery, like a custom VIP shortcode
                return $val;
                
            global $post;
            
            self::set_atts_rbd( $atts );
    
            $attachments = self::get_attachments_rbd(); // get modified function
            if ( empty( $attachments ) )
                return '';
    
            if ( is_feed() || defined( 'IS_HTML_EMAIL' ) ) {
                return '';
            }
           
            if (
                in_array(
                    $this->atts['type'],
                    /**
                     * Filters the permissible Tiled Gallery types.
                     *
                     * @module tiled-gallery
                     *
                     * @since 3.7.0
                     *
                     * @param array Array of allowed types. Default: 'rectangular', 'square', 'circle', 'rectangle', 'columns'.
                     */
                    $talaveras = apply_filters( 'jetpack_tiled_gallery_types', self::$talaveras )
                )
            ) {
                // Enqueue styles and scripts
                Jetpack_Tiled_Gallery::default_scripts_and_styles();
    
                // Generate gallery HTML
                $gallery_class = 'Jetpack_Tiled_Gallery_Layout_' . ucfirst( $this->atts['type'] );
                $gallery = new $gallery_class( $attachments, $this->atts['link'], $this->atts['grayscale'], (int) $this->atts['columns'] );
                $gallery_html = $gallery->HTML() . '<script> 
            
                jQuery(document).ready(function($) {
                   
                  $("img[data-attachment-id]").each(function() {
                    var regex = new RegExp("[^0-9]","g");
                      var url = $(this).data("attachment-id"); 
                      if(regex.test(url) != false){ 
                        $(this).attr("src",url);
                        $(this).parent().attr("href",url);
                        $(this).attr("data-orig-file",url);
                        $(this).attr("data-large-file",url);
                        $(this).attr("data-medium-file",url);
                        $(this).attr("data-attachment-id",12);
                        console.log(url);
                    }
                    
                    
                  });

              
                  });

                        </script>
                        <style> .jp-carousel-msg {display:none;} 
                        .jp-carousel-slide {
                            left: 0px !important;
                            width: 400px !important;
                            height: 400px !important;
                            top: 323px !important;
                            position: fixed !important;
                            overflow: hidden;
                        }
                   

                        .jp-carousel-slide img {
                            
                            width: auto !important;
                            height: auto !important;
                            object-fit: contain;
                        }
                        .jp-carousel-transitions .jp-carousel-close-hint {
                            position: fixed;
                            text-align: right;
                            right: 12px;
                            width: auto;
                          }

                          /* Center the caption. */
                        .jp-carousel-info h2 {
                             text-align: center !important;
                        }

                        /* Hide comment form header. */
                        .jp-carousel-left-column-wrapper {
                             display: none !important;
                        }

                        /* Center the metabox. */
                        .jp-carousel-image-meta {
                         float: none !important;
                         margin-left: auto;
                         margin-right: auto;
                        }
                        </style>
                        '; // inject javascript
               
                if ( $gallery_html && class_exists( 'Jetpack' ) && class_exists( 'Jetpack_Photon' ) ) {
                    // Tiled Galleries in Jetpack require that Photon be active.
                    // If it's not active, run it just on the gallery output.
                    if ( ! in_array( 'photon', Jetpack::get_active_modules() ) && ! Jetpack::is_development_mode() )
                        $gallery_html = Jetpack_Photon::filter_the_content( $gallery_html );
                }
    
                return trim( preg_replace( '/\s+/', ' ', $gallery_html ) ); // remove any new lines from the output so that the reader parses it better
            }
    
            return '';
        }
    
    }
    function jquery_ui_function(){
        //Enqueue date picker UI from WP core:
        wp_enqueue_script('jquery-ui-core');

        }
        add_action('wp_enqueue_scripts', 'jquery_ui_function');