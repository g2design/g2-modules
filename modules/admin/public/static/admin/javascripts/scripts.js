jQuery(function($) {
//	$(document).off('.datepicker.data-api');
	$('.datepicker').datepicker({
//            changeMonth: true,
//            changeYear: true,
//            showButtonPanel: true,
		format: 'd MM yyyy'
	});
	$('.datum').datepicker
	$(window).load(function() {
		$('.field_error').animate({
			backgroundColor: "red"
		});
	});

	$('.editor').summernote({
		height: 150, //set editable area's height
		codemirror: {// codemirror options
			theme: 'monokai',
		}
	});

});
