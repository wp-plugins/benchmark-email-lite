<form method="post" action="#benchmark-email-lite-<?php echo $widgetid; ?>">
<?php echo $description; ?>
<ul style="list-style-type:none;margin:0;">
	<?php if ($showname) { ?>
	<li>
		<label for="subscribe_first-<?php echo $widgetid; ?>" style="display:block;"><?php echo __('First Name', 'benchmark-email-lite'); ?></label>
		<input type="text" maxlength="100" id="subscribe_first-<?php echo $widgetid; ?>" name="subscribe_first[<?php echo $widgetid; ?>]" value="<?php echo $first; ?>" />
	</li>
	<li>
		<label for="subscribe_last-<?php echo $widgetid; ?>" style="display:block;"><?php echo __('Last Name', 'benchmark-email-lite'); ?></label>
		<input type="text" maxlength="100" id="subscribe_last-<?php echo $widgetid; ?>" name="subscribe_last[<?php echo $widgetid; ?>]" value="<?php echo $last; ?>" />
	</li>
	<?php } ?>
	<li>
		<label for="subscribe_email-<?php echo $widgetid; ?>" style="display:block;"><?php echo __('Email Address', 'benchmark-email-lite'); ?></label>
		<input type="text" maxlength="100" id="subscribe_email-<?php echo $widgetid; ?>" name="subscribe_email[<?php echo $widgetid; ?>]" value="<?php echo $email; ?>" />
	</li>
	<li>
		<input type="hidden" name="formid" value="benchmark-email-lite-<?php echo $widgetid; ?>" />
		<input type="hidden" name="subscribe_key" value="<?php echo $widgetid; ?>" />
		<input type="submit" value="<?php echo $instance['button']; ?>" onclick="document.getElementById('subscribe_spinner-<?php echo $widgetid; ?>').style.display='block';this.form.style.display='none';" />
	</li>
	<li><?php echo $printresponse; ?></li>
</ul>
</form>
<p id="subscribe_spinner-<?php echo $widgetid; ?>" style="display:none;text-align:center;">
	<br /><img alt="Loading" src="<?php echo plugins_url(); ?>/benchmark-email-lite/loading.gif" />
	<br /><?php echo __('Loading - Please wait', 'benchmark-email-lite'); ?>
</p>