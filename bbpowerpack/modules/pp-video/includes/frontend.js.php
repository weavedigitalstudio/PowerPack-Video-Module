;(function($) {
	
	pp_video_<?php echo $id; ?> = new PPVideo({
		id: '<?php echo $id; ?>',
		type: '<?php echo $settings->video_type; ?>',
		autoplay: <?php echo 'yes' == $settings->autoplay ? 'true' : 'false'; ?>,
		mute: <?php echo 'yes' == $settings->mute ? 'true' : 'false'; ?>,
		loop: <?php echo 'yes' == $settings->loop ? 'true' : 'false'; ?>,
		controls: <?php echo 'yes' == $settings->controls ? 'true' : 'false'; ?>,
		modestbranding: <?php echo 'yes' == $settings->modestbranding ? 'true' : 'false'; ?>,
		yt_privacy: <?php echo 'yes' == $settings->yt_privacy ? 'true' : 'false'; ?>,
		rel: '<?php echo $settings->rel; ?>',
		startTime: '<?php echo $settings->start_time; ?>',
		endTime: '<?php echo $settings->end_time; ?>',
		aspectRatio: '<?php echo $settings->aspect_ratio; ?>',
		aspectRatioLightbox: '<?php echo isset( $settings->aspect_ratio_lightbox ) && 'default' !== $settings->aspect_ratio_lightbox ? $settings->aspect_ratio_lightbox : $settings->aspect_ratio; ?>',
		lightbox: <?php echo $module->has_lightbox() ? 'true' : 'false'; ?>,
		overlay: <?php echo $module->has_image_overlay() ? 'true' : 'false'; ?>,
		triggerSelector: '<?php echo isset( $settings->custom_trigger_selector ) ? $settings->custom_trigger_selector : ''; ?>',
	});

})(jQuery);
