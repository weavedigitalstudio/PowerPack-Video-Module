<?php
// Video type.
$video_type = $settings->video_type;

// Video URL.
$video_url = $module->get_video_url();

if ( empty( $video_url ) ) {
	return;
}

// Video HTML.
$video_html    = $module->get_video_html();
// Video thumbnail.
$thumbnail_url = $module->get_overlay_image_url( $video_url );

$has_remote_thumbnail = ! $module->has_image_overlay() && ! empty( $thumbnail_url );

// Do not render anything if there is no video HTML.
if ( empty( $video_html ) ) {
	echo esc_url( $video_url );

	return;
}

// Lightbox - enabled or not.
$lightbox = $module->has_lightbox();

// CSS classes for wrapper element.
$wrapper_classes = array(
	'pp-video-wrapper',
	'pp-video-source-' . $settings->video_type,
	'pp-aspect-ratio-' . $settings->aspect_ratio,
);

// Attributes for image overlay.
$overlay_attrs = array();

// Structured data.
$schema = $module->get_structured_data( $settings );

$module->before_render();
?>

<div class="<?php echo implode( ' ', $wrapper_classes ); ?>"<?php echo $schema ? ' itemscope itemtype="https://schema.org/VideoObject"' : ''; ?>>
	<?php
		if ( $schema ) {
			echo $schema;
		}
	?>
	<div class="pp-fit-aspect-ratio">
	<?php
	if ( ! $lightbox ) {
		echo $video_html; // XSS ok.
	}

	if ( ! empty( $thumbnail_url ) || $lightbox ) {
		$overlay_attrs['class'] = 'pp-video-image-overlay';
		$overlay_attrs['class'] .= $has_remote_thumbnail ? ' pp-video-thumbnail-default' : '';

		if ( $lightbox ) {
			$wrapper_classes[] = 'pp-video-has-lightbox';
		} else {
			$overlay_attrs['style'] = 'background-image: url(' . $thumbnail_url . ');';
		}
		?>
		<div <?php echo $module->render_html_attributes( $overlay_attrs ); ?>>
			<?php if ( $lightbox ) {
				echo $module->get_overlay_image( $video_url );
			} ?>
			<?php if ( 'show' === $settings->play_icon || $has_remote_thumbnail ) { ?>
				<div class="pp-video-play-icon<?php echo 'show' !== $settings->play_icon ? ' play-icon-default' : ''; ?>" role="button" tabindex="0">
					<?php echo apply_filters( 'pp_video_play_button_html', file_get_contents( BB_POWERPACK_DIR . 'modules/pp-video/play-button.svg' ), $settings ); ?>
					<span class="pp-screen-only"><?php _e( 'Play Video', 'bb-powerpack' ); ?></span>
				</div>
			<?php } ?>
		</div>
	<?php } ?>
	</div>
</div>
<?php if ( $lightbox ) { ?>
	<script type="text/html" class="pp-video-lightbox-content">
		<div class="pp-video-container">
			<div class="pp-fit-aspect-ratio">
			<?php echo $video_html; ?>
			</div>
		</div>
	</script>
<?php } ?>

<?php $module->after_render(); ?>