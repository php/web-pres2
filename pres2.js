$(document).ready(function() {
	$("*[class^='effect']").hide();
	$("*[class^='effect-fade-out']").show();
});

function processNextEvent()
{
	var effect = $("*[class^='effect']:first");
	if (effect.length) {
		var class = effect.attr('class');
		if (class == 'effect-slide') {
			$(effect).slideDown();
			$(effect).removeClass(class);
		} else if (class == 'effect-fade-in-out') {
			$(effect).fadeIn( 250 );
			$(effect).removeClass(class);
			$(effect).addClass( 'effect-fade-out' );
		} else if (class == 'effect-fade-out') {
			$(effect).fadeOut( 250, function() { processNextEvent(); } );
			$(effect).removeClass(class);
		} else if (class == 'effect-fade-in') {
			$(effect).fadeIn( 250 );
			$(effect).removeClass(class);
		} else if (class == 'effect-hide') {
			$(effect).show();
			$(effect).removeClass(class);
		}

} else {
	document.location = '/show2.php/profiling-4dev11/3';

	}
}

function processPrevEvent()
{

	document.location = '/show2.php/profiling-4dev11/1';
}

$('*').keypress(function(event) {
	if (event.keyCode == '39') {
		processNextEvent();
		event.preventDefault();
	}
	if (event.keyCode == '37') {
		processPrevEvent();
		event.preventDefault();
	}

});
