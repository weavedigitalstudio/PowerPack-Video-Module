<?php
/**
 * THEME OVERRIDE: This file is overridden from the PowerPack plugin
 * Modified to support additional screen recording services (Zight, Komodo Decks, Supercut)
 */

/**
 * @class PPVideoModule
 */
class PPVideoModule extends FLBuilderModule {
	/**
	 * @method __construct
	 */
	public function __construct() {
		parent::__construct( array(
			'name'              => __( 'Video', 'bb-powerpack' ),
			'description'       => __( 'A module that displays a video player.', 'bb-powerpack' ),
			'group'             => pp_get_modules_group(),
			'category'		    => pp_get_modules_cat( 'media' ),
			'dir'               => BB_POWERPACK_DIR . 'modules/pp-video/',
			'url'               => BB_POWERPACK_URL . 'modules/pp-video/',
			'editor_export'     => true,
			'enabled'           => true,
			'partial_refresh'   => true,
		) );
	}

	/**
	 * Enqueue scripts.
	 */
	public function enqueue_scripts() {
		if ( FLBuilderModel::is_builder_active() || ( isset( $this->settings ) && $this->has_lightbox() ) ) {
			$this->add_css( 'pp-jquery-fancybox' );
			$this->add_js( 'pp-jquery-fancybox' );
		}
	}

	/**
	 * Whether the video module has an overlay image or not.
	 *
	 * Used to determine whether an overlay image was set for the video.
	 *
	 * @since 2.7.2
	 *
	 * @return bool Whether an image overlay was set for the video.
	 */
	public function has_image_overlay() {
		return 'custom' === $this->settings->overlay && ! empty( $this->settings->custom_overlay );
	}

	/**
	 * Whether the video module has lightbox enabled or not.
	 *
	 * @since 2.7.2
	 *
	 * @return bool Whether the lightbox was enabled for the video.
	 */
	public function has_lightbox() {
		return 'yes' === $this->settings->lightbox;
	}

	/**
	 * Retrieve the video properties for a given video URL.
	 *
	 * @since 2.7.2
	 *
	 * @param string $video_url Video URL.
	 *
	 * @return null|array The video properties, or null.
	 */
	public function get_video_properties( $video_url ) {
		$provider_regex = array(
			'youtube'     => '/^.*(?:youtu\.be\/|youtube(?:-nocookie)?\.com\/(?:(?:watch|playlist)?\?(?:.*&)?(?:list|v)?=|(?:embed|v|vi|user|list|shorts)\/))([^\?&\"\'>]+)/',
			'vimeo'       => '/^.*vimeo\.com\/(?:[a-z]*\/)*([‌​0-9]{6,11})[?]?.*/',
			'dailymotion' => '/^.*dailymotion.com\/(?:video|hub)\/([^_]+)[^#]*(#video=([^_&]+))?/',
			'wistia'      => '/^.*(?:wistia\.net|wistia\.com)\/(?:embed\/iframe|medias)\/(.*)/',
			'facebook'    => '/^.*(?:facebook\.com)\/(?:.*)\/(.*)/',
			'zight'       => '/^.*(?:share\.zight\.com|share\.weave\.nz)\/([a-zA-Z0-9_-]+)/',
			'komodo_decks' => '/^.*(?:komododecks\.com|share\.weave\.co\.nz)\/recordings\/([a-zA-Z0-9_-]+)/',
			'supercut'    => '/^.*supercut\.ai\/share\/[^\/]+\/([a-zA-Z0-9_-]+)/'
		);

		$provider_regex = apply_filters( 'pp_video_provider_regex', $provider_regex, $video_url );

		foreach ( $provider_regex as $provider => $match_mask ) {
			preg_match( $match_mask, $video_url, $matches );

			if ( $matches ) {
				return array(
					'provider' => $provider,
					'video_id' => $matches[1],
				);
			}
		}

		return null;
	}

	/**
	 * Retrieve video module embed parameters.
	 *
	 * @since 2.7.2
	 *
	 * @return array Video embed parameters.
	 */
	public function get_embed_params() {
		$settings = $this->settings;
		$params = array();
		$is_mobile = wp_is_mobile();

		if ( 'yes' === $settings->autoplay ) {
			$params['autoplay'] = '1';
		}

		$params_dictionary = array();

		if ( 'youtube' === $settings->video_type ) {
			$params_dictionary = array(
				'loop',
				'controls',
				'mute',
				'rel',
				'modestbranding',
			);

			if ( 'yes' === $settings->loop ) {
				$video_properties = $this->get_video_properties( $settings->youtube_url );

				$params['playlist'] = $video_properties['video_id'];
			}

			$params['start'] = $settings->start_time;

			$params['end'] = $settings->end_time;

			$params['wmode'] = 'opaque';
		} elseif ( 'vimeo' === $settings->video_type ) {
			$params_dictionary = array(
				'loop',
				'mute' => 'muted',
				'vimeo_title' => 'title',
				'vimeo_portrait' => 'portrait',
				'vimeo_byline' => 'byline',
			);

			$params['color'] = $settings->color;

			$params['autopause'] = '0';
		} elseif ( 'dailymotion' === $settings->video_type ) {
			$params_dictionary = array(
				'controls',
				'mute' => 'mute',
				'showinfo' => 'ui-start-screen-info',
				'logo' => 'ui-logo',
			);

			$params['ui-highlight'] = $settings->color;

			$params['start'] = $settings->start_time;

			$params['endscreen-enable'] = '0';
		} elseif ( 'wistia' === $settings->video_type ) {
			$params_dictionary = array(
				'mute' => 'muted',
			);

			$params['controlsVisibleOnLoad'] = 'yes' === $settings->controls ? 'true' : 'false';
			$params['muted'] = 'yes' === $settings->mute ? 'true' : 'false';
			$params['playerColor'] = $settings->color;
			$params['copyLinkAndThumbnailEnabled'] = 'false';

			if ( 'yes' === $settings->loop ) {
				$params['endVideoBehavior'] = 'loop';
			}

			$start_time = $settings->start_time;

			if ( ! empty( $start_time ) ) {
				if ( $start_time < 60 ) {
					$params['time'] = $start_time . 's';
				} else {
					$time = explode( ':', gmdate( 'i:s', $start_time ) );
					$params['time'] = $time[0] . 'm' . $time[1] . 's';
				}
			}
		} elseif ( 'facebook' === $settings->video_type ) {
			$params['autoplay'] = 0;
			$params['show_text'] = false;
			$params['mute'] = 0;

			if ( ! empty( $settings->start_time ) ) {
				$params['t'] = $settings->start_time;
			}
		} elseif ( 'zight' === $settings->video_type ) {
			// Zight embed parameters - keep simple for screen recordings
			$params['autoplay'] = 0;
			// Note: Zight embeds use ?embed=true which is already in the URL pattern
		} elseif ( 'komodo_decks' === $settings->video_type ) {
			// Komodo Decks embed parameters - keep simple for screen recordings
			$params['autoplay'] = 0;
			// Note: Komodo Decks embeds use ?onlyRecording=1 which is already in the URL pattern
		} elseif ( 'supercut' === $settings->video_type ) {
			// Supercut embed parameters - keep simple for screen recordings
			$params['autoplay'] = 0;
			// No additional parameters needed for Supercut embeds
		}

		foreach ( $params_dictionary as $key => $param_name ) {
			$setting_name = $param_name;

			if ( is_string( $key ) ) {
				$setting_name = $key;
			}

			$setting_value = 'yes' === $settings->{$setting_name} ? '1' : '0';

			$params[ $param_name ] = $setting_value;
		}

		// Override and force mute if autoplay is enabled.
		if ( 'yes' === $settings->autoplay && isset( $params_dictionary['mute'] ) ) {
			$params[ $params_dictionary['mute'] ] = '1';
		}

		// Extra URL parameters.
		if ( 'vimeo' === $settings->video_type ) {
			$url_frags = wp_parse_url( $settings->vimeo_url );

			// Workaround for private videos.
			if ( isset( $url_frags['path'] ) ) {
				$video_properties = $this->get_video_properties( $settings->vimeo_url );

				$h_param = str_replace( $video_properties['video_id'], '', $url_frags['path'] );
				$h_param = str_replace( '/', '', $h_param );

				$params['h'] = $h_param;
			}

			if ( isset( $url_frags['query'] ) ) {
				$url_query_args = explode( '&', $url_frags['query'] );

				foreach ( $url_query_args as $arg ) {
					$arr = explode( '=', $arg );
					if ( ! isset( $params[ $arr[0] ] ) ) {
						$params[ $arr[0] ] = $arr[1];
					}
				}
			}
		}

		return $params;
	}

