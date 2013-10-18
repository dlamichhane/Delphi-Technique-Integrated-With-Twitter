$(document).ready(function() {
	$("#question").keyup(function() {
		var text_length = $(this).val().length;
		$("#text_length").text(140 - text_length);
	});
});

