<div class="wrap">
	<div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
	<h2><?php echo $title;?> - <?php _e('step 1', 'cvm_video');?></h2>
	<form method="post" action="" >
		<?php wp_nonce_field('cvm_query_new_video', 'wp_nonce');?>
		
		<p><?php _e('Please enter the video ID you want to search for:', 'cvm_video');?></p>
		<input type="text" name="cvm_video_id" value="" />
		<a href="#" id="cvm_explain"><?php _e('how to get video ID', 'cvm_video');?></a>
		<?php if( $theme_supported = cvm_check_theme_support() ):?>
		<br />
		<input type="checkbox" disabled="disabled" />
		<label for="single_theme_import"><?php printf( __('Import as <strong>%s</strong> post<sup>PRO</sup> (PRO version can import videos directly as posts into your theme)', 'cvm_video'), $theme_supported['theme_name'] );?></label>
		<?php endif;?>
		<p class="hidden" id="cvm_explain_output">
			<?php _e('<strong>Step 1</strong> - open any Vimeo video page with your favourite browser.', 'cvm_video');?><br />
			<?php _e('<strong>Step 2</strong> - From your browser address bar copy the ID (highlighted in image below).', 'cvm_video');?><br />
			<img vspace="10" src="<?php echo CVM_URL;?>assets/back-end/images/vimeo-video-id-example.png" /><br />
			<?php _e('<strong>Step 3</strong> - paste the ID into the field above and hit Search video below.', 'cvm_video');?>
		</p>
		
		<input type="hidden" name="cvm_source" value="vimeo" />
		<?php submit_button(__('Search video', 'cvm_video'));?>
	</form>
</div>