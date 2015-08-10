<div class="wrap">
	<div class="icon32" id="icon-options-general"><br></div>
	<h2><?php _e('Videos - Plugin settings', 'cvm_video');?></h2>
	<?php if( isset( $authorize_error ) ):?>
	<div class="error">
		<p><?php echo $authorize_error;?></p>
	</div>
	<?php endif;?>
	<div id="cvm_tabs">	
		<form method="post" action="">
			<?php wp_nonce_field('cvm-save-plugin-settings', 'cvm_wp_nonce');?>
			<ul class="cvm-tab-labels">
				<li><a href="#cvm-settings-post-options"><i class="dashicons dashicons-arrow-right"></i> <?php _e('Post options', 'cvm_video')?></a></li>
				<li><a href="#cvm-settings-content-options"><i class="dashicons dashicons-arrow-right"></i> <?php _e('Content options', 'cvm_video')?></a></li>
				<li><a href="#cvm-settings-embed-options"><i class="dashicons dashicons-arrow-right"></i> <?php _e('Embed options', 'cvm_video')?></a></li>
				<li><a href="#cvm-settings-auth-options"><i class="dashicons dashicons-arrow-right"></i> <?php _e('API access', 'cvm_video')?></a></li>
			</ul>
			
			<!-- Post options tab -->
			<div id="cvm-settings-post-options">
				<table class="form-table">
				<tbody>
					<tr><th colspan="2"><h4><i class="dashicons dashicons-admin-tools"></i> <?php _e('General settings', 'cvm_video');?></h4></th></tr>
					<tr valign="top">
						<th scope="row"><label for="public"><?php _e('Allow videos in front-end as posts', 'cvm_video')?>:</label></th>
						<td>
							<input type="checkbox" name="public" value="1" id="public"<?php cvm_check( $options['public'] );?> />
							<span class="description">
							<?php if( !$options['public'] ):?>
								<span style="color:red;"><?php _e('Videos are not available in front-end to be used as posts. You can only incorporate them in playlists or display them in regular posts.', 'cvm_video');?></span>
							<?php else:?>
							<?php _e('Videos are available in front-end as regular posts are and can also be incorporated in playlists or displayed in regular posts.', 'cvm_video');?>
							<?php endif;?>
							</span>
						</td>
					</tr>
					
					<tr valign="top">
						<th scope="row"><label for="archives"><?php _e('Allow videos in archive pages', 'cvm_video')?>:</label></th>
						<td>
							<input type="checkbox" name="archives" value="1" id="archives"<?php cvm_check( $options['archives'] );?> />
							<span class="description">
								<?php _e('When checked, videos will be visible on all pages displaying lists of video posts.', 'cvm_video');?>
							</span>
						</td>
					</tr>					
				</tbody>
				</table>
				<?php submit_button(__('Save settings', 'cvm_video'));?>
			</div>
			<!-- /Post options tab -->
			
			<!-- Content options tab -->
			<div id="cvm-settings-content-options" class="hide-if-js">
				<table class="form-table">
				<tbody>
					<tr><th colspan="2"><h4><i class="dashicons dashicons-admin-post"></i> <?php _e('Post content settings', 'cvm_video');?></h4></th></tr>
					<tr valign="top">
						<th scope="row"><label for="import_title"><?php _e('Import titles', 'cvm_video')?>:</label></th>
						<td>
							<input type="checkbox" value="1" id="import_title" name="import_title"<?php cvm_check($options['import_title']);?> />
							<span class="description"><?php _e('Automatically import video titles from feeds as post title.', 'cvm_video');?></span>
						</td>
					</tr>
					
					<tr valign="top">
						<th scope="row"><label for="import_description"><?php _e('Import descriptions as', 'cvm_video')?>:</label></th>
						<td>
							<?php 
								$args = array(
									'options' => array(
										'content' 			=> __('post content', 'cvm_video'),
										'excerpt' 			=> __('post excerpt', 'cvm_video'),
										'content_excerpt' 	=> __('post content and excerpt', 'cvm_video'),
										'none'				=> __('do not import', 'cvm_video')
									),
									'name' => 'import_description',
									'selected' => $options['import_description']								
								);
								cvm_select($args);
							?>
							<p class="description"><?php _e('Import video description from feeds as post description, excerpt or none.')?></p>
						</td>
					</tr>
					
					<tr valign="top">
						<th scope="row"><label for="import_status"><?php _e('Import status', 'cvm_video')?>:</label></th>
						<td>
							<?php 
								$args = array(
									'options' => array(
										'publish' 	=> __('Published', 'cvm_video'),
										'draft' 	=> __('Draft', 'cvm_video'),
										'pending'	=> __('Pending', 'cvm_video')
									),
									'name' 		=> 'import_status',
									'selected' 	=> $options['import_status']
								);
								cvm_select($args);
							?>
							<p class="description"><?php _e('Bulk imported videos will have this status.', 'cvm_video');?></p>
						</td>
					</tr>
				</tbody>
				</table>
				<?php submit_button(__('Save settings', 'cvm_video'));?>
			</div>
			<!-- /Content options tab -->
			
			<!-- Embed options tab -->
			<div id="cvm-settings-embed-options" class="hide-if-js">
				<table class="form-table cvm-player-settings-options">
				<tbody>
					<tr>
						<th colspan="2">
							<h4><i class="dashicons dashicons-leftright"></i> <?php _e('Player size and position', 'cvm_video');?></h4>
							<p class="description"><?php _e('General player size settings. These settings will be applied to any new video by default and can be changed individually for every imported video.', 'cvm_video');?></p>
						</th>
					</tr>
					<tr>
						<th><label for="cvm_aspect_ratio"><?php _e('Player size', 'cvm_video');?>:</label></th>
						<td>
							<label for="cvm_aspect_ratio"><?php _e('Aspect ratio');?> :</label>
							<?php 
								$args = array(
									'options' 	=> array(
										'4x3' 	=> '4x3',
										'16x9' 	=> '16x9'
									),
									'name' 		=> 'aspect_ratio',
									'id'		=> 'cvm_aspect_ratio',
									'class'		=> 'cvm_aspect_ratio',
									'selected' 	=> $player_opt['aspect_ratio']
								);
								cvm_select( $args );
							?>
							<label for="cvm_width"><?php _e('Width', 'cvm_video');?> :</label>
							<input type="text" name="width" id="cvm_width" class="cvm_width" value="<?php echo $player_opt['width'];?>" size="2" />px
							| <?php _e('Height', 'cvm_video');?> : <span class="cvm_height" id="cvm_calc_height"><?php echo cvm_player_height( $player_opt['aspect_ratio'], $player_opt['width'] );?></span>px
						</td>
					</tr>
					
					<tr>
						<th><label for="cvm_video_position"><?php _e('Display video in custom post','cvm_video');?>:</label></th>
						<td>
							<?php 
								$args = array(
									'options' => array(
										'above-content' => __('Above post content', 'cvm_video'),
										'below-content' => __('Below post content', 'cvm_video')
									),
									'name' 		=> 'video_position',
									'id'		=> 'cvm_video_position',
									'selected' 	=> $player_opt['video_position']
								);
								cvm_select($args);
							?>
						</td>
					</tr>
					
					<tr>
						<th><label for="cvm_volume"><?php _e('Volume', 'cvm_video');?></label>:</th>
						<td>
							<input type="text" name="volume" id="cvm_volume" value="<?php echo $player_opt['volume'];?>" size="1" maxlength="3" />
							<label for="cvm_volume"><span class="description">( <?php _e('number between 0 (mute) and 100 (max)', 'cvm_video');?> )</span></label>
						</td>
					</tr>
					
					<tr valign="top">
						<th scope="row"><label for="title"><?php _e('Show video title', 'cvm_video')?>:</label></th>
						<td><input type="checkbox" value="1" id="title" name="title"<?php cvm_check( (bool )$player_opt['title'] );?> /></td>
					</tr>
					
					<tr valign="top">
						<th scope="row"><label for="byline"><?php _e('Show video uploader', 'cvm_video')?>:</label></th>
						<td><input type="checkbox" value="1" id="byline" name="byline"<?php cvm_check( (bool )$player_opt['byline'] );?> /></td>
					</tr>
					
					<tr valign="top">
						<th scope="row"><label for="portrait"><?php _e('Show video uploader image', 'cvm_video')?>:</label></th>
						<td><input type="checkbox" value="1" id="portrait" name="portrait"<?php cvm_check( (bool )$player_opt['portrait'] );?> /></td>
					</tr>
					
					<tr valign="top">
						<th scope="row"><label for="autoplay"><?php _e('Autoplay', 'cvm_video')?>:</label></th>
						<td><input type="checkbox" value="1" id="autoplay" name="autoplay"<?php cvm_check( (bool )$player_opt['autoplay'] );?> /></td>
					</tr>
					
					<tr valign="top">
						<th scope="row"><label for="fullscreen"><?php _e('Allow fullscreen', 'cvm_video')?>:</label></th>
						<td><input type="checkbox" name="fullscreen" id="fullscreen" value="1"<?php cvm_check( (bool)$player_opt['fullscreen'] );?> /></td>
					</tr>
					
					<tr valign="top">
						<th scope="row"><label for="color"><?php _e('Player color', 'cvm_video')?>:</label></th>
						<td>
							#<input type="text" name="color" id="color" value="<?php echo $player_opt['color'];?>" />
						</td>
					</tr>
				</tbody>
				</table>
				<?php submit_button(__('Save settings', 'cvm_video'));?>
			</div>
			<!-- /Embed options tab -->
			
			<!-- API auth tab -->
			<div id="cvm-settings-auth-options" class="hide-if-js">
				<table class="form-table">
				<tbody>
					<tr>
						<th colspan="2">
							<h4><i class="dashicons dashicons-admin-network"></i> <?php _e('Vimeo oAuth keys', 'cvm_video');?></h4>
							<p class="description">
								<?php _e( 'In order to be able to make requests to Vimeo API, you must first have a Vimeo account and create the credentials.', 'cvm_video');?><br />
								<?php _e( 'To register your App, please visit <a target="_blank" href="https://developer.vimeo.com/apps/new">Vimeo App registration page</a> (requires login to Vimeo).', 'cvm_video')?><br />
								<?php printf( __( 'A step by step tutorial on how to create an app on Vimeo can be found %shere%s.', 'cvm_video' ), '<a href="' . cvm_docs_link( 'plugin-settings/vimeo-oauth' ) . '" target="_blank">', '</a>');?>
							</p>
							<p class="description">
								<?php printf( __( 'Make sure that you have set up the APP Callback URL to: <br /><span class="emphasize">%s</span><br /> in your APP settings on Vimeo.' , 'cvm_video' ), $vimeo->get_redirect_url() );?>
							</p>
						</th>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="vimeo_consumer_key"><?php _e('Enter Vimeo Client Identifier', 'cvm_video')?>:</label></th>
						<td>
							<input type="text" name="vimeo_consumer_key" id="vimeo_consumer_key" value="<?php echo $options['vimeo_consumer_key'];?>" size="60" />
							<p class="description"><?php _e('You first need to create a Vimeo Account.', 'cvm_video');?></p>						
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="vimeo_secret_key"><?php _e('Enter Vimeo Client Secrets', 'cvm_video')?>:</label></th>
						<td>
							<input type="text" name="vimeo_secret_key" id="vimeo_secret_key" value="<?php echo $options['vimeo_secret_key'];?>" size="60" />
							<p class="description"><?php _e('You first need to create a Vimeo Account.', 'cvm_video');?></p>						
						</td>
					</tr>
				</tbody>
				</table>
				<?php submit_button(__('Save settings', 'cvm_video'));?>
			</div>
			<!-- /API auth tab -->
		</form>
	</div>	
</div>