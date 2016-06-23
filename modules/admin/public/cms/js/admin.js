$(function(){
	$('.t-center').hide();
	$('[data-toggle]').click(function(){
		var toggle = $(this).data('toggle');
		$('.t-center').show();
		$('.'+toggle).show();
	});
	$('.btn-cancel').click(function(e){
//		e.preventDefault();
		$(this).parents('.t-center').hide();
		$(this).parents('.toggle').hide();
	});
	
	
	$(document).delegate('[name="slug"]','keyup',function(){
		var value = $(this).val();
		value = value.toLowerCase();
		value = value.replace(' ', "-");
		value = value.replace('&', "and");
		value = value.replace('@', "at");
		
		$(this).val(value);
//		convert to lowercase
	});
	$(['[name="slug"]']).click(function(){
		
//		
		
	});
	
	$('.mvc-admin-bar .top-bar').hide();
	$('.mvc-admin-bar .button-show').click(function(ev){
		ev.preventDefault();
		$(this).parent().find('.top-bar').toggleClass('show');
	});
});