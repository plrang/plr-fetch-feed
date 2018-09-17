<?php
// RSS - Get This Feed WIDGET part
// Plrang Art 2013 / 2018
// [Fetch feed] widget for plugin

class PLR_fetch_feed_widget extends WP_Widget {

    /**
     * Register widget with WordPress.
     */
    public function __construct() {
        parent::__construct(
            'PLR_fetch_feed_widget', // Base ID
                'RSS Get This Feed ', // Name
                array(
                        'description' => __( 'Get FEED and build HTML for display', 'text_domain' ),

                ) // Args
        );
    }


    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget( $args, $instance ) {

        global $SY_PLR_fetch_feed;

        extract( $args );
        $title = apply_filters( 'widget_title', $instance['title'] );
        $items_count = $instance['items_count'];

        echo $before_widget;
        if ( ! empty( $title ) )
            echo $before_title . $title . $after_title;
        //echo __( 'Hello, World!', 'text_domain' );

        $items_count = ( ! empty( $items_count ) ) ? $items_count : 3;
        $sort = $instance['sort'];  
        $feed_URL = esc_attr($instance['feed_URL']);
        
        $theme =  $instance['theme'];

        $_tmp_ = $SY_PLR_fetch_feed->get_feed( $items_count, $sort, $feed_URL, $theme ); 

        echo $_tmp_;
        echo $after_widget;
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = strip_tags( $new_instance['title'] );
        
        
        $num = strip_tags( $new_instance['items_count'] );
        $num = (int)$num;
        $num = intval($num);
        $num = ($num>1)?$num:1;
        $num = ($num<=16)?$num:16;
        
        $instance['items_count'] = $num;
        $instance['sort'] = strip_tags($new_instance['sort']);
        $instance['feed_URL'] = strip_tags($new_instance['feed_URL']);
        $instance['theme'] = $new_instance['theme'];
        

        return $instance;
        
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    public function form( $instance ) {

        if ( isset( $instance[ 'title' ] ) ) {
            $title = $instance[ 'title' ];
        }
        else {
            $title = __( 'Feed...', 'text_domain' );
        }

        $select = esc_attr($instance['sort']);
        $feed_URL = esc_attr($instance['feed_URL']);
        $theme = esc_attr($instance['theme']);

        ?>

        <p>
        <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
        <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>
        
        <p>
        <label for="<?php echo $this->get_field_id( 'feed_URL' ); ?>"><?php _e( 'Feed URL:' ); ?></label> 
        <input class="widefat" id="<?php echo $this->get_field_id( 'feed_URL' ); ?>" name="<?php echo $this->get_field_name( 'feed_URL' ); ?>" type="text" value="<?php echo esc_attr( $feed_URL ); ?>" />
        </p>

        <label for="<?php echo  $this->get_field_id("items_count"); ?>">
        <p>Images count <input  type="text" size="3" value="<?php echo  $instance['items_count']; ?>" name="<?php echo  $this->get_field_name("items_count"); ?>" id="<?php echo  $this->get_field_id("items_count") ?>"></p>
        </label>    
        
         <label for="<?php echo  $this->get_field_id("sort"); ?>">
         <p>Sort <select name="<?php echo  $this->get_field_name("sort"); ?>" id="<?php echo  $this->get_field_id("sort") ?>">
        
            <?php
                 $options = array('DESC'=>"Latest", 'rand'=>"Random", 'ASC'=>"Oldest");
             foreach ($options as $option=>$_val) {
                 echo '<option value="' . $option . '" id="' . $option . '"', $select == $option ? ' selected="selected"' : '', '>', $_val, '</option>';
             }
             
             ?>
            </select>

         </p>
         </label>
        
        
         <label for="<?php echo  $this->get_field_id("theme"); ?>">
         <p>Theme <select name="<?php echo  $this->get_field_name("theme"); ?>" id="<?php echo  $this->get_field_id("theme") ?>">
        
            <?php
                 $options = array('dark'=>"Dark", 'light'=>"Light", 'off'=>"OFF");
             foreach ($options as $option=>$_val) {
                 echo '<option value="' . $option . '" id="' . $option . '"', $theme == $option ? ' selected="selected"' : '', '>', $_val, '</option>';
             }
             
             ?>
            </select>

         </p>
         </label>




        <?php 
    }

} // class PLR_fetch_feed_widget

// register PLR_fetch_feed_widget widget
add_action( 'widgets_init', create_function( '', 'register_widget( "PLR_fetch_feed_widget" );' ) );

?>