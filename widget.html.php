<form method="post" action="#benchmark-email-lite-<?php echo $data['widgetid']; ?>">
<input type="hidden" name="formid" value="benchmark-email-lite-<?php echo $data['widgetid']; ?>" />
<input type="hidden" name="subscribe_key" value="<?php echo $data['widgetid']; ?>" />
<?php echo $data['description']; ?>
<ul style="list-style-type:none;margin:0;">
	<?php if ($data['showname']) { ?>
	<li>
		<label for="subscribe_first-<?php echo $data['widgetid']; ?>" style="display:block;"><?php echo __('First Name'); ?></label>
		<input type="text" maxlength="100" id="subscribe_first-<?php echo $data['widgetid']; ?>" name="subscribe_first[<?php echo $data['widgetid']; ?>]" value="<?php echo $data['first']; ?>" />
	</li>
	<li>
		<label for="subscribe_last-<?php echo $data['widgetid']; ?>" style="display:block;"><?php echo __('Last Name'); ?></label>
		<input type="text" maxlength="100" id="subscribe_last-<?php echo $data['widgetid']; ?>" name="subscribe_last[<?php echo $data['widgetid']; ?>]" value="<?php echo $data['last']; ?>" />
	</li>
	<?php } ?>
	<li>
		<label for="subscribe_email-<?php echo $data['widgetid']; ?>" style="display:block;"><?php echo __('Email Address'); ?></label>
		<input type="text" maxlength="100" id="subscribe_email-<?php echo $data['widgetid']; ?>" name="subscribe_email[<?php echo $data['widgetid']; ?>]" value="<?php echo $data['email']; ?>" />
	</li>
	<li><input type="submit" value="<?php echo $data['button']; ?>" onclick="document.getElementById('subscribe_spinner-<?php echo $data['widgetid']; ?>').style.display='block';this.form.style.display='none';" /></li>
	<li><?php echo $data['printresponse']; ?></li>
</ul>
</form>
<p id="subscribe_spinner-<?php echo $data['widgetid']; ?>" style="display:none;text-align:center;">
	<br /><img alt="Loading" src="<?php echo plugins_url(); ?>/benchmark-email-lite/loading.gif" />
	<br /><?php echo __('Loading - Please wait'); ?>
</p>