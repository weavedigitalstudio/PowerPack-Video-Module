;(function($) {
	
	FLBuilder.registerModuleHelper( 'pp-video', {
		init: function() {
			var form = $('.fl-builder-settings');
			var self = this;
			
			// self._toggleOverlayFields();

			// form.find('#fl-field-custom_overlay').on('DOMSubtreeModified', function() {
			// 	self._toggleOverlayFields();
			// });

			self._setVideoTypeClass();

			self._toggleVideoOptions();

			form.find( 'select[name=video_type]' ).on( 'change', function() {
				self._setVideoTypeClass();
				self._toggleVideoOptions();
			} );
		},

		submit: function() {

			var form      = $( '.fl-builder-settings' ),
				enabled     = form.find( 'input[name=schema_enabled]' ).val(),
				name        = form.find( 'input[name=video_title]' ).val(),
				description = form.find( 'input[name=video_desc]' ).val();
				thumbnail   = form.find( 'input[name=video_thumbnail]' ).val();
				update      = form.find( 'input[name=video_upload_date]' ).val();

			if( 'no' === enabled ) {
				return true;
			}

			if ( 0 === name.length ) {
				FLBuilder.alert( FLBuilderStrings.schemaAllRequiredMessage );
				return false;
			}
			else if ( 0 === description.length ) {
				FLBuilder.alert( FLBuilderStrings.schemaAllRequiredMessage );
				return false;
			}
			else if ( 0 === thumbnail.length ) {

				FLBuilder.alert( FLBuilderStrings.schemaAllRequiredMessage );

				return false;
			}
			else if( 0 === update.length ) {
				FLBuilder.alert( FLBuilderStrings.schemaAllRequiredMessage );
				return false;
			}

			return true;
		},

		_toggleOverlayFields: function() {
			var form = $('.fl-builder-settings');
			var field = form.find('input[name="custom_overlay"]');
			if ( '' === field.val() || 'default' === form.find('input[name="overlay"]').val() ) {
				form.find('#fl-field-play_icon').hide();
				//form.find('#fl-field-lightbox').hide();
			} else {
				form.find('#fl-field-play_icon').show();
				//form.find('#fl-field-lightbox').show();
			}
		},

		_toggleVideoOptions: function() {
			var form = $('.fl-builder-settings');
			var videoType = form.find( 'select[name=video_type]' ).val();

			// Hide video options for services that don't support custom controls
			if ( 'facebook' === videoType || 'zight' === videoType || 'komodo_decks' === videoType || 'supercut' === videoType ) {
				form.find( '#fl-builder-settings-section-video_options' ).hide();
			} else {
				form.find( '#fl-builder-settings-section-video_options' ).show();
			}
		},

		_setVideoTypeClass: function() {
			var form = $('.fl-builder-settings');

			var $videoType = form.find( 'select[name=video_type]' );

			$videoType.find( 'option' ).each(function() {
				if ( form.hasClass( 'video-type-' + $(this).attr( 'value' ) ) ) {
					form.removeClass( 'video-type-' + $(this).attr( 'value' ) );
				}
			});

			form.addClass( 'video-type-' + $videoType.val() );
		}
	});
})(jQuery);