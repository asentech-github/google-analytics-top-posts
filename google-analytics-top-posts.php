<?php
/*
Plugin Name: Google Analytics Top Posts
Plugin URI: https://profiles.wordpress.org/asentechllc/
Description: The [displayTopPosts] is an open-source initiative aiming to fetch most read articles from google analytics. Most read articles will display real time top 5 articles fetching from GA and display in page, post, and sidebar using shortcode.
Author: @asentechllc
Author URI: https://profiles.wordpress.org/asentechllc/
Version: 1.0
*/
require_once('google-analytics-top-posts-widget.php');

function ga_topposts_init() {

	$gaTopPosts = new GATopPosts();
	$gaTopPosts->run();
}

ga_topposts_init(); 
defined( 'WPINC' ) || die( 'Access restricted' );

class GATopPosts{
	
	//define const variables
	const PLUGIN_FILE = '/google-analytics-top-posts.php';
	const PLUGIN_BASE_FOLDER = 'google-analytics-top-posts';
	const OPTION_NAME = 'top-post-data';
	const NONCE_ACTION = 'custom_top_posts_save_authorized';
	const SYNC_NONCE_ACTION = 'custom_top_posts_sync_authorized';
	
	public function run(){
		add_action( 'admin_menu', array($this, 'add_menu_page') );
		add_action('admin_footer',array($this,'enquee_js'));
		add_action('admin_enqueue_scripts',array($this,'enquee_css'));		
		add_action('wp_ajax_insert_conf_data',array($this,'insert_conf_data'));
		add_action('wp_action_nopriv_insert_conf_data',array($this,'insert_conf_data'));
		add_action('wp_ajax_sync_from_ga',array($this,'sync_from_ga'));
		add_action('wp_action_nopriv_sync_from_ga',array($this,'sync_from_ga'));		
		add_shortcode( 'displayTopPosts', array($this,'shortcode_for_display_toppost'));		
	}
	public function add_menu_page() {
		add_submenu_page( 'options-general.php', 'GA Top Posts', 'GA Top Posts', 'manage_options', 'ga-top-posts', array( $this, 'displayform' ) );
	}
	public function enquee_js(){
		$nonce    = wp_create_nonce( self::NONCE_ACTION );
		$synce_nonce = wp_create_nonce( self::SYNC_NONCE_ACTION );
		wp_enqueue_script('toppostsjs',plugin_dir_url( realpath( self::PLUGIN_FILE ) ).self::PLUGIN_BASE_FOLDER.'/js/google-analytics-top-post.js',array(),'',true);
	}
	public function enquee_css(){			
		wp_enqueue_style('toppostscss',plugin_dir_url( realpath( self::PLUGIN_FILE ) ) .self::PLUGIN_BASE_FOLDER. '/css/google-analytics-top-post.css',array(),null);
	}
	
