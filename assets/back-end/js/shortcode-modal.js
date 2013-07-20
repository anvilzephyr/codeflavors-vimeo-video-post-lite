/**
 * TinyMce playlist shortcode insert 
 */
var CVMVideo_DIALOG_WIN = false;
;(function($){
	$(document).ready(function(){
		$('#cvm-insert-playlist-shortcode').live('click', function(e){
			e.preventDefault();
			var videos 		= $('#cvm-playlist-items').find('input[name=cvm_selected_items]').val();
			if( '' == videos ){
				$('#cvm-list-items').addClass('error');
				return;
			}
			
			var theme = $('#cvm_playlist_theme').val(),
				aspect = $('#aspect_ratio').val(),
				width = $('#width').val(),
				volume = $('#volume').val();
			
			var videos_array = $.grep( videos.split('|'), function(val){ return '' != val }),
				shortcode 	= '[cvm_playlist theme="' + theme + '" aspect_ratio="' + aspect + '" width="' + width + '" volume="' + volume + '" videos="'+( videos_array.join(',') )+'"]';;
			
			$('#cvm-playlist-items').find('input[name=cvm_selected_items]').val('');
			$('#cvm-list-items').empty().html(CVM_SHORTCODE_MODAL.no_videos);
			
			var iframe = $('#cvm-display-videos').find('iframe');
			$('input[type=checkbox]', iframe.contents()).removeAttr('checked');
			
			
			send_to_editor(shortcode);
			$(CVMVideo_DIALOG_WIN).dialog('close');
		});
		
		$('#cvm-shortcode-2-post').click(function(e){
			e.preventDefault();
			if( CVMVideo_DIALOG_WIN ){
				CVMVideo_DIALOG_WIN.dialog('open');
			}
		});
		
		var dialog = $('#CVMVideo_Modal_Window').dialog({
			'autoOpen'		: false,
			'width'			: '90%',
			'height'		: 750,
			'maxWidth'		: '90%',
			'maxHeight'		: 750,
			'minWidth'		: '90%',
			'minHeight'		: 750,
			'modal'			: true,
			'dialogClass'	: 'wp-dialog',
			'title'			: '',
			'resizable'		: true,
			'open'			:function(ui){
				
			},
			'close':function(ui){
				
			}
		})		
		CVMVideo_DIALOG_WIN = dialog;		
	});
})(jQuery);