<?php

/**
 * Creates from a number of given seconds a readable duration ( HH:MM:SS )
 * @param int $seconds
 */
function cvm_human_time( $seconds ){
	
	$seconds = absint( $seconds );
	
	if( $seconds < 0 ){
		return;
	}
	
	$h = floor( $seconds / 3600 );
	$m = floor( $seconds % 3600 / 60 );
	$s = floor( $seconds %3600 % 60 );
	
	return ( ($h > 0 ? $h . ":" : "").($m > 0 ? ($h > 0 && $m < 10 ? "0" : "") . $m . ":" : "0:") . ($s < 10 ? "0" : "") . $s);
	
}

/**
 * Query Vimeo for single video details
 * @param string $video_id
 * @param string $source
 */
function cvm_query_video( $video_id, $source = 'vimeo' ){
	
	$sources = array(
		'vimeo' 	=> 'http://vimeo.com/api/v2/video/%s.json'
	);
	
	if( !array_key_exists($source, $sources) ){
		return false;
	}
	
	$url 		= $sources[ $source ];
	$request 	= wp_remote_get( sprintf( $url, $video_id ) );
	
	return $request;
}

/**
 * Checks that global $post is video post type
 */
function cvm_is_video_post(){
	global $post;
	if( !$post ){
		return false;
	}
	
	global $CVM_POST_TYPE;
	if( $CVM_POST_TYPE->get_post_type() !== $post->post_type ){
		return false;
	}
	return true;
}

function cvm_get_post_type(){
	global $CVM_POST_TYPE;
	return $CVM_POST_TYPE->get_post_type();
}

/**
 * Adds video player script to page
 */
function cvm_enqueue_player(){	
	wp_enqueue_script(
		'cvm-video-player',
		CVM_URL.'assets/front-end/js/video-player.js',
		array('jquery', 'swfobject'),
		'1.0'
	);
}

/**
 * Formats the response from the feed for a single entry
 * @param array $entry
 */
function cvm_format_video_entry( $raw_entry ){
	
	// playlists have individual items stored under key video
	if( array_key_exists('video', $raw_entry) ){
		$raw_entry = $raw_entry['video'];
	}
	
	$thumbnails = array();
	
	if( isset( $raw_entry['thumbnails']['thumbnail'] ) ){	
		foreach( $raw_entry['thumbnails']['thumbnail'] as $thumbnail ){
			$thumbnails[] = $thumbnail['_content'];
		}
	}else if( isset( $raw_entry['thumbnail_small'] ) ){
		$thumbnails = array(
			$raw_entry['thumbnail_small'],
			$raw_entry['thumbnail_medium'],
			$raw_entry['thumbnail_large']
		);
	}	

	$stats = array(
		'comments' 	=> 0,
		'likes' 	=> 0,
		'views' 	=> 0
	);
	
	if( isset( $raw_entry['number_of_comments'] ) ){
		$stats['comments'] = $raw_entry['number_of_comments'];
	}else if( isset( $raw_entry['stats_number_of_comments'] ) ){
		$stats['comments'] = $raw_entry['stats_number_of_comments'];
	}
	
	if( isset( $raw_entry['number_of_likes'] ) ){
		$stats['likes'] = $raw_entry['number_of_likes'];
	}else if( isset( $raw_entry['stats_number_of_likes'] ) ){
		$stats['likes'] = $raw_entry['stats_number_of_likes'];
	}
	
	if( isset( $raw_entry['number_of_plays'] ) ){
		$stats['views'] = $raw_entry['number_of_plays'];
	}else if( isset( $raw_entry['stats_number_of_plays'] ) ){
		$stats['views'] = $raw_entry['stats_number_of_plays'];
	}
		
	$entry = array(
		'video_id'		=> $raw_entry['id'],
		'uploader'		=> isset( $raw_entry['user_name'] ) ? $raw_entry['user_name'] : $raw_entry['owner']['display_name'],
		'published' 	=> $raw_entry['upload_date'],
		'updated'		=> isset( $raw_entry['modified_date'] ) ? $raw_entry['modified_date'] : false,
		'title'			=> $raw_entry['title'],
		'description' 	=> $raw_entry['description'],
		'category'		=> false,
		'duration'		=> $raw_entry['duration'],
		'thumbnails'	=> $thumbnails,				
		'stats'			=> $stats				
	);
	
	return $entry;
}

