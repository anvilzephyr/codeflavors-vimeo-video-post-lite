<?php
/**
 * Latest videos widget
 */
class CVM_Latest_Videos_Widget extends WP_Widget{
	/**
	 * Constructor
	 */
	function CVM_Latest_Videos_Widget(){
		/* Widget settings. */
		$widget_options = array( 
			'classname' 	=> 'cvm-latest-videos', 
			'description' 	=> __('The most recent videos on your site.', 'cvm_video') 
		);

		/* Widget control settings. */
		$control_options = array( 
			'id_base' => 'cvm-latest-videos-widget' 
		);

		/* Create the widget. */
		$this->WP_Widget( 
			'cvm-latest-videos-widget', 
			__('Recent videos', 'cvm_video'), 
			$widget_options, 
			$control_options 
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_Widget::widget()
	 */
	function widget( $args, $instance ){
		
		extract($args);
		
		global $CVM_POST_TYPE;
		$posts = absint($instance['cvm_posts_number']);
		
		// display a list of video posts
		$posts = get_posts(array(
			'numberposts' 		=> $posts,
			'posts_per_page' 	=> $posts,
			'orderby' 			=> 'post_date',
			'order' 			=> 'DESC',
			'post_type' 		=> $CVM_POST_TYPE->get_post_type(),
			'post_status' 		=> 'publish',
			'suppress_filters' 	=> true
		));
		if( !$posts ){
			return;
		}
		
		echo $before_widget;
		
		if( !empty( $instance['cvm_widget_title'] ) ){		
			echo $before_title . apply_filters('widget_title', $instance['cvm_widget_title']) . $after_title;
		}
		?>
		<ul class="cvm-recent-videos-widget">
			<?php foreach($posts as $post):?>
			<?php 
			if( $instance['cvm_vim_image'] ){
				$video_data = get_post_meta($post->ID, '__cvm_video_data', true);
				if( isset( $video_data['thumbnails'][0] ) ){
					$thumbnail = sprintf('<img src="%s" alt="%s" />', $video_data['thumbnails'][0], apply_filters('the_title', $post->post_title));
				}
			}else{
				$thumbnail = '';
			}
			?>
			<li><a href="<?php echo get_permalink($post->ID);?>" title="<?php echo apply_filters('the_title', $post->post_title);?>"><?php echo $thumbnail;?> <?php echo apply_filters('post_title', $post->post_title);?></a></li>
			<?php endforeach;?>
		</ul>
		<?php 
		echo $after_widget;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see WP_Widget::update()
	 */
	function update($new_instance, $old_instance){

		$instance = $old_instance;
		$instance['cvm_widget_title'] 	= $new_instance['cvm_widget_title'];
		$instance['cvm_posts_number'] 	= (int)$new_instance['cvm_posts_number'];
		$instance['cvm_vim_image']	  	= (bool)$new_instance['cvm_vim_image'];		
		return $instance;		
	}
	
	/**
	 * (non-PHPdoc)
	 * @see WP_Widget::form()
	 */
	function form( $instance ){
		
		$defaults 	= $this->get_defaults();;
		$options 	= wp_parse_args( (array)$instance, $defaults );
		
		?>
	<div class="cvm-player-settings-options">	
		<p>
			<label for="<?php echo  $this->get_field_id('cvm_widget_title');?>"><?php _e('Title', 'cvm_video');?>: </label>
			<input type="text" name="<?php echo  $this->get_field_name('cvm_widget_title');?>" id="<?php echo  $this->get_field_id('cvm_widget_title');?>" value="<?php echo $options['cvm_widget_title'];?>" class="widefat" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('cvm_posts_number');?>"><?php _e('Number of videos to show', 'cvm_video');?>: </label>
			<input type="text" name="<?php echo $this->get_field_name('cvm_posts_number');?>" id="<?php echo $this->get_field_id('cvm_posts_number');?>" value="<?php echo $options['cvm_posts_number'];?>" size="3" />
		</p>
		<p class="cvm-widget-show-vim-thumbs"<?php if( $options['cvm_show_playlist'] ):?> style="display:none;"<?php endif;?>>
			<input class="checkbox" type="checkbox" name="<?php echo $this->get_field_name('cvm_vim_image')?>" id="<?php echo $this->get_field_id('cvm_vim_image');?>"<?php cvm_check( (bool)$options['cvm_vim_image'] );?> />
			<label for="<?php echo $this->get_field_id('cvm_vim_image');?>"><?php _e('Display Vimeo thumbnails?', 'cvm_video');?></label>
		</p>
		<p>
			<input class="checkbox cvm-show-as-playlist-widget" type="checkbox" disabled="disabled" />
			<label><?php _e('Show as video playlist<sup>PRO</sup>', 'cvm_video');?></label>
		</p>		
	</div>	
		<?php 		
	}
	
	/**
	 * Default widget values
	 */
	private function get_defaults(){
		$player_defaults = cvm_get_player_settings();		
		$defaults = array(
			'cvm_widget_title' 	=> '',
			'cvm_posts_number' 	=> 5,
			'cvm_vim_image'		=> false			
		);
		return $defaults;
	}
}


class CVM_Video_Categories_Widget extends WP_Widget{
	/**
	 * Constructor
	 */
	function CVM_Video_Categories_Widget(){
		/* Widget settings. */
		$widget_options = array( 
			'classname' 	=> 'cvm-video-categories', 
			'description' 	=> __('A list or dropdown of video categories.', 'cvm_video') 
		);

		/* Widget control settings. */
		$control_options = array( 
			'id_base' => 'cvm-video-categories-widget' 
		);

		/* Create the widget. */
		$this->WP_Widget( 
			'cvm-video-categories-widget', 
			__('Video categories', 'cvm_video'), 
			$widget_options, 
			$control_options 
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_Widget::widget()
	 */
	function widget( $args, $instance ){
		
		extract($args);
		
		global $CVM_POST_TYPE;
				
		$widget_title = '';
		if( isset( $instance['title'] ) && !empty( $instance['title'] ) ){
			$widget_title = $before_title . apply_filters('widget_title', $instance['title']) . $after_title;
		}
		
		$args = array(
			'taxonomy' => $CVM_POST_TYPE->get_post_tax(),
			'pad_counts' => true,
			'title_li'	=> false,
			'show_count' => $instance['post_count'],
			'hierarchical' => $instance['hierarchy']
		);
		
		echo $before_widget;
		echo $widget_title;
		echo '<ul>';
		wp_list_categories( $args );
		echo '</ul>';
		echo $after_widget;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see WP_Widget::update()
	 */
	function update($new_instance, $old_instance){

		$instance = $old_instance;
		$instance['title'] 				= $new_instance['title'];
		$instance['dropdown'] 			= (bool)$new_instance['dropdown'];
		$instance['post_count']	  		= (bool)$new_instance['post_count'];
		$instance['hierarchy'] 			= (bool)$new_instance['hierarchy'];
		
		return $instance;		
	}
	
	/**
	 * (non-PHPdoc)
	 * @see WP_Widget::form()
	 */
	function form( $instance ){
		
		$defaults 	= $this->get_defaults();;
		$options 	= wp_parse_args( (array)$instance, $defaults );
		
		?>	
		<p>
			<label for="<?php echo  $this->get_field_id('title');?>"><?php _e('Title', 'cvm_video');?>: </label>
			<input type="text" name="<?php echo  $this->get_field_name('title');?>" id="<?php echo  $this->get_field_id('title');?>" value="<?php echo $options['title'];?>" class="widefat" />
		</p>
		<p>
			<input class="checkbox" type="checkbox" name="<?php echo $this->get_field_name('post_count');?>" id="<?php echo $this->get_field_id('post_count')?>"<?php cvm_check((bool)$options['post_count']);?> />
			<label for="<?php echo $this->get_field_id('post_count')?>"><?php _e('Show videos count', 'cvm_video');?></label>
			<br />
			<input class="checkbox" type="checkbox" name="<?php echo $this->get_field_name('hierarchy');?>" id="<?php echo $this->get_field_id('hierarchy')?>"<?php cvm_check((bool)$options['hierarchy']);?> />
			<label for="<?php echo $this->get_field_id('hierarchy')?>"><?php _e('Show hierarchy', 'cvm_video');?></label>
		</p>	
		<?php 		
	}
	
	/**
	 * Default widget values
	 */
	private function get_defaults(){
		$defaults = array(
			'title' 			=> '',
			'post_count'		=> false,
			'hierarchy'			=> false
		);
		return $defaults;
	}
}