<p>
	<a href="<?php echo $url; ?>" title="<?php echo __('Back to Email Summary', 'benchmark-email-lite'); ?>">
	<?php echo __('Back to Email Summary', 'benchmark-email-lite'); ?></a>
</p>
<h3><?php echo $title; ?></h3>
<p>
	<strong><?php echo __('Email name', 'benchmark-email-lite'); ?>:</strong>
	<?php echo $response['emailName']; ?><br />
	<strong><?php echo __('Subject', 'benchmark-email-lite'); ?>:</strong>
	<?php echo $response['subject']; ?>
</p>
<?php benchmarkemaillite::maketable( $data ); ?>