$(document).ready(function() {
	$('.backlight tr').on('click', function() {
		if ($(this).hasClass('active')) {
			$(this).removeClass('active');
		} else {
			$(this).addClass('active');
		}
	});
});