	/**
	 * Get embed options for YouTube and Vimeo based on settings.
	 *
	 * @since 2.7.2
	 *
	 * @return array Embed options.
	 */
	public function get_embed_options() {
		$settings = $this->settings;
		$embed_options = array();

		if ( 'youtube' === $settings->video_type ) {
			$embed_options['privacy'] = 'yes' === $settings->yt_privacy;
		} elseif ( 'vimeo' === $settings->video_type ) {
			$embed_options['start'] = $settings->start_time;
		}

		return $embed_options;
	}

	/**
	 * Retrieve the embed URL for a given video.
	 *
	 * @since 2.7.2
	 *
	 * @param string $video_url        Video URL.
	 * @param array  $embed_url_params Optional. Embed parameters. Default is an
	 *                                 empty array.
	 * @param array  $options          Optional. Embed options. Default is an
	 *                                 empty array.
	 *
	 * @return null|array The video properties, or null.
	 */
	public function get_embed_url( $video_url, array $embed_url_params = array(), array $options = array() ) {
		$video_url = do_shortcode( $video_url );

		$video_properties = $this->get_video_properties( $video_url );

		if ( ! $video_properties ) {
			return null;
		}

		$embed_patterns = array(
			'youtube' 		=> 'https://www.youtube{NO_COOKIE}.com/embed/{VIDEO_ID}?feature=oembed',
			'vimeo' 		=> 'https://player.vimeo.com/video/{VIDEO_ID}',
			'dailymotion' 	=> 'https://dailymotion.com/embed/video/{VIDEO_ID}',
			'wistia' 		=> 'https://fast.wistia.net/embed/iframe/{VIDEO_ID}',
			'facebook' 		=> 'https://www.facebook.com/plugins/video.php?href={VIDEO_URL}',
			'zight' 		=> '{ZIGHT_BASE_URL}/{VIDEO_ID}?embed=true',
			'komodo_decks' 	=> 'https://komododecks.com/embed/recordings/{VIDEO_ID}?onlyRecording=1',
			'supercut' 		=> 'https://supercut.ai/embed/weave/{VIDEO_ID}',
		);

		$embed_patterns = apply_filters( 'pp_video_embed_patterns', $embed_patterns, $video_url, $video_properties );

		$embed_pattern = $embed_patterns[ $video_properties['provider'] ];

		$replacements = array(
			'{VIDEO_ID}' => $video_properties['video_id'],
			'{VIDEO_URL}' => $video_url,
		);

		if ( 'youtube' === $video_properties['provider'] ) {
			$replacements['{NO_COOKIE}'] = ! empty( $options['privacy'] ) ? '-nocookie' : '';
			// YouTube playlist quick workaround.
			if ( false !== strpos( $video_url, '/playlist' ) ) {
				$replacements['{VIDEO_ID}'] = 'videoseries';
				$embed_pattern .= '&list=' . $video_properties['video_id'];
			}
		} elseif ( 'vimeo' === $video_properties['provider'] ) {
			$time_text = '';

			if ( ! empty( $options['start'] ) ) {
				$embed_pattern .= '#t={TIME}';
				$time_text = date( 'H\hi\ms\s', $options['start'] );
			}

			$replacements['{TIME}'] = $time_text;
		} elseif ( 'zight' === $video_properties['provider'] ) {
			// Determine base URL based on original URL
			$zight_base_url = 'https://share.zight.com';
			if ( false !== strpos( $video_url, 'share.weave.nz' ) ) {
				$zight_base_url = 'https://share.weave.nz';
			}
			$replacements['{ZIGHT_BASE_URL}'] = $zight_base_url;
		}

		$embed_pattern = str_replace( array_keys( $replacements ), $replacements, $embed_pattern );

		$embed_url = add_query_arg( $embed_url_params, $embed_pattern );

		return apply_filters( 'pp_video_embed_url', $embed_url );
	}

