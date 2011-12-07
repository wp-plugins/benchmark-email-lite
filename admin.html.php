<p>
	<a href="<?php echo self::$linkaffiliate; ?>" target="BenchmarkEmail">
	<?php echo __('Signup for a 30-day FREE Trial and support this plugin\'s development!'); ?></a>
</p>
<p>
	<?php echo __('Benchmark Email API key'); ?> (<?php echo __('may expire after one year'); ?>):
	<input class="widefat" name="<?php echo $this->get_field_name('token'); ?>" type="text"
		value="<?php echo esc_attr($instance['token']); ?>" id="<?php echo $this->get_field_name('token'); ?>" /><br />
	<span id="<?php echo $this->get_field_name('token'); ?>-response"></span>
</p>
<p>
	<a href="http://ui.benchmarkemail.com/EditSetting#_ctl0_ContentPlaceHolder1_UC_ClientSettings1_lnkGenerate" target="BenchmarkEmail">
	<?php echo __('Log in to Benchmark Email to get your API key.'); ?></a>
</p>
<p>
	<?php echo __('Benchmark Email contact list name'); ?>:
	<input class="widefat" name="<?php echo $this->get_field_name('list'); ?>" type="text"
		value="<?php echo esc_attr($instance['list']); ?>" id="<?php echo $this->get_field_name('list'); ?>" /><br />
	<span id="<?php echo $this->get_field_name('list'); ?>-response"></span>
</p>
<p>
	<a href="http://ui.benchmarkemail.com/Contacts" target="BenchmarkEmail">
	<?php echo __('Log in to Benchmark Email to view your list names.'); ?></a>
</p>
<p>
	<input type="button" class="button-secondary" value="Verify API key and list name"
		onclick="benchmarkemaillite_check('<?php echo $this->get_field_name('token'); ?>', '<?php echo $this->get_field_name('list'); ?>', '<?php echo $this->get_field_name('token'); ?>');benchmarkemaillite_check('<?php echo $this->get_field_name('list'); ?>', '<?php echo $this->get_field_name('list'); ?>', '<?php echo $this->get_field_name('token'); ?>');" />
</p>
<script type='text/javascript'>jQuery('[id$=<?php echo esc_attr($instance['id']); ?>]').find('.in-widget-title').html(': <?php echo esc_attr($instance['title']); ?>');</script>
<p>
	<?php echo __('Widget title'); ?>:
	<input class="widefat" name="<?php echo $this->get_field_name('title'); ?>" type="text"
		value="<?php echo esc_attr($instance['title']); ?>" />
</p>
<p>
	<?php echo __('Optional text to display to your readers'); ?>:
	<textarea class="widefat" cols="20" rows="3" name="<?php echo $this->get_field_name('description'); ?>"><?php echo esc_html($instance['description']); ?></textarea><br />
	<input type="checkbox" id="<?php echo $this->get_field_name('filter'); ?>" name="<?php echo $this->get_field_name('filter'); ?>" value="1" <?php checked($instance['filter'], 1); ?> />
	<label for="<?php echo $this->get_field_name('filter'); ?>"><?php echo __('Automatically add paragraphs'); ?></label>
</p>
<p>
	<input type="checkbox" id="<?php echo $this->get_field_name('showname'); ?>" name="<?php echo $this->get_field_name('showname'); ?>" value="1" <?php checked($instance['showname'], 1); ?> />
	<label for="<?php echo $this->get_field_name('showname'); ?>"><?php echo __('Display first and last name fields'); ?></label>
</p>
<p>
	<?php echo __('Limit to page'); ?>:
	<?php wp_dropdown_pages(array('depth' => 0, 'child_of' => 0,
		'selected' => esc_attr($instance['page']), 'echo' => 1, 'name' => $this->get_field_name('page'),
		'show_option_none' => '- ' . __('Show Everywhere') . ' -')); ?>
</p>
<p>
	<?php echo __('Subscribe button title'); ?>:
	<input class="widefat" name="<?php echo $this->get_field_name('button'); ?>" type="text"
		value="<?php echo esc_attr($instance['button']); ?>" />
</p>