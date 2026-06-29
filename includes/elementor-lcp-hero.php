<?php
/**
 * Elementor LCP Hero Extension
 *
 * Adds an "LCP Optimization" toggle to every Elementor Container. When enabled,
 * the container's existing CSS background image is mirrored into a real <img>
 * element injected inside the container. Because the <img> lives in the initial
 * HTML it is discoverable by the browser's preload scanner, and it carries
 * fetchpriority="high" + loading="eager" — satisfying the PageSpeed LCP audits:
 *
 *   - "fetchpriority=high should be applied"
 *   - "Request is discoverable in initial document"
 *
 * No second image upload is required: the toggle reuses the container's own
 * Background > Image setting. An optional override image is available.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LNC_Elementor_LCP_Hero {

	/** @var array<string,array> Pending injections keyed by element ID. */
	private $pending = [];

	public function __construct() {
		// Register the controls on the container's Layout section.
		add_action( 'elementor/element/container/section_layout/after_section_end', [ $this, 'register_controls' ], 10, 2 );

		// Wrap container output to inject the <img>. Late priority so our buffer
		// wraps the full element markup.
		add_action( 'elementor/frontend/container/before_render', [ $this, 'before_render' ], 999 );
		add_action( 'elementor/frontend/container/after_render', [ $this, 'after_render' ], 999 );

		// Stacking CSS for the injected image.
		add_action( 'wp_head', [ $this, 'print_styles' ], 5 );
	}

	/**
	 * Add the LCP controls to the Container Layout tab.
	 *
	 * @param \Elementor\Element_Base $element
	 */
	public function register_controls( $element ) {
		$element->start_controls_section(
			'lnc_lcp_section',
			[
				'label' => esc_html__( 'LCP Optimization', 'legal-nurse-core' ),
				'tab'   => \Elementor\Controls_Manager::TAB_LAYOUT,
			]
		);

		$element->add_control(
			'lnc_lcp_enable',
			[
				'label'        => esc_html__( 'Optimize as LCP image', 'legal-nurse-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'On', 'legal-nurse-core' ),
				'label_off'    => esc_html__( 'Off', 'legal-nurse-core' ),
				'return_value' => 'yes',
				'default'      => '',
				'description'  => esc_html__( 'Turn on for the hero section. Outputs the background image as a real, high-priority <img> the browser can discover immediately.', 'legal-nurse-core' ),
			]
		);

		$element->add_control(
			'lnc_lcp_override_image',
			[
				'label'       => esc_html__( 'Override image (optional)', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::MEDIA,
				'description' => esc_html__( 'Leave empty to use this container\'s Background image automatically.', 'legal-nurse-core' ),
				'condition'   => [ 'lnc_lcp_enable' => 'yes' ],
			]
		);

		$element->add_control(
			'lnc_lcp_suppress_bg',
			[
				'label'        => esc_html__( 'Remove duplicate CSS background', 'legal-nurse-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'legal-nurse-core' ),
				'label_off'    => esc_html__( 'No', 'legal-nurse-core' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'description'  => esc_html__( 'Hides the container background-image so the image is not downloaded twice. The injected <img> replaces it visually.', 'legal-nurse-core' ),
				'condition'    => [ 'lnc_lcp_enable' => 'yes' ],
			]
		);

		$element->add_control(
			'lnc_lcp_alt',
			[
				'label'       => esc_html__( 'Alt text', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => esc_html__( 'Describe the hero image', 'legal-nurse-core' ),
				'condition'   => [ 'lnc_lcp_enable' => 'yes' ],
			]
		);

		$element->end_controls_section();
	}

	/**
	 * Resolve the image source for a flagged container.
	 *
	 * Priority: explicit override image, then the container's Background image.
	 *
	 * @param array $settings get_settings_for_display() output.
	 * @return array{src:string,srcset:string,sizes:string}|null
	 */
	private function resolve_image( $settings ) {
		$image_id  = 0;
		$image_url = '';

		// 1) Override image control.
		if ( ! empty( $settings['lnc_lcp_override_image']['url'] ) ) {
			$image_id  = (int) ( $settings['lnc_lcp_override_image']['id'] ?? 0 );
			$image_url = $settings['lnc_lcp_override_image']['url'];
		} elseif ( ! empty( $settings['background_image']['url'] ) ) {
			// 2) Container's own background image.
			$image_id  = (int) ( $settings['background_image']['id'] ?? 0 );
			$image_url = $settings['background_image']['url'];
		}

		if ( ! $image_id && ! $image_url ) {
			return null;
		}

		$src    = $image_id ? wp_get_attachment_image_url( $image_id, 'full' ) : $image_url;
		$srcset = $image_id ? (string) wp_get_attachment_image_srcset( $image_id, 'full' ) : '';
		$sizes  = $image_id ? (string) wp_get_attachment_image_sizes( $image_id, 'full' ) : '';

		if ( ! $src ) {
			return null;
		}

		return [
			'src'    => $src,
			'srcset' => $srcset,
			'sizes'  => $sizes,
		];
	}

	/**
	 * Start buffering the container output if it is flagged for LCP.
	 *
	 * @param \Elementor\Element_Base $element
	 */
	public function before_render( $element ) {
		$settings = $element->get_settings_for_display();

		if ( 'yes' !== ( $settings['lnc_lcp_enable'] ?? '' ) ) {
			return;
		}

		$image = $this->resolve_image( $settings );
		if ( null === $image ) {
			return;
		}

		// Tag the wrapper so our CSS can position the image.
		$element->add_render_attribute( '_wrapper', 'class', 'lnc-lcp-hero' );

		// Optionally suppress the duplicate CSS background image (default on).
		if ( 'yes' === ( $settings['lnc_lcp_suppress_bg'] ?? 'yes' ) ) {
			$element->add_render_attribute( '_wrapper', 'style', 'background-image:none !important;' );
		}

		$image['alt'] = sanitize_text_field( $settings['lnc_lcp_alt'] ?? '' );

		$this->pending[ $element->get_id() ] = $image;

		ob_start();
	}

	/**
	 * Flush the buffer, injecting the <img> after the container's opening tag.
	 *
	 * @param \Elementor\Element_Base $element
	 */
	public function after_render( $element ) {
		$id = $element->get_id();

		if ( ! isset( $this->pending[ $id ] ) ) {
			return;
		}

		$html  = ob_get_clean();
		$image = $this->pending[ $id ];
		unset( $this->pending[ $id ] );

		$srcset_attr = $image['srcset'] ? ' srcset="' . esc_attr( $image['srcset'] ) . '"' : '';
		$sizes_attr  = $image['sizes'] ? ' sizes="' . esc_attr( $image['sizes'] ) . '"' : '';

		$img = sprintf(
			'<img class="lnc-lcp-hero__img" src="%s"%s%s alt="%s" fetchpriority="high" loading="eager" decoding="async" %s>',
			esc_url( $image['src'] ),
			$srcset_attr,
			$sizes_attr,
			esc_attr( $image['alt'] ),
			$image['alt'] ? '' : 'aria-hidden="true"'
		);

		// Inject right after the wrapper's opening tag (matched by its data-id).
		$pattern  = '/(<[a-zA-Z][^>]*\sdata-id="' . preg_quote( $id, '/' ) . '"[^>]*>)/';
		$replaced = preg_replace( $pattern, '$1' . $img, $html, 1, $count );

		if ( $count && null !== $replaced ) {
			$html = $replaced;
		} else {
			// Fallback: inject after the first opening tag of the buffered markup.
			$replaced = preg_replace( '/(<[a-zA-Z][^>]*>)/', '$1' . $img, $html, 1 );
			$html     = ( null !== $replaced ) ? $replaced : $html;
		}

		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Output the stacking CSS so the injected <img> sits behind the content,
	 * filling the container exactly like a CSS background.
	 */
	public function print_styles() {
		echo '<style id="lnc-lcp-hero-css">'
			. '.lnc-lcp-hero{position:relative;overflow:hidden;}'
			. '.lnc-lcp-hero>.lnc-lcp-hero__img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center center;z-index:0;pointer-events:none;}'
			. '.lnc-lcp-hero>.e-con-inner,.lnc-lcp-hero>:not(.lnc-lcp-hero__img){position:relative;z-index:1;}'
			. '</style>' . "\n";
	}
}