/**
 * Utility function, returns plugin default settings
 */
function cvm_plugin_settings_defaults(){
	$defaults = array(
		'public'				=> true, // post type is public or not
		'archives'				=> false,
		'import_title' 			=> true, // import titles on custom posts
		'import_description' 	=> 'post_content', // import descriptions on custom posts
		'import_status'			=> 'draft', // default import status of videos
		'vimeo_consumer_key'	=> '',
		'vimeo_secret_key'		=> '',
		'oauth_token'			=> '',// retrieved from Vimeo
	);
	return $defaults;
}

/**
 * Utility function, returns plugin settings
 */
function cvm_get_settings(){
	$defaults = cvm_plugin_settings_defaults();
	$option = get_option('_cvm_plugin_settings', $defaults);
	
	foreach( $defaults as $k => $v ){
		if( !isset( $option[ $k ] ) ){
			$option[ $k ] = $v;
		}
	}
	
	return $option;
}

/**
 * Utility function, updates plugin settings
 */
function cvm_update_settings(){	
	$defaults = cvm_plugin_settings_defaults();
	foreach( $defaults as $key => $val ){
		if( is_numeric( $val ) ){
			if( isset( $_POST[ $key ] ) ){
				$defaults[ $key ] = (int)$_POST[ $key ];
			}
			continue;
		}
		if( is_bool( $val ) ){
			$defaults[ $key ] = isset( $_POST[ $key ] );
			continue;
		}
		
		if( isset( $_POST[ $key ] ) ){
			$defaults[ $key ] = $_POST[ $key ];
		}
	}
	// current settings
	$plugin_settings = cvm_get_settings();
	// reset oauth if user changes the keys
	if( isset( $_POST['vimeo_consumer_key'] ) && isset( $_POST['vimeo_secret_key'] ) ){
		if( 
			($_POST['vimeo_consumer_key'] != $plugin_settings['vimeo_consumer_key']) || 
			($_POST['vimeo_secret_key'] != $plugin_settings['vimeo_secret_key'] ) 
		){
			$defaults['oauth_token'] = '';
		}		
	}
	
	update_option('_cvm_plugin_settings', $defaults);
}

/**
 * Global player settings defaults.
 */
function cvm_player_settings_defaults(){
	$defaults = array(
		'title'		=> 1,
		'byline' 	=> 1, // show player controls. Values: 0 or 1
		'portrait' 	=> 1, // 0 - always show controls; 1 - hide controls when playing; 2 - hide progress bar when playing
		'color'		=> 'FF0000', // 0 - fullscreen button hidden; 1 - fullscreen button displayed
		'fullscreen'=> 1,	
	
		'autoplay'	=> 0, // 0 - on load, player won't play video; 1 - on load player plays video automatically
		//'loop'		=> 0, // 0 - video won't start again once finished; 1 - video will play again once finished

		// extra settings
		'aspect_ratio'		=> '16x9',
		'width'				=> 640,
		'video_position' 	=> 'below-content', // in front-end custom post, where to display the video: above or below post content
		'volume'			=> 30, // video default volume	
	);
	return $defaults;
}

/**
 * Get general player settings
 */
function cvm_get_player_settings(){
	$defaults 	= cvm_player_settings_defaults();
	$option 	= get_option('_cvm_player_settings', $defaults);
	
	foreach( $defaults as $k => $v ){
		if( !isset( $option[ $k ] ) ){
			$option[ $k ] = $v;
		}
	}
	
	// various player outputs may set their own player settings. Return those.
	global $CVM_PLAYER_SETTINGS;
	if( $CVM_PLAYER_SETTINGS ){
		foreach( $option as $k => $v ){
			if( isset( $CVM_PLAYER_SETTINGS[$k] ) ){
				$option[$k] = $CVM_PLAYER_SETTINGS[$k];
			}
		}
	}
	
	return $option;
}

/**
 * Update general player settings
 */
