/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import {
    PanelBody,
    TextControl,
    Button,
    TextareaControl,
    Placeholder,
    Modal
} from '@wordpress/components';
import {
    useBlockProps,
    InspectorControls,
    BlockControls,
    AlignmentToolbar
} from '@wordpress/block-editor';
import { useState, useEffect } from '@wordpress/element';
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import './editor.scss';

/**
 * Edit component for the Random Description block
 */
export default function Edit({ attributes, setAttributes, clientId, isSelected }) {
    const {
        taglines = [],
        textAlign,
        style
    } = attributes;

    const [showBulkImportModal, setShowBulkImportModal] = useState(false);
    const [bulkImportText, setBulkImportText] = useState('');
    const [csvFile, setCsvFile] = useState(null);

    // Get site description from WordPress
    const siteDescription = useSelect(select => {
        return select('core').getSite()?.description || '';
    }, []);

    // Add a tagline
    const addTagline = () => {
        const newTaglines = [...taglines, ''];
        setAttributes({ taglines: newTaglines });
    };

    // Remove a tagline by index
    const removeTagline = (index) => {
        const newTaglines = [...taglines];
        newTaglines.splice(index, 1);
        setAttributes({ taglines: newTaglines });
    };

    // Update a tagline by index
    const updateTagline = (index, value) => {
        // Validate input
        if (typeof value !== 'string') return;
        
        // Limit tagline length to 500 characters
        const sanitizedValue = value.substring(0, 500);
        
        const newTaglines = [...taglines];
        newTaglines[index] = sanitizedValue;
        setAttributes({ taglines: newTaglines });
    };

    // Handle bulk import
    const handleBulkImport = () => {
        if (!bulkImportText.trim()) return;
        
        // Validate bulk import text size
        if (bulkImportText.length > 50000) { // 50KB limit
            alert(__('Text content too large. Please limit to 50KB.', 'awesome-random-tagline'));
            return;
        }
        
        const newTaglines = bulkImportText
            .split('\n')
            .slice(0, 100) // Limit to 100 lines
            .map(line => {
                // Sanitize each line
                let cleaned = line.trim();
                // Prevent injection by removing dangerous characters at the start
                cleaned = cleaned.replace(/^[=+\-@]/, '');
                return cleaned.substring(0, 500); // Limit line length
            })
            .filter(line => line.length > 0);
        
        if (newTaglines.length === 0) {
            alert(__('No valid taglines found in the text.', 'awesome-random-tagline'));
            return;
        }
        
        setAttributes({ taglines: [...taglines, ...newTaglines] });
        setBulkImportText('');
        setShowBulkImportModal(false);
    };

    // Handle CSV file import
    const handleFileImport = (event) => {
        const file = event.target.files[0];
        if (!file) return;
        
        // Validate file type
        if (file.type !== 'text/csv' && !file.name.toLowerCase().endsWith('.csv')) {
            alert(__('Please select a valid CSV file.', 'awesome-random-tagline'));
            event.target.value = ''; // Clear the input
            return;
        }
        
        // Validate file size (limit to 1MB)
        if (file.size > 1024 * 1024) {
            alert(__('File size must be less than 1MB.', 'awesome-random-tagline'));
            event.target.value = ''; // Clear the input
            return;
        }
        
        const reader = new FileReader();
        reader.onload = (e) => {
            const content = e.target.result;
            
            // Validate content length
            if (content.length > 100000) { // 100KB limit
                alert(__('File content too large.', 'awesome-random-tagline'));
                return;
            }
            
            const lines = content.split('\n');
            const newTaglines = lines
                .slice(0, 100) // Limit to 100 lines
                .map(line => {
                    // Prevent CSV injection by removing dangerous characters at the start
                    let cleaned = line.trim().replace(/^[=+\-@]/, '');
                    // Remove quotes and unescape doubled quotes
                    cleaned = cleaned.replace(/^"(.*)"$/, '$1').replace(/""/g, '"');
                    return cleaned;
                })
                .filter(line => line.length > 0 && line.length <= 500) // Reasonable length limits
                .map(line => line.substring(0, 500)); // Ensure we don't exceed 500 chars
                
            if (newTaglines.length === 0) {
                alert(__('No valid taglines found in the CSV file.', 'awesome-random-tagline'));
                return;
            }
                
            setAttributes({ taglines: [...taglines, ...newTaglines] });
        };
        
        reader.onerror = () => {
            alert(__('Error reading file. Please try again.', 'awesome-random-tagline'));
        };
        
        reader.readAsText(file);
        event.target.value = ''; // Clear the input
        setShowBulkImportModal(false);
    };

    // Handle CSV export
    const handleExport = () => {
        // Create CSV content with proper escaping
        const csvContent = taglines
            .map(tagline => `"${tagline.replace(/"/g, '""')}"`)
            .join('\n');
        
        // Create a blob and download link
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        
        // Set up the download
        link.href = URL.createObjectURL(blob);
        link.download = 'taglines.csv';
        link.style.display = 'none';
        
        // Trigger the download
        document.body.appendChild(link);
        link.click();
        
        // Clean up
        document.body.removeChild(link);
        URL.revokeObjectURL(link.href);
    };

    // UseEffect to auto-import site tagline if no taglines exist
    useEffect(() => {
        if (taglines.length === 0 && siteDescription) {
            setAttributes({ taglines: [siteDescription] });
        }
    }, [siteDescription]);

    // Block props with styles
    const blockProps = useBlockProps({
        className: textAlign ? `has-text-align-${textAlign}` : '',
        style: { textAlign }
    });

    // Combine classes
    blockProps.className = `${blockProps.className} ${attributes.className || ''}`.trim();

    return (
        <>
            <BlockControls>
                <AlignmentToolbar
                    value={textAlign}
                    onChange={(value) => setAttributes({ textAlign: value })}
                />
            </BlockControls>
            
            <InspectorControls>
                <PanelBody title={__('Taglines', 'awesome-random-tagline')} initialOpen={true}>
                    <div className="taglines-panel">
                        {taglines.length === 0 ? (
                            <p>{__('No taglines added yet. Add some below!', 'awesome-random-tagline')}</p>
                        ) : (
                            taglines.map((tagline, index) => (
                                <div key={index} className="tagline-item components-flex">
                                    <div className="components-flex-item components-flex-block">
                                        <TextControl
                                            value={tagline}
                                            onChange={(value) => updateTagline(index, value)}
                                        />
                                    </div>
                                    <Button
                                        isDestructive
                                        onClick={() => removeTagline(index)}
                                        icon="no-alt"
                                        label={__('Remove tagline', 'awesome-random-tagline')}
                                    />
                                </div>
                            ))
                        )}
                        
                        <div className="tagline-actions components-flex components-flex-block components-flex-direction-column">
                            <Button
                                className="components-flex-item"
                                isPrimary
                                onClick={addTagline}
                                icon="plus"
                            >
                                {__('Add Tagline', 'awesome-random-tagline')}
                            </Button>
                            
                            <Button
                                className="components-flex-item"
                                isSecondary
                                onClick={() => setShowBulkImportModal(true)}
                                icon="upload"
                            >
                                {__('Bulk Import', 'awesome-random-tagline')}
                            </Button>

                            {taglines.length > 0 && (
                                <Button
                                    className="components-flex-item"
                                    isSecondary
                                    onClick={handleExport}
                                    icon="download"
                                >
                                    {__('Export CSV', 'awesome-random-tagline')}
                                </Button>
                            )}
                        </div>
                    </div>
                </PanelBody>
            </InspectorControls>
            
            <div {...blockProps}>
                {taglines.length === 0 ? (
                    <Placeholder
                        icon="format-quote"
                        label={__('Random Description', 'awesome-random-tagline')}
                        instructions={__('Add taglines in the block settings to display a random one each time the page loads.', 'awesome-random-tagline')}
                    >
                        <Button
                            isPrimary
                            onClick={addTagline}
                        >
                            {__('Add Your First Tagline', 'awesome-random-tagline')}
                        </Button>
                    </Placeholder>
                ) : (
                    <div className="random-description-preview">
                        <div className="random-description-content">
                            <p>{taglines[0] || __('Your random tagline will appear here', 'awesome-random-tagline')}</p>
                            <div className="random-description-info">
                                <span className="random-description-count">
                                    {__('You have', 'awesome-random-tagline')} {taglines.length} {taglines.length === 1 ? __('tagline', 'awesome-random-tagline') : __('taglines', 'awesome-random-tagline')}
                                </span>
                            </div>
                        </div>
                    </div>
                )}
            </div>
            
            {showBulkImportModal && (
                <Modal
                    title={__('Bulk Import Taglines', 'awesome-random-tagline')}
                    onRequestClose={() => setShowBulkImportModal(false)}
                >
                    <div className="bulk-import-modal">
                        <TextareaControl
                            label={__('Enter one tagline per line:', 'awesome-random-tagline')}
                            value={bulkImportText}
                            onChange={setBulkImportText}
                            rows={10}
                        />
                        
                        <div className="bulk-import-divider">
                            <span>{__('OR', 'awesome-random-tagline')}</span>
                        </div>
                        
                        <div className="bulk-import-file">
                            <label htmlFor="tagline-csv">{__('Import from CSV:', 'awesome-random-tagline')}</label>
                            <input
                                id="tagline-csv"
                                type="file"
                                accept=".csv,text/csv"
                                onChange={handleFileImport}
                            />
                        </div>
                        
                        <div className="bulk-import-actions">
                            <Button
                                isPrimary
                                onClick={handleBulkImport}
                                disabled={!bulkImportText.trim()}
                            >
                                {__('Import Text', 'awesome-random-tagline')}
                            </Button>
                            
                            <Button
                                isSecondary
                                onClick={() => setShowBulkImportModal(false)}
                            >
                                {__('Cancel', 'awesome-random-tagline')}
                            </Button>
                        </div>
                    </div>
                </Modal>
            )}
        </>
    );
} 