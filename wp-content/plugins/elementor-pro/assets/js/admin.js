/*! elementor-pro - v1.14.0 - 12-02-2018 */
(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
var modules = {
	widget_template_edit_button: require( 'modules/library/assets/js/admin' ),
	forms_integrations: require( 'modules/forms/assets/js/admin' )
};

window.elementorProAdmin = {
	widget_template_edit_button: new modules.widget_template_edit_button(),
	forms_integrations: new modules.forms_integrations()
};
},{"modules/forms/assets/js/admin":2,"modules/library/assets/js/admin":4}],2:[function(require,module,exports){
module.exports = function(){
	var apiValidations = require( './admin/api-validations' );
	this.dripButton = new apiValidations( 'drip_api_token' );
	this.getResponse = new apiValidations( 'getresponse_api_key' );
	this.convertKit = new apiValidations( 'convertkit_api_key' );
	this.mailChimp = new apiValidations( 'mailchimp_api_key' );
	this.activeCcampaign = new apiValidations( 'activecampaign_api_key', 'activecampaign_api_url' );
};
},{"./admin/api-validations":3}],3:[function(require,module,exports){
module.exports = function( key, fieldID ) {
	var self = this;
	self.cacheElements = function() {
		this.cache = {
			$button: jQuery( '#elementor_pro_' + key + '_button' ),
			$apiKeyField: jQuery( '#elementor_pro_' + key ),
			$apiUrlField: jQuery( '#elementor_pro_' + fieldID )
		};
	};
	self.bindEvents = function() {
		var self = this;
		this.cache.$button.on( 'click', function( event ) {
			event.preventDefault();
			self.validateApi();
		});

		this.cache.$apiKeyField.on( 'change', function( event ) {
			self.setState( 'clear' );
		} );
	};
	self.validateApi = function() {
		this.setState( 'loading' );
		var apiKey = this.cache.$apiKeyField.val(),
		self = this;

		if ( '' === apiKey ) {
			this.setState( 'clear' );
			return;
		}

		if ( this.cache.$apiUrlField.length && '' === this.cache.$apiUrlField.val() ) {
			this.setState( 'clear' );
			return;
		}

		jQuery.post( ajaxurl, {
			action: self.cache.$button.data( 'action' ),
			api_key: apiKey,
			api_url: this.cache.$apiUrlField.val(),
			_nonce: self.cache.$button.data( 'nonce' )
		} ).done( function( data ) {
			if ( data.success ) {
				self.setState( 'success' );
			} else {
				self.setState( 'error' );
			}
		} ).fail( function() {
			self.setState();
		} );
	};
	self.setState = function( type ){
		var classes = [ 'loading', 'success', 'error' ],
			currentClass, classIndex;

		for ( classIndex in classes ) {
			currentClass = classes[ classIndex ];
			if ( type === currentClass ) {
				this.cache.$button.addClass( currentClass );
			} else {
				this.cache.$button.removeClass( currentClass );
			}
		}
	};
	self.init = function() {
		this.cacheElements();
		this.bindEvents();
	};
	self.init();
};

},{}],4:[function(require,module,exports){
module.exports = function(){
	var EditButton = require( './admin/edit-button' );
	this.editButton = new EditButton();
};
},{"./admin/edit-button":5}],5:[function(require,module,exports){
module.exports = function() {
	var self = this;

	self.init = function() {
		jQuery( document ).on( 'change', '.elementor-widget-template-select', function() {
			var $this = jQuery( this ),
				templateID = $this.val(),
				$editButton = $this.parents( 'p' ).find( '.elementor-edit-template' ),
				type = $this.find( '[value="' + templateID + '"]' ).data( 'type' );

			if ( 'page' !== type ) { // 'widget' is editable only from Elementor page
				$editButton.hide();

				return;
			}

			var editUrl = ElementorProConfig.i18n.home_url + '?p=' + templateID + '&elementor';

			$editButton
				.prop( 'href', editUrl )
				.show();

		} );
	};

	self.init();
};

},{}]},{},[1])
//# sourceMappingURL=admin.js.map
