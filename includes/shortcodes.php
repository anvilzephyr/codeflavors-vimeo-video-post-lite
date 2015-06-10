<?php
/**
 * Shortcode to display a single video in post/page
 * Usage:
 * 
 * [cvm_video id="video_id_from_wp"]
 * 
 * Complete params:
 * 
 * - id : video ID from WordPress import (post ID) - required
 * - volume : video volume (number between 1 and 100) - optional
 * - width : width of video (number) - optional; works in conjunction with aspect_ratio
 * - aspect_ratio : aspect ratio of video ( 16x9 or 4x3 ) - optional; needed to calculate height
 * - autoplay : play video on page load ( 0 or 1 ) - optional
 * - controls : display controls on video ( 0 or 1 ) - optional
 * 
 * @param array $atts
 * @param string $content
 */
function cvm_single_video( $atts = array(), $content = '' ){
	// check if atts is set
	if( !is_array( $atts ) ){
		return;
	}	
	// look for video ID
	if( !array_key_exists('id', $atts) ){
		return;
	}
	// post id from atts
	$post_id = absint( $atts['id'] );
	// post
	$post = get_post( $atts['id'] );
	if( !$post ){
		return;
	}	
	// check post type
	global $CVM_POST_TYPE;
	if( $CVM_POST_TYPE->get_post_type() !== $post->post_type ){
		return false;
	}
	
	// get video options attached to post
	$vars = cvm_get_video_settings( $post_id );
	// get video data
	$video 	= get_post_meta($post_id, '__cvm_video_data', true);	
		
	$width	= absint( $vars['width'] );
	$height = cvm_player_height( $vars['aspect_ratio'] , $vars['width']);
	
	$settings = $vars;
	$settings['video_id'] = $video['video_id'];
	
	$video_container = '<div class="cvm_single_video_player" style="width:'.$width.'px; height:'.$height.'px; max-width:100%;"><!--'.json_encode($settings).'--></div>';
	// add JS file
	cvm_enqueue_player();
	
	return $video_container;
}
add_shortcode('cvm_video', 'cvm_single_video');

/**
 * Shortcode to display a playlist of videos
 * @param array $atts
 * @param string $content
 */
function cvm_video_playlist( $atts = array(), $content = '' ){
	// check if atts is set
	if( !is_array( $atts ) ){
		return;
	}	
	// look for video ID's
	if( !array_key_exists('videos', $atts) ){
		return;
	}
	// look for video ids
	$video_ids = explode(',', $atts['videos']);
	if( !$video_ids ){
		return;
	}
	
	unset( $atts['videos'] );
	$defaults = cvm_get_player_settings();
	$player_settings = wp_parse_args($atts, $defaults);
	
	if( !isset( $atts['theme'] ) ){
		$atts['theme'] = 'default';
	}
	
	$content = cvm_output_playlist( $video_ids, count($video_ids), $atts['theme'], $player_settings );	
	return $content;
}
add_shortcode('cvm_playlist', 'cvm_video_playlist');


function cvm_output_playlist( $videos = 'latest', $results = 5, $theme = 'default', $player_settings = array() ){
	global $CVM_POST_TYPE;
	$args = array(
		'post_type' 		=> $CVM_POST_TYPE->get_post_type(),
		'posts_per_page' 	=> absint( $results ),
		'numberposts'		=> absint( $results ),
		'post_status'		=> 'publish',
		'supress_filters'	=> true
	);
	// if $videos is array, the function was called with an array of video ids
	if( is_array( $videos ) ){
		
		$ids = array();
		foreach( $videos as $video_id ){
			$ids[] = absint( $video_id );
		}		
		$args['include'] 		= $ids;
		$args['posts_per_page'] = count($ids);
		$args['numberposts'] 	= count($ids);
		
	}elseif( is_string( $videos ) ){
		
		$found = false;
		switch( $videos ){
			case 'latest':
				$args['orderby']	= 'post_date';
				$args['order']		= 'DESC';
				$found 				= true;
			break;	
		}
		if( !$found ){
			return;
		}
				
	}else{ // if $videos is anything else other than array or string, bail out		
		return;		
	}
	
	// get video posts
	$posts = get_posts( $args );
	
	if( !$posts ){
		return;
	}
	
	$videos = array();
	foreach( $posts as $post_key => $post ){
		if( isset( $ids ) ){
			$key = array_search($post->ID, $ids);
		}else{
			$key = $post_key;
		}	
		
		if( is_numeric( $key ) ){
			$videos[ $key ] = array(
				'ID'			=> $post->ID,
				'title' 		=> $post->post_title,
				'video_data' 	=> get_post_meta( $post->ID, '__cvm_video_data', true )
			);
		}
	}
	ksort( $videos );
	
	ob_start();
	
	// set custom player settings if any
	global $CVM_PLAYER_SETTINGS;
	if( $player_settings && is_array( $player_settings ) ){

		$defaults = cvm_get_player_settings();
		foreach ( $defaults as $setting => $value ){
			if( isset( $player_settings[$setting] ) && !empty( $player_settings[$setting] ) ){
				$defaults[ $setting ] = $player_settings[ $setting ];
			}
		}
		
		$CVM_PLAYER_SETTINGS = $defaults;
	}
	
	global $cvm_video;
	
	if( !array_key_exists($theme, cvm_playlist_themes()) ){
		$theme = 'default';
	}
	
	$file = CVM_PATH.'themes/'.$theme.'/player.php';
	if( !is_file($file) ){
		$theme = 'default';
	}
	
	include( CVM_PATH.'themes/'.$theme.'/player.php' );
	$content = ob_get_contents();
	ob_end_clean();
	
	cvm_enqueue_player();
	wp_enqueue_script(
		'cvm-vim-player-'.$theme, 
		CVM_URL.'themes/'.$theme.'/assets/script.js', 
		array('cvm-video-player'), 
		'1.0'
	);
	wp_enqueue_style(
		'cvm-vim-player-'.$theme, 
		CVM_URL.'themes/'.$theme.'/assets/stylesheet.css', 
		false, 
		'1.0'
	);
	
	// remove custom player settings
	$CVM_PLAYER_SETTINGS = false;
	
	return $content;
	
}
