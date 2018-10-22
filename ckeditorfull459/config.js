/**
 * @license Copyright (c) 2003-2015, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';

	// allowedContent = true - disables automatic filtering and organizing of html code, necessary
	// if colgroups need to be used
    config.allowedContent = true;
	config.extraAllowedContent = 'table[class]; td(subhead); span(score); th[class];';
};
