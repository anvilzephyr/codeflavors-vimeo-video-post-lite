	<?php if( !isset( $metabox ) ):?>
	<p class="description">
		<?php _e('Import videos from Vimeo.', 'cvm_video');?><br />
		<?php _e('Enter your search criteria and submit. All found videos will be displayed and you can selectively import videos into WordPress.', 'cvm_video');?>
	</p>
	<?php endif;?>
	<form method="get" action="" id="cvm_load_feed_form">
		<input type="hidden" name="post_type" value="<?php echo $this->post_type;?>" />
		<input type="hidden" name="page" value="cvm_import" />
		<input type="hidden" name="cvm_source" value="vimeo" />
		<?php if( !isset( $metabox ) ):?>
		<table class="form-table">
			<tr class="cvm_feed">
				<th valign="top" scope="row">
		<?php endif;?>
					<label for="cvm_feed"><?php _e('Feed type', 'cvm_video');?>:</label>
		<?php if( !isset( $metabox ) ):?>
				</th>
				<td>
		<?php endif;?>		
					<?php 
						$args = array(
							'options' => array(
								'search' => array(
									'text' => __('Search videos on Vimeo', 'cvm_video'),
									'title' => __('Enter Vimeo search query', 'cvm_video'),
								),
								'album' => array(
									'text' => __('Load Vimeo album', 'cvm_video'),
									'title' => __('Enter Vimeo album ID', 'cvm_video'),
								), 
								'channel' => array(
									'text' => __('Load Vimeo channel', 'cvm_video'),
									'title' => __('Enter Vimeo channel', 'cvm_video'),
								),
								'user' => array(
									'text' => __('User uploads', 'cvm_video'),
									'title' => __('Enter Vimeo user', 'cvm_video'),
								),
								'group' => array(
									'text' => __('Group videos', 'cvm_video'),
									'title' => __('Enter Vimeo group', 'cvm_video'),
								),
								'category' => array(
									'text' => __('Vimeo category', 'cvm_video'),
									'title' => __('Enter Vimeo category', 'cvm_video'),
								),
							),
							'name' 		=> 'cvm_feed',
							'id' 		=> 'cvm_feed',
							'selected' 	=> isset( $_GET['cvm_feed'] ) ? $_GET['cvm_feed'] : false
						);
						cvm_select($args);
					?>
			<?php if( !isset( $metabox ) ):?>		
					<span class="description"><?php _e('Select the type of feed you want to load.', 'cvm_video');?></span>									
				</td>
			</tr>
			
			<tr class="cvm_query">
				<th valign="top" scope="row">
			<?php endif;?>	
					<label for="cvm_query"><?php _e('Vimeo search query', 'cvm_video');?>:</label>
			<?php if( !isset( $metabox ) ):?>	
				</th>
				<td>
			<?php endif;?>	
					<input type="text" name="cvm_query" id="cvm_query" value="<?php echo  isset( $_GET['cvm_query'] ) ? $_GET['cvm_query'] : '';?>" />
			<?php if( !isset( $metabox ) ):?>		
					<span class="description"><?php _e('Enter search query, user ID, group ID, channel ID or album ID according to Feed Type selection.', 'cvm_video');?></span>
				</td>
			</tr>
			
			<tr class="cvm_order">
				<th valign="top" scope="row">
			<?php endif;?>
			
			<?php if( isset( $metabox ) ):?>
			<span class="cvm_order">
			<?php endif;?>
				
					<label for="cvm_order"><?php _e('Order by', 'cvm_video');?>:</label>
			<?php if( !isset( $metabox ) ):?>		
					</th>
				<td>
			<?php endif;?>	
					<?php 
						$args = array(
							'options' => array(
								'new' => __('Newest videos first', 'cvm_video'),
								'old' => __('Oldest videos first', 'cvm_video'),
								'played' => __('Most played', 'cvm_video'),
								'likes' => __('Most liked', 'cvm_video'),
								'comments' => __('Most commented', 'cvm_video'),
								'relevant' => __('Relevancy', 'cvm_video')
							),
							'name' 		=> 'cvm_order',
							'id'		=> 'cvm_order',
							'selected' 	=> isset( $_GET['cvm_order'] ) ? $_GET['cvm_order'] : false
						);
						cvm_select( $args );
					?>
			
			<?php if( isset( $metabox ) ):?>
			</span>
			<?php endif;?>		
					
			<?php if( !isset( $metabox ) ):?>							
				</td>
			</tr>
			<?php endif;?>
		
			<!-- 
			<tr>
				<td valign="top"><label for=""></label></td>
				<td></td>
			</tr>
			-->	
		
		<?php if( !isset($metabox) ):?>					
		</table>
		<?php endif;?>
		<?php wp_nonce_field('cvm-video-import', 'cvm_search_nonce', false);?>
		<?php
			$type = isset( $metabox ) ? 'secondary' : 'primary'; 
			submit_button( __('Load feed', 'cvm_video'), $type, 'submit', !isset($metabox) );
		?>
	</form>