/**
 * Video import form functionality
 * @version 1.0
 */
;(function($){
	$(document).ready(function(){
		
		var sort_row = $('#cvm_load_feed_form').find('tr.cvm_order, span.cvm_order');
		
		// search criteria form functionality
		$('#cvm_feed').change(function(){
			
			var val = $(this).val(),
				ordVal = $('#cvm_order').val();
			
			$('label[for=cvm_query]').html($(this).find('option:selected').attr('title')+' :');
			
			switch( val ){
				// no sorting available
				case 'album':
				case 'channel':
				case 'category':
					$(sort_row).hide();
				break;	
				
				case 'search':
					$(sort_row).show();
					$('#cvm_order option[value=relevant]').removeAttr('disabled');
				break;
				
				default:
					$(sort_row).show();
					$('#cvm_order option[value=relevant]').attr({'disabled' : 'disabled'});
				break;				
			}			
		}).trigger('change');
		
		$('#cvm_load_feed_form').submit(function(e){
			var s = $('#cvm_query').val();
			if( '' == s ){
				e.preventDefault();
				$('#cvm_query, label[for=cvm_query]').addClass('cvm_error');
			}
		});
		$('#cvm_query').keyup(function(){
			var s = $(this).val();
			if( '' == s ){
				$('#cvm_query, label[for=cvm_query]').addClass('cvm_error');
			}else{
				$('#cvm_query, label[for=cvm_query]').removeClass('cvm_error');
			}	
		})
		
		/**
		 * Feed results table functionality
		 */		
		// rename table action from action (which conflicts with ajax) to action_top
		$('.ajax-submit .tablenav.top .actions select[name=action]').attr({'name' : 'action_top'});		
		// form submit on search results
		var submitted = false;
		$('.ajax-submit').submit(function(e){
			e.preventDefault();
			if( submitted ){
				$('.cvm-ajax-response')
					.html(cvm_importMessages.wait);
				return;
			}
			
			var dataString 	= $(this).serialize();
			submitted = true;
			
			$('.cvm-ajax-response')
				.removeClass('success error')
				.addClass('loading')
				.html(cvm_importMessages.loading);
			
			$.ajax({
				type 	: 'post',
				url 	: ajaxurl,
				data	: dataString,
				dataType: 'json',
				success	: function(response){
					if( response.success ){
						$('.cvm-ajax-response')
							.removeClass('loading error')
							.addClass('success')
							.html( response.success );
					}
					if( response.error ){
						$('.cvm-ajax-response')
							.removeClass('loading success')
							.addClass('error')
							.html( response.error );
					}
										
					submitted = false;
				}
			});			
		});	
		
		// ajax create new categories
		var form = $('#cvm-add-video-category');
		
		$('#cvm-toggle-new-cat').click(function(e){
			e.preventDefault();
			$('#cvm-add-video-category').toggle();
		})
		
		$('#cvm-new-category').click(function(){
			var data = {};
				elems = $(form).find('input');
				
			$.each( elems, function(){
				data[ $(this).attr('name') ] = $(this).val();
			});	
			
			submitted = true;
			
			$.ajax({
				type 	: 'post',
				url 	: ajaxurl,
				data	: data,
				dataType: 'json',
				success	: function(response){
					
					if( response.error ){
						if( response.existing ){
							$('#cvm-list-categories-checkboxes').find('input[value='+response.existing+']').attr({'checked' : 'checked'});
							$(form).find('input[name=cvm-ajax-cat-new]').val('');
							$('#cvm-add-video-category').hide();
						}						
					}else{
						$(form).find('input[name=cvm-ajax-cat-new]').val('');
						$('#cvm-add-video-category').hide();
						$('<li />', {
							'html' : response.output
						}).appendTo( $('#cvm-list-categories-checkboxes') );
					}					
					submitted = false;
				}				
			})
		});		
		
		$('#cvm_theme_import').click(function(){
			
			if( $(this).is(':checked') ){
				$('#cvm-import-new-category-metabox').hide();
				$('#cvm_theme_tax_container').show();
			}else{
				$('#cvm-import-new-category-metabox').show();
				$('#cvm_theme_tax_container').hide();
			}
			
		})
		
	})
})(jQuery);