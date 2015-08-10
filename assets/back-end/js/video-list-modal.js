/**
 * Modal window for videos list functionality
 */
;(function($){
	$(document).ready(function(){
		
		// check all functionality
		var chkbxs = $('#cb-select-all-1, #cb-select-all-2, .cvm-video-list-select-all');		
		$(chkbxs).click(function(){
			if( $(this).is(':checked') ){
				$('.cvm-video-checkboxes').attr('checked', 'checked').trigger('change');
				$(chkbxs).attr('checked', 'checked');
			}else{
				$('.cvm-video-checkboxes').removeAttr('checked').trigger('change');
				$(chkbxs).removeAttr('checked');
			}
		});
		
		// some elements
		var playlistItemsContainer 	= window.parent.jQuery('#cvm-list-items'),
			m						= window.parent.CVM_SHORTCODE_MODAL,
			inputField				= $( window.parent.jQuery('#cvm-playlist-items') ).find('input[name=cvm_selected_items]'),
			in_playlist				= $.grep( $(inputField).val().split('|'), function(val){ return '' != val });
			
		
		// check boxes on load
		if(in_playlist.length > 0){
			$.each( in_playlist, function(i, post_id){
				$('#cvm-video-'+post_id).attr('checked', 'checked');
			});
		}
		
		// checkboxes functionality
		$('.cvm-video-checkboxes').change( function(){
			var post_id = $(this).val();			
			if( $(this).is(':checked') ){				
				if( in_playlist.length == 0 ){
					$(playlistItemsContainer).empty();
				}				
				if( -1 == $.inArray( post_id, in_playlist ) ){				
					in_playlist = $.merge(in_playlist, [post_id]);				
					var c = $('<div />', {
						'class'	: 'playlist_item',
						'id' 	: 'playlist_item_'+post_id,
						'html' 	: $('#title'+post_id).html() + ' <span class="duration">[' + $('#duration'+post_id).html() + ']</span>'
					}).appendTo( playlistItemsContainer );
					
					$('<a />', {
						'id' 	: 'cvm-del-'+post_id,
						'class' : 'cvm-del-item',
						'html' 	: m.deleteItem,
						'href' 	: '#',
						'click' : function(e){
							e.preventDefault();
							$('#cvm-video-'+post_id).removeAttr('checked');
							$(c).remove();
							in_playlist = $.grep( in_playlist, function(value, i){
								return post_id != value;
							});							
							if( in_playlist.length == 0 ){
								$(playlistItemsContainer).empty().html( '<em>'+m.no_videos+'</em>' );				
							}							
							$(inputField).val( in_playlist.join('|') );
						}
					}).prependTo(c);					
				}				
			}else{
				in_playlist = $.grep( in_playlist, function(value, i){
					if( post_id == value ){
						$(playlistItemsContainer).find('div#playlist_item_'+post_id).remove();
					}					
					return post_id != value;
				})
			}			
			if( in_playlist.length == 0 ){
				$(playlistItemsContainer).empty().html( '<em>'+m.no_videos+'</em>' );				
			}
			
			$(playlistItemsContainer).removeClass('error');
			
			$(inputField).val( in_playlist.join('|') );
		});
		
		
		// single shortcode
		var form = $('#cvm-video-list-form'),
			attsContainer = $('#cvm-shortcode-atts'),
			divId = false;
		
		$('.cvm-show-form').live('click', function(e){
			e.preventDefault();
			var post_id = $(this).attr('id').replace('cvm-embed-', '');
			var shortcode = '[cvm_video id="'+post_id+'"]';
			window.parent.send_to_editor(shortcode);
			window.parent.jQuery(window.parent.window.CVMVideo_DIALOG_WIN).dialog('close');
			
		})
		
	});	
})(jQuery);
