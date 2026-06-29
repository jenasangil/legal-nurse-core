<?php
/**
 * Elementor LCP Hero Extension
 *
 * Extends the Elementor Container widget with a "LCP Hero Image" control group.
 * When enabled, an <img> element is injected inside the container so the browser
 * can discover the hero image in the initial HTML document and apply
 * fetchpriority="high", satisfying Core Web Vitals LCP requirements.
 *
 * The <img> is absolutely positioned and sized to cover the container,
 * mirroring how Elementor renders CSS background images.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LNC_Elementor_LCP_Hero {

	public function __construct() {
		add_action( 'elementor/element/container/section_layout/after_section_end', [ $this, 'register_controls' ], 10, 2 );
		add_action( 'elementor/frontend/container/before_render', [ $this, 'before_render' ] );
		add_action( 'elementor/frontend/container/after_render', [ $this, 'after_render' ] );
	}

	/**
	 * Add LCP Hero controls to the Container widget.
	 *
	 * @param \Elementor\Element_Base $element
	 */
	public function register_controls( $element ) {
		$element->start_controls_section(
			'lnc_lcp_hero_section',
			[
				'label' => esc_html__( 'LCP Hero Image', 'legal-nurse-core' ),
				'tab'   => \Elementor\Controls_Manager::TAB_LAYOUT,
			]
		);

		$element->add_control(
			'lnc_lcp_enable',
			[
				'label'        => esc_html__( 'Enable LCP Hero Image', 'legal-nurse-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'legal-nurse-core' ),
				'label_off'    => esc_html__( 'No', 'legal-nurse-core' ),
				'return_value' => 'yes',
				'default'      => '',
				'description'  => esc_html__( 'Injects a discoverable <img> element for the hero background image, fixing LCP fetchpriority and discoverability issues.', 'legal-nurse-core' ),
			]
		);

		$element->add_control(
			'lnc_lcp_image',
			[
				'label'     => esc_html__( 'Hero Image', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::MEDIA,
				'condition' => [ 'lnc_lcp_enable' => 'yes' ],
			]
		);

		$element->add_control(
			'lnc_lcp_image_size',
			[
				'label'     => esc_html__( 'Image Size', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'full',
				'options'   => lnc_get_image_size_options(),
				'condition' => [ 'lnc_lcp_enable' => 'yes' ],
			]
		);

		$element->add_control(
			'lnc_lcp_alt',
			[
				'label'       => esc_html__( 'Alt Text', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => esc_html__( 'Describe the hero image for screen readers', 'legal-nurse-core' ),
				'condition'   => [ 'lnc_lcp_enable' => 'yes' ],
			]
		);

		$element->add_control(
			'lnc_lcp_object_position',
			[
				'label'     => esc_html__( 'Image Position', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'center center',
				'options'   => [
					'center center' => esc_html__( 'Center Center', 'legal-nurse-core' ),
					'center top'    => esc_html__( 'Center Top', 'legal-nurse-core' ),
					'center bottom' => esc_html__( 'Center Bottom', 'legal-nurse-core' ),
					'left center'   => esc_html__( 'Left Center', 'legal-nurse-core' ),
					'right center'  => esc_html__( 'Right Center', 'legal-nurse-core' ),
				],
				'condition' => [ 'lnc_lcp_enable' => 'yes' ],
			]
		);

		$element->end_controls_section();
	}

	/**
	 * Collect containers flagged as LCP heroes so we can preload them in <head>.
	 * Hooked early enough that it runs on every frontend render pass.
	 *
	 * @param \Elementor\Element_Base $element
	 */
	public function before_render( $element ) {
		$settings = $element->get_settings_for_display();

		if ( 'yes' !== ( $settings['lnc_lcp_enable'] ?? '' ) ) {
			return;
		}

		$image_id  = $settings['lnc_lcp_image']['id'] ?? 0;
		$image_url = $settings['lnc_lcp_image']['url'] ?? '';

		if ( ! $image_id && ! $image_url ) {
			return;
		}

		$size = $settings['lnc_lcp_image_size'] ?? 'full';
		$src  = $image_id ? wp_get_attachment_image_url( $image_id, $size ) : esc_url( $image_url );

		if ( ! $src ) {
			return;
		}

		// Store for preload output in wp_head (only if head hasn't printed yet).
		if ( ! did_action( 'wp_head' ) ) {
			lnc_lcp_register_preload( $src );
		}
	}

	/**
	 * Inject the <img> after the container opening tag.
	 *
	 * Elementor calls before_render then renders the widget's HTML, so we use
	 * after_render and move the injected markup with a brief output-buffer trick
	 * via before_render instead. Because Elementor doesn't offer a clean
	 * "inside opening tag" hook for containers, we use output buffering to
	 * insert the <img> immediately after the container's opening <div>.
	 *
	 * @param \Elementor\Element_Base $element
	 */
	public function after_render( $element ) {
		// Intentionally empty — rendering handled in before_render via ob_start.
	}
}

// ---------------------------------------------------------------------------
// Output-buffer injection
// ---------------------------------------------------------------------------

add_action( 'elementor/frontend/container/before_render', 'lnc_lcp_ob_start', 5 );
add_action( 'elementor/frontend/container/after_render', 'lnc_lcp_ob_end', 5 );

/** @var array<string,array> Keyed by element ID */
$lnc_lcp_pending = [];

function lnc_lcp_ob_start( $element ) {
	global $lnc_lcp_pending;

	$settings = $element->get_settings_for_display();

	if ( 'yes' !== ( $settings['lnc_lcp_enable'] ?? '' ) ) {
		return;
	}

	$image_id  = $settings['lnc_lcp_image']['id'] ?? 0;
	$image_url = $settings['lnc_lcp_image']['url'] ?? '';

	if ( ! $image_id && ! $image_url ) {
		return;
	}

	$size     = $settings['lnc_lcp_image_size'] ?? 'full';
	$src      = $image_id ? wp_get_attachment_image_url( $image_id, $size ) : esc_url( $image_url );
	$alt      = sanitize_text_field( $settings['lnc_lcp_alt'] ?? '' );
	$position = sanitize_text_field( $settings['lnc_lcp_object_position'] ?? 'center center' );

	if ( ! $src ) {
		return;
	}

	// Build srcset/sizes if we have an attachment ID.
	$srcset = '';
	$sizes  = '';
	if ( $image_id ) {
		$srcset = wp_get_attachment_image_srcset( $image_id, $size );
		$sizes  = wp_get_attachment_image_sizes( $image_id, $size );
	}

	$lnc_lcp_pending[ $element->get_id() ] = compact( 'src', 'srcset', 'sizes', 'alt', 'position' );

	ob_start();
}

function lnc_lcp_ob_end( $element ) {
	global $lnc_lcp_pending;

	$id = $element->get_id();

	if ( ! isset( $lnc_lcp_pending[ $id ] ) ) {
		return;
	}

	$html = ob_get_clean();
	$data = $lnc_lcp_pending[ $id ];
	unset( $lnc_lcp_pending[ $id ] );

	$style = implode( ';', [
		'position:absolute',
		'inset:0',
		'width:100%',
		'height:100%',
		'object-fit:cover',
		'object-position:' . esc_attr( $data['position'] ),
		'pointer-events:none',
		'z-index:0',
	] );

	$srcset_attr = $data['srcset'] ? ' srcset="' . esc_attr( $data['srcset'] ) . '"' : '';
	$sizes_attr  = $data['sizes']  ? ' sizes="'  . esc_attr( $data['sizes'] )  . '"' : '';

	$img = sprintf(
		'<img src="%s"%s%s alt="%s" fetchpriority="high" loading="eager" decoding="sync" aria-hidden="%s" style="%s">',
		esc_url( $data['src'] ),
		$srcset_attr,
		$sizes_attr,
		esc_attr( $data['alt'] ),
		$data['alt'] ? 'false' : 'true',
		$style
	);

	// Insert <img> immediately after the first closing > of the container's root <div>.
	$patched = preg_replace( '/(<div[^>]+>)/i', '$1' . $img, $html, 1 );

	echo $patched ?? $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

// ---------------------------------------------------------------------------
// Preload registry — collects URLs before wp_head fires
// ---------------------------------------------------------------------------

/** @var string[] */
global $lnc_lcp_preload_urls;
$lnc_lcp_preload_urls = [];

function lnc_lcp_register_preload( $url ) {
	global $lnc_lcp_preload_urls;
	$lnc_lcp_preload_urls[] = $url;
}

add_action( 'wp_head', 'lnc_lcp_output_preload_links', 1 );
function lnc_lcp_output_preload_links() {
	global $lnc_lcp_preload_urls;

	if ( empty( $lnc_lcp_preload_urls ) ) {
		return;
	}

	foreach ( array_unique( $lnc_lcp_preload_urls ) as $url ) {
		printf(
			'<link rel="preload" as="image" href="%s" fetchpriority="high">' . "\n",
			esc_url( $url )
		);
	}
}

// ---------------------------------------------------------------------------
// Inline CSS — ensure the container has position:relative so the abs img fits
// ---------------------------------------------------------------------------

add_action( 'wp_head', 'lnc_lcp_hero_inline_style', 5 );
function lnc_lcp_hero_inline_style() {
	echo '<style id="lnc-lcp-hero">.lnc-lcp-hero{position:relative;overflow:hidden;}</style>' . "\n";
}

add_action( 'elementor/frontend/container/before_render', 'lnc_lcp_add_css_class' );
function lnc_lcp_add_css_class( $element ) {
	$settings = $element->get_settings_for_display();
	if ( 'yes' === ( $settings['lnc_lcp_enable'] ?? '' ) ) {
		$element->add_render_attribute( '_wrapper', 'class', 'lnc-lcp-hero' );
	}
}

// ---------------------------------------------------------------------------
// Helper
// ---------------------------------------------------------------------------

function lnc_get_image_size_options() {
	$sizes   = get_intermediate_image_sizes();
	$options = [ 'full' => esc_html__( 'Full', 'legal-nurse-core' ) ];
	foreach ( $sizes as $size ) {
		$options[ $size ] = ucwords( str_replace( [ '-', '_' ], ' ', $size ) );
	}
	return $options;
}
