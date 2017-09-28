/* global _wpCustomizeWcApiDevGuidedTourSteps */
( function( wp, $ ) {
	'use strict';

	if ( ! wp || ! wp.customize ) { return; }

	// Set up our namespace.
	var api = wp.customize;

	api.WcApiDevGuidedTourSteps = [];

	if ( 'undefined' !== typeof _wpCustomizeWcApiDevGuidedTourSteps ) {
		$.extend( api.WcApiDevGuidedTourSteps, _wpCustomizeWcApiDevGuidedTourSteps );
	}

	/**
	 * wp.customize.SFGuidedTour
	 *
	 */
	api.WcApiDevGuidedTour = {
		$container: null,
		currentStep: -1,
		childTourStep: -1,

		init: function() {
			this._setupUI();
		},

		_setupUI: function() {
			var self = this,
			    $wpCustomize = $( 'body.wp-customizer .wp-full-overlay' );

			this.$container = $( '<div/>' ).addClass( 'sf-guided-tour' );

			// Add guided tour div
			$wpCustomize.prepend( this.$container );

			// Add listeners
			this._addListeners();

			// Initial position
			this.$container.css( 'left', ( $( '#customize-controls' ).width() + 10 ) + 'px' ).on( 'transitionend', function() {
				self.$container.addClass( 'sf-loaded' );
			});

			// Show first step
			this._showNextStep();

			$( document ).on( 'click', '.sf-guided-tour-step .sf-nux-alt-button', function() {
				self._doAltStep();
				return false;
			});

			$( document ).on( 'click', '.sf-guided-tour-step .sf-nux-primary-button', function() {
				self._showNextStep();
				return false;
			});

			$( document ).on( 'click', '.sf-guided-tour-step .sf-guided-tour-skip', function() {
				if ( 0 === self.currentStep ) {
					self._hideTour( true );
				} else {
					self._showNextStep();
				}

				return false;
			});
		},

		_addListeners: function() {
			var self = this;

			api.state( 'expandedSection' ).bind( function() {
				self._adjustPosition();
			});

			api.state( 'expandedPanel' ).bind( function() {
				self._adjustPosition();
			});

			window.onbeforeunload = function( e ) {
				self._hideTour( true );
			};
		},

		_doAltStep: function() {
			var step = this._getCurrentStep(),
				self = this;

			switch ( step.altStep.action ) {
				case 'expandThemes':
					var nextStep, themePanel;
					themePanel = api.section.instance( 'themes' );
					themePanel.expand();
					
					nextStep = step.altStep;
					delete( nextStep.buttonText );
					self._renderStep( nextStep );
				break;

				case 'exit':
					this._hideTour( true );
				break;
			}
		},

		_doChildTourStep: function() {

		},

		_adjustPosition: function() {
			var step            = this._getCurrentStep(),
				expandedSection = api.state( 'expandedSection' ).get(),
				expandedPanel   = api.state( 'expandedPanel' ).get();

			if ( ! step ) {
				return;
			}

			this.$container.removeClass( 'sf-inside-section' );
			if ( expandedSection && step.section === expandedSection.id ) {
				this._moveContainer( $( expandedSection.container[1] ).find( '.customize-section-title' ) );
				this.$container.addClass( 'sf-inside-section' );
			} else if ( expandedPanel && step.panel === expandedPanel.id ) {
				this._moveContainer( $( expandedPanel.container[1] ).find( step.panelSection ) );
				this.$container.addClass( 'sf-inside-section' );
			} else if ( false === expandedSection && false === expandedPanel ) {
				if ( this._isTourHidden() ) {
					this._revealTour();
				} else {
					var selector = this._getSelector( step.section );
					this._moveContainer( selector );
				}
			} else {
				this._hideTour();
			}
		},

		_hideTour: function( remove ) {
			var self = this;

			// Already hidden?
			if ( this._isTourHidden() ) {
				return;
			}

			this.$container.css({
				transform: '',
				top: this.$container.offset().top
			});

			$( 'body' ).addClass( 'sf-exiting' ).on( 'animationend.storefront webkitAnimationEnd.storefront', function() {
				$( this ).removeClass( 'sf-exiting' ).off( 'animationend.storefront webkitAnimationEnd.storefront' ).addClass( 'sf-hidden' );
				self.$container.hide();

				if ( ! _.isUndefined( remove ) && true === remove ) {
					self._removeTour();
				}
			});
		},

		_revealTour: function() {
			 var self = this;

			$( 'body' ).removeClass( 'sf-hidden' );

			self.$container.show();

			$( 'body' ).addClass( 'sf-entering' ).on( 'animationend.storefront webkitAnimationEnd.storefront', function() {
				$( this ).removeClass( 'sf-entering' ).off( 'animationend.storefront webkitAnimationEnd.storefront' );

				self.$container.css({
					top: 'auto',
					transform: 'translateY(' + parseInt( self.$container.offset().top, 10 ) + 'px)'
				});
			});
		},

		_removeTour: function() {
			this.$container.remove();
		},

		_closeAllSections: function() {
			api.section.each( function ( section ) {
				section.collapse( { duration: 0 } );
			});

			api.panel.each( function ( panel ) {
				panel.collapse( { duration: 0 } );
			});
		},

		_showNextStep: function() {
			var step, template, self = this;

			if ( this._isLastStep() ) {
				this._hideTour( true );
				return;
			}

			// Get next step
			step = this._getNextStep();

			// Does this have a custom action?
			if ( step.action ) {
				switch ( step.action ) {
					case 'addLogo':
						api.section.instance('title_tagline').expand();
					break;

					case 'updateMenus':
						api.panel('nav_menus').expand();
					break;
				}
			} else {
				this._closeAllSections();
			}

			// Does this step have a child tour?
			if ( step.childSteps ) {
				
				// Currently only the menu item has child steps, so we can
				// add listeners here accordingly

				// Listener for selection of a menu panel, Display Child Step 0.
				api.state( 'expandedSection' ).bind( function() {
					var menuControl, section = api.state( 'expandedSection' ).get();
					
					if ( 
						! section.params ||
						section.params.type !== 'nav_menu' ||
						self.childTourStep !== -1
					) {
						return;
					}
					
					// A nav_menu section has been selected, advance to next step
					self.childTourStep = 0;
					self._renderStep( step.childSteps[ self.childTourStep ] );

					menuControl = api.Menus.getMenuControl( section.params.menu_id );

					// so dirty.
					// there is no core event emitted ( that i could find ) when the Add New Menu Item button is clicked.
					// this is why we can't have nice things.
					menuControl.container.find( '.add-new-menu-item' ).on( 'click', function( event ) {
						// Adjust the left attribute of the container
						var currentLeft = parseInt( self.$container.css( 'left' ) );
						self.$container.css( 'left', ( $( '#available-menu-items-search' ).width() + currentLeft ) + 'px' );

						self.childTourStep = 1;
						self._renderStep( step.childSteps[ self.childTourStep ] );
					} );
				} );

			}

			this._renderStep( step );
		},

		_renderStep: function( step ) {
			var template;
			// Convert line breaks to paragraphs
			step.message = this._lineBreaksToParagraphs( step.message );

			// Load template
			template = wp.template( 'wc-api-guided-tour-step' );

			this.$container.removeClass( 'sf-first-step' );

			if ( 0 === this.currentStep ) {
				step.first_step = true;
				this.$container.addClass( 'sf-first-step' );
			}

			if ( this._isLastStep() ) {
				step.last_step = true;
				this.$container.addClass( 'sf-last-step' );
			}

			this._moveContainer( this._getSelector( step.section ) );

			this.$container.html( template( step ) );
		},

		_moveContainer: function( $selector ) {
			var self = this, position;

			if ( ! $selector ) {
				return;
			}

			position = parseInt( $selector.offset().top, 10 ) + ( $selector.height() / 2 ) - 44;

			this.$container.addClass( 'sf-moving' ).css({ 'transform': 'translateY(' + parseInt( position, 10 ) + 'px)' }).on( 'transitionend.storefront', function() {
				self.$container.removeClass( 'sf-moving' );
				self.$container.off( 'transitionend.storefront' );
			} );
		},

		_getSelector: function( pointTo ) {
			var section = api.section( pointTo );

			// Check whether this is a section or a regular selector
			if ( ! _.isUndefined( section ) ) {
				return $( section.container[0] );
			}
			return $( pointTo );
		},

		_getCurrentStep: function() {
			return api.WcApiDevGuidedTourSteps[ this.currentStep ];
		},

		_getNextStep: function() {
			this.currentStep = this.currentStep + 1;
			return api.WcApiDevGuidedTourSteps[ this.currentStep ];
		},

		_isTourHidden: function() {
			return ( ( $( 'body' ).hasClass( 'sf-hidden' ) ) ? true : false );
		},

		_isLastStep: function() {
			return ( ( ( this.currentStep + 1 ) < api.WcApiDevGuidedTourSteps.length ) ? false : true );
		},

		_lineBreaksToParagraphs: function( message ) {
			return '<p>' + message.replace( '\n\n', '</p><p>' ) + '</p>';
		}
	};

	$( document ).ready( function() {
		api.WcApiDevGuidedTour.init();
	});
} )( window.wp, jQuery );