function cvm_update_player_settings(){
	$defaults = cvm_player_settings_defaults();
	foreach( $defaults as $key => $val ){
		if( is_numeric( $val ) ){
			if( isset( $_POST[ $key ] ) ){
				$defaults[ $key ] = (int)$_POST[ $key ];
			}else{
				$defaults[ $key ] = 0;
			}
			continue;
		}
		if( is_bool( $val ) ){
			$defaults[ $key ] = isset( $_POST[ $key ] );
			continue;
		}
		
		if( isset( $_POST[ $key ] ) ){
			$defaults[ $key ] = $_POST[ $key ];
		}
	}
	
	update_option('_cvm_player_settings', $defaults);	
}

/**
 * Displays checked argument in checkbox
 * @param bool $val
 * @param bool $echo
 */
function cvm_check( $val, $echo = true ){
	$checked = '';
	if( is_bool($val) && $val ){
		$checked = ' checked="checked"';
	}
	if( $echo ){
		echo $checked;
	}else{
		return $checked;
	}	
}

/**
 * Displays a style="display:hidden;" if passed $val is bool and false
 * @param bool $val
 * @param string $before
 * @param string $after
 * @param bool $echo
 */
function cvm_hide( $val, $compare = false, $before=' style="', $after = '"', $echo = true ){
	$output = '';
	if(  $val == $compare ){
		$output .= $before.'display:none;'.$after;
	}
	if( $echo ){
		echo $output;
	}else{
		return $output;
	}
}

/**
 * Display select box
 * @param array $args - see $defaults in function
 * @param bool $echo
 */
function cvm_select( $args = array(), $echo = true ){
	
	$defaults = array(
		'options' 	=> array(),
		'name'		=> false,
		'id'		=> false,
		'class'		=> '',
		'selected'	=> false,
		'use_keys'	=> true,
		'before'	=> '',
		'after'		=> ''
	);
	
	$o = wp_parse_args($args, $defaults);
	
	if( !$o['id'] ){
		$output = sprintf( '<select name="%1$s" id="%1$s" class="%2$s">', $o['name'], $o['class']);
	}else{
		$output = sprintf( '<select name="%1$s" id="%2$s" class="%3$s">', $o['name'], $o['id'], $o['class']);
	}	
	
	foreach( $o['options'] as $val => $text ){
		$opt = '<option value="%1$s" title="%4$s"%2$s>%3$s</option>';
		
		if( is_array( $text ) ){
			$title = $text['title'];
			$text = $text['text'];			
		}else{
			$title = '';
		}
		
		$value = $o['use_keys'] ? $val : $text;
		$c = $o['use_keys'] ? $val == $o['selected'] : $text == $o['selected'];
		$checked = $c ? ' selected="selected"' : '';		
		$output .= sprintf($opt, $value, $checked, $text, $title);		
	}
	
	$output .= '</select>';
	
	if( $echo ){
		echo $o['before'].$output.$o['after'];
	}
	
	return $o['before'].$output.$o['after'];
}

/**
 * Calculate player height from given aspect ratio and width
 * @param string $aspect_ratio
 * @param int $width
 */
function cvm_player_height( $aspect_ratio, $width ){
	$width = absint($width);
	$height = 0;
	switch( $aspect_ratio ){
		case '4x3':
			$height = ($width * 3) / 4;
		break;
		case '16x9':
		default:	
			$height = ($width * 9) / 16;
		break;	
	}
	return $height;
}

/**
 * Single post default settings
 */
function cvm_post_settings_defaults(){
	// general player settings
	$plugin_defaults = cvm_get_player_settings();	
	return $plugin_defaults;
}

/**
 * Returns playback settings set on a video post
 */
function cvm_get_video_settings( $post_id = false, $output = false ){
	global $CVM_POST_TYPE;
	if( !$post_id ){
		global $post;
		if( !$post || $CVM_POST_TYPE->get_post_type() !== $post->post_type ){
			return false;
		}
		$post_id = $post->ID;		
	}else{
		$post = get_post( $post_id );
		if( $CVM_POST_TYPE->get_post_type() !== $post->post_type ){
			return false;
		}
	}
	
	$defaults = cvm_post_settings_defaults();
	$option = get_post_meta( $post_id, '__cvm_playback_settings', true );
	
	foreach( $defaults as $k => $v ){
		if( !isset( $option[ $k ] ) ){
			$option[ $k ] = $v;
		}
	}
	
	if( $output ){
		foreach( $option as $k => $v ){
			if( is_bool( $v ) ){
				$option[$k] = absint( $v );
			}
		}
	}
	
	return $option;	
}