	public function displayform(){	
		$options   = get_option( self::OPTION_NAME, array() );
		$profile_id = isset( $options['profile_id'] ) ? $options['profile_id'] : null;
		$site_url    = isset( $options['site_url'] ) ? $options['site_url'] : null;
		$keyfile    = isset( $options['file'] ) ? $options['file'] : null;
		$service_email    = isset( $options['service_email'] ) ? $options['service_email'] : null;		
		$exclude_url    = isset( $options['exclude_url'] ) ? $options['exclude_url'] : null;		

		$nonce = wp_create_nonce( self::NONCE_ACTION );
		$synce_nonce = wp_create_nonce( self::SYNC_NONCE_ACTION );
		echo $formhtml = '<div id="custom-top-post-wrap">
			<div>
			<form id="custom-top-post-form" enctype="multipart/form-data">
			<table width="100%" cellspacing="0" cellpading="0">
			<tr><td colspan="2"><h2>Google Analytics Configuration for top posts</h2><b>Shortcode:</b> [displayTopPosts]</td></tr>
			<tr><td><label>Profile Id </label></td><td><input type="text" name="profile_id" value="'.$profile_id.'"> <input type="hidden" value="'.$nonce.'" name="nonce"><input type="hidden" value="insert_conf_data" name="action"><input type="hidden" value="'.plugin_dir_url( realpath( self::PLUGIN_FILE ) ) .self::PLUGIN_BASE_FOLDER.'" name="path"></tr>
			<tr><td><label>Site Url </label></td><td><input type="text" name="site_url" value="'.$site_url.'"></td></tr>
			<tr><td><label>Key File </label></td><td><input type="file" id="file" name="file" ><input type="hidden" id="pfile" name="pfile" value="'.$keyfile.'"><br> <label>Filename : </label> '.$keyfile.' 
        </td></tr>
			<tr><td><label>Service Email </label></td><td><input type="text" name="service_email" value="'.$service_email.'"></td></tr>
			<tr><td><label>Exclude URL(Add comma(,) seperated URL)</label></td><td><input type="text" name="exclude_url" value="'.$exclude_url.'"></td></tr>
			
			<tr><td colspan="2"><input type="submit" name="submit" value="Submit" class="btn btn-sm btn-outline-secondary"> &nbsp; &nbsp; <span id="connection-error-message" style="display: none"></span></td></tr>
			</table>
			
			</form>
			</div>
			<div style="clear:both;">&nbsp;</div>
			<div class="sync-form-div">
				<form id="custom-top-post-sync-form">
					<table width="100%" cellspacing="0" cellpading="0">
						<tr><td colspan="2"><h2>Sync from Google Analytics to database</h2><b>Click on Sync from GA button to update database entries:</b></td></tr>
						<tr><td colspan="2"><input type="hidden" value="'.$synce_nonce.'" name="nonce"><input type="hidden" value="sync_from_ga" name="action"><input type="submit" name="submit" value="Submit" class="btn btn-sm btn-outline-secondary"> &nbsp; &nbsp; <span id="connection-error-message-sync" style="display: none"></span></td></tr>
					</table>
				</form>
			<div>
		</div>';
	}
	
