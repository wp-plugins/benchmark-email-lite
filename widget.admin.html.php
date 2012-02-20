<script type='text/javascript'>jQuery('[id$=<?php echo esc_attr($instance['id']); ?>]').find('.in-widget-title').html(': <?php echo esc_attr($instance['title']); ?>');</script>
<p>
	<?php echo __('Benchmark Email contact list name', 'benchmark-email-lite'); ?>:
	<select name="<?php echo $this->get_field_name('list'); ?>"><?php echo $dropdown; ?></select>
</p>
<p>
	<?php echo __('Widget title', 'benchmark-email-lite'); ?>:
	<input class="widefat" name="<?php echo $this->get_field_name('title'); ?>" type="text"
		value="<?php echo esc_attr($instance['title']); ?>" />
</p>
<p>
	<?php echo __('Optional text to display to your readers', 'benchmark-email-lite'); ?>:
	<textarea class="widefat" cols="20" rows="3" name="<?php echo $this->get_field_name('description'); ?>"><?php echo esc_html($instance['description']); ?></textarea><br />
	<input type="checkbox" id="<?php echo $this->get_field_name('filter'); ?>" name="<?php echo $this->get_field_name('filter'); ?>" value="1" <?php checked($instance['filter'], 1); ?> />
	<label for="<?php echo $this->get_field_name('filter'); ?>"><?php echo __('Automatically add paragraphs', 'benchmark-email-lite'); ?></label>
</p>
<p>
	<input type="checkbox" id="<?php echo $this->get_field_name('showname'); ?>" name="<?php echo $this->get_field_name('showname'); ?>" value="1" <?php checked($instance['showname'], 1); ?> />
	<label for="<?php echo $this->get_field_name('showname'); ?>"><?php echo __('Display first and last name fields', 'benchmark-email-lite'); ?></label>
</p>
<p>
	<?php echo __('Limit to page', 'benchmark-email-lite'); ?>:
	<?php wp_dropdown_pages(array('depth' => 0, 'child_of' => 0,
		'selected' => esc_attr($instance['page']), 'echo' => 1, 'name' => $this->get_field_name('page'),
		'show_option_none' => '- ' . __('Show Everywhere', 'benchmark-email-lite') . ' -')); ?>
</p>
<p>
	<?php echo __('Subscribe button title', 'benchmark-email-lite'); ?>:
	<input class="widefat" name="<?php echo $this->get_field_name('button'); ?>" type="text"
		value="<?php echo esc_attr($instance['button']); ?>" />
</p>
