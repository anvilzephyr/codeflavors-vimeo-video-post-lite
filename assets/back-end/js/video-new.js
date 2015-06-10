/**
 * Add new video step 1 functionality
 */

;(function($){
	$(document).ready(function(){
		
		// toggle explanation
		$('#cvm_explain').click(function(e){
			e.preventDefault();
			$('#cvm_explain_output').toggle();
		})		
	})
})(jQuery);