/**
 * WordPress dependencies
 */
import { addFilter } from '@wordpress/hooks';

/**
 * Add custom attributes to the core/site-tagline block for our variation.
 *
 * @param {Object} settings Block settings.
 * @param {string} name     Block name.
 * @return {Object} Modified settings.
 */
function addRandomTaglineAttributes( settings, name ) {
	if ( name !== 'core/site-tagline' ) {
		return settings;
	}

	return {
		...settings,
		attributes: {
			...settings.attributes,
			isRandomTagline: {
				type: 'boolean',
				default: false,
			},
			taglines: {
				type: 'array',
				default: [],
			},
		},
	};
}

addFilter(
	'blocks.registerBlockType',
	'awesome-random-description/site-tagline-attributes',
	addRandomTaglineAttributes
);