/**
 * Utility function, updates video settings
 */
function cvm_update_video_settings( $post_id ){
	
	if( !$post_id ){
		return false;
	}
	
	global $CVM_POST_TYPE;
	$post = get_post( $post_id );
	if( $CVM_POST_TYPE->get_post_type() !== $post->post_type ){
		return false;
	}
	
	$defaults = cvm_post_settings_defaults();
	foreach( $defaults as $key => $val ){
		if( is_numeric( $val ) ){
			if( isset( $_POST[ $key ] ) ){
				$defaults[ $key ] = (int)$_POST[ $key ];
			}else{
				$defaults[ $key ] = 0;
			}
			continue;
		}
		if( is_bool( $val ) ){
			$defaults[ $key ] = isset( $_POST[ $key ] );
			continue;
		}
		
		if( isset( $_POST[ $key ] ) ){
			$defaults[ $key ] = $_POST[ $key ];
		}
	}
	
	update_post_meta($post_id, '__cvm_playback_settings', $defaults);	
}


/**
 * Register widgets.
 */
function cvm_load_widgets() {
	// check if posts are public
	$options = cvm_get_settings();
	if( !isset( $options['public'] ) || !$options['public'] ){
		return;
	}
		
	include CVM_PATH.'includes/libs/video-widgets.class.php';
	register_widget( 'CVM_Latest_Videos_Widget' );
	register_widget( 'CVM_Video_Categories_Widget' );
	
}
add_action( 'widgets_init', 'cvm_load_widgets' );

/**
 * TinyMce
 */
function cvm_tinymce_buttons(){
	// Don't bother doing this stuff if the current user lacks permissions
	if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
		return;
 	
	// Don't load unless is post editing (includes post, page and any custom posts set)
	$screen = get_current_screen();
	global $CVM_POST_TYPE;
	if( 'post' != $screen->base || $CVM_POST_TYPE->get_post_type() == $screen->post_type ){
		return;
	}  
	
	// Add only in Rich Editor mode
	if ( get_user_option('rich_editing') == 'true') {
   		
		wp_enqueue_script(array(
			'jquery-ui-dialog'
		));
			
		wp_enqueue_style(array(
			'wp-jquery-ui-dialog'
		));
   	
	    add_filter('mce_external_plugins', 'cvm_tinymce_plugin');
	    add_filter('mce_buttons', 'cvm_register_buttons');
   }	
}

function cvm_register_buttons($buttons) {	
	array_push($buttons, 'separator', 'cvm_shortcode');
	return $buttons;
}

// Load the TinyMCE plugin : editor_plugin.js (wp2.5)
function cvm_tinymce_plugin($plugin_array) {
	$plugin_array['cvm_shortcode'] = CVM_URL.'assets/back-end/js/tinymce/shortcode.js';
	return $plugin_array;
}

add_action('admin_head', 'cvm_tinymce_buttons');

function cvm_load_post_edit_styling(){
	global $post, $CVM_POST_TYPE;
	if( !$post || $CVM_POST_TYPE->get_post_type() == $post->post_type ){
		return;
	}
	
	wp_enqueue_style(
		'cvm-shortcode-modal',
		CVM_URL.'assets/back-end/css/shortcode-modal.css',
		false,
		'1.0'
	);
	
	wp_enqueue_script(
		'cvm-shortcode-modal',
		CVM_URL.'assets/back-end/js/shortcode-modal.js',
		false,
		'1.0'
	);
	
	wp_enqueue_script(
		'cvm-video-edit',
		CVM_URL.'assets/back-end/js/video-edit.js',
		array('jquery'),
		'1.0'
	);
	
	
	$messages = array(
		'playlist_title' => __('Videos in playlist', 'cvm_video'),
		'no_videos'		 => __('No videos selected.<br />To create a playlist check some videos from the list on the right.', 'cvm_video'),
		'deleteItem'	 => __('Delete from playlist', 'cvm_video'),
		'insert_playlist'=> __('Add shortcode into post', 'cvm_video')
	);
	
	wp_localize_script('cvm-shortcode-modal', 'CVM_SHORTCODE_MODAL', $messages);
}
add_action('admin_print_styles-post.php', 'cvm_load_post_edit_styling');
add_action('admin_print_styles-post-new.php', 'cvm_load_post_edit_styling');

