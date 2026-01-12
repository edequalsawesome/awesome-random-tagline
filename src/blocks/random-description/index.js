/**
 * Registers the Random Description block.
 *
 * NOTE: This block is deprecated in favor of the Random Site Tagline variation
 * of the core site-tagline block. It is kept for backwards compatibility with
 * existing content.
 */
import { registerBlockType, createBlock } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import './style.scss';
import './editor.scss';
import './styles';
import Edit from './edit';
import metadata from './block.json';

/**
 * Register the block (deprecated - kept for backwards compatibility)
 */
registerBlockType( metadata.name, {
	...metadata,

	/**
	 * Mark as deprecated with guidance.
	 */
	description: __(
		'(Legacy) Use the new "Random Site Tagline" block instead. This block is kept for backwards compatibility.',
		'awesome-random-tagline'
	),

	/**
	 * Add transforms to allow converting to the new variation.
	 */
	transforms: {
		to: [
			{
				type: 'block',
				blocks: [ 'core/site-tagline' ],
				transform: ( attributes ) => {
					return createBlock( 'core/site-tagline', {
						isRandomTagline: true,
						taglines: attributes.taglines || [],
					} );
				},
			},
		],
	},

	/**
	 * @see ./edit.js
	 */
	edit: Edit,

	/**
	 * Save is handled server-side by PHP callback
	 */
	save: () => null,
} ); 