	public function insert_conf_data(){		
		global $wb;
		 $nonce = $_REQUEST['nonce'];
		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			// This nonce is not valid.
			die( 'Security check' ); 
		} else {
			$options = array( 
			'profile_id'=> $_POST['profile_id'],
			'site_url'=> $_POST['site_url'],
			'service_email'=> $_POST['service_email'],
			'file'=> $_POST['pfile'],
			'exclude_url'=> $_POST['exclude_url']
			);
			update_option( self::OPTION_NAME, $options );
			$message = 'Configuration updated.';
		echo json_encode( array( 'type' => 'success', 'message' => $message ) );
			die;
		}		
	}
	
	// function for fetch data from GA using API

	public function get_ga_result($dimensions=null, $metrics, $sort_metric=null, $filter=null, $start_date=null, $end_date=null, $start_index=1, $max_results=10000) {

		// define base folder path
		$base_folder = plugin_dir_url( realpath( self::PLUGIN_FILE ) ).self::PLUGIN_BASE_FOLDER;
		
		// fetch configuration option from wp_options table
		$options   = get_option( self::OPTION_NAME, array() );
		$profile_id = isset( $options['profile_id'] ) ? $options['profile_id'] : null;
		$site_url    = isset( $options['site_url'] ) ? $options['site_url'] : null;

		$keyfile = isset( $options['file'] ) ? $options['file'] : null;
		$uploaddir = wp_upload_dir();

		// key file will store in upload folder with creating separate folder
       	$keyfile = $uploaddir['basedir'].'/ga_top_posts/'.$keyfile;

		
		$service_email    = isset( $options['service_email'] ) ? $options['service_email'] : null;		
		$duration_time    = isset( $options['duration_time'] ) ? $options['duration_time'] : null;
		$duration_scale    = isset( $options['duration_scale'] ) ? $options['duration_scale'] : null;
		$store_array = array();

		//fetch option from wp_options table if already exists then fetch record and return from here so API will call one time only
		$option_name = 'ga_top_post_data_res';
		$get_ga = get_option( $option_name );
    
		if ( $get_ga ) {
			// unserialize data to array 
			$ga_return = unserialize($get_ga);
			
			return $ga_return;
		}
		
		if($profile_id !="" && $site_url !="" && $keyfile !="" && $service_email !="")
		{
			// include ga api class file
			require_once("gapi.class.php");

			// call ga api class from here
			$ga = new gapi($service_email, $keyfile );

			// sending request to GA API
			$ga->requestReportData(
				$profile_id, //report_id
				$dimensions, //array('pagePath'), //dimensions
				$metrics, //array('pageviews','visits','uniquePageviews'), //metrics
				$sort_metric, //array('-uniquePageviews'),  //sort
				$filter, //null,  //filter
				$start_date, //$current_year.'-'.$current_month.'-01', //start_date
				$end_date, //$current_year.'-'.$current_month.'-'.$current_day,  //end_date
				$start_index, //2, //start_index
				$max_results //5 //max_results
			);
			
			
			$results = $ga->getResults();
		}
		if(!empty($results))
		{
			// making array because of needs to store in wp_options table with serialize data
			foreach($results as $result) {
				$store_array[] = array('pageviews'=>$result->getPageviews(),'visits'=>$result->getVisits(),'uniquePageviews'=>$result->getUniquepageviews(),'pagePath'=>$result->getPagepath());
			}
		}
		$value = serialize($store_array);
		
		// store result array in database with serialized data
		update_option( $option_name, $value );

		//fetch data from wp_options table and unserialize and then return
    	$get_ga = get_option( $option_name );
		$ga_return = unserialize($get_ga);
	
		return $ga_return;
	}

	// create function for shortcode with attributes

	public function shortcode_for_display_toppost($atts){
		// get options of configuration from wp_options table.
		$options   = get_option( self::OPTION_NAME, array() );
		$site_url    = isset( $options['site_url'] ) ? $options['site_url'] : null;
		$exclude_url = isset( $options['exclude_url'] ) ? $options['exclude_url'] : array();

		//get post from ga by default it will fetch last 7 days
	    $last_7_days = strtotime("-7 days");
	    $last_year = date("Y", $last_7_days);
	    $last_month = date("m", $last_7_days);
	    $last_day = date("d", $last_7_days);

	    $current_year = date("Y");
	    $current_month = date("m");
	    $current_day = date("d");

		//defined required variable if attributes not added in shortcode then it will use default seted variables.
	    $top_post_title = "Most Read"; //dimensions
		if(!empty($atts['title'])) $top_post_title = $atts['title'];

		$dimensions = array('pagePath'); //dimensions
		if(!empty($atts['dimensions'])) $dimensions = $atts['dimensions'];

		$metrics = array('pageviews','visits','uniquePageviews'); //metrics
		if(!empty($atts['metrics'])) $metrics = $atts['metrics'];

		$sort_metric = array('-uniquePageviews'); //sort
		if(!empty($atts['sort_metric'])) $sort_metric = $atts['sort_metric'];

		$filter = null; //'ga:pagePath!=/'; //filter
		if(!empty($atts['filter'])) $filter = $atts['filter'];

		$start_date = $last_year.'-'.$last_month.'-'.$last_day; //start_date
		if(!empty($atts['start_date'])) $start_date = $atts['start_date'];
		//$start_date = "2019-01-01";

		$end_date = $current_year.'-'.$current_month.'-'.$current_day; //end_date
		if(!empty($atts['end_date'])) $end_date = $atts['end_date'];
		//$end_date = '2019-05-31';

		$start_index = 1; //start_index
		if(!empty($atts['start_index'])) $start_index = $atts['start_index'];

		$max_results = 6; //max_results
		if(!empty($atts['max_results'])) $max_results = $atts['max_results'];
		$ga_max_results = $max_results;
    	
    	if($exclude_url) {
			$exclude_url = explode(",", $exclude_url);
			$ga_max_results += count($exclude_url); //get more result from google analytics then remove exluded url added from admin bakend
		}

		//call ga API function 
		$top_posts = $this->get_ga_result($dimensions, $metrics, $sort_metric, $filter, $start_date, $end_date, $start_index, $ga_max_results);
		
		if ( empty($top_posts) ) {
	        echo '<p>' . 'There are no posts to display.' . '</p>';
	        return;
	    }

	    $posts = array();
	    $result_count = 0;		
		$results = $top_posts;
		
		foreach($results as $result) {
			
			if($result_count == $max_results) break;
			
			if($exclude_url && in_array($result['pagePath'], $exclude_url)) continue;
		
			$result_count++;

            $slug = trim($result['pagePath'], '/');
            $slug = explode("/", $slug);
            $slug = end($slug);

            $args = array(
                'name'           => $slug,
                'post_type'      => 'post',
                'post_status'    => 'publish',
                'posts_per_page' => 1
            );
			// fetching posts from GA provided pagePath
            $top_post = get_posts( $args );
            if( !empty($top_post) ) {
                $posts[] = $top_post[0];
            }

		}
		// generate html from here
	    echo '<div class="widget-top custom-top-post-title"><h4>'.$top_post_title.'</h4><div class="stripe-line"></div></div>';
	    if(!empty($posts)) {
	    	
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
		else{
			echo '<p>' . 'There are no posts to display.' . '</p>';
		}
	}
	// this plugin is defined for manually syncing with GA once we click on sync button latest articles will update in wp_options table.
	public function sync_from_ga()
	{
		global $wb;
		$nonce = $_REQUEST['nonce'];
		if ( ! wp_verify_nonce( $nonce, self::SYNC_NONCE_ACTION ) ) {
			// This nonce is not valid.
			die( 'Security check' ); 
		} else {
			$options   = get_option( self::OPTION_NAME, array() );
			$site_url    = isset( $options['site_url'] ) ? $options['site_url'] : null;
			$exclude_url = isset( $options['exclude_url'] ) ? $options['exclude_url'] : array();

			// defined default parameters for GA API
			//get post from ga
			$last_7_days = strtotime("-7 days");
			$last_year = date("Y", $last_7_days);
			$last_month = date("m", $last_7_days);
			$last_day = date("d", $last_7_days);

			$current_year = date("Y");
			$current_month = date("m");
			$current_day = date("d");

			$dimensions = array('pagePath'); //dimensions
			$metrics = array('pageviews','visits','uniquePageviews'); //metrics
			$sort_metric = array('-uniquePageviews'); //sort
			$filter = null; //'ga:pagePath!=/'; //filter
			$start_date = $last_year.'-'.$last_month.'-'.$last_day; //start_date
			$end_date = $current_year.'-'.$current_month.'-'.$current_day; //end_date
			$start_index = 1; //start_index
			$max_results = 6; //max_results
			$ga_max_results = $max_results;
			
			if($exclude_url) {
				$exclude_url = explode(",", $exclude_url);
				$ga_max_results += count($exclude_url); //get more result from google analytics then remove exluded url added from admin bakend
			}

			$top_posts = $this->get_ga_result($dimensions, $metrics, $sort_metric, $filter, $start_date, $end_date, $start_index, $ga_max_results);
			$results = $top_posts;
			if(!empty($results))
			{
				$message = 'Syncing updated.';
				echo json_encode( array( 'type' => 'success', 'message' => $message ) );
				die;
			}
			else
			{
				$message = 'Something wrong please check error log.';
				echo json_encode( array( 'type' => 'error', 'message' => $message ) );
				die;
			}
		}
		die;

	}
	
}
?>