/**
 * Modal window post edit shortcode output
 */
function cvm_post_edit_modal(){
	global $post, $CVM_POST_TYPE;
	if( !$post || $CVM_POST_TYPE->get_post_type() == $post->post_type ){
		return;
	}
	
	$options = cvm_get_player_settings();
	
	?>
	<div id="CVMVideo_Modal_Window" style="display:none;">	
		<div class="wrap">
			<div id="cvm-playlist-items">
				<div class="inside">
					<h2><?php _e('Playlist settings', 'cvm_video');?></h2>
					<div id="cvm-playlist-settings" class="cvm-player-settings-options">
						<table>
							<tr>
								<th><label for="cvm_playlist_theme"><?php _e('Theme', 'cvm_video');?>:</label></th>
								<td>
								<?php
								$playlist_themes = cvm_playlist_themes();
								cvm_select(array(
									'name' 		=> 'cvm_playlist_theme',
									'id' 		=> 'cvm_playlist_theme',
									'options' 	=> $playlist_themes
								));
								?>
								</td>
							</tr>
							<tr>
								<th><label for="cvm_aspect_ratio"><?php _e('Aspect', 'cvm_video');?>:</label></th>
								<td>
								<?php 
									$args = array(
										'options' 	=> array(
											'4x3' 	=> '4x3',
											'16x9' 	=> '16x9'
										),
										'name' 		=> 'aspect_ratio',
										'id'		=> 'aspect_ratio',
										'class'		=> 'cvm_aspect_ratio'
									);
									cvm_select( $args );
								?>
								</td>
							</tr>
							
							<tr>
								<th><label for="width"><?php _e('Width', 'cvm_video');?>:</label></th>
								<td>
									<input type="text" class="cvm_width" name="width" id="width" value="<?php echo $options['width'];?>" size="2" />px
									| <?php _e('Height', 'cvm_video');?> : <span class="cvm_height" id="cvm_calc_height"><?php echo cvm_player_height( $options['aspect_ratio'], $options['width'] );?></span>px
								</td>
							</tr>
							
							<tr>
								<th><label for="volume"><?php _e('Volume', 'cvm_video');?></label>:</th>
								<td>
									<input type="text" name="volume" id="volume" value="<?php echo $options['volume'];?>" size="1" maxlength="3" />
									<label for="volume"><span class="description"><?php _e('number between 0 (mute) and 100 (max)', 'cvm_video');?></span></label>
								</td>
							</tr>
						</table>
						<input type="button" id="cvm-insert-playlist-shortcode" class="button primary" value="<?php _e('Insert playlist', 'cvm_video');?>" />						
					</div>
					
					<input type="hidden" name="cvm_selected_items"  value="" />
					<h2><?php _e('Videos in playlist', 'cvm_video');?></h2>
					
					<div id="cvm-list-items">
						<em><?php _e('No video selected', 'cvm_video');?><br /><?php _e('To create a playlist check some videos from the list on the right.', 'cvm_video');?></em>
					</div>
				</div>	
			</div>
			<div id="cvm-display-videos">
				<iframe src="edit.php?post_type=<?php echo $CVM_POST_TYPE->get_post_type();?>&page=cvm_videos" frameborder="0" width="100%" height="100%"></iframe>
			</div>
		</div>	
	</div>
	<?php	
}

add_action('admin_footer', 'cvm_post_edit_modal');

/**
 * Available player themes
 */
function cvm_playlist_themes(){
	return array(
		'default' 	=> __('Default theme', 'cvm_video'),
		'carousel' 	=> __('Carousel navigation (PRO)', 'cvm_video')
	);
}

/**
 * Enqueue some functionality scripts on widgets page
 */
