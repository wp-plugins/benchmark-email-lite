function benchmarkemaillite_check(field, list, token) {
	var lval = document.getElementById(list).value;
	var tval = document.getElementById(token).value;
	var output = document.getElementById(field + "-response");
	jQuery(output).html('<img alt="Loading" src="images/loading.gif" />');
	var data = { action: "bmewidget", bmefield: field, bmelist: lval, bmetoken: tval }
	jQuery.post(ajaxurl, data, function(response) { jQuery(output).html(response); });
}