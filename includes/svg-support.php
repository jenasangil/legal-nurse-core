<?php
/**
 * SVG Support
 *
 * Enables SVG uploads and safe rendering in the WordPress media library.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Allow SVG uploads in the media library.
 */
add_filter( 'upload_mimes', 'lnc_allow_svg_uploads' );
function lnc_allow_svg_uploads( $mimes ) {
	$mimes['svg']  = 'image/svg+xml';
	$mimes['svgz'] = 'image/svg+xml';
	return $mimes;
}

/**
 * Sanitize SVG files on upload to strip potentially malicious code.
 */
add_filter( 'wp_handle_upload_prefilter', 'lnc_sanitize_svg_on_upload' );
function lnc_sanitize_svg_on_upload( $file ) {
	if ( 'image/svg+xml' !== $file['type'] ) {
		return $file;
	}

	$sanitized = lnc_sanitize_svg( $file['tmp_name'] );

	if ( is_wp_error( $sanitized ) ) {
		$file['error'] = $sanitized->get_error_message();
	}

	return $file;
}

/**
 * Strip disallowed elements and attributes from an SVG file.
 *
 * @param string $file Absolute path to the uploaded SVG file.
 * @return true|WP_Error
 */
function lnc_sanitize_svg( $file ) {
	$content = file_get_contents( $file );

	if ( false === $content ) {
		return new WP_Error( 'lnc_svg_read_error', __( 'Could not read the SVG file.', 'legal-nurse-core' ) );
	}

	// Disallow PHP/script tags before parsing.
	if ( preg_match( '/<\?php/i', $content ) ) {
		return new WP_Error( 'lnc_svg_invalid', __( 'Invalid SVG file.', 'legal-nurse-core' ) );
	}

	$dom = new DOMDocument();
	libxml_use_internal_errors( true );
	$dom->loadXML( $content, LIBXML_NONET );
	libxml_clear_errors();

	$disallowed_tags = [
		'script', 'use', 'foreignObject', 'animate', 'set',
		'animateMotion', 'animateTransform', 'animateColor',
	];

	foreach ( $disallowed_tags as $tag ) {
		foreach ( $dom->getElementsByTagName( $tag ) as $node ) {
			$node->parentNode->removeChild( $node );
		}
	}

	// Remove event-handler attributes (on*) and xlink:href pointing to URLs.
	$xpath = new DOMXPath( $dom );
	foreach ( $xpath->query( '//@*[starts-with(local-name(), "on")]' ) as $attr ) {
		$attr->ownerElement->removeAttributeNode( $attr );
	}

	$clean = $dom->saveXML();

	if ( false === $clean || ! file_put_contents( $file, $clean ) ) {
		return new WP_Error( 'lnc_svg_write_error', __( 'Could not save the sanitized SVG file.', 'legal-nurse-core' ) );
	}

	return true;
}

/**
 * Fix the SVG thumbnail display in the media library grid view.
 */
add_filter( 'wp_prepare_attachment_for_js', 'lnc_fix_svg_media_thumbnail', 10, 3 );
function lnc_fix_svg_media_thumbnail( $response, $attachment, $meta ) {
	if ( 'image/svg+xml' === $response['mime'] && empty( $response['sizes'] ) ) {
		$svg_url = wp_get_attachment_url( $attachment->ID );
		$response['sizes'] = [
			'full' => [
				'url'         => $svg_url,
				'width'       => isset( $meta['width'] ) ? $meta['width'] : 0,
				'height'      => isset( $meta['height'] ) ? $meta['height'] : 0,
				'orientation' => 'landscape',
			],
		];
	}
	return $response;
}

/**
 * Display SVG images correctly in the media library list view.
 */
add_filter( 'wp_get_attachment_image_src', 'lnc_fix_svg_attachment_image_src', 10, 4 );
function lnc_fix_svg_attachment_image_src( $image, $attachment_id, $size, $icon ) {
	if ( $image ) {
		return $image;
	}

	$mime = get_post_mime_type( $attachment_id );
	if ( 'image/svg+xml' !== $mime ) {
		return $image;
	}

	return [ wp_get_attachment_url( $attachment_id ), 0, 0, false ];
}
