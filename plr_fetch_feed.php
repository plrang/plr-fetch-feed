<?php
/*
Plugin Name: RSS - Get This Feed
Plugin URI: https://plrang.com/projects/
Description: Wordpress plugin to get the feed with images ASAP & maybe configure it a bit - simply get the job done and nothing more. Display Thumbnails
Author: Plrang Art
Author URI: https://plrang.com/
Version: 1.0.6
License: GNU General Public License v2.0 or later
License URI: http://www.opensource.org/licenses/gpl-license.php
*/

/*
Copyright 2013  / 2018 Plrang Art (email : rssgtf@plrang.com)
It's just a working proof of concept, under continuous development
Using: https://codex.wordpress.org/Widgets_API

TODO: CSS variables
TODO: configurable CSS/theming in the WP widget (started)
*/

/*
* Register with hook 'wp_enqueue_scripts', which can be used for front end CSS and JavaScript
*/

add_action( 'wp_enqueue_scripts', 'plr_get_this_feed_CSS' );

/*
* Enqueue plugin style-file
*/

function plr_get_this_feed_CSS() {
    // Respects SSL, style.css is relative to the current file
    wp_register_style( 'plr-GTFstyle', plugins_url('gtf-style.css', __FILE__) );
    wp_enqueue_style( 'plr-GTFstyle' );
}

add_filter( 'wp_feed_cache_transient_lifetime', create_function( '$a', 'return 36000;' ) );


// Get RSS Feed(s)
include_once( ABSPATH . WPINC . '/feed.php' );



if ( !class_exists( 'PLR_fetch_feed' ))
{
    class PLR_fetch_feed
    {
        public $html = "No RSS data available";
        function PLR_fetch_feed ()
        {
            //  add_action( 'template_redirect', array( &$this, 'get_thumbs' ) );
        }



    function get_feed( $_items_count=20, $_sort='rand', $_feed_URL = '' , $_theme = 'off', $_style = false)
        {

        // not used here yet
        $_sort_by = ($_sort == 'rand' ) ? 'rand' : 'date';

        //$_feed_URL
        // Get a SimplePie feed object from the specified feed source.

        $feed = fetch_feed( $_feed_URL );

    if ( ! is_wp_error( $feed ) ) : // Checks that the object is created correctly

        $feed->enable_cache(true);
        $feed->strip_htmltags();
        $feed->strip_attributes();
        $feed->remove_div();

        // Figure out how many total items there are, but limit it to 5.
        $maxitems = $feed->get_item_quantity( $_items_count );

        // Build an array of all the items, starting with element 0 (first element).
        $feed_items = $feed->get_items( 0, $maxitems );

        // SORT type
        switch($_sort) {
            case 'rand':
                shuffle( $feed_items );
                break;

            case 'ASC':
                $feed_items = array_reverse( $feed_items );
                break;

            case 'DESC':
            // TODO
                break;
        }

    //var_dump( $feed_items );
    //echo "---------EXIT2"; exit;

        $_counter = 0;
        $_feed_html = "";
        $_feed_html .= "<div class=\"GTF_thumbs_area\" >";

        foreach ($feed_items as $item):
            /*
            if ($_counter % 10 == false)
                $_feed_html .= $_items_count.' <div style="width:230px;float:left;margin:8px">';
            */

                $_desc = $item->get_description();

                // TODO: at the moment it is divided to pieces
                // TODO: had to hard code: object-fit:cover !important 

                $_desc = preg_replace ( '/alt=".*-minipic".*/' , '>' , $_desc);
                $_desc = preg_replace ( '/<\/*p>/' , '' , $_desc);
                $_desc = preg_replace ( '/The post.*/' , '' , $_desc);
                $_desc = preg_replace ( '/alt=""[\/\s]*>(.*)/' , 'alt="$1" style="object-fit:cover !important" />' , $_desc);

            /*
            if ($enclosure = $item->get_enclosure())
                {
                echo $enclosure->get_thumbnail();
                }
            */


            /* THEME CSS switch class */
            /* TODO: unoptimized working draft */    

            switch($_theme) {
                case 'dark':
                    $_theme_class = 'GTF-theme-dark';
                break;
                
                case 'light':
                    $_theme_class = 'GTF-theme-light';
                break;                

                case 'off':
                    $_theme_class = '';
                break;                            

                default:
                    $_theme_class = '';

            }

            // TODO: move divs to modern figure and flex / grid
            
            switch ($_style){
                case 'image-list':
                    $_feed_html .= '<div class="GTF_thumb_frame-list" >';
                    $_feed_html .= '<div class="GTF_thumb_inner_frame-list" >'; // floater
                    $_feed_html .= '<div class="GTF_thumb_cell-list" >';
                    $_feed_html .= '<div style="overflow:hidden;float:left;margin:0px 10px 4px 0px;width:120px;background-color:black;min-height:100px"><a href="'
                        .$item->get_permalink()
                        . '" target="_imagerion" >'
                        . ''.$_desc.'</a>'
                        . '</div>'
                        . '<div style="margin:0;text-align:left;display:block;"><p style="margin:0"><strong><br />'
                        . $item->get_title()
                        . "</strong></p></div>"
                        . "</div>"
                        . '<div class="GTF_thumb_spacing">&nbsp;</div>';
                break;


                default:

                    $_feed_html .= '<div class="GTF_thumb_frame ' .$_theme_class. '" >';
                    $_feed_html .= '<div class="GTF_thumb_inner_frame" >'; 
                    $_feed_html .= '<div class="GTF_thumb_cell" >';
                    $_feed_html .= '<a href="' . $item->get_permalink()
                        . '" target="_imagerion" >'
                        . '<h5>'
                        . $item->get_title()
                        . "</h5>"
                        . "".$_desc."</a>"
                        . "</div>";
                break;
            }   // switch


            $_feed_html .= "</div>";
            $_feed_html .= "</div>";

            // spacing between frames
            if ($_counter < $_items_count )   
                $_feed_html .= '<div class="GTF_thumb_spacing">&nbsp;</div>';

            $_counter++;

            /*
            if ($_counter % 10 == false)
                $_feed_html .= '</div>';
            */

        endforeach;

        $_feed_html .= "</div>";

    endif;



        $this->html = $_feed_html;
        return $_feed_html;

        } // function get_feed

} // class


$SY_PLR_fetch_feed = new PLR_fetch_feed();

} // if


