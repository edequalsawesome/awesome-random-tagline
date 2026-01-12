/**
 * WordPress dependencies
 */
import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useEffect } from '@wordpress/element';
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import TaglineControls from '../components/TaglineControls';
import '../styles/editor.scss';

/**
 * Higher-order component to add random tagline controls to site-tagline block.
 */
const withRandomTaglineControls = createHigherOrderComponent(
	( BlockEdit ) => {
		return ( props ) => {
			const { name, attributes, setAttributes } = props;

			// Only apply to site-tagline block with our variation active.
			if ( name !== 'core/site-tagline' || ! attributes.isRandomTagline ) {
				return <BlockEdit { ...props } />;
			}

			const { taglines = [] } = attributes;

			// Get site description from WordPress.
			const siteDescription = useSelect( ( select ) => {
				return select( 'core' ).getSite()?.description || '';
			}, [] );

			// Auto-import site tagline if no taglines exist.
			useEffect( () => {
				if ( taglines.length === 0 && siteDescription ) {
					setAttributes( { taglines: [ siteDescription ] } );
				}
			}, [ siteDescription, taglines.length, setAttributes ] );

			/**
			 * Update taglines attribute.
			 *
			 * @param {Array} newTaglines New taglines array.
			 */
			const setTaglines = ( newTaglines ) => {
				setAttributes( { taglines: newTaglines } );
			};

			return (
				<>
					<BlockEdit { ...props } />
					<InspectorControls>
						<PanelBody
							title={ __(
								'Random Taglines',
								'awesome-random-tagline'
							) }
							initialOpen={ true }
						>
							<p className="components-base-control__help">
								{ __(
									'Add multiple taglines below. A random one will be displayed each time the page loads.',
									'awesome-random-tagline'
								) }
							</p>
							<TaglineControls
								taglines={ taglines }
								setTaglines={ setTaglines }
							/>
						</PanelBody>
					</InspectorControls>
				</>
			);
		};
	},
	'withRandomTaglineControls'
);

addFilter(
	'editor.BlockEdit',
	'awesome-random-description/random-tagline-controls',
	withRandomTaglineControls
);
