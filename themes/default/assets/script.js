/**
 * Theme Default
 */
;(function($){	
	$(document).ready(function(){		
		$('.cvm-vim-playlist.default').CVM_Player_Default();
		
		$.each( $('.cvm-vim-playlist.default'), function(i, p){
			$(this).find('.playlist-visibility').click(function(e){
				e.preventDefault();
				if( $(this).hasClass('collapse') ){
					$(this).removeClass('collapse').addClass('expand');
					$(p).find('.cvm-playlist').slideUp();
				}else{
					$(this).removeClass('expand').addClass('collapse');
					$(p).find('.cvm-playlist').slideDown();
				}
			})
		});
	});	
})(jQuery);