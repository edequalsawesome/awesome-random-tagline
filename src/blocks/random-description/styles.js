/**
 * WordPress dependencies
 */
import { registerBlockStyle } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

/**
 * Register block styles.
 */
registerBlockStyle( 'awesome-random-description/random-description', [
	{
		name: 'default',
		label: __( 'Default', 'awesome-random-tagline' ),
		isDefault: true,
	},
	{
		name: 'fancy',
		label: __( 'Fancy', 'awesome-random-tagline' ),
	},
	{
		name: 'minimal',
		label: __( 'Minimal', 'awesome-random-tagline' ),
	},
	{
		name: 'bold',
		label: __( 'Bold', 'awesome-random-tagline' ),
	},
] );
