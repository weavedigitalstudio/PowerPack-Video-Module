;(function( $ ) {

	PPVideo = function( settings ) {
		this.id			= settings.id;
		this.type		= settings.type;
		this.autoplay	= settings.autoplay;
		this.lightbox 	= settings.lightbox;
		this.aspectRatio = settings.aspectRatioLightbox;
		this.overlay	= settings.overlay;
		this.triggerSelector = settings.triggerSelector;
		this.node		= $('.fl-node-' + this.id);
		this.nodeCount  = this.node.length;
		this.settings   = settings;
		this.api        = {};

		this._init();
	};

	PPVideo.prototype = {
		_init: function() {
			if ( this.lightbox ) {
				this._initLightbox();
			} else {
				this._inlinePlay();
			}

			this._initApi();
		},

		_initApi: function() {
			var self = this;

			this.api.youtube = {};

			self.api.youtube.loadApiScript = function() {
				$( 'script:first' ).before( $( '<script>', { src: 'https://www.youtube.com/iframe_api' } ) );
				self.api.youtube.apiScriptLoaded = true;
			};
			self.api.youtube.isApiLoaded = function() {
				return window.YT && YT.loaded;
			};
			self.api.youtube.getApiObject = function() {
				return YT;
			};
			self.api.youtube.onApiReady = function(callback) {
				if ( ! self.api.youtube.apiScriptLoaded ) {
					self.api.youtube.loadApiScript();
				}
				if ( self.api.youtube.isApiLoaded() ) {
					callback( self.api.youtube.getApiObject() );
				} else {
					setTimeout( function() {
						self.api.youtube.onApiReady(callback);
					}, 350 );
				}
			};
			self.api.youtube.getVideoId = function(url) {
				var videoIDParts = url.match( /^(?:https?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?vi?=|(?:embed|v|vi|user)\/))([^?&"'>]+)/ );
				return videoIDParts && videoIDParts[1];
			};

			// if ( 'youtube' == self.type ) {
			// 	self.api.youtube.onApiReady( function( apiObject ) {
			// 		self._prepareYTVideo( apiObject );
			// 	}.bind( self ) );
			// }
		},

		_inlinePlay: function() {
			var self = this;
			var videoFrame = this.node.find( '.pp-video-iframe' );
			var videoPlayer = this.node.find( '.pp-video-player' );
			var hasOverlay = this.node.find('.pp-video-image-overlay').length > 0;

			if ( videoPlayer.length > 0 ) {
				videoPlayer.attr( 'playsinline', '' );
				if ( this.autoplay && ! hasOverlay ) {
					if ( this._isIOS() ) {
						videoPlayer.attr( 'muted', '' );
					}
					videoPlayer[0].play();
				}
			}

			if ( videoFrame.length > 0 ) {
				videoFrame.show();

				var src = videoFrame.data( 'src' ) || videoFrame.data( 'cli-src' ) || videoFrame.data( 'src-cmplz' );

				videoFrame.data( 'src', src.replace('&autoplay=1', '') );
				videoFrame.data( 'src', src.replace('autoplay=1', '') );

				if ( ! this.autoplay && ! hasOverlay ) {
					videoFrame.attr( 'src', videoFrame.data( 'src' ) );
				}

				if ( this.autoplay && ! this.lightbox ) {
					var src = videoFrame.data( 'src' ).split('#');
					var iframeSrc = src[0] + '&autoplay=1';

					if ( 'youtube' == self.type ) {
						iframeSrc += '&enablejsapi=1';
					}

					if ( 'undefined' !== typeof src[1] ) {
						iframeSrc += '#' + src[1];
					}

					videoFrame.attr( 'src', iframeSrc );

					this.node.find( '.pp-video-image-overlay' ).fadeOut(100, function() {
						this.remove();
						if ( 'youtube' == self.type ) {
							self.api.youtube.onApiReady( function( apiObject ) {
								self._prepareYTVideo( apiObject, true );
							}.bind( self ) );
						}
					});
				}
			}

			this.node.find('.pp-video-image-overlay').on('click keyup', function(e) {
				// Click or keyboard (enter or spacebar) input?
				if ( ! this._validClick(e) ) {
					return;
				}

				e.preventDefault();

				this.node.find( '.pp-video-image-overlay' ).fadeOut(800, function() {
					this.remove();
				});

				if ( videoPlayer.length > 0 ) {
					videoPlayer[0].play();

					return;
				}

				videoFrame.show();

				var lazyLoad = videoFrame.data( 'src' );

				if ( lazyLoad ) {
					videoFrame.attr( 'src', lazyLoad );
				}

				var iframeSrc = videoFrame[0].src.replace('&autoplay=0', '');
				iframeSrc = iframeSrc.replace('autoplay=0', '');

				var src = iframeSrc.split('#');
				iframeSrc = src[0];

				if ( 'facebook' === this.type ) {
					iframeSrc += '&autoplay=0';
				} else {
					iframeSrc += '&autoplay=1';
				}

				if ( 'undefined' !== typeof src[1] ) {
					iframeSrc += '#' + src[1];
				}
				videoFrame[0].src = iframeSrc;
			}.bind( this ) );
		},

		_initLightbox: function() {
			var id = this.id;
			var self = this;
			var options = {
				modal: false,
				enableEscapeButton: true,
				type: 'inline',
				baseClass: 'fancybox-' + id + ' pp-video-lightbox',
				buttons: [
					'close'
				],
				wheel: false,
				touch: false,
				iframe: {
					preload: false
				},
				keys: {
					close: [27],
				},
				clickSlide: 'close',
				clickOutside: 'close',
				afterLoad: function(current, previous) {
					$('.fancybox-' + id).find('.fancybox-bg').addClass('fancybox-' + id + '-overlay');
					if ( $('.fancybox-' + id).find( '.pp-video-iframe' ).length > 0 ) {
						var iframeSrc = $('.fancybox-' + id).find( '.pp-video-iframe' )[0].src.replace('&autoplay=0', '');
						iframeSrc = iframeSrc.replace('autoplay=0', '');

						var src = iframeSrc.split('#');
						iframeSrc = src[0];

						if ( 'facebook' === self.type ) {
							iframeSrc += '&autoplay=0';
						} else {
							iframeSrc += '&autoplay=1';
						}
						if ( 'youtube' == self.type ) {
							iframeSrc += '&enablejsapi=1';
						}

						if ( 'undefined' !== typeof src[1] ) {
							iframeSrc += '#' + src[1];
						}
						$('.fancybox-' + id).find( '.pp-video-iframe' )[0].src = iframeSrc;

						if ( 'youtube' == self.type ) {
							self.api.youtube.onApiReady( function( apiObject ) {
								self._prepareYTVideo( apiObject, false );
							}.bind( self ) );
						}

						setTimeout(function() {
							$('.fancybox-' + id).trigger('focus');
						}, 1200);
					}

					$('.fancybox-' + id).on('click.pp-video', '.fancybox-content', function(e) {
						if ( $(this).hasClass( 'fancybox-content' ) ) {
							$.fancybox.close();
						}
					});

					$(document).trigger( 'pp_video_lightbox_after_load', [ $('.fancybox-' + id), id ] );
				},
				afterClose: function() {
					if ( self.node.find('.pp-video-play-icon').length > 0 ) {
						self.node.find('.pp-video-play-icon').attr( 'tabindex', '0' );
						self.node.find('.pp-video-play-icon')[0].focus();
					}
				}
			};

			var wrapperClasses = 'pp-aspect-ratio-' + this.aspectRatio;

			var selector = '.fl-node-' + this.id + ' .pp-video-image-overlay';

			if ( '' !== this.triggerSelector ) {
				selector += ', ' + this.triggerSelector;
			}

			$( 'body' ).on('click keyup', selector, function(e) {
				// Click or keyboard (enter or spacebar) input?
				if ( ! this._validClick(e) ) {
					return;
				}

				e.preventDefault();
				e.stopPropagation();

				if ( this.nodeCount > 1 ) {
					this.node = $(e.target).closest( '.fl-module' );
				}

				var lightboxContent = this.node.find('.pp-video-lightbox-content').html();

				$.fancybox.open($( '<div class="'+wrapperClasses+'"></div>').html( lightboxContent ), options );

				this.node.find('.pp-video-play-icon').attr( 'tabindex', '-1' );
			}.bind( this ) );

			$(document).on('keyup', function(e) {
				if ( e.keyCode === 27 ) {
					$.fancybox.close();
				}
			});

			$(window).on( 'hashchange', this._onHashChange.bind( this ) );

			this._onHashChange();
		},

		_onHashChange: function() {
			var hash = location.hash,
				validHash = '#video-' + this.id;

			if ( hash === validHash ) {
				$('.fl-node-' + this.id + ' .pp-video-image-overlay').trigger( 'click' );
			}
		},

		_prepareYTVideo: function(YT, onOverlayClick) {
			var $iframe = this._getIframe(),
				iframeSrc = $iframe.attr( 'src' ) || $iframe.attr( 'data-src' ),
				videoID = this.api.youtube.getVideoId( iframeSrc ),
				self = this;

			var playerOptions = {
				videoId: videoID,
				events: {
					onReady: function() {
						if (self.settings.mute) {
							self.youtubePlayer.mute();
						}

						if (self.settings.autoplay || onOverlayClick) {
							self.youtubePlayer.playVideo();
						}
					},
					onStateChange: function( event ) {
						if (event.data === YT.PlayerState.ENDED && self.settings.loop) {
							self.youtubePlayer.seekTo(self.settings.startTime || 0);
						}
					}
				},
				playerVars: {
					controls: self.settings.controls ? 1 : 0,
					rel: self.settings.rel ? 1 : 0,
					playsinline: self.settings.autoplay ? 1 : 0,
					modestbranding: self.settings.modestbranding ? 1 : 0,
					autoplay: self.settings.autoplay ? 1 : 0,
					start: self.settings.startTime,
					end: self.settings.endTime
				}
			};
			
			// To handle CORS issues, when the default host is changed, the origin parameter has to be set.
			if (self.settings.yt_privacy) {
				playerOptions.host = 'https://www.youtube-nocookie.com';
				playerOptions.origin = window.location.hostname;
			}

			this.youtubePlayer = new YT.Player($iframe[0], playerOptions);
		},

		_getIframe: function() {
			if ( ! this.lightbox ) {
				return $( '.fl-node-' + this.id + ' .pp-video-iframe' );
			} else {
				return $( '.fancybox-' + this.id + ' .pp-video-iframe' );
			}
		},

		_validClick: function(e) {
			return (e.which == 1 || e.which == 13 || e.which == 32 || e.which == undefined) ? true : false;
		},

		_isIOS: function() {
			return !window.MSStream && /iPad|iPhone|iPod/.test(navigator.userAgent);
		}
	};

})(jQuery);