function cvm_widgets_scripts(){
	$plugin_settings = cvm_get_settings();
	if( isset( $plugin_settings['public'] ) && !$plugin_settings['public'] ){
		return;
	}
}
add_action('admin_print_scripts-widgets.php', 'cvm_widgets_scripts');
/**
 * TEMPLATING
 */

/**
 * Outputs default player data
 */
function cvm_output_player_data( $echo = true ){
	$player = cvm_get_player_settings();
	$data = '<!--'.json_encode($player).'-->';
	if( $echo ){
		echo $data;
	}
	return $data;
}

/**
 * Outputs the default player size
 * @param string $before
 * @param string $after
 * @param bool $echo
 */
function cvm_output_player_size( $before = ' style="', $after='"', $echo = true ){
	$player = cvm_get_player_settings();
	$height = cvm_player_height($player['aspect_ratio'], $player['width']);
	$output = 'width:'.$player['width'].'px; height:'.$height.'px;';
	if( $echo ){
		echo $before.$output.$after;
	}
	
	return $before.$output.$after;
}

/**
 * Output width according to player
 * @param string $before
 * @param string $after
 * @param bool $echo
 */
function cvm_output_width( $before = ' style="', $after='"', $echo = true ){
	$player = cvm_get_player_settings();
	if( $echo ){
		echo $before.'width: '.$player['width'].'px; '.$after;
	}
	return $before.'width: '.$player['width'].'px; '.$after;
}

/**
 * Output video thumbnail
 * @param string $before
 * @param string $after
 * @param bool $echo
 */
function cvm_output_thumbnail( $before = '', $after = '', $echo = true ){
	global $cvm_video;
	$output = '';
	
	if( isset( $cvm_video['video_data']['thumbnails'][0] ) ){
		$output = sprintf('<img src="%s" alt="" />', $cvm_video['video_data']['thumbnails'][0]);
	}
	if( $echo ){
		echo $before.$output.$after;
	}
	
	return $before.$output.$after;
}

/**
 * Output video title
 * @param string $before
 * @param string $after
 * @param bool $echo
 */
function cvm_output_title( $include_duration = true,  $before = '', $after = '', $echo = true  ){
	global $cvm_video;
	$output = '';
	if( isset( $cvm_video['title'] ) ){
		$output = $cvm_video['title'];
	}
	
	if( $include_duration ){
		$output .= ' <span class="duration">['.cvm_human_time( $cvm_video['video_data']['duration'] ).']</span>';
	}
	
	if( $echo ){
		echo $before.$output.$after;
	}
	return $before.$output.$after;
}

/**
 * Outputs video data
 * @param string $before
 * @param string $after
 * @param bool $echo
 */
function cvm_output_video_data( $before = " rel='", $after="'", $echo = true ){
	global $cvm_video;
	
	$video_settings = cvm_get_video_settings( $cvm_video['ID'] );	
	$video_id 		= $cvm_video['video_data']['video_id'];
	$data = array(
		'video_id' 	=> $video_id,
		'autoplay' 	=> $video_settings['autoplay'],
		'volume'  	=> $video_settings['volume']
	);
	
	$output = json_encode( $data );
	if( $echo ){
		echo $before.$output.$after;
	}
	
	return $before.$output.$after;
}

function cvm_video_post_permalink( $echo  = true ){
	global $cvm_video;
	
	$pl = get_permalink( $cvm_video['ID'] );
	
	if( $echo ){
		echo $pl;
	}
	
	return $pl;
	
}

/**
 * Themes compatibility layer
 */

/**
 * Supported themes
 */
function cvm_supported_themes(){	
	$themes = array(
		'detube' => array(
			'theme_name' => 'DeTube'		
		),
		'Avada' => array(
			'theme_name' => 'Avada'
		)
	);
	return $themes;
}

/**
 * Check if theme is supported by the plugin.
 * Returns false or an array containing a mapping for custom post fields to store information on
 */
function cvm_check_theme_support(){
	
	$template 	= get_template();
	$themes 	= cvm_supported_themes();
	
	if( !array_key_exists($template, $themes) ){
		return false;
	}
	
	return $themes[$template];		
}

/**
 * Returns contextual help content from file
 * @param string $file - partial file name
 */
