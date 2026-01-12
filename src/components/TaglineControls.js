/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import {
	TextControl,
	Button,
	TextareaControl,
	Modal,
} from '@wordpress/components';
import { useState } from '@wordpress/element';

/**
 * TaglineControls component for managing random taglines.
 *
 * @param {Object}   props             Component props.
 * @param {Array}    props.taglines    Current taglines array.
 * @param {Function} props.setTaglines Function to update taglines.
 * @return {JSX.Element} The TaglineControls component.
 */
export default function TaglineControls( { taglines = [], setTaglines } ) {
	const [ showBulkImportModal, setShowBulkImportModal ] = useState( false );
	const [ bulkImportText, setBulkImportText ] = useState( '' );

	/**
	 * Add a new empty tagline.
	 */
	const addTagline = () => {
		setTaglines( [ ...taglines, '' ] );
	};

	/**
	 * Remove a tagline by index.
	 *
	 * @param {number} index Index of tagline to remove.
	 */
	const removeTagline = ( index ) => {
		const newTaglines = [ ...taglines ];
		newTaglines.splice( index, 1 );
		setTaglines( newTaglines );
	};

	/**
	 * Update a tagline by index.
	 *
	 * @param {number} index Index of tagline to update.
	 * @param {string} value New value.
	 */
	const updateTagline = ( index, value ) => {
		if ( typeof value !== 'string' ) {
			return;
		}

		// Limit tagline length to 500 characters.
		const sanitizedValue = value.substring( 0, 500 );

		const newTaglines = [ ...taglines ];
		newTaglines[ index ] = sanitizedValue;
		setTaglines( newTaglines );
	};

	/**
	 * Handle bulk text import.
	 */
	const handleBulkImport = () => {
		if ( ! bulkImportText.trim() ) {
			return;
		}

		// Validate bulk import text size.
		if ( bulkImportText.length > 50000 ) {
			// eslint-disable-next-line no-alert
			alert(
				__(
					'Text content too large. Please limit to 50KB.',
					'awesome-random-tagline'
				)
			);
			return;
		}

		const newTaglines = bulkImportText
			.split( '\n' )
			.slice( 0, 100 ) // Limit to 100 lines.
			.map( ( line ) => {
				// Sanitize each line.
				let cleaned = line.trim();
				// Prevent injection by removing dangerous characters at the start.
				cleaned = cleaned.replace( /^[=+\-@]/, '' );
				return cleaned.substring( 0, 500 ); // Limit line length.
			} )
			.filter( ( line ) => line.length > 0 );

		if ( newTaglines.length === 0 ) {
			// eslint-disable-next-line no-alert
			alert(
				__(
					'No valid taglines found in the text.',
					'awesome-random-tagline'
				)
			);
			return;
		}

		setTaglines( [ ...taglines, ...newTaglines ] );
		setBulkImportText( '' );
		setShowBulkImportModal( false );
	};

	/**
	 * Handle CSV file import.
	 *
	 * @param {Event} event File input change event.
	 */
	const handleFileImport = ( event ) => {
		const file = event.target.files[ 0 ];
		if ( ! file ) {
			return;
		}

		// Validate file type.
		if (
			file.type !== 'text/csv' &&
			! file.name.toLowerCase().endsWith( '.csv' )
		) {
			// eslint-disable-next-line no-alert
			alert(
				__(
					'Please select a valid CSV file.',
					'awesome-random-tagline'
				)
			);
			event.target.value = ''; // Clear the input.
			return;
		}

		// Validate file size (limit to 1MB).
		if ( file.size > 1024 * 1024 ) {
			// eslint-disable-next-line no-alert
			alert(
				__(
					'File size must be less than 1MB.',
					'awesome-random-tagline'
				)
			);
			event.target.value = ''; // Clear the input.
			return;
		}

		const reader = new FileReader();
		reader.onload = ( e ) => {
			const content = e.target.result;

			// Validate content length.
			if ( content.length > 100000 ) {
				// eslint-disable-next-line no-alert
				alert(
					__(
						'File content too large.',
						'awesome-random-tagline'
					)
				);
				return;
			}

			const lines = content.split( '\n' );
			const newTaglines = lines
				.slice( 0, 100 ) // Limit to 100 lines.
				.map( ( line ) => {
					// Prevent CSV injection.
					let cleaned = line.trim().replace( /^[=+\-@]/, '' );
					// Remove quotes and unescape doubled quotes.
					cleaned = cleaned
						.replace( /^"(.*)"$/, '$1' )
						.replace( /""/g, '"' );
					return cleaned;
				} )
				.filter( ( line ) => line.length > 0 && line.length <= 500 )
				.map( ( line ) => line.substring( 0, 500 ) );

			if ( newTaglines.length === 0 ) {
				// eslint-disable-next-line no-alert
				alert(
					__(
						'No valid taglines found in the CSV file.',
						'awesome-random-tagline'
					)
				);
				return;
			}

			setTaglines( [ ...taglines, ...newTaglines ] );
		};

		reader.onerror = () => {
			// eslint-disable-next-line no-alert
			alert(
				__(
					'Error reading file. Please try again.',
					'awesome-random-tagline'
				)
			);
		};

		reader.readAsText( file );
		event.target.value = ''; // Clear the input.
		setShowBulkImportModal( false );
	};

	/**
	 * Handle CSV export.
	 */
	const handleExport = () => {
		// Create CSV content with proper escaping.
		const csvContent = taglines
			.map( ( tagline ) => `"${ tagline.replace( /"/g, '""' ) }"` )
			.join( '\n' );

		// Create a blob and download link.
		const blob = new Blob( [ csvContent ], {
			type: 'text/csv;charset=utf-8;',
		} );
		const link = document.createElement( 'a' );

		// Set up the download.
		link.href = URL.createObjectURL( blob );
		link.download = 'taglines.csv';
		link.style.display = 'none';

		// Trigger the download.
		document.body.appendChild( link );
		link.click();

		// Clean up.
		document.body.removeChild( link );
		URL.revokeObjectURL( link.href );
	};

	return (
		<>
			<div className="taglines-panel">
				{ taglines.length === 0 ? (
					<p>
						{ __(
							'No taglines added yet. Add some below!',
							'awesome-random-tagline'
						) }
					</p>
				) : (
					taglines.map( ( tagline, index ) => (
						<div
							key={ index }
							className="tagline-item components-flex"
						>
							<div className="components-flex-item components-flex-block">
								<TextControl
									value={ tagline }
									onChange={ ( value ) =>
										updateTagline( index, value )
									}
									placeholder={ __(
										'Enter tagline...',
										'awesome-random-tagline'
									) }
								/>
							</div>
							<Button
								isDestructive
								onClick={ () => removeTagline( index ) }
								icon="no-alt"
								label={ __(
									'Remove tagline',
									'awesome-random-tagline'
								) }
							/>
						</div>
					) )
				) }

				<div className="tagline-actions components-flex components-flex-block components-flex-direction-column">
					<Button
						className="components-flex-item"
						variant="primary"
						onClick={ addTagline }
						icon="plus"
					>
						{ __( 'Add Tagline', 'awesome-random-tagline' ) }
					</Button>

					<Button
						className="components-flex-item"
						variant="secondary"
						onClick={ () => setShowBulkImportModal( true ) }
						icon="upload"
					>
						{ __( 'Bulk Import', 'awesome-random-tagline' ) }
					</Button>

					{ taglines.length > 0 && (
						<Button
							className="components-flex-item"
							variant="secondary"
							onClick={ handleExport }
							icon="download"
						>
							{ __( 'Export CSV', 'awesome-random-tagline' ) }
						</Button>
					) }
				</div>
			</div>

			{ showBulkImportModal && (
				<Modal
					title={ __(
						'Bulk Import Taglines',
						'awesome-random-tagline'
					) }
					onRequestClose={ () => setShowBulkImportModal( false ) }
				>
					<div className="bulk-import-modal">
						<TextareaControl
							label={ __(
								'Enter one tagline per line:',
								'awesome-random-tagline'
							) }
							value={ bulkImportText }
							onChange={ setBulkImportText }
							rows={ 10 }
						/>

						<div className="bulk-import-divider">
							<span>
								{ __( 'OR', 'awesome-random-tagline' ) }
							</span>
						</div>

						<div className="bulk-import-file">
							<label htmlFor="tagline-csv">
								{ __(
									'Import from CSV:',
									'awesome-random-tagline'
								) }
							</label>
							<input
								id="tagline-csv"
								type="file"
								accept=".csv,text/csv"
								onChange={ handleFileImport }
							/>
						</div>

						<div className="bulk-import-actions">
							<Button
								variant="primary"
								onClick={ handleBulkImport }
								disabled={ ! bulkImportText.trim() }
							>
								{ __(
									'Import Text',
									'awesome-random-tagline'
								) }
							</Button>

							<Button
								variant="secondary"
								onClick={ () => setShowBulkImportModal( false ) }
							>
								{ __( 'Cancel', 'awesome-random-tagline' ) }
							</Button>
						</div>
					</div>
				</Modal>
			) }
		</>
	);
}