include_once 'plr_fetch_feed_widget.php';


// Add an active shortcode inside the post or a widget
add_action( 'init', 'plr_reg_shortcode');

function plr_reg_shortcode(){
   add_shortcode('getthisfeed', 'plr_getthisfeed_shortcode');
}


function plr_getthisfeed_shortcode ( $atts )
{
    global $SY_PLR_fetch_feed;

    extract( shortcode_atts( array(
      'cnt' => 1,
      'sort' => 'desc',
      'url' => '',
      'style' => 'default', /* predefined layout styles */
      'theme' => 'off'     /* quick theme force CSS dark or light - default in the shortcode is OFF */
   ), $atts));


    /*
    if ( !empty( $include ))
        $include = ( $_t = explode (",", $include)) ? $_t : array ($include);
        else
            $include = false;    // FROM DEBUG
            $exclude = ( $_t = explode (",", $exclude)) ? $_t : array ($exclude);

    if ($use_shape)
        $SY_PLR_latest_thumbs->_shape_in_shortcode = $use_shape;
    else
        $SY_PLR_latest_thumbs->_shape_in_shortcode = 'square';
    */

   $_html_thumbs =  $SY_PLR_fetch_feed->get_feed( $cnt, $sort, $url , $theme, $style );

   if ($_html_thumbs)
    {
       //if ( $verbose == "true")
       $_html = $_html_thumbs;
    }
   else {

       $_html = 'Feed processing error - no items?';
       /*
        $_html = '<h2>No images found: ' . $SY_PLR_latest_thumbs->_batch_query_verbose . '</h2>';
        $_html .= "<blockquote>Try different search<br />
        Browse " . plr_wrapin_sy_url ( 'image-categories', 'Categories') . "<br />
        Read " . plr_wrapin_sy_url ( '?s=&post_type=post', 'News') . "<br />"
        . plr_wrapin_sy_url ( '', 'Stock Home') . "<br /></blockquote>";
        */
   }

   return $_html;
}


?>