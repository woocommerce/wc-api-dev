/* global _wpStoreNuxTourSteps, _wpStoreNuxSettings */
( function( wp, $ ) {
	'use strict';

	if ( ! wp || ! wp.customize ) { return; }

	// Set up our namespace.
	var api = wp.customize;
	var settings = _wpStoreNuxSettings || {};

	api.WcStoreNuxSteps = [];

	if ( 'undefined' !== typeof _wpStoreNuxTourSteps ) {
		$.extend( api.WcStoreNuxSteps, _wpStoreNuxTourSteps );
	}

	// Simple support for bumping mc stats.
	function bumpStat( name ) {
		var uriComponent = '&x_store_nux=' + encodeURIComponent( name );
		new Image().src = document.location.protocol + '//pixel.wp.com/g.gif?v=wpcom-no-pv' + uriComponent + '&t=' + Math.random();
	}

	/**
	 * wp.customize.WcStoreNuxTour
	 *
	 */
	api.WcStoreNuxTour = {
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
				var step = self._getCurrentStep();
				if ( step.stat ) {
					bumpStat( step.stat + '-skip' );
				}

				if ( 0 === self.currentStep ) {
					self._hideTour( true );
				} else {
					self._showNextStep();
				}

				return false;
			});

			$( document ).on( 'click', '.sf-guided-tour-close', function() {
				self._hideTour( true );
			} );
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

			// Since the tour opens the api.Menus.availableMenuItemsPanel, we need to listen to this event too
			// and the availableMenuItemsPanel doesn't fire standard panel state events.
			$( '#customize-controls, .customize-section-back' ).on( 'click keydown', function( e ) {
				var currentStep = self._getCurrentStep();

				// If we aren't in the tour, or not in the updateMenus step and proper child steps, bail on this listener
				if (
					self._isTourHidden() ||
					! currentStep.action ||
					currentStep.action !== 'updateMenus' ||
					self.childTourStep <= 0
				) {
					return;
				}

				// Logic copied from api.Menus.AvailableMenuItemsPanelView, it is okay for clicks on these targets to keep the panel open.
				var isDeleteBtn = $( e.target ).is( '.item-delete, .item-delete *' ),
					isAddNewBtn = $( e.target ).is( '.add-new-menu-item, .add-new-menu-item *' );
				if ( ! isDeleteBtn && ! isAddNewBtn ) {
					self._hideTour( true );
				}
			} );

			// Listen to changes on Homepage Display Radio
			$( '#customize-control-show_on_front' ).on( 'change', function( e ) {
				var currentStep = self._getCurrentStep();

				if (
					self._isTourHidden() ||
					currentStep.section !== '#customize-control-show_on_front'
				){
					return;
				}

				if ( e && e.target && e.target.value === 'page' ) {
					// User has toggled proceed to next step
					self._showNextStep();
				}
			} );

			// Listen to changes on Homepage Select
			$('#_customize-dropdown-pages-page_on_front' ).on( 'change', function( e ) {
				var currentStep = self._getCurrentStep();

				if (
					self._isTourHidden() ||
					currentStep.section !== '#_customize-dropdown-pages-page_on_front'
				){
					return;
				}

				// Obvioulsy won't work for non enUs
				if ( $(' #_customize-dropdown-pages-page_on_front option:selected' ).text() === 'Shop' ) {
					self._showNextStep();
				}
			} );
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
					if ( step.stat ) {
						bumpStat( step.stat );
					}
				break;

				case 'exit':
					this._hideTour( true );
					if ( step.stat ) {
						bumpStat( step.stat );
					}
				break;
			}
		},

		_doChildSteps: function( step ) {
			var self = this;

			// Menus Child Tour.
			if ( step.action && step.action === 'updateMenus' ) {
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
					self.childTourStep += 1;
					self._renderStep( step.childSteps[ self.childTourStep ] );

					menuControl = api.Menus.getMenuControl( section.params.menu_id );

					// there is no core event emitted ( that i could find ) when the Add New Menu Item button is clicked.
					menuControl.container.find( '.add-new-menu-item' ).on( 'click', function( event ) {
						var currentStep, currentLeft = parseInt( self.$container.css( 'left' ) );
						currentStep = self._getCurrentStep();

						// If we aren't in the tour, or not in the updateMenus step, bail on this listener
						if (
							self._isTourHidden() ||
							! currentStep.action ||
							currentStep.action !== 'updateMenus'
						) {
							return;
						}

						// if this listener gets fired more than once, we should bail on the tour
						if ( self.childTourStep > 0 ) {
							self._hideTour();
							return;
						}

						// Adjust the left attribute of the container
						self.$container.css( 'left', ( $( '#available-menu-items-search' ).width() + currentLeft ) + 'px' );

						self.childTourStep += 1;
						self._renderStep( step.childSteps[ self.childTourStep ] );
					} );

					// Watch for clicks on pages in Menus.availableMenuItemsPanel
					$( '#available-menu-items-post_type-page li.menu-item-tpl' ).on( 'click', function(){
						var currentStep = self._getCurrentStep();

						// If we aren't in the tour, or not in the updateMenus step, bail on this listener
						if (
							self._isTourHidden() ||
							! currentStep.action ||
							currentStep.action !== 'updateMenus'
						) {
							return;
						}

						self.childTourStep += 1;

						if ( step.childSteps[ self.childTourStep ] ) {
							self._renderStep( step.childSteps[ self.childTourStep ] );
						}
					} );
				} );
			}
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
				// When deep in the menus, customize.state gets in a place where section and panel are both set
				// and this logic gets triggered when moving to the next homepage step
				// provide a way to short circuit it via the step config.
				if ( ! step.suppressHide ) {
					this._hideTour();
				}
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
						api.section.instance( 'title_tagline' ).expand();
					break;

					case 'updateMenus':
						api.panel( 'nav_menus' ).expand();
					break;

					case 'resetChildTour':
						self.childTourStep = -1;

						// close menu items panel
						api.Menus.availableMenuItemsPanel.close();

						// collapse menu panel
						api.panel( 'nav_menus' ).collapse();

						// open homepage section
						api.section( 'static_front_page' ).expand();

						// reset left position
						this.$container.css( 'left', ( $( '#customize-controls' ).width() + 10 ) + 'px' );
					break;

					case 'verifyHomepage':
						var dirtyValues = api.dirtyValues();

						// Verify the homepage has been set to a 'page', otherwise skip this step.
						// Needed to allow skipping of the homepage step entirely.
						if (
							api.settings.settings.show_on_front !== 'page' &&
							dirtyValues.show_on_front !== 'page'
						) {
							step = this._getNextStep();
						}
					break;
				}
			} else {
				this._closeAllSections();
			}

			// Does this step have a child tour?
			if ( step.childSteps ) {
				this._doChildSteps( step );
			}

			this._renderStep( step );
		},

		_renderStep: function( step ) {
			var template = wp.template( 'wc-store-tour-step' );

			this.$container.removeClass( 'sf-first-step' );
			this.$container.removeClass( 'sf-show-close' );

			if ( step.stat ) {
				bumpStat( step.stat );
			}

			if ( 0 === this.currentStep ) {
				step.first_step = true;
				this.$container.addClass( 'sf-first-step' );
			}

			if ( this._isLastStep() ) {
				step.last_step = true;
				this.$container.addClass( 'sf-last-step' );
			}

			if ( step.showClose ) {
				this.$container.addClass( 'sf-show-close' );
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
			return api.WcStoreNuxSteps[ this.currentStep ];
		},

		_getNextStep: function() {
			this.currentStep = this.currentStep + 1;
			return api.WcStoreNuxSteps[ this.currentStep ];
		},

		_isTourHidden: function() {
			return ( ( $( 'body' ).hasClass( 'sf-hidden' ) ) ? true : false );
		},

		_isLastStep: function() {
			return ( ( ( this.currentStep + 1 ) < api.WcStoreNuxSteps.length ) ? false : true );
		},

		showAlert: function() {
			var helpButton = $( '#customize-info button.customize-help-toggle' ), customizeInfoPanel, customizeInfoMessagePanel, currentMessage;

			// Remove some classes, add some others.
			customizeInfoPanel = $( '#customize-info' );
			customizeInfoPanel.addClass( 'sf-customize-alert' );
			helpButton.removeClass( 'dashicons-editor-help' );
			helpButton.addClass( 'dashicons-warning' );

			// Inject warning message.
			customizeInfoMessagePanel = customizeInfoPanel.find( '.customize-panel-description' );
			currentMessage = customizeInfoMessagePanel.text();
			customizeInfoMessagePanel.html(
				'<p class="sf-customize-alert-message">' + settings.alertMessage + '</p><p>' + currentMessage + '</p>'
			);
			bumpStat( 'alert-shown' );
		},
	};

	$( document ).ready( function() {
		if ( settings.autoStartTour ) {
			api.WcStoreNuxTour.init();
		}

		if ( settings.showTourAlert ) {
			api.WcStoreNuxTour.showAlert();
		}
	});
} )( window.wp, jQuery );