	/**
	 * Get embed HTML.
	 *
	 * Retrieve the final HTML of the embedded URL.
	 *
	 * @since 2.7.2
	 *
	 * @param string $video_url        Video URL.
	 * @param array  $embed_url_params Optional. Embed parameters. Default is an
	 *                                 empty array.
	 * @param array  $options          Optional. Embed options. Default is an
	 *                                 empty array.
	 * @param array  $frame_attributes Optional. IFrame attributes. Default is an
	 *                                 empty array.
	 *
	 * @return string The embed HTML.
	 */
	public function get_embed_html( $video_url, array $embed_url_params = array(), array $options = array(), array $frame_attributes = array() ) {
		$default_frame_attributes = array(
			'class' => 'pp-video-iframe',
			'allowfullscreen',
			'allow'	=> 'autoplay',
		);

		$video_embed_url = $this->get_embed_url( $video_url, $embed_url_params, $options );
		if ( ! $video_embed_url ) {
			return null;
		}
		// if ( $this->has_lightbox() || ( ! $this->has_image_overlay() && false !== strpos( $video_url, 'facebook.com' ) ) ) {
		if ( $this->has_lightbox() ) {
			$default_frame_attributes['src'] = $video_embed_url;
		} else {
			$default_frame_attributes['data-src'] = $video_embed_url;
		}

		$frame_attributes = array_merge( $default_frame_attributes, $frame_attributes );

		$frame_attributes = apply_filters( 'pp_video_iframe_attrs', $frame_attributes, $this->settings );

		$title_attr = $this->get_title_attr_text();

		if ( ! empty( $title_attr ) ) {
			$frame_attributes['title'] = $title_attr;
		}

		$attributes_for_print = array();

		foreach ( $frame_attributes as $attribute_key => $attribute_value ) {
			$attribute_value = esc_attr( $attribute_value );

			if ( is_numeric( $attribute_key ) ) {
				$attributes_for_print[] = $attribute_value;
			} else {
				$attributes_for_print[] = sprintf( '%1$s="%2$s"', $attribute_key, $attribute_value );
			}
		}

		$attributes_for_print = implode( ' ', $attributes_for_print );

		$iframe_html = "<iframe $attributes_for_print></iframe>";

		/** This filter is documented in wp-includes/class-oembed.php */
		return apply_filters( 'oembed_result', $iframe_html, $video_url, $frame_attributes );
	}

	/**
	 * Get URL of video.
	 *
	 * @since 2.7.2
	 *
	 * @return string|bool Video URL or false.
	 */
	public function get_video_url() {
		$settings = $this->settings;
		$video_type = $settings->video_type;
		$video_url = false;

		if ( 'hosted' == $video_type || 'external' == $video_type ) {
			$video_url = $this->get_hosted_video_url();
		}
		if ( isset( $settings->{$video_type . '_url'} ) ) {
			$video_url = $settings->{$video_type . '_url'};
		}

		return apply_filters( 'pp_video_video_url', $video_url, $settings );
	}

	/**
	 * Get HTML of video to render.
	 *
	 * @since 2.7.2
	 *
	 * @return string Video HTML.
	 */
	public function get_video_html() {
		$settings = $this->settings;
		$video_url = $this->get_video_url();
		$video_html = '';

		if ( ! $video_url ) {
			return $video_html;
		}

		if ( ( 'hosted' === $settings->video_type || 'external' === $settings->video_type ) && false === strpos( $settings->class, 'video-iframe' ) ) {
			ob_start();

			$this->render_hosted_video();

			$video_html = ob_get_clean();
		} else {
			$embed_params = $this->get_embed_params();

			$embed_options = $this->get_embed_options();

			$video_html = $this->get_embed_html( $video_url, $embed_params, $embed_options );
		}

		return apply_filters( 'pp_video_video_html', $video_html, $settings );
	}

	/**
	 * Get parameters for hosted video.
	 *
	 * @since 2.7.2
	 *
	 * @return array Video parameters.
	 */
	public function get_hosted_params() {
		$settings = $this->settings;
		$video_params = array();

		foreach ( array( 'autoplay', 'loop' ) as $option_name ) {
			if ( 'yes' === $settings->{$option_name} ) {
				$video_params[ $option_name ] = '';
				if ( 'autoplay' == $option_name ) {
					$video_params['webkit-playsinline'] = '';
					$video_params['playsinline'] = '';
				}
			}
		}

		if ( 'yes' === $settings->controls ) {
			$video_params['controls'] = '';
		}

		if ( 'yes' === $settings->mute ) {
			$video_params['muted'] = 'muted';
		}

		if ( 'hide' === $settings->download_button ) {
			$video_params['controlsList'] = 'nodownload';
		}

		if ( ! empty( $settings->poster ) && isset( $settings->poster_src ) ) {
			$video_params['poster'] = $settings->poster_src;
			$video_params['preload'] = 'none';
		}

		return apply_filters( 'pp_video_hosted_params', $video_params, $settings );
	}

	/**
	 * Get URL of hosted video with time parameter.
	 *
	 * @since 2.7.2
	 *
	 * @return string Video URL.
	 */
	public function get_hosted_video_url() {
		$settings = $this->settings;

		if ( 'external' === $settings->video_type ) {
			$video_url = $settings->external_url;
		} else {
			$video_data = FLBuilderPhoto::get_attachment_data( $settings->hosted_url );
			$video_url = $video_data->url;
		}

		if ( empty( $video_url ) ) {
			return '';
		}

		if ( $settings->start_time || $settings->end_time ) {
			$video_url .= '#t=';
		}

		if ( $settings->start_time ) {
			$video_url .= $settings->start_time;
		}

		if ( $settings->end_time ) {
			$video_url .= ',' . $settings->end_time;
		}

		return $video_url;
	}

	/**
	 * Render hosted video.
	 *
	 * @since 2.7.2
	 *
	 * @return void
	 */
	public function render_hosted_video() {
		$video_url = $this->get_hosted_video_url();
		if ( empty( $video_url ) ) {
			return;
		}

		$video_params = $this->get_hosted_params();
		?>
		<video class="pp-video-player" src="<?php echo esc_url( $video_url ); ?>" <?php echo $this->render_html_attributes( $video_params ); ?>></video>
		<?php
	}

	/**
	 * Render html attributes
	 *
	 * @param array $attributes
	 *
	 * @return string
	 */
	public function render_html_attributes( array $attributes ) {
		$rendered_attributes = array();
		$title_attr = $this->get_title_attr_text();

		if ( ! empty( $title_attr ) ) {
			$attributes['title'] = $title_attr;
		}

		foreach ( $attributes as $attribute_key => $attribute_values ) {
			if ( is_array( $attribute_values ) ) {
				$attribute_values = implode( ' ', $attribute_values );
			}

			$rendered_attributes[] = sprintf( '%1$s="%2$s"', $attribute_key, esc_attr( $attribute_values ) );
		}

		return implode( ' ', $rendered_attributes );
	}

	/**
	 * Return a text for the HTML "title" attribute.
	 *
	 * @return string
	 */
	public function get_title_attr_text() {
		$text = '';

		if ( isset( $this->settings->title_attr_text ) && ! empty( $this->settings->title_attr_text ) ) {
			$text = strip_tags( $this->settings->title_attr_text );
		}

		return $text;
	}

