/**
 * WordPress dependencies
 */
import { registerBlockVariation } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import domReady from '@wordpress/dom-ready';

/**
 * Register block variations for core/site-tagline.
 */
domReady( () => {
	// Register the Random Site Tagline variation.
	registerBlockVariation( 'core/site-tagline', {
		name: 'random-tagline',
		title: __( 'Random Site Tagline', 'awesome-random-tagline' ),
		description: __(
			'Display a random tagline from a custom list on each page load.',
			'awesome-random-tagline'
		),
		icon: 'randomize',
		attributes: {
			isRandomTagline: true,
			taglines: [],
		},
		isActive: ( blockAttributes ) => blockAttributes.isRandomTagline === true,
		scope: [ 'inserter', 'transform' ],
	} );

	// Register a "default" variation so users can transform back from random.
	registerBlockVariation( 'core/site-tagline', {
		name: 'default-tagline',
		title: __( 'Site Tagline', 'awesome-random-tagline' ),
		description: __(
			'Display your site tagline or description.',
			'awesome-random-tagline'
		),
		icon: 'admin-site-alt3',
		attributes: {
			isRandomTagline: false,
		},
		isActive: ( blockAttributes ) => ! blockAttributes.isRandomTagline,
		scope: [ 'transform' ],
	} );
} );
