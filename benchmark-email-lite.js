$(document).ready(function() {
	$('.benchmark-email-lite').ajaxForm(function(data) {
		data = data.split('|');
		document.getElementById('subscribe_first-' + data[0]).value = '';
		document.getElementById('subscribe_last-' + data[0]).value = '';
		document.getElementById('subscribe_email-' + data[0]).value = '';
		switch (data[1]) {
			case '0':
				document.getElementById('subscribe_fail-' + data[0]).innerHTML = data[2];
				$('#subscribe_pass-' + data[0]).fadeOut('fast');
				$('#subscribe_fail-' + data[0]).fadeIn('slow');
				break;
			case '1':
				document.getElementById('subscribe_pass-' + data[0]).innerHTML = data[2];
				$('#subscribe_fail-' + data[0]).fadeOut('fast');
				$('#subscribe_pass-' + data[0]).fadeIn('slow');
				break;
		}
	});
});