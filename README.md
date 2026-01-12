# Awesome Random Site Tagline

A lightweight WordPress plugin that adds a "Random Site Tagline" variation to the core Site Tagline block, displaying a random tagline from a custom list on each page load.

## Description

The Awesome Random Site Tagline plugin extends the core WordPress Site Tagline block with a new variation that displays random taglines. Instead of showing your site's static tagline, you can provide a list of taglines and one will be randomly selected on each page load. The plugin integrates seamlessly with WordPress's native block system, inheriting all core styling and typography settings.

## Features

- **Native Integration**: Extends the core Site Tagline block instead of adding a custom block
- **Automatic Styling**: Inherits all theme styling from the core Site Tagline block
- **Easy Management**: Add, edit, and remove taglines directly from the block inspector
- **Bulk Import**: Import multiple taglines at once via text input or CSV file upload
- **Export Functionality**: Export your taglines to CSV for backup or sharing
- **Immediate Display**: Random taglines appear instantly with no flash or loading delay
- **Responsive Design**: Works beautifully on all device sizes
- **Accessibility**: Built with proper ARIA labels and semantic HTML
- **Performance Optimized**: Lightweight filter-based approach with minimal overhead
- **Backwards Compatible**: Existing blocks from previous versions continue to work

## Installation

### From WordPress Admin

1. Go to Plugins > Add New
2. Search for "Awesome Random Description Block"
3. Install and activate the plugin

### Manual Installation

1. Upload the plugin files to the `/wp-content/plugins/awesome-random-tagline` directory
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Gutenberg editor to add the 'Awesome Random Description' block to your pages

## Usage

1. **Add the Block**: Insert the "Random Site Tagline" block from the Gutenberg block inserter (look for it in the Widgets or Design category)
2. **Add Taglines**: Use the "Random Taglines" panel in the block inspector to add your taglines
3. **Customize**: Use the standard Site Tagline block controls for typography, colors, and spacing
4. **Publish**: Save your page and watch your taglines rotate randomly on each page load

### Migrating from Previous Versions

If you have existing "Random Description" blocks from previous versions (1.x), they will continue to work. However, you can migrate them to the new Site Tagline variation:

1. Edit the page containing the old block
2. Select the "Random Description" block
3. Click the block toolbar and select "Transform to"
4. Choose "Site Tagline" from the options

The migration will preserve all your taglines.

## Performance & Accessibility

### Performance Optimizations
- **Server-Side Randomization**: Random taglines are selected server-side for immediate display
- **No Flash Effect**: Taglines appear instantly without any visual delay or replacement
- **Minimal Dependencies**: Streamlined codebase with only essential dependencies
- **Optimized Build Process**: Minified and optimized assets
- **Lightweight Frontend**: No JavaScript required for basic functionality

### Accessibility Features
- **ARIA Live Regions**: Screen readers are notified of content changes
- **Semantic HTML**: Proper heading structure and meaningful markup
- **Keyboard Navigation**: Full keyboard accessibility support
- **High Contrast Support**: Works with high contrast themes and modes

## Frequently Asked Questions

### How often do the descriptions change?

The descriptions change each time a page is loaded or refreshed. Each visitor will see a random tagline from your collection, selected server-side for immediate display.

### Can I use this with any theme?

Yes, the Awesome Random Description Block is designed to work with any WordPress theme that supports Gutenberg blocks.

### Are there any animations?

No, the block displays taglines immediately without any animations or transitions. This ensures fast loading and eliminates any flash effects.

### Can I style the block?

Absolutely! The block supports all WordPress block styling options including:
- Typography (font size, weight, style)
- Colors (text and background)
- Spacing (margin and padding)
- Text alignment (left, center, right, justify)
- Custom CSS classes

### Can I export my taglines?

Yes, you can export all your taglines to a CSV file using the export button in the block settings.

### Can I import taglines in bulk?

Yes, you can import multiple taglines at once by:
1. Typing or pasting them in the bulk import text area (one per line)
2. Uploading a CSV file with your taglines

## Technical Details

- **WordPress Version**: 6.0+
- **PHP Version**: 7.4+
- **Architecture**: Block variation extending core/site-tagline
- **Dependencies**: WordPress core only
- **File Size**: < 50KB total

## Changelog

### 2026.01.12 (Major Redesign)
* **Complete Architecture Overhaul**
* Converted from standalone block to Site Tagline block variation
* Now extends core/site-tagline block instead of registering a custom block
* Automatic inheritance of all core block styling (typography, colors, spacing)
* Reduced maintenance overhead by leveraging WordPress core updates
* Switched to date-based versioning (YYYY.MM.DD)
* Increased minimum WordPress requirement to 6.0+
* Legacy blocks continue to work with backwards compatibility
* Added transform option to migrate old blocks to new variation
* Admin notice for users with legacy blocks encouraging migration
* Cleaner, more maintainable codebase using WordPress hooks and filters
* Import/export CSV functionality preserved

### 1.7.1
* Fixed issue where multiple blocks on the same page would display the same random tagline
* Replaced array_rand() with wp_rand() to ensure each block gets a truly random selection

### 1.7.0
* Added full text alignment controls with left, center, right, and justify options
* Implemented standard WordPress block toolbar alignment buttons

### 1.6.1
* Security enhancement update with comprehensive input validation
* Enhanced file upload security with type, size, and content validation
* Added capability checks and user permission validation

### 1.6.0
* Removed all animation functionality for immediate display
* Implemented server-side random tagline selection
* Eliminated flash effect on page load

### 1.5.0
* Fixed naming inconsistencies
* Optimized frontend script loading
* Improved accessibility

## Development

### Building the Plugin

```bash
npm install
npm run build
```

### Development Mode

```bash
npm run start
```

### Linting

```bash
npm run lint:js
npm run lint:css
```

## Support

For support, feature requests, or bug reports, please visit [our website](https://edequalsaweso.me/random-site-description) or contact the plugin author.

## License

This plugin is licensed under the GPL-3.0+ license.