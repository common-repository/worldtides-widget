/*<![CDATA[*/
(
	function($) {
		'use strict';
		function init_basic() {
			// $('.color_picker').wpColorPicker();
			$('.color_picker').wpColorPicker({ change: function(e, ui) { $( e.target ).val( ui.color.toString() ); $( e.target ).trigger('change');} });			
		}
		$(document).ready(function($){
			init_basic();
		});
		$( document ).ajaxComplete(function() {
            init_basic();
		});
	}
)( jQuery );
/*]]>*/