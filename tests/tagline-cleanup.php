<?php
/**
 * Run with: wp eval-file tests/tagline-cleanup.php
 */

if ( ! class_exists( 'Random_Tagline_Variation' ) ) {
	throw new RuntimeException( 'Activate Awesome Random Site Tagline before running this check.' );
}

function art_test_assert( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

function art_site_tagline_attrs( $blocks, &$taglines = array() ) {
	foreach ( $blocks as $block ) {
		if ( 'core/site-tagline' === $block['blockName'] ) {
			$taglines[] = $block['attrs'];
		}
		if ( ! empty( $block['innerBlocks'] ) ) {
			art_site_tagline_attrs( $block['innerBlocks'], $taglines );
		}
	}

	return $taglines;
}

function art_paragraph_blocks( $blocks, &$paragraphs = array() ) {
	foreach ( $blocks as $block ) {
		if ( 'core/paragraph' === $block['blockName'] ) {
			$paragraphs[] = $block;
		}
		if ( ! empty( $block['innerBlocks'] ) ) {
			art_paragraph_blocks( $block['innerBlocks'], $paragraphs );
		}
	}

	return $paragraphs;
}

$inactive = array( 'isRandomTagline' => false, 'taglines' => array( "O'Reilly", 'café 👋' ) );
$active   = array( 'isRandomTagline' => true, 'taglines' => array( "Keep 'this'", 'café 👋' ) );
$sibling  = array(
	'blockName'    => 'core/paragraph',
	'attrs'        => array( 'metadata' => array( 'name' => "Sibling O'Reilly 👋" ) ),
	'innerBlocks'  => array(),
	'innerHTML'    => '<p>Keep \\ raw path and O\'Reilly café 👋</p>',
	'innerContent' => array( '<p>Keep \\ raw path and O\'Reilly café 👋</p>' ),
);
$blocks   = array(
	array(
		'blockName'    => 'core/template-part',
		'attrs'        => array( 'slug' => 'header', 'theme' => 'test' ),
		'innerBlocks'  => array(
			array(
				'blockName'    => 'core/group',
				'attrs'        => array(),
				'innerBlocks'  => array(
					array( 'blockName' => 'core/site-tagline', 'attrs' => $inactive, 'innerBlocks' => array(), 'innerHTML' => '', 'innerContent' => array() ),
					array( 'blockName' => 'core/site-tagline', 'attrs' => $active, 'innerBlocks' => array(), 'innerHTML' => '', 'innerContent' => array() ),
					$sibling,
				),
				'innerHTML'    => '<div class="wp-block-group"></div>',
				'innerContent' => array( '<div class="wp-block-group">', null, null, null, '</div>' ),
			),
		),
		'innerHTML'    => '',
		'innerContent' => array( null ),
	),
);
$nested = serialize_blocks( $blocks );
$legacy = '<!-- wp:awesome-random-description/random-description {"taglines":["O\'Reilly","café 👋"]} /-->';
$plain  = '<!-- wp:paragraph --><p>O\'Reilly café 👋</p><!-- /wp:paragraph -->';
$ids    = array();
$cases  = array(
	'canonical'  => $nested,
	'namespaced' => str_replace( 'wp:site-tagline', 'wp:core/site-tagline', $nested ),
	'legacy'     => $legacy,
	'plain'      => $plain,
);

try {
	foreach ( $cases as $name => $content ) {
		$id = wp_insert_post(
			array(
				'post_title'   => 'ART cleanup fixture ' . $name,
				'post_status'  => 'draft',
				'post_type'    => 'wp_template_part',
				'post_content' => wp_slash( $content ),
			),
			true
		);
		art_test_assert( ! is_wp_error( $id ), 'Could not create ' . $name . ' fixture.' );
		$ids[] = $id;
		$saved = get_post_field( 'post_content', $id, 'raw' );

		if ( 'canonical' === $name || 'namespaced' === $name ) {
			$attrs = art_site_tagline_attrs( parse_blocks( $saved ) );
			art_test_assert( 2 === count( $attrs ), 'Nested taglines were not both parsed for ' . $name . '.' );
			art_test_assert( ! isset( $attrs[0]['taglines'] ), 'Inactive nested tagline retained its list for ' . $name . '.' );
			art_test_assert( $active['taglines'] === $attrs[1]['taglines'], 'Active nested tagline changed for ' . $name . '.' );
			$paragraphs = art_paragraph_blocks( parse_blocks( $saved ) );
			art_test_assert( 1 === count( $paragraphs ) && $sibling['attrs'] === $paragraphs[0]['attrs'] && $sibling['innerHTML'] === $paragraphs[0]['innerHTML'], 'Unrelated sibling changed for ' . $name . '.' );
			wp_update_post( array( 'ID' => $id, 'post_content' => wp_slash( $saved ) ) );
			$repeat = art_site_tagline_attrs( parse_blocks( get_post_field( 'post_content', $id, 'raw' ) ) );
			art_test_assert( $attrs === $repeat, 'Second save changed parsed tagline attributes for ' . $name . '.' );
		} else {
			art_test_assert( $content === $saved, 'No-op content changed for ' . $name . '.' );
		}
	}
} finally {
	foreach ( $ids as $id ) {
		wp_delete_post( $id, true );
	}
}

WP_CLI::success( 'Nested inactive taglines are cleaned without changing active, legacy, or no-op content.' );
