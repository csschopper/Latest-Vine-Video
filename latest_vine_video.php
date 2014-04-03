<?php
/*
 Plugin Name: Latest Vine Video
 Plugin URI: http://www.csschopper.com/
 Description: Widget to get the latest vine video from twitter using hashtag.
 Version: 1.0.0
 Author: Robin Gupta
 Author URI: http://www.csschopper.com/
 Author Email: robin.gupta@sparxtechnologies.com
 License: GPL
 */

/**
 * Latest Vine Widget   
 */
 error_reporting(1);
class Latest_Vine_Video extends WP_Widget {

	/**
	 * Widget Constuctor
	 */
	function Latest_Vine_Video() {

		$widget_ops = array(
			'classname'   => 'widget_vine_video',
			'description' => __( 'Latest Vine Video', 'lvv' )
		);

		$this->WP_Widget( 'widget_vine_video', __( 'Latest Vine Video', 'lvv' ), $widget_ops );

	}
	
	
	/**
	 * Gives long url for any short url 
	 *
	 * @param $url (string)
	 * 
	 * returns long url 
	 */
	
	function unshorten_url($url) {
		$ch = curl_init($url);
		curl_setopt_array($ch, array(
			CURLOPT_FOLLOWLOCATION => TRUE,   
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_SSL_VERIFYHOST => FALSE,  
			CURLOPT_SSL_VERIFYPEER => FALSE, 
		));
		 curl_exec($ch);
		 $url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
		 curl_close($ch);
   	   return $url;
    }


	/**
	 * Widget Output
	 *
	 * @param $args (array)
	 * @param $instance (array) Widget values.
	 *
	 */
	function widget( $args, $instance ) {		

		global $cache_enabled;

		extract( $args );	

		 /*CONTENT FOR THE WIDGET*/	 
		 
		  $title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Latest Vine Video', 'lvv' ) : $instance['title'] );
		  $hashtag =  $instance['hashtag'];
		  $heading = esc_attr( $instance['heading'] );
		  $content = esc_attr( $instance['content'] );
		  $srchterm = urlencode($hashtag);
		 
		  echo $before_widget;  
		  echo $before_title . $title  . $after_title;	  
		
		
		 $srch_twitts = "http://search.twitter.com/search.atom?q=".$srchterm."";
 
		// use curl to execute the Twitter URL
		$twits = curl_init();
		curl_setopt($twits, CURLOPT_URL, $srch_twitts);
		curl_setopt($twits, CURLOPT_RETURNTRANSFER, TRUE);
		$twi = curl_exec($twits);		 
		
		$search_res = new SimpleXMLElement($twi);	
		
		$i = 0; 

		foreach ($search_res->entry as $result) 
		{
			$pos = 0 ;
			$video_url = '';
			$pos = strpos( $result->content , 'https://t.co/' ) ;
			 if( $pos > 0 && is_integer( $pos ) ){
				$video_url = substr( $result->content , $pos , 23 );
				  
				if( !empty( $video_url ) ){		
				$i = 1; 	
				$video_url = $this->unshorten_url( $video_url );
			  ?>
              <div class="lvv-wrap">
				<iframe scrolling="no" height="150" src="<?php echo $video_url;?>/card" frameborder="0" width="70" style="border: 0 none; height: 160px;  overflow: hidden;  padding: 0;  width: 200px;" ></iframe>
               
			  <?php		
			  echo '<h1 class="lvv-heading">'.$heading.'</h1>';
			  echo '<p class="lvv-para">'.$content .'</p>';
			  echo '</div>';
					break;
				}
			 }	
		}

	if( $i == 0 ) 
		echo 'hashtag not found';
		 
		 /*CONTENT FOR THE WIDGET*/	 
		 
		// End widget output
		echo '</div></div>';
		echo $after_widget;	
	
	}

	/**
	 * Update Widget
	 *
	 * @param $new_instance (array) New widget values.
	 * @param $old_instance (array) Old widget values.
	 *
	 * @return (array) New values.
	 */
	function update( $new_instance, $old_instance ) {		

		$instance = $old_instance;
		 
		$instance['title']    = strip_tags( $new_instance['title'] );			
		$instance['hashtag']  = strip_tags( $new_instance['hashtag'] );
		$instance['heading']  = strip_tags( $new_instance['heading'] );
		$instance['content']  = strip_tags( $new_instance['content'] );	
		
		return $instance;	
	}

	/**
	 * Widget Options Form
	 *
	 * @param $instance (array) Widget values.
	 */
	function form( $instance ) {
		global $wpdb;

		// Defaults
		$instance = wp_parse_args( (array)$instance, array(
			'title' => __( 'Latest Vine Video', 'lvv' )				
		) );
		
		// Values
		 
		$title = esc_attr( $instance['title'] );
		$hashtag = esc_attr( $instance['hashtag'] );
		$heading = esc_attr( $instance['heading'] );
		$content = esc_attr( $instance['content'] );
	   
	   ?>
       
        <p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:', 'lvv' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</p> 
         <p>
			<label for="<?php echo $this->get_field_id('hashtag'); ?>"><?php _e( 'Hashtag:', 'lvv' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'hashtag' ); ?>" name="<?php echo $this->get_field_name( 'hashtag' ); ?>" type="text" value="<?php echo $hashtag; ?>" />
		 </p> 
          <p>
			<label for="<?php echo $this->get_field_id('heading'); ?>"><?php _e( 'Heading:', 'lvv' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'heading' ); ?>" name="<?php echo $this->get_field_name( 'heading' ); ?>" type="text" value="<?php echo $heading; ?>" />
		 </p> 
          <p>
			<label for="<?php echo $this->get_field_id('content'); ?>"><?php _e( 'Content:', 'lvv' ); ?></label>
			<textarea class="widefat" id="<?php echo $this->get_field_id( 'content' ); ?>" name="<?php echo $this->get_field_name( 'content' ); ?>"> <?php echo $content; ?></textarea>
		</p>    
       <?php  	
		
	}
}
add_action( 'widgets_init', create_function( '', 'return register_widget("Latest_Vine_Video");' ) );

?>