<div class="cvm-vim-playlist default"<?php cvm_output_width();?>>
	<div class="cvm-player"<?php cvm_output_player_size();?>><?php cvm_output_player_data();?></div>
	<div class="cvm-playlist-wrap">
		<div class="cvm-playlist">
			<?php foreach( $videos as $cvm_video ): ?>
			<div class="cvm-playlist-item">
				<a href="<?php cvm_video_post_permalink();?>"<?php cvm_output_video_data();?>>
					<?php cvm_output_thumbnail();?>
					<?php cvm_output_title();?>
				</a>
			</div>
			<?php endforeach;?>
		</div>
		<a href="#" class="playlist-visibility collapse"></a>
	</div>	
</div>