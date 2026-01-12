/**
 * WordPress dependencies
 */
import { registerBlockVariation } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

/**
 * Register the Random Site Tagline variation for core/site-tagline.
 */
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
