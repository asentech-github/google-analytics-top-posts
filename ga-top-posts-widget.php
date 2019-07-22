<?php 
// Register and load the widget
function wpb_load_top_posts_widget() {
    register_widget( 'GATopPosts_widget' );
}
add_action( 'widgets_init', 'wpb_load_top_posts_widget' );
// Creating the widget 
class GATopPosts_widget extends WP_Widget {
	// define constant variables
	const PLUGIN_FILE = '/ga-top-posts.php';
	const PLUGIN_BASE_FOLDER = 'ga-custom-top-posts';
	const FILTER_OPTION_NAME ='widget_GATopPosts_widget';
	const OPTION_NAME ='top-post-data';
	// class constructor
	public function __construct() {
		//add_shortcode( 'displayTopPostsWidget', array($this,'shortcode_for_display_toppost_widget'));
		$widget_ops = array( 
		'classname' => 'ga-top-posts-widget',
		'description' => 'A plugin for display top posts from google analytics',
		);
		parent::__construct( 'GATopPosts_widget', 'GA Top Posts Widget', $widget_ops );
	}
	// output the option form field in admin Widgets screen
	public function form( $instance ) {		
		 $title = !empty($instance['title']) ? $instance['title']: esc_html__( 'Title', 'text_domain' );	
		 $class = !empty($instance['class']) ? $instance['class']: esc_html__( 'Class Name', 'text_domain' );
		 $order_by = !empty($instance['order_by']) ? $instance['order_by']: null;	
		 $how_many_posts = !empty($instance['how_many_posts']) ? $instance['how_many_posts']: null;
		$display_on_page = !empty($instance['display_on_page_view']) ? $instance['display_on_page_view']: null;	
		 $duration_time = !empty($instance['duration_time']) ? $instance['duration_time']: null;	
		 $duration_scale = !empty($instance['duration_scale']) ? $instance['duration_scale']: null;	
		?>
		<div id="custtoppostsfrm">
		
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
				<?php esc_attr_e( 'Title:', 'text_domain' ); ?>
			</label> 
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">			
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'class' ) ); ?>">
				<?php esc_attr_e( 'Class:', 'text_domain' ); ?>
			</label> 
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'class' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'class' ) ); ?>" type="text" value="<?php echo esc_attr( $class ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'how_many_posts' ) ); ?>">
				<?php esc_attr_e( 'Display Posts:', 'text_domain' ); ?>
			</label> 
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'how_many_posts' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'how_many_posts' ) ); ?>">
				<option value="0">Please Select Posts</option>
				<?php for($i=1;$i<=15;$i++){?>
					<option value="<?php echo $i;?>" <?php if($how_many_posts == $i){echo "selected";}?>><?php echo $i;?></option>
				<?php }?>
			</select>
		</p>		
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'duration_time' ) ); ?>">
				<?php esc_attr_e( 'For Duration quantity:', 'text_domain' ); ?>
			</label> 
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'duration_time' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'duration_time' ) ); ?>" type="number" placeholder="Dutation" value="<?php echo esc_attr( $duration_time ); ?>" style="width:100%;">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'duration_time' ) ); ?>">
				<?php esc_attr_e( 'For Duration scale:', 'text_domain' ); ?>
			</label>
			<select class="widefat" style="width:100%" id="<?php echo esc_attr( $this->get_field_id( 'duration_scale' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'duration_scale' ) ); ?>" >
				<option value="">Please select duration</option>			
				<option value="day" <?php if($duration_scale=='day'){echo "selected";}?>>Day</option>
				<option value="week" <?php if($duration_scale=='week'){echo "selected";}?>>Week</option>
				<option value="month" <?php if($duration_scale=='month'){echo "selected";}?>>Month</option>
			</select>
		</p>
		</div>
		<?php	
	}
	// save options
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['class'] = ( ! empty( $new_instance['class'] ) ) ? strip_tags( $new_instance['class'] ) : '';
		$instance['order_by'] = ( ! empty( $new_instance['order_by'] ) ) ? strip_tags( $new_instance['order_by'] ) : '';
		$instance['how_many_posts'] = ( ! empty( $new_instance['how_many_posts'] ) ) ? strip_tags( $new_instance['how_many_posts'] ) : '';		
		$instance['duration_time'] = ( ! empty( $new_instance['duration_time'] ) ) ? strip_tags( $new_instance['duration_time'] ) : '';
		$instance['duration_scale'] = ( ! empty( $new_instance['duration_scale'] ) ) ? strip_tags( $new_instance['duration_scale'] ) : '';
		$selected_posts = ( ! empty ( $new_instance['display_on_page'] ) ) ? (array) $new_instance['display_on_page'] : array();
		$instance['display_on_page_view'] = array_map( 'sanitize_text_field', $selected_posts );
		return $instance;
	}
	function shortcode_for_display_toppost_widget($args, $gtoptions) {
		$options = get_option( self::OPTION_NAME, array() );
		$profile_id = isset( $options['profile_id'] ) ? $options['profile_id'] : null;
		$site_url    = isset( $options['site_url'] ) ? $options['site_url'] : null;
		$keyfile    = isset( $options['file'] ) ? $options['file'] : null;
		$service_email    = isset( $options['service_email'] ) ? $options['service_email'] : null;
		$exclude_url = isset( $options['exclude_url'] ) ? $options['exclude_url'] : array();
		$title = isset( $gtoptions['title'] ) ? $gtoptions['title'] : null;
		$class = isset( $gtoptions['class'] ) ? $gtoptions['class'] : null;
		$order_by = isset( $gtoptions['order_by'] ) ? $gtoptions['order_by'] : null;
		$how_many_posts = isset( $gtoptions['how_many_posts'] ) ? $gtoptions['how_many_posts'] : null;
		$duration_time = isset( $gtoptions['duration_time'] ) ? $gtoptions['duration_time'] : null;
		$duration_scale = isset( $gtoptions['duration_scale'] ) ? $gtoptions['duration_scale'] : null;		
		$display_on_page = isset( $gtoptions['display_on_page_view'] ) ? (array) $gtoptions['display_on_page_view'] : array();
		$dimensions = array('pagePath'); //dimensions
		$metrics = array('pageviews','visits','uniquePageviews'); //metrics
		$sort_metric = array('-uniquePageviews'); //sort
		$filter = null; //'ga:pagePath!=/'; //filter
		$ga_max_results = $how_many_posts;
    	if($exclude_url) {
			$exclude_url = explode(",", $exclude_url);
			$ga_max_results += count($exclude_url); //get more result from google analytics then remove exluded url added from admin bakend
		}
		$start_index =1;		
		$durattime = "- ".$duration_time." ".$duration_scale;
		$start_date = date("Y-m-d", strtotime($durattime));		
		$end_date = date("Y-m-d");	
		$keyfile = isset( $options['file'] ) ? $options['file'] : null;
		$uploaddir = wp_upload_dir();
		$keyfile = $uploaddir['basedir'].'/ga_top_posts/'.$keyfile;
		$option_name = 'ga_top_post_data_res';
		$get_ga = get_option( $option_name );
		$store_array = array();
		if ( $get_ga ) {
			$get_ga = get_option( $option_name );
			$ga_return = unserialize($get_ga);
		}
		elseif($profile_id !="" && $site_url !="" && $keyfile !="" && $service_email !="")
		{
			// call GA API from here
			require_once("gapi.class.php");	
			$top_posts = new gapi($service_email, $keyfile);
			$top_posts->requestReportData(
				$profile_id, //report_id
				$dimensions, //array('pagePath'), //dimensions
				$metrics, //array('pageviews','visits','uniquePageviews'), //metrics
				$sort_metric, //array('-uniquePageviews'),  //sort
				$filter, //null,  //filter
				$start_date, //$current_year.'-'.$current_month.'-01', //start_date
				$end_date, //$current_year.'-'.$current_month.'-'.$current_day,  //end_date
				$start_index, //2, //start_index
				$ga_max_results //5 //max_results
			);
			$results = $top_posts->getResults();
			if(!empty($results))
			{
				foreach($results as $result) {
					$store_array[] = array('pageviews'=>$result->getPageviews(),'visits'=>$result->getVisits(),'uniquePageviews'=>$result->getUniquepageviews(),'pagePath'=>$result->getPagepath());
				}
			}
			$value = serialize($store_array);
			// Store in wp_option table.
			update_option( $option_name, $value );
			$get_ga = get_option( $option_name );
			$ga_return = unserialize($get_ga);
		}
		if ( empty($ga_return) ) {
	        echo '<p>' . 'There are no posts to display.' . '</p>';
	        return;
	    }
		$results = $ga_return;
	    $posts = array();
		$result_count = 0;
		foreach($results as $result) {
			if($result_count == $how_many_posts) break;
			if($exclude_url && in_array($result['pagePath'], $exclude_url)) continue;
            $slug = trim($result['pagePath'], '/');
            $slug = explode("/", $slug);
            $slug = end($slug);
			// fetching posts from GA provided pagePath
            $args = array(
                'name'           => $slug,
                'post_type'      => 'any',
                'post_status'    => 'publish',
                'posts_per_page' => 1
            );
            $top_post = get_posts( $args );
            if( !empty($top_post) ) {
                $posts[] = $top_post[0];
				$result_count++;
            }
        }
		// generate html from here
		if(!empty($posts)) {
			echo '<div class="widget widget-top custom-top-post-title '.$class.'"><h4 class="custom-top-post-title">'.$title.'</h4><div class="stripe-line"></div></div>';
	        echo '<div class="widget widget_top-posts"><div class="widget-container"><ol class="popular-post">';
	        foreach ( $posts as $post ) :
        ?>
	            <li>
	                <span class="count"></span>
	                <a href="<?php echo esc_url( get_the_permalink($post->ID) ); ?>" class="bump-view" data-bump-view="tp">
	                    <?php echo esc_html( wp_kses( $post->post_title, array() ) ); ?>
	                </a>
	            </li>
        <?php
	        endforeach;
	        echo '</ol></div></div>';
	    }
	}
	// output the widget content on the front-end
	public function widget( $args, $instance ) {		
		$this->shortcode_for_display_toppost_widget($args, $instance);		
	}
} // Class wpb_widget ends here
?>