<?php
/*
Plugin Name: CodeFlavors Vimeo Video Post Lite
Plugin URI: http://www.codeflavors.com/vimeo-video-post/
Description: Import Vimeo videos directly into WordPress and display them as posts or embeded in existing posts and/or pages as single videos or playlists.
Author: CodeFlavors
Version: 1.1
Author URI: http://www.codeflavors.com
*/	

define( 'CVM_PATH'		, plugin_dir_path(__FILE__) );
define( 'CVM_URL'		, plugin_dir_url(__FILE__) );
define( 'CVM_VERSION'	, '1.1');

include_once CVM_PATH.'includes/functions.php';
include_once CVM_PATH.'includes/shortcodes.php';
include_once CVM_PATH.'includes/libs/custom-post-type.class.php';
include_once CVM_PATH.'includes/libs/video-import.class.php';

/**
 * Enqueue player script on single custom post page
 */
function cvm_single_video_scripts(){
	
	$settings 	= cvm_get_settings();
	$is_visible = $settings['archives'] ? true : is_single();
	
	if( is_admin() || !$is_visible || !cvm_is_video_post() ){
		return;
	}
	cvm_enqueue_player();	
}
add_action('wp_print_scripts', 'cvm_single_video_scripts');

/**
 * Filter custom post content
 */
function cvm_single_custom_post_filter( $content ){
	
	$settings 	= cvm_get_settings();
	$is_visible = $settings['archives'] ? true : is_single();
	
	if( is_admin() || !$is_visible || !cvm_is_video_post() ){
		return $content;
	}
	
	global $post;
	$settings 	= cvm_get_video_settings( $post->ID, true );
	$video 		= get_post_meta($post->ID, '__cvm_video_data', true);
	
	$settings['video_id'] = $video['video_id'];
		
	$width 	= $settings['width'];
	$height = cvm_player_height( $settings['aspect_ratio'] , $width);
	
	$video_container = '<div class="cvm_single_video_player" style="width:'.$width.'px; height:'.$height.'px; max-width:100%;"><!--'.json_encode($settings).'--></div>';
	
	if( 'below-content' == $settings['video_position'] ){
		return $content.$video_container;
	}else{
		return $video_container.$content;
	}
}
add_filter('the_content', 'cvm_single_custom_post_filter');

/**
 * Plugin activation; register permalinks for videos
 */
function cvm_activation_hook(){
	global $CVM_POST_TYPE;
	if( !$CVM_POST_TYPE ){
		return;
	}
	// register custom post
	$CVM_POST_TYPE->register_post();
	// create rewrite ( soft )
	flush_rewrite_rules( false );
}
register_activation_hook( __FILE__, 'cvm_activation_hook');