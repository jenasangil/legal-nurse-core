<?php
/**
 * Shortcodes
 *
 * [site_var] — output a value from the ACF "Site Variables" options page.
 *
 * The options page holds two repeaters, each with `key` / `value` sub-fields:
 *   - text_variables   (e.g. phone_number, contact_email, address_line_1)
 *   - image_variables  (key + image value)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_shortcode( 'site_var', 'lnc_site_var_shortcode' );

/**
 * [site_var key="phone_number"]
 * [site_var key="contact_email" type="text" default="—"]
 * [site_var key="logo" type="image" output="img" size="medium" alt="Logo"]
 *
 * Attributes:
 *   key      (required) The variable key to look up.
 *   type     text | image           (default: text)
 *   default  Fallback if not found. (default: empty)
 *   output   For images: url | img  (default: url)
 *   size     For images: WP image size (default: full)
 *   alt      For images with output=img: alt text.
 *   source   ACF source id.         (default: option)
 *
 * @param array $atts
 * @return string
 */
function lnc_site_var_shortcode( $atts ) {
	$atts = shortcode_atts(
		[
			'key'     => '',
			'type'    => 'text',
			'default' => '',
			'output'  => 'url',
			'size'    => 'full',
			'alt'     => '',
			'source'  => 'option',
		],
		$atts,
		'site_var'
	);

	if ( '' === $atts['key'] || ! function_exists( 'get_field' ) ) {
		return esc_html( $atts['default'] );
	}

	$repeater = ( 'image' === $atts['type'] ) ? 'image_variables' : 'text_variables';
	$rows     = get_field( $repeater, $atts['source'] );

	if ( ! is_array( $rows ) ) {
		return esc_html( $atts['default'] );
	}

	// Find the matching row by key.
	$match = null;
	foreach ( $rows as $row ) {
		if ( isset( $row['key'] ) && $row['key'] === $atts['key'] ) {
			$match = $row;
			break;
		}
	}

	if ( null === $match ) {
		return esc_html( $atts['default'] );
	}

	// The stored value: prefer a "value" sub-field, else the first non-key sub-field.
	$value = $match['value'] ?? null;
	if ( null === $value ) {
		foreach ( $match as $sub_key => $sub_val ) {
			if ( 'key' !== $sub_key ) {
				$value = $sub_val;
				break;
			}
		}
	}

	if ( 'image' === $atts['type'] ) {
		return lnc_site_var_render_image( $value, $atts );
	}

	if ( is_array( $value ) ) {
		return esc_html( $atts['default'] );
	}

	return esc_html( (string) $value );
}

/**
 * Build a map of all site variables: key => value.
 * Text values are strings; image values resolve to their URL.
 * Cached per request.
 *
 * @return array<string,string>
 */
function lnc_get_site_vars_map() {
	static $map = null;

	if ( null !== $map ) {
		return $map;
	}

	$map = [];

	if ( ! function_exists( 'get_field' ) ) {
		return $map;
	}

	$text = get_field( 'text_variables', 'option' );
	if ( is_array( $text ) ) {
		foreach ( $text as $row ) {
			if ( isset( $row['key'], $row['value'] ) && ! is_array( $row['value'] ) ) {
				$map[ $row['key'] ] = (string) $row['value'];
			}
		}
	}

	$images = get_field( 'image_variables', 'option' );
	if ( is_array( $images ) ) {
		foreach ( $images as $row ) {
			if ( empty( $row['key'] ) ) {
				continue;
			}
			$value = $row['value'] ?? null;
			if ( is_array( $value ) ) {
				$url = (string) ( $value['url'] ?? '' );
			} elseif ( is_numeric( $value ) ) {
				$url = (string) wp_get_attachment_image_url( (int) $value, 'full' );
			} else {
				$url = (string) $value;
			}
			if ( '' !== $url ) {
				$map[ $row['key'] ] = $url;
			}
		}
	}

	return $map;
}

/**
 * Replace {key} tokens in content with their Site Variable values.
 * Only tokens matching a known key are replaced, so other braces are left alone.
 *
 * @param string $content
 * @return string
 */
function lnc_replace_site_var_tokens( $content ) {
	if ( ! is_string( $content ) || false === strpos( $content, '{' ) ) {
		return $content;
	}

	$map = lnc_get_site_vars_map();
	if ( empty( $map ) ) {
		return $content;
	}

	return preg_replace_callback(
		'/\{([a-z0-9_\-]+)\}/i',
		function ( $matches ) use ( $map ) {
			return array_key_exists( $matches[1], $map ) ? $map[ $matches[1] ] : $matches[0];
		},
		$content
	);
}

add_filter( 'the_content', 'lnc_replace_site_var_tokens', 20 );
add_filter( 'widget_text', 'lnc_replace_site_var_tokens', 20 );
add_filter( 'elementor/frontend/the_content', 'lnc_replace_site_var_tokens', 20 );

/**
 * Resolve an ACF image value (array | attachment ID | URL) into a URL or <img>.
 *
 * @param mixed $value
 * @param array $atts
 * @return string
 */
function lnc_site_var_render_image( $value, $atts ) {
	$id  = 0;
	$url = '';

	if ( is_array( $value ) ) {
		$id  = (int) ( $value['ID'] ?? $value['id'] ?? 0 );
		$url = (string) ( $value['url'] ?? '' );
	} elseif ( is_numeric( $value ) ) {
		$id = (int) $value;
	} elseif ( is_string( $value ) ) {
		$url = $value;
	}

	if ( 'img' === $atts['output'] ) {
		if ( $id ) {
			return wp_get_attachment_image( $id, $atts['size'], false, [ 'alt' => $atts['alt'] ] );
		}
		if ( $url ) {
			return sprintf( '<img src="%s" alt="%s">', esc_url( $url ), esc_attr( $atts['alt'] ) );
		}
		return esc_html( $atts['default'] );
	}

	// output = url
	if ( $id ) {
		$src = wp_get_attachment_image_url( $id, $atts['size'] );
		if ( $src ) {
			return esc_url( $src );
		}
	}

	return $url ? esc_url( $url ) : esc_html( $atts['default'] );
}