function cvm_get_contextual_help( $file ){
	if( !$file ){
		return false;
	}	
	$file_path = CVM_PATH. 'views/help/' . $file.'.html.php';
	if( is_file($file_path) ){
		ob_start();
		include( $file_path );
		$help_contents = ob_get_contents();
		ob_end_clean();		
		return $help_contents;
	}else{
		return false;
	}
}

/**
 * Returns video URL for a given video ID
 * @param string $video_id
 */
function cvm_video_url( $video_id ){
	return sprintf('http://vimeo.com/%s', $video_id);
}

/**
 * Returns embed code for a given video ID
 * @param string $video_id
 */
function cvm_video_embed( $video_id ){
	$options = cvm_get_player_settings();
	return sprintf( '<iframe src="http://player.vimeo.com/video/%s?title=%d&amp;byline=%d&amp;portrait=%d&amp;color=%s" width="%d" height="%d" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>',
		$video_id,
		$options['title'],
		$options['byline'],
		$options['portrait'],
		$options['color'],
		$options['width'],
		cvm_player_height($options['aspect_ratio'], $options['width'])
	);
}

/**
 * Outputs any errors issued by Vimeo when importing videos
 * @param bool $echo
 * @param string $before
 * @param string $after
 */
function cvm_import_errors( $error, $echo = true, $before = '<div class="error"><p>', $after = '</p></div>' ){
	if( !is_wp_error( $error ) ){
		return;
	}
	
	// wp error message
	$code = 'cvm_wp_error';
	$message = $error->get_error_message( $code );
	if( $message ){		
		$output = __('WordPress encountered and error while trying to query Vimeo:', 'cvm_video'). '<br />' . '<strong>'.$message.'</strong></p>';		
		if( $echo ){
			echo $before.$output.$after;
		}		
		return $before.$output.$after;
	}
	
	// vimeo api errors
	$code = 'cvm_vimeo_query_error';	
	$message 	= $error->get_error_message( $code );
	$data		= $error->get_error_data( $code );
	
	$output = '<strong>'.$message.'</strong></p>';
	$output.= sprintf( __('Vimeo error code: %s (<em>%s</em>) - <strong>%s</strong>', 'cvm_video'), $data['code'], $data['msg'], $data['expl'] );
	
	if( 401 == $data['code'] ){
		$url = menu_page_url('cvm_settings', false).'#vimeo_consumer_key';
		$link = sprintf('<a href="%s">%s</a>', $url, __('Settings page', 'cvm_video'));		
		$output.= '<br /><br />' . sprintf(__('Please visit %s and enter your consumer and secret keys.', 'cvm_video'), $link);
	}
	
	if( $echo ){
		echo $before.$output.$after;
	}
	
	return $before.$output.$after;	
}

/**
 * Display update notices on plugin pages.
 */
function cvm_admin_messages(){
	global $CVM_POST_TYPE;
	if( !isset( $_GET['post_type'] ) || $CVM_POST_TYPE->get_post_type() != $_GET['post_type'] ){
		return;
	}
	
	$messages 	= array();
	$o 			= cvm_get_settings();
	
	if( empty( $o['vimeo_consumer_key'] ) || empty( $o['vimeo_secret_key'] ) ){
		$messages[] = 'In order to be able to bulk import videos using Vimeo videos plugin, you must register on <a href="https://developer.vimeo.com/apps/new">Vimeo App page</a>.<br />
					   Please note that you must have a valid Vimeo account and also you must be logged into Vimeo before being able to register your app.<br />
					   After you registered your app, please visit <a href="'.menu_page_url('cvm_settings', false).'#cvm_vimeo_keys">Settings page</a> and enter your Vimeo consumer and secret keys.';
	}
	
	if( $messages ){
		echo '<div class="update-nag"><span>'.implode('</span><hr /><span>', $messages).'</span></div>';
	}
}
add_action('all_admin_notices', 'cvm_admin_messages');

function cvm_docs_link( $path ){
	$base = 'http://www.codeflavors.com/documentation/vimeo-video-post-wp-plugin/';
	$vars = array(
		'utm_source' => 'plugin',
		'utm_medium' => 'doc_link',
		'utm_campaign' => 'vimeo-lite-plugin'
	);
	$q = http_build_query( $vars );
	return $base . trailingslashit( $path ) . '?' . $q;
}