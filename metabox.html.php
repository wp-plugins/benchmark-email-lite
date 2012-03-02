<p>
	<label for="bmelist"><?php echo __('Select a list for this campaign', 'benchmark-email-lite'); ?></label><br />
	<select style="width:100%;" name="bmelist" id="bmelist"><?php echo $dropdown; ?></select>
</p>
<p>
	<label for="bmetitle"><?php echo __('Email name', 'benchmark-email-lite'); ?></label><br />
	<input style="width:100%;" type="text" id="bmetitle" name="bmetitle" value="<?php echo $title; ?>" /><br />
	<small><?php echo __('For your personal use (not displayed in your emails).', 'benchmark-email-lite'); ?></small>
</p>
<p>
	<label for="bmefrom"><?php echo __('From name', 'benchmark-email-lite'); ?></label><br />
	<input style="width:100%;" type="text" id="bmefrom" name="bmefrom" value="<?php echo $from; ?>" /><br />
	<small><?php echo __('Use something they\'ll instantly recognize, like your company name.', 'benchmark-email-lite'); ?></small>
</p>
<p>
	<label for="bmesubject"><?php echo __('Subject', 'benchmark-email-lite'); ?></label><br />
	<input style="width:100%;" type="text" id="bmesubject" name="bmesubject" value="<?php echo $subject; ?>" /><br />
	<small><?php echo __('Interesting, non-spammy subject lines help your open rates.', 'benchmark-email-lite'); ?></small>
</p>
<p><strong>Email Delivery Options</strong></p>
<p>
	<input type="radio" name="bmeaction" value="1" id="bmeaction_1" checked="checked" />
	<label for="bmeaction_1"><?php echo __('Send a test email', 'benchmark-email-lite'); ?></label>
</p>
<p>
	<label for="bmetestto"><?php echo __('Test email address', 'benchmark-email-lite'); ?></label><br />
	<input style="width:100%;" type="text" maxlength="75" id="bmetestto" name="bmetestto" value="<?php echo $email; ?>" /><br />
	<small><?php echo __('Send a test version of your email.', 'benchmark-email-lite'); ?></small>
</p>
<p>
	<input type="radio" name="bmeaction" value="2" id="bmeaction_2" />
	<label for="bmeaction_2"><?php echo __('Send immediately', 'benchmark-email-lite'); ?></label><br />
	<small><?php echo __('Your email will be sent within the next 15 minutes.', 'benchmark-email-lite'); ?></small>
</p>
<p>
	<input type="radio" name="bmeaction" value="3" id="bmeaction_3" />
	<label for="bmeaction_3"><?php echo __('Schedule delivery', 'benchmark-email-lite'); ?></label>
</p>
<p>
	<select name="bmedate">
	<?php
	for ($i = 0; $i <= 30; $i++) {
		$inc = $localtime + (86400 * $i);
		echo '<option value="' . date('d M Y', $inc) . '">' . date('M d Y - D', $inc) . '</option>';
	}
	?>
	</select> @
	<select name="bmetime">
	<?php
	for ($i = 0; $i <= 95; $i++) {
		$inc = $localtime_quarterhour + (900 * $i);
		echo '<option value="' . date('H:i', $inc) . '">' . date('H:i', $inc) . '</option>';
	}
	?>
	</select>
	<a href="options-general.php" title="<?php echo __('Set your WordPress timezone', 'benchmark-email-lite'); ?>">
	<?php echo $localtime_zone; ?></a>
</p>
<p><?php echo __('Need help? Please call Benchmark Email at 800.430.4095.', 'benchmark-email-lite'); ?></p>
<input type="hidden" name="bmesubmit" id="bmesubmit" value="" />
<input style="float:right;min-width:80px;font-weight:bold;" id="bmesubmitbtn"
	type="submit" class="button-primary" value="Send" /><br />
<div class="clear"> </div>
<script type="text/javascript">
jQuery('#bmesubmitbtn').click(function(e) {
	e.preventDefault();
	if (jQuery('#bmetitle').val() == '') {
		jQuery('#bmetitle').focus();
		jQuery('#bmetitle').css('border', '2px solid red');
		alert('<?php echo __('Please enter an email name.', 'benchmark-email-lite'); ?>');
	} else if (jQuery('#bmefrom').val() == '') {
		jQuery('#bmetitle').css('border', '0');
		jQuery('#bmefrom').focus();
		jQuery('#bmefrom').css('border', '2px solid red');
		alert('<?php echo __('Please enter a from name.', 'benchmark-email-lite'); ?>');
	} else if (jQuery('#bmesubject').val() == '') {
		jQuery('#bmetitle').css('border', '0');
		jQuery('#bmefrom').css('border', '0');
		jQuery('#bmesubject').focus();
		jQuery('#bmesubject').css('border', '2px solid red');
		alert('<?php echo __('Please enter a subject.', 'benchmark-email-lite'); ?>');
	} else if (jQuery('#bmetestto').val() == '' && jQuery('#bmeaction_1').is(':checked')) {
		jQuery('#bmetitle').css('border', '0');
		jQuery('#bmefrom').css('border', '0');
		jQuery('#bmesubject').css('border', '0');
		jQuery('#bmetestto').focus();
		jQuery('#bmetestto').css('border', '2px solid red');
		alert('<?php echo __('Please enter a test email address.', 'benchmark-email-lite'); ?>');
	} else {
		jQuery('#bmetitle').css('border', '0');
		jQuery('#bmefrom').css('border', '0');
		jQuery('#bmesubject').css('border', '0');
		jQuery('#bmetestto').css('border', '0');
		jQuery('#bmesubmit').val('yes');
		jQuery('#bmesubmitbtn').attr('disabled', 'disabled');
		if (document.getElementById('save-post')) { jQuery('#save-post').click(); }
		else { jQuery('#publish').click(); }
	}
});
</script>
