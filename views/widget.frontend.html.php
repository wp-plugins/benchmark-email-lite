<?php $uniqid = uniqid(); ?>
<form method="post" action="#benchmark-email-lite-<?php echo $uniqid; ?>"
	onsubmit="return benchmarkemaillite_<?php echo $uniqid; ?>(this);">
<?php echo $description; ?>
<ul style="list-style-type:none;margin:0;">
	<?php
	foreach ($instance['fields'] as $key => $field) {
		$label = isset($instance['fields_labels'][$key]) ? $instance['fields_labels'][$key] : $field;
		switch ($field) {
			case 'Email': $value = $email; break;
			case 'First Name': $value = $first; break;
			case 'Last Name': $value = $last; break;
			default: $value = '';
		}
		$field = sanitize_title($field);
		$id = "{$field}-{$key}-{$widgetid}-{$uniqid}";
		$value = isset($_POST[$id]) ? esc_attr($_POST[$id]) : $value;
	?>
	<li>
		<label for="<?php echo $id; ?>" style="display:block;"><?php echo $label; ?></label>
		<input type="text" maxlength="200" id="<?php echo $id; ?>"
			name="<?php echo $id; ?>" value="<?php echo $value; ?>" />
	</li>
	<?php } ?>
	<li>
		<input type="hidden" name="formid" value="benchmark-email-lite-<?php echo $uniqid; ?>" />
		<input type="hidden" name="widgetid" value="<?php echo $widgetid; ?>" />
		<input type="hidden" name="uniqid" value="<?php echo $uniqid; ?>" />
		<input type="submit" value="<?php echo $instance['button']; ?>" />
	</li>
	<li><?php echo $printresponse; ?></li>
</ul>
</form>
<p id="subscribe_spinner-<?php echo $uniqid; ?>" style="display:none;text-align:center;">
	<br /><img alt="Loading" src="<?php echo plugins_url(); ?>/benchmark-email-lite/loading.gif" />
	<br /><?php echo __('Loading - Please wait', 'benchmark-email-lite'); ?>
</p>
<script type="text/javascript">
function benchmarkemaillite_<?php echo $uniqid; ?>(theForm) {
	var errors = new Array();
	<?php
	foreach ($instance['fields'] as $key => $field) {
		$label = isset($instance['fields_labels'][$key]) ? $instance['fields_labels'][$key] : $field;
		$field = sanitize_title($field);
		$id = "{$field}-{$key}-{$widgetid}-{$uniqid}";
		if (isset($instance['fields_required'][$key]) && $instance['fields_required'][$key] == '1') {
	?>
	var elem = document.getElementById('<?php echo $id; ?>');
	if (elem.value == '') { errors.push('<?php echo $label; ?>'); }
	<?php } } ?>
	if (errors.length > 0) {
		alert('<?php echo __('Please complete the field(s):', 'benchmark-email-lite'); ?>\r' + errors.join('\r'));
		return false;
	}
	document.getElementById('subscribe_spinner-<?php echo $uniqid; ?>').style.display='block';
	theForm.style.display='none';
	return true;
}
</script>