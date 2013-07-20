/**
 * Playlist creation/editing script 
 */
;(function($){
	
	$(document).ready(function(){
		
		var submitted 	= false,
			om			= $('#cvm_check_playlist').html(),
			message 	= $('#cvm_check_playlist');
		
		$('#playlist_id').keydown(function(){
			$(message).html(om);
		});
		
		$('#cvm_verify_playlist').click(function(e){
			e.preventDefault();
			$(this).addClass('loading');
			
			if( submitted ){
				return;
			}
			submitted = true;
			$(message).html('&nbsp;');
			
			var playlist_id 	= $('#playlist_id').val(),
				playlist_type 	= $('#playlist_type').val();
			
			var data = {
				'action' 	: 'cvm_check_playlist',
				'id'		: playlist_id,
				'type'		: playlist_type
			};
			
			$.ajax({
				type 	: 'post',
				url 	: ajaxurl,
				data	: data,
				success	: function( response ){
					$(message).html( response );
					submitted = false;
				}
			});			
		});
		
		var checkbox = $('#theme_import');
		if( 0 == checkbox.length ){
			return;
		}
		
		$(checkbox).click(function(){
			if( $(this).is(':checked') ){
				$('#native_tax_row').hide();
				$('#theme_tax_row').show();
			}else{
				$('#native_tax_row').show();
				$('#theme_tax_row').hide();
			}			
		});
		
	});	
	
})(jQuery);