	/**
	 * Fetch thumbnail from the video provider.
	 *
	 * @param string $video_url
	 *
	 * @return string
	 */
	public function get_remote_thumbnail( $video_url ) {
		if ( ! apply_filters( 'pp_video_fetch_remote_thumbnail', true, $this->settings ) ) {
			return;
		}

		$video_url = do_shortcode( $video_url );

		$video_properties = $this->get_video_properties( $video_url );

		if ( ! $video_properties ) {
			return null;
		}
		if ( ! in_array( $video_properties['provider'], array( 'youtube', 'vimeo', 'dailymotion', 'wistia' ) ) ) {
			return null;
		}

		$video_id        = $video_properties['video_id'];
		$video_thumbnail = '';

		$cache_key       = "pp_video_thumbnail_cache";
		$cache_data      = get_transient( $cache_key );
		$cache_data      = ! is_array( $cache_data ) ? array() : $cache_data;
		$cache_data_key  = $video_properties['provider'] . "_$video_id";
		$check_cache     = 'youtube' !== $video_properties['provider'];

		if ( isset( $cache_data[ $cache_data_key ] ) && $check_cache ) {
			$video_thumbnail = $cache_data[ $cache_data_key ];
			return apply_filters( 'pp_video_remote_thumbnail_url', $video_thumbnail, $this->settings );
		}

		if ( 'youtube' === $video_properties['provider'] && false === strpos( $video_url, '/playlist' ) ) {
			$default_yt_url = "https://i.ytimg.com/vi/{$video_id}/hqdefault.jpg";
			// $default_yt_webp = "https://i.ytimg.com/vi_webp/{$video_id}/hqdefault.webp";
			// $default_yt_max = "https://i.ytimg.com/vi/{$video_id}/maxresdefault.jpg";
			$video_thumbnail = $default_yt_url;

			global $is_safari;

			if ( $is_safari ) {
				$video_thumbnail = $default_yt_url;
			}
		}
		if ( 'vimeo' === $video_properties['provider'] ) {
			$remote_data = $this->get_remote_data( "https://vimeo.com/api/v2/video/$video_id.php" );

			if ( is_array( $remote_data ) && ! empty( $remote_data ) && isset( $remote_data[0]['thumbnail_large'] ) ) {
				$video_thumbnail = $remote_data[0]['thumbnail_large'];
			}
		}
		if ( 'dailymotion' === $video_properties['provider'] ) {
			//$remote_data = json_decode( @file_get_contents( "https://api.dailymotion.com/video/$video_id?fields=thumbnail_url" ), true );
			$remote_data = $this->get_remote_data( "https://api.dailymotion.com/video/$video_id?fields=thumbnail_url" );
			$remote_data = ! is_array( $remote_data ) ? json_decode( $remote_data, true ) : $remote_data;

			if ( is_array( $remote_data ) && isset( $remote_data['thumbnail_url'] ) ) {
				$video_thumbnail = $remote_data['thumbnail_url'];
			}
		}
		if ( 'wistia' === $video_properties['provider'] ) {
			$remote_data = json_decode( $this->get_remote_data( "https://fast.wistia.net/oembed?url=http://home.wistia.com/medias/$video_id?embedType=async_popover" ), true );

			if ( is_array( $remote_data ) && isset( $remote_data['thumbnail_url'] ) ) {
				$video_thumbnail = $remote_data['thumbnail_url'];
			}
		}

		if ( ! empty( $video_thumbnail ) && $check_cache ) {
			$cache_data[ $cache_data_key ] = $video_thumbnail;
			set_transient( $cache_key, $cache_data, DAY_IN_SECONDS );
		}

		return apply_filters( 'pp_video_remote_thumbnail_url', $video_thumbnail, $this->settings );
	}

	/**
	 * Return an overlay image URL for the video.
	 *
	 * @param string $video_url
	 *
	 * @return string
	 */
	public function get_overlay_image_url( $video_url ) {
		if ( $this->has_image_overlay() ) {
			return $this->settings->custom_overlay_src;
		}

		return $this->get_remote_thumbnail( $video_url );
	}

	/**
	 * Return an overlay image HTML tag for the video.
	 *
	 * @param string $video_url
	 *
	 * @return string
	 */
	public function get_overlay_image( $video_url ) {
		$settings = $this->settings;

		if ( $this->has_image_overlay() ) {
			$attachment_data = FLBuilderPhoto::get_attachment_data( $settings->custom_overlay );
			return sprintf(
				'<img class="%s" src="%s" title="%s" alt="%s" />',
				'pp-video-thumbnail-img wp-image-' . $settings->custom_overlay,
				$settings->custom_overlay_src,
				is_object( $attachment_data ) ? strip_tags( $attachment_data->title ) : '',
				is_object( $attachment_data ) ? strip_tags( $attachment_data->alt ) : ''
			);
		} else {
			$thumbnail_url = $this->get_remote_thumbnail( $video_url );

			if ( ! empty( $thumbnail_url ) ) {
				return sprintf(
					'<img class="pp-video-default-thumbnail" src="%s" alt="%s" />',
					$thumbnail_url,
					! empty( $settings->title_attr_text ) ? $this->get_title_attr_text() : esc_html__( 'Video' )
				);
			}
		}
	}

	public function get_remote_data( $url ) {
		$response = wp_remote_get( $url );
		$data = array();

		if ( is_wp_error( $response ) ) {
			return $data;
		}

		$response_code = wp_remote_retrieve_response_code( $response );

		if ( 200 !== $response_code ) {
			$data['error'] = true;
			return $data;
		}

		$data = maybe_unserialize( wp_remote_retrieve_body( $response ) );

		return apply_filters( 'pp_video_remote_thumbnail_response', $data, $this->settings );
	}

