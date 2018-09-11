( function( $, JetDataImport ) {

	"use strict";

	JetDataImport = {

		selectors: {
			trigger: '#jet-import-start',
			advancedTrigger: 'button[data-action="start-install"]',
			popupTrigger: 'button[data-action="confirm-install"]',
			removeContent: 'button[data-action="remove-content"]',
			upload: '#jet-file-upload',
			globalProgress: '#jet-import-progress'
		},

		globalProgress: null,

		init: function(){

			$( function() {

				JetDataImport.globalProgress = $( JetDataImport.selectors.globalProgress ).find( '.cdi-progress__bar' );

				$( 'body' )
				.on( 'click.cdiImport', JetDataImport.selectors.trigger, JetDataImport.goToImport )
				.on( 'click.cdiImport', JetDataImport.selectors.advancedTrigger, JetDataImport.advancedImport )
				.on( 'click.cdiImport', JetDataImport.selectors.popupTrigger, JetDataImport.confirmImport )
				.on( 'click.cdiImport', JetDataImport.selectors.removeContent, JetDataImport.removeContent )
				.on( 'focus.cdiImport', '.cdi-remove-form__input', JetDataImport.clearRemoveNotices )
				.on( 'change.cdiImport', 'input[name="install-type"]', JetDataImport.advancedNotice )
				.on( 'click.cdiImport', '.cdi-advanced-popup__close', JetDataImport.closePopup );

				$( document )
				.on( 'tm-wizard-install-finished', JetDataImport.wizardPopup )
				.on( 'cdiSliderInit', JetDataImport.initSlider );

				if ( window.JetDataImportVars.autorun ) {
					JetDataImport.startImport();
				}

				if ( undefined !== window.JetRegenerateData ) {
					JetDataImport.regenerateThumbnails();
				}

				JetDataImport.fileUpload();

				JetDataImport.initSlider();

			} );

		},

		initSlider: function() {

			var $slider = $( '.cdi-slider .swiper-container' );

			if ( ! $slider.length ) {
				return;
			}

			new Swiper( $slider[0], {
				paginationClickable: true,
				autoplay: 15000,
				pagination: '.slider-pagination',
				parallax: true,
				speed: 600
			} );

		},

		wizardPopup: function () {
			$( '.cdi-advanced-popup' ).removeClass( 'popup-hidden' ).trigger( 'cdi-popup-opened' );
		},

		removeContent: function() {

			var $this    = $( this ),
				$pass    = $this.prev(),
				$form    = $this.closest( '.cdi-remove-form' ),
				$notices = $( '.cdi-remove-form__notices', $form ),
				data     = {};

			if ( $this.hasClass( 'in-progress' ) ) {
				return;
			}

			data.action   = 'jet-data-import-remove-content';
			data.nonce    = window.JetDataImportVars.nonce;
			data.password = $pass.val();

			$this.addClass( 'in-progress' );

			$.ajax({
				url: window.ajaxurl,
				type: 'post',
				dataType: 'json',
				data: data,
				error: function() {
					$this.removeClass( 'in-progress' );
				}
			}).done( function( response ) {
				if ( true === response.success ) {

					$form.addClass( 'content-removed' );
					$notices.removeClass( 'cdi-hide' );
					$notices.html( response.data.message ).removeClass( 'cdi-error' );

					if ( undefined !== response.data.slider ) {
						JetDataImport.showSlider( $form, response.data.slider );
					}

					JetDataImport.startImport();

				} else {
					$notices.addClass( 'cdi-error' ).removeClass( 'cdi-hide' );
					$notices.html( response.data.message );
				}

				$this.removeClass( 'in-progress' );
			});

		},

		showSlider: function( where, slider ) {
			setTimeout( function() {
				where.before( slider );
				where.remove();
				console.log('');
				$( document ).trigger( 'cdiSliderInit' );
			}, 2000 );
		},

		clearRemoveNotices: function() {

			var $this = $( this ),
				$form    = $this.closest( '.cdi-remove-form' ),
				$notices = $( '.cdi-remove-form__notices', $form );

			$notices.removeClass( 'cdi-error' ).addClass( 'cdi-hide' );

		},

		closePopup: function() {
			$( '.cdi-advanced-popup' ).addClass( 'popup-hidden' ).data( 'url', null );
			$( '.cdi-btn.in-progress' ).removeClass( 'in-progress' );
		},

		confirmImport: function() {
			var $this     = $( this ),
				$popup    = $this.closest( '.cdi-advanced-popup' ),
				$checkbox = $( '.cdi-advanced-popup__item input[type="radio"]:checked', $popup ),
				type      = 'append',
				url       = $popup.data( 'url' );

			$this.addClass( 'in-progress' );

			if ( undefined !== $checkbox.val() && '' !== $checkbox.val() ) {
				type = $checkbox.val();
			}

			url = url + '&type=' + type;

			window.location = url;
		},

		advancedImport: function() {

			var $this = $( this ),
				$item = $this.closest( '.advanced-item' ),
				$type = $( '.advanced-item__type-checkbox input[type="checkbox"]', $item ),
				url   = window.JetDataImportVars.advURLMask,
				full  = $item.data( 'full' ),
				skin  = $item.data( 'skin' ),
				min   = $item.data( 'lite' );

			$this.addClass( 'in-progress' );

			if ( $type.is(':checked') ) {
				url = url.replace( '<-file->', min );
			} else {
				url = url.replace( '<-file->', full );
			}

			url += '&skin=' + skin;

			$( '.cdi-advanced-popup' ).removeClass( 'popup-hidden' ).data( 'url', url );

		},

		advancedNotice: function() {
			var $this   = $( this ),
				$popup  = $this.closest( '.cdi-advanced-popup__content' ),
				$notice = $( '.cdi-advanced-popup__warning', $popup );

			if ( $this.is( ':checked' ) && 'replace' === $this.val() ) {
				$notice.removeClass( 'cdi-hide' );
			} else if ( ! $notice.hasClass( 'cdi-hide' ) ) {
				$notice.addClass( 'cdi-hide' );
			}

		},

		regenerateThumbnails: function() {

			var data = {
				action: 'jet-data-thumbnails',
				offset: 0,
				step:   window.JetRegenerateData.step,
				total:  window.JetRegenerateData.totalSteps
			};

			JetDataImport.ajaxRequest( data );
		},

		ajaxRequest: function( data ) {

			var complete;

			data.nonce    = window.JetDataImportVars.nonce;
			data.file     = window.JetDataImportVars.file;
			data.skin     = window.JetDataImportVars.skin;
			data.xml_type = window.JetDataImportVars.xml_type;

			$.ajax({
				url: window.ajaxurl,
				type: 'get',
				dataType: 'json',
				data: data,
				error: function() {

					if ( data.step ) {

						complete = Math.ceil( ( data.offset + data.step ) * 100 / ( data.total * data.step ) );

						JetDataImport.globalProgress
							.css( 'width', complete + '%' )
							.find( '.cdi-progress__label' ).text( complete + '%' );

						data.offset = data.offset + data.step;

						JetDataImport.ajaxRequest( data );
					} else {
						$( '#jet-import-progress' ).replaceWith(
							'<div class="import-failed">' + window.JetDataImportVars.error + '</div>'
						);
					}
				}
			}).done( function( response ) {
				if ( true === response.success && ! response.data.isLast ) {
					JetDataImport.ajaxRequest( response.data );
				}

				if ( response.data && response.data.redirect ) {
					window.location = response.data.redirect;
				}

				if ( response.data && response.data.complete ) {

					JetDataImport.globalProgress
						.css( 'width', response.data.complete + '%' )
						.find( '.cdi-progress__label' ).text( response.data.complete + '%' )
						.closest( '.cdi-progress__bar' )
						.next( '.cdi-progress__sub-label' ).text( response.data.complete + '%' );

					JetDataImport.globalProgress.siblings( '.cdi-progress__placeholder' ).remove();
				}

				if ( response.data && response.data.processed ) {
					$.each( response.data.processed, JetDataImport.updateSummary );
				}

			});

		},

		updateSummary: function( index, value ) {

			var $row       = $( 'tr[data-item="' + index + '"]' ),
				total      = parseInt( $row.data( 'total' ), 10 ),
				$done      = $( '.cdi-install-summary__done', $row ),
				$percent   = $( '.cdi-install-summary__percent', $row ),
				$progress  = $( '.cdi-progress__bar', $row ),
				$status    = $( '.cdi-progress-status', $row ),
				percentVal = Math.round( ( parseInt( value, 10 ) / total ) * 100 );

			if ( $done.hasClass( 'is-finished' ) ) {
				return;
			}

			if ( 100 === percentVal ) {
				$done.addClass( 'is-finished' ).closest( 'td' ).addClass( 'is-finished' );
				$status.html( '<span class="dashicons dashicons-yes"></span>' );
			}

			$done.html( value );
			$percent.html( percentVal );
			$progress.css( 'width', percentVal + '%' );

		},

		startImport: function() {

			var data    = {
					action: 'jet-data-import-chunk',
					chunk:  1
				};

			JetDataImport.ajaxRequest( data );

		},

		prepareImportArgs: function() {

			var file    = null,
				$upload = $( 'input[name="upload_file"]' ),
				$select = $( 'select[name="import_file"]' );

			if ( $upload.length && '' !== $upload.val() ) {
				file = $upload.val();
			}

			if ( $select.length && null === file ) {
				file = $( 'option:selected', $select ).val();
			}

			return '&tab=' + window.JetDataImportVars.tab + '&step=2&file=' + file;

		},

		goToImport: function() {

			var url = $('input[name="referrer"]').val();

			if ( ! $( this ).hasClass( 'disabled' ) ) {
				window.location = url + JetDataImport.prepareImportArgs();
			}

		},

		fileUpload: function() {

			var $button      = $( JetDataImport.selectors.upload ),
				$container   = $button.closest('.import-file'),
				$placeholder = $container.find('.import-file__placeholder'),
				$input       = $container.find('.import-file__input'),
				uploader     = wp.media.frames.file_frame = wp.media({
					title: window.JetDataImportVars.uploadTitle,
					button: {
						text: window.JetDataImportVars.uploadBtn
					},
					multiple: false
				}),
				openFrame = function () {
					uploader.open();
					return !1;
				},
				onFileSelect = function() {
					var attachment = uploader.state().get( 'selection' ).toJSON(),
						xmlData    = attachment[0],
						inputVal   = '';

					$placeholder.val( xmlData.url );
					JetDataImport.getFilePath( xmlData.url, $input );
				};

			$button.on( 'click', openFrame );
			uploader.on('select', onFileSelect );

		},

		getFilePath: function( fileUrl, $input ) {

			var $importBtn = $( JetDataImport.selectors.trigger ),
				path       = '';

			$importBtn.addClass( 'disabled' );

			$.ajax({
				url: window.ajaxurl,
				type: 'get',
				dataType: 'json',
				data: {
					action: 'jet-data-import-get-file-path',
					file: fileUrl,
					nonce: window.JetDataImportVars.nonce
				},
				error: function() {
					$importBtn.removeClass( 'disabled' );
					return !1;
				}
			}).done( function( response ) {
				$importBtn.removeClass( 'disabled' );
				if ( true === response.success ) {
					$input.val( response.data.path );
				}
			});

		}

	};

	JetDataImport.init();

}( jQuery, window.JetDataImport ) );