	/**
	 * Get structured data - https://schema.org/VideoObject
	 *
	 * @param object $settings
	 *
	 * @return string
	 */
	public function get_structured_data( $settings = null ) {
		$settings = ! is_object( $settings ) ? $this->settings : $settings;
		
		if ( ! isset( $settings->schema_enabled ) || 'no' == $settings->schema_enabled ) {
			return false;
		}

		$markup = '';
		$url 	= $this->get_video_url();

		if ( '' == $settings->video_title || '' == $settings->video_desc || '' == $settings->video_thumbnail || '' == $settings->video_upload_date ) {
			return false;
		}

		$upload_datetime = esc_attr( $settings->video_upload_date );

		if ( isset( $settings->video_upload_time ) && is_array( $settings->video_upload_time ) ) {
			$hours      = $settings->video_upload_time['hours'];
			$minutes    = $settings->video_upload_time['minutes'];
			$day_period = ! isset( $settings->video_upload_time['day_period'] ) ? 'am' : $settings->video_upload_time['day_period'];
			$time_24    = date( 'H:i', strtotime( $hours . ':' . $minutes . ' ' . $day_period ) );
			$tz         = get_option( 'timezone_string' );
			if ( empty( $tz ) ) {
				$offset  = (float) get_option( 'gmt_offset' );
				$hours   = (int) $offset;
				$minutes = ( $offset - $hours );

				$sign     = ( $offset < 0 ) ? '-' : '+';
				$abs_hour = abs( $hours );
				$abs_mins = abs( $minutes * 60 );
				$tz       = sprintf( '%s%02d:%02d', $sign, $abs_hour, $abs_mins );
			}
			$upload_datetime .= 'T' . $time_24 . $tz;
		}
	
		$markup .= sprintf( '<meta itemprop="name" content="%s" />', esc_attr( $settings->video_title ) );
		$markup .= sprintf( '<meta itemprop="description" content="%s" />', esc_attr( $settings->video_desc ) );
		$markup .= sprintf( '<meta itemprop="uploadDate" content="%s" />', $upload_datetime );
		$markup .= sprintf( '<meta itemprop="thumbnailUrl" content="%s" />', $settings->video_thumbnail_src );

		if ( ! empty( $url ) ) {
			$markup .= sprintf( '<meta itemprop="contentUrl" content="%s" />', $url );
			$markup .= sprintf( '<meta itemprop="embedUrl" content="%s" />', $url );
		}

		return $markup;
	}

	public function cmplz_filter_iframe_tags( $tags ) {
		return array();
	}

	public function before_render() {
		// Exclude the video iframe from getting blocked by Complianz cookie plugin.
		add_filter( 'cmplz_known_iframe_tags', array( $this, 'cmplz_filter_iframe_tags' ) );
	}

	public function after_render() {
		remove_filter( 'cmplz_known_iframe_tags', array( $this, 'cmplz_filter_iframe_tags' ) );
	}
}

BB_PowerPack::register_module(
	'PPVideoModule',
	array(
		'general' => array(
			'title'		=> __( 'General', 'bb-powerpack' ),
			'sections'	=> array(
				'general'	=> array(
					'title'		=> '',
					'fields'	=> array(
						'video_type'	=> array(
							'type'			=> 'select',
							'label'			=> __( 'Source', 'bb-powerpack' ),
							'description'   => sprintf( '<span class="pp-fb-embed-desc">%s</span>', __( 'Please note that there are certain limitations from Facebook therefore you cannot set the video on autoplay and cannot provide any other options.', 'bb-powerpack' ) ),
							'options' 		=> array(
								'youtube' 		=> __( 'YouTube', 'bb-powerpack' ),
								'vimeo' 		=> __( 'Vimeo', 'bb-powerpack' ),
								'dailymotion' 	=> __( 'Dailymotion', 'bb-powerpack' ),
								'wistia' 		=> __( 'Wistia', 'bb-powerpack' ),
								'facebook' 		=> __( 'Facebook', 'bb-powerpack' ),
								'zight' 		=> __( 'Zight', 'bb-powerpack' ),
								'komodo_decks' 	=> __( 'Komodo Decks', 'bb-powerpack' ),
								'supercut' 		=> __( 'Supercut', 'bb-powerpack' ),
								'hosted' 		=> __( 'Self Hosted', 'bb-powerpack' ),
								'external'		=> __( 'External URL', 'bb-powerpack' ),
							),
							'toggle'		=> array(
								'youtube'		=> array(
									'fields'		=> array( 'youtube_url', 'end_time', 'loop', 'controls', 'modestbranding', 'yt_privacy', 'rel' ),
								),
								'vimeo'		=> array(
									'fields'	=> array( 'vimeo_url', 'loop', 'color', 'vimeo_title', 'vimeo_portrait', 'vimeo_byline' ),
								),
								'dailymotion'	=> array(
									'fields'		=> array( 'dailymotion_url', 'controls', 'showinfo', 'logo', 'color' ),
								),
								'wistia'	=> array(
									'fields'		=> array( 'wistia_url', 'loop', 'controls', 'color' ),
								),
								'facebook'	=> array(
									'fields'		=> array( 'facebook_url' ),
								),
								'zight'	=> array(
									'fields'		=> array( 'zight_url' ),
								),
								'komodo_decks'	=> array(
									'fields'		=> array( 'komodo_decks_url' ),
								),
								'supercut'	=> array(
									'fields'		=> array( 'supercut_url' ),
								),
								'hosted'	=> array(
									'fields'	=> array( 'hosted_url', 'end_time', 'loop', 'controls', 'download_button', 'poster' ),
								),
								'external'	=> array(
									'fields'	=> array( 'external_url', 'end_time', 'loop', 'controls', 'download_button', 'poster' ),
								),
							),
							'connections' => array( 'string' ),
						),
						'youtube_url'	=> array(
							'type'			=> 'text',
							'label'			=> __( 'Link', 'bb-powerpack' ),
							'placeholder'	=> __( 'Enter YouTube URL', 'bb-powerpack' ),
							'default'		=> 'https://www.youtube.com/watch?v=A7ZkZazfvao',
							'connections'	=> array( 'url' ),
						),
						'vimeo_url'		=> array(
							'type'			=> 'text',
							'label'			=> __( 'Link', 'bb-powerpack' ),
							'placeholder'	=> __( 'Enter Viemo URL', 'bb-powerpack' ),
							'default'		=> 'https://vimeo.com/103344490',
							'connections'	=> array( 'url' ),
						),
						'dailymotion_url'	=> array(
							'type'			=> 'text',
							'label'			=> __( 'Link', 'bb-powerpack' ),
							'placeholder'	=> __( 'Enter Dailymotion URL', 'bb-powerpack' ),
							'default'		=> '',
							'connections'	=> array( 'url' ),
						),
						'wistia_url'	=> array(
							'type'			=> 'text',
							'label'			=> __( 'Link', 'bb-powerpack' ),
							'placeholder'	=> __( 'Enter Wistia URL', 'bb-powerpack' ),
							'default'		=> '',
							'connections'	=> array( 'url' ),
						),
						'facebook_url'	=> array(
							'type'			=> 'text',
							'label'			=> __( 'Link', 'bb-powerpack' ),
							'placeholder'	=> __( 'https://www.facebook.com/FacebookDevelopers/videos/10152454700553553', 'bb-powerpack' ),
							'help'          => __( 'Make sure the Facebook video is publicly visible and should not be private or restricted. Also, make sure to provide URL in this format https://www.facebook.com/FacebookDevelopers/videos/10152454700553553', 'bb-powerpack' ),
							'default'		=> '',
							'connections'	=> array( 'url' ),
						),
						'zight_url'	=> array(
							'type'			=> 'text',
							'label'			=> __( 'Link', 'bb-powerpack' ),
							'placeholder'	=> __( 'https://share.zight.com/bLuLzwNP or https://share.weave.nz/bLuLzwNP', 'bb-powerpack' ),
							'help'          => __( 'Enter your Zight share URL. Both share.zight.com and share.weave.nz formats are supported.', 'bb-powerpack' ),
							'default'		=> '',
							'connections'	=> array( 'url' ),
						),
						'komodo_decks_url'	=> array(
							'type'			=> 'text',
							'label'			=> __( 'Link', 'bb-powerpack' ),
							'placeholder'	=> __( 'https://komododecks.com/recordings/0YYtxyPhjtrBRgeKVzb7', 'bb-powerpack' ),
							'help'          => __( 'Enter your Komodo Decks recording URL. Both komododecks.com and share.weave.co.nz formats are supported.', 'bb-powerpack' ),
							'default'		=> '',
							'connections'	=> array( 'url' ),
						),
						'supercut_url'	=> array(
							'type'			=> 'text',
							'label'			=> __( 'Link', 'bb-powerpack' ),
							'placeholder'	=> __( 'https://supercut.ai/share/weave/iLUm59_Iu7kvWjsvFSOn7a', 'bb-powerpack' ),
							'help'          => __( 'Enter your Supercut share URL.', 'bb-powerpack' ),
							'default'		=> '',
							'connections'	=> array( 'url' ),
						),
						'hosted_url'	=> array(
							'type'			=> 'video',
							'label'			=> __( 'Choose File', 'bb-powerpack' ),
							'show_remove' 	=> true,
						),
						'external_url'	=> array(
							'type'			=> 'text',
							'label'			=> __( 'External URL', 'bb-powerpack' ),
							'default'		=> '',
							'connections'	=> array( 'url', 'custom_field' ),
						),
						'start_time'	=> array(
							'type'			=> 'unit',
							'label'			=> __( 'Start Time', 'bb-powerpack' ),
							'default'		=> '',
							'slider'		=> true,
							'units'			=> array( 'seconds' ),
							'help'			=> __( 'Specify a start time (in seconds)', 'bb-powerpack' ),
							'connections' => array( 'string' ),
						),
						'end_time'		=> array(
							'type'			=> 'unit',
							'label'			=> __( 'End Time', 'bb-powerpack' ),
							'default'		=> '',
							'slider'		=> true,
							'units'			=> array( 'seconds' ),
							'help'			=> __( 'Specify a end time (in seconds)', 'bb-powerpack' ),
							'connections' => array( 'string' ),
						),
						'aspect_ratio'	=> array(
							'type'			=> 'select',
							'label'			=> __( 'Video Aspect Ratio', 'bb-powerpack' ),
							'default' 		=> '169',
							'options' 		=> array(
								'916' 			=> '9:16',
								'219' 			=> '21:9',
								'169' 			=> '16:9',
								'45' 			=> '4:5',
								'43' 			=> '4:3',
								'32' 			=> '3:2',
								'11' 			=> '1:1',
								'auto'          => __('Auto', 'bb-powerpack'),
							),
						),
						'title_attr_text'	=> array(
							'type'	=> 'text',
							'label'	=> __( 'Text for HTML "title" attribute', 'bb-powerpack' ),
							'default' => '',
							'help' => __( 'This text will be applied to "title" attribute of HTML iframe or video tag.', 'bb-powerpack' ),
							'connections' => array( 'string' ),
						),
					),
				),
				'video_options'	=> array(
					'title'			=> __( 'Video Options', 'bb-powerpack' ),
					'collapsed'		=> true,
					'fields'		=> array(
						'autoplay'		=> array(
							'type'			=> 'pp-switch',
							'label'			=> __( 'Auto Play', 'bb-powerpack' ),
							'description'	=> '<span style="font-style: normal !important; line-height: 1.5;">' . __( 'Please note that browsers do not support autoplaying a video that has sound so make sure to enable the Mute option below.', 'bb-powerpack' ) . '</span>',
							'default'		=> 'no',
							'options'		=> array(
								'yes'			=> __( 'Yes', 'bb-powerpack' ),
								'no'			=> __( 'No', 'bb-powerpack' ),
							),
						),
						'mute'			=> array(
							'type'			=> 'pp-switch',
							'label'			=> __( 'Mute', 'bb-powerpack' ),
							'default'		=> 'no',
							'options'		=> array(
								'yes'			=> __( 'Yes', 'bb-powerpack' ),
								'no'			=> __( 'No', 'bb-powerpack' ),
							),
						),
						'loop'			=> array(
							'type'			=> 'pp-switch',
							'label'			=> __( 'Loop', 'bb-powerpack' ),
							'default'		=> 'no',
							'options'		=> array(
								'yes'			=> __( 'Yes', 'bb-powerpack' ),
								'no'			=> __( 'No', 'bb-powerpack' ),
							),
						),
						'controls'		=> array(
							'type'			=> 'pp-switch',
							'label'			=> __( 'Controls', 'bb-powerpack' ),
							'default'		=> 'yes',
							'options'		=> array(
								'yes'			=> __( 'Show', 'bb-powerpack' ),
								'no'			=> __( 'Hide', 'bb-powerpack' ),
							),
						),
						'showinfo'		=> array(
							'type'			=> 'pp-switch',
							'label'			=> __( 'Video Info', 'bb-powerpack' ),
							'default'		=> 'show',
							'options'		=> array(
								'show'			=> __( 'Show', 'bb-powerpack' ),
								'hide'			=> __( 'Hide', 'bb-powerpack' ),
							),
						),
						'modestbranding'	=> array(
							'type'			=> 'pp-switch',
							'label'			=> __( 'Modest Branding', 'bb-powerpack' ),
							'help'			=> __( 'This option lets you use a YouTube player that does not show a YouTube logo. Note that a small YouTube text label will still display in the upper-right corner of a paused video when the user\'s mouse pointer hovers over the player.', 'bb-powerpack' ),
							'default'		=> 'no',
							'options'		=> array(
								'yes'			=> __( 'Yes', 'bb-powerpack' ),
								'no'			=> __( 'No', 'bb-powerpack' ),
							),
						),
						'logo'			=> array(
							'type'			=> 'pp-switch',
							'label'			=> __( 'Logo', 'bb-powerpack' ),
							'default'		=> 'show',
							'options'		=> array(
								'show'			=> __( 'Show', 'bb-powerpack' ),
								'hide'			=> __( 'Hide', 'bb-powerpack' ),
							),
						),
						'color'			=> array(
							'type'			=> 'color',
							'label'			=> __( 'Controls Color', 'bb-powerpack' ),
							'default'		=> '',
							'show_reset'	=> true,
							'connections'	=> array( 'color' ),
						),
						'yt_privacy'	=> array(
							'type'			=> 'pp-switch',
							'label'			=> __( 'Privacy Mode', 'bb-powerpack' ),
							'help'			=> __( 'When you turn on privacy mode, YouTube won\'t store information about visitors on your website unless they play the video.', 'bb-powerpack' ),
							'default'		=> 'no',
							'options'		=> array(
								'yes'			=> __( 'Yes', 'bb-powerpack' ),
								'no'			=> __( 'No', 'bb-powerpack' )
							),
						),
						'rel'		=> array(
							'type'		=> 'select',
							'label'		=> __( 'Suggested Video', 'bb-powerpack' ),
							'options'	=> array(
								''			=> __( 'Current Video Channel', 'bb-powerpack' ),
								'any'		=> __( 'Any Video', 'bb-powerpack' ),
							),
						),
						'vimeo_title'	=> array(
							'type'			=> 'pp-switch',
							'label'			=> __( 'Intro Title', 'bb-powerpack' ),
							'default'		=> 'show',
							'options'		=> array(
								'show'			=> __( 'Show', 'bb-powerpack' ),
								'hide'			=> __( 'Hide', 'bb-powerpack' ),
							),
						),
						'vimeo_portrait'	=> array(
							'type'				=> 'pp-switch',
							'label'				=> __( 'Intro Portrait', 'bb-powerpack' ),
							'default'			=> 'show',
							'options'			=> array(
								'show'				=> __( 'Show', 'bb-powerpack' ),
								'hide'				=> __( 'Hide', 'bb-powerpack' ),
							),
						),
						'vimeo_byline'	=> array(
							'type'			=> 'pp-switch',
							'label'			=> __( 'Intro Byline', 'bb-powerpack' ),
							'default'		=> 'show',
							'options'		=> array(
								'show'			=> __( 'Show', 'bb-powerpack' ),
								'hide'			=> __( 'Hide', 'bb-powerpack' ),
							),
						),
						'download_button'	=> array(
							'type'				=> 'pp-switch',
							'label'				=> __( 'Download Button', 'bb-powerpack' ),
							'default'			=> 'show',
							'options'			=> array(
								'show'				=> __( 'Show', 'bb-powerpack' ),
								'hide'				=> __( 'Hide', 'bb-powerpack' ),
							),
							'preview'			=> array(
								'type'				=> 'none',
							),
						),
						'poster'	=> array(
							'type'		=> 'photo',
							'label'		=> __( 'Poster', 'bb-powerpack' ),
							'show_remove' => true,
							'connections' => array( 'photo' ),
						),
					),
				),
				'overlay'	=> array(
					'title'		=> __( 'Overlay', 'bb-powerpack' ),
					'collapsed'	=> true,
					'fields'	=> array(
						'overlay'	=> array(
							'type'		=> 'pp-switch',
							'label'		=> __( 'Use Custom Overlay', 'bb-powerpack' ),
							'default'	=> 'default',
							'options'	=> array(
								'custom'	=> __( 'Yes', 'bb-powerpack' ),
								'default'	=> __( 'No', 'bb-powerpack' ),
							),
							'toggle'	=> array(
								'custom'	=> array(
									'fields'	=> array( 'custom_overlay' ),
								),
							),
						),
						'custom_overlay'	=> array(
							'type'				=> 'photo',
							'label'				=> __( 'Overlay Image', 'bb-powerpack' ),
							'show_remove'		=> true,
							'connections'		=> array( 'photo' ),
						),
						'play_icon'	=> array(
							'type'		=> 'pp-switch',
							'label'		=> __( 'Custom Play Button', 'bb-powerpack' ),
							'default'	=> 'show',
							'options'	=> array(
								'show'		=> __( 'Show', 'bb-powerpack' ),
								'hide'		=> __( 'Hide', 'bb-powerpack' ),
							),
							'toggle'	=> array(
								'show'		=> array(
									'sections'	=> array( 'play_icon' ),
								),
							),
						),
					),
				),
				'lightbox' => array(
					'title'       => __( 'Lightbox', 'bb-powerpack' ),
					'description' => __( 'Lightbox works only when there is an overlay image either default or custom.', 'bb-powerpack' ),
					'collapsed'   => true,
					'fields'      => array(
						'lightbox'	=> array(
							'type'		=> 'pp-switch',
							'label'		=> __( 'Enable Lightbox', 'bb-powerpack' ),
							'default'	=> 'no',
							'toggle'	=> array(
								'yes'		=> array(
									'sections'	=> array( 'lightbox_style' ),
									'fields'    => array( 'aspect_ratio_lightbox', 'custom_trigger_selector', 'custom_trigger_hide_video' ),
								),
							),
						),
						'aspect_ratio_lightbox'	=> array(
							'type'			=> 'select',
							'label'			=> __( 'Video Aspect Ratio in Lightbox', 'bb-powerpack' ),
							'default' 		=> 'default',
							'options' 		=> array(
								'default'       => __( 'Auto', 'bb-powerpack' ),
								'169' 			=> '16:9',
								'219' 			=> '21:9',
								'43' 			=> '4:3',
								'32' 			=> '3:2',
								'11' 			=> '1:1',
							),
						),
						'custom_trigger_selector' => array(
							'type'	  => 'text',
							'label'	  => __( 'Custom Trigger Selector (optional)', 'bb-powerpack' ),
							'placeholder' => '#play-my-video or .play-my-video',
							'help'    => __( 'This additional selector will be used to play the video in lightbox on click. Add an ID prefixed with hash # or a class prefix with dot .', 'bb-powerpack' ),
						),
						'custom_trigger_hide_video' => array(
							'type'    => 'pp-switch',
							'label'   => __( 'Hide video from the page', 'bb-powerpack' ),
							'default' => 'no',
							'help'    => __( 'If you want to use custom trigger only then you can enable this option to hide the video from the page and let the custom trigger open the video in lightbox. Video will be visible only in the builder.', 'bb-powerpack' ),
						),
					),
				),
			),
		),
		'style'   => array(
			'title'       => __( 'Style', 'bb-powerpack' ),
			'sections'    => array(
				'general_style'  => array(
					'title'  => __( 'Box Style', 'bb-powerpack' ),
					'fields' => array(
						'box_border' => array(
							'type'    => 'border',
							'label'   => __( 'Border', 'bb-powerpack' ),
							'preview' => array(
								'type'     => 'css',
								'selector' => '.pp-video-wrapper',
							),
						),
					),
				),
				'play_icon'	     => array(
					'title'		=> __( 'Custom Play Icon', 'bb-powerpack' ),
					'collapsed' => true,
					'fields'	=> array(
						'play_icon_bg_color'	=> array(
							'type'				=> 'color',
							'label'				=> __( 'Background Color', 'bb-powerpack' ),
							'default'			=> '',
							'show_reset'		=> true,
							'show_alpha'		=> true,
							'connections'		=> array( 'color' ),
							'preview'			=> array(
								'type'				=> 'css',
								'selector'			=> '.pp-video-play-icon',
								'property'			=> 'background',
							),
						),
						'play_icon_bg_hover_color'	=> array(
							'type'				=> 'color',
							'label'				=> __( 'Background Hover Color', 'bb-powerpack' ),
							'default'			=> '',
							'show_reset'		=> true,
							'show_alpha'		=> true,
							'connections'		=> array( 'color' ),
							'preview'			=> array(
								'type'				=> 'none',
							),
						),
						'play_icon_color'	=> array(
							'type'				=> 'color',
							'label'				=> __( 'Color', 'bb-powerpack' ),
							'default'			=> '',
							'show_reset'		=> true,
							'show_alpha'		=> true,
							'connections'		=> array( 'color' ),
							'preview'			=> array(
								'type'				=> 'css',
								'selector'			=> '.pp-video-play-icon svg',
								'property'			=> 'fill',
							),
						),
						'play_icon_hover_color'	=> array(
							'type'				=> 'color',
							'label'				=> __( 'Hover Color', 'bb-powerpack' ),
							'default'			=> '',
							'show_reset'		=> true,
							'show_alpha'		=> true,
							'connections'		=> array( 'color' ),
							'preview'			=> array(
								'type'				=> 'none',
							),
						),
						'play_icon_size'	=> array(
							'type'				=> 'unit',
							'label'				=> __( 'Size', 'bb-powerpack' ),
							'default'			=> '',
							'slider'			=> array(
								'min'				=> '10',
								'max'				=> '300',
								'step'				=> '1',
							),
							'units'				=> array( 'px' ),
							'responsive'		=> true,
						),
						'play_icon_border'	=> array(
							'type'				=> 'border',
							'label'				=> __( 'Border', 'bb-powerpack' ),
							'preview'			=> array(
								'type'				=> 'css',
								'selector'			=> '.pp-video-play-icon'
							)
						),
						'play_icon_border_hover_color'	=> array(
							'type'		=> 'color',
							'label'		=> __( 'Border Hover Color', 'bb-powerpack' ),
							'default'	=> '',
							'show_reset' => true,
							'connections'	=> array( 'color' ),
							'preview'	=> array(
								'type'		=> 'none',
							),
						),
					),
				),
				'lightbox_style' => array(
					'title'				=> __( 'Lightbox', 'bb-powerpack' ),
					'collapsed'			=> true,
					'fields'			=> array(
						'lightbox_bg_color'	=> array(
							'type'				=> 'color',
							'label'				=> __( 'Background Color', 'bb-powerpack' ),
							'default'			=> '',
							'show_reset'		=> true,
							'show_alpha'		=> true,
							'connections'		=> array( 'color' ),
							'preview'			=> array(
								'type'				=> 'none',
							),
						),
						'lightbox_color'	=> array(
							'type'				=> 'color',
							'label'				=> __( 'Close Button Color', 'bb-powerpack' ),
							'default'			=> '',
							'show_reset'		=> true,
							'connections'		=> array( 'color' ),
							'preview'			=> array(
								'type'				=> 'none',
							),
						),
						'lightbox_hover_color'	=> array(
							'type'				=> 'color',
							'label'				=> __( 'Close Button Hover Color', 'bb-powerpack' ),
							'default'			=> '',
							'show_reset'		=> true,
							'connections'		=> array( 'color' ),
							'preview'			=> array(
								'type'				=> 'none',
							),
						),
						'lightbox_video_width'	=> array(
							'type'		=> 'unit',
							'label'		=> __( 'Content Width', 'bb-powerpack' ),
							'default'	=> '',
							'slider'	=> true,
							'units'		=> array( '%' ),
							'preview'	=> array(
								'type'		=> 'none',
							),
						),
						'lightbox_video_position'	=> array(
							'type'		=> 'pp-switch',
							'label'		=> __( 'Content Position', 'bb-powerpack' ),
							'default'	=> 'center',
							'options'	=> array(
								'center'	=> __( 'Center', 'bb-powerpack' ),
								'top'		=> __( 'Top', 'bb-powerpack' ),
							),
							'preview'	=> array(
								'type'		=> 'none',
							),
						),
					),
				),
			),
		),
		'structured_data'	=> array(
			'title'		=> __( 'Structured Data', 'bb-powerpack' ),
			'sections'	=> array(
				'video_info'	=> array(
					'title'			=> '',
					'fields'		=> array(
						'schema_enabled'	=> array(
							'type'		=> 'pp-switch',
							'label'		=> __( 'Enable Structured Data?', 'bb-powerpack' ),
							'default'	=> 'no',
							'options'	=> array(
								'yes'		=> __( 'Yes', 'bb-powerpack' ),
								'no'		=> __( 'No', 'bb-powerpack' ),
							),
							'toggle'	=> array(
								'yes'		=> array(
									'fields'	=> array( 'video_title', 'video_desc', 'video_thumbnail', 'video_upload_date', 'video_upload_time' ),
								),
							),
						),
						'video_title'	=> array(
							'type'			=> 'text',
							'label'			=> __( 'Video Title', 'bb-powerpack' ),
							'default'		=> '',
							'connections'	=> array( 'string' ),
							'preview' 		=> array(
								'type' 			=> 'none',
							),
						),
						'video_desc'	=> array(
							'type'			=> 'text',
							'label'			=> __( 'Video Description', 'bb-powerpack' ),
							'default'		=> '',
							'connections'	=> array( 'string' ),
							'preview' 		=> array(
								'type' 			=> 'none',
							),
						),
						'video_thumbnail'	=> array(
							'type'			=> 'photo',
							'label'			=> __( 'Video Thumbnail', 'bb-powerpack' ),
							'show_remove'	=> true,
							'connections'	=> array( 'photo' ),
							'preview' 		=> array(
								'type' 			=> 'none',
							),
						),
						'video_upload_date'	=> array(
							'type'   		=> 'date',
							'label'   		=> __( 'Upload Date', 'bb-powerpack' ),
							'connections'	=> array( 'string' ),
							'preview' 		=> array(
								'type' 			=> 'none',
							),
						),
						'video_upload_time'	=> array(
							'type'   		=> 'time',
							'label'   		=> __( 'Upload Time', 'bb-powerpack' ),
							'default'       => array(
								'hours'   => '10',
								'minutes' => '00'
							),
							'connections'	=> array( 'string' ),
							'preview' 		=> array(
								'type' 			=> 'none',
							),
						),
					),
				),
			),
		),
	)
);
