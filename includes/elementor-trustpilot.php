<?php
/**
 * Elementor Trustpilot Widget
 *
 * Renders a Trustpilot "TrustBox" widget (.trustpilot-widget) and loads the
 * official bootstrap script, which hydrates the placeholder into the live
 * widget. All the usual TrustBox data-* attributes are exposed as controls so
 * different templates (micro, mini, review carousel, etc.) can be configured
 * from the Elementor panel.
 *
 * @package LegalNurseCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LNC_Trustpilot_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'lnc_trustpilot';
	}

	public function get_title() {
		return esc_html__( 'LN - Trustpilot', 'legal-nurse-core' );
	}

	public function get_icon() {
		return 'eicon-star';
	}

	public function get_categories() {
		return [ 'legal-nurse' ];
	}

	public function get_keywords() {
		return [ 'trustpilot', 'reviews', 'rating', 'stars', 'trustbox', 'testimonial' ];
	}

	public function get_script_depends() {
		return [ 'trustpilot-bootstrap' ];
	}

	protected function register_controls() {

		$this->start_controls_section( 'section_content', [ 'label' => esc_html__( 'Trustpilot', 'legal-nurse-core' ) ] );

		$this->add_control(
			'template_id',
			[
				'label'       => esc_html__( 'Template ID', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '53aa8807dec7e10d38f59f32',
				'description' => esc_html__( 'The TrustBox template ID (data-template-id).', 'legal-nurse-core' ),
			]
		);

		$this->add_control(
			'businessunit_id',
			[
				'label'       => esc_html__( 'Business Unit ID', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '642a2e3bfa07a577374e5553',
				'description' => esc_html__( 'Your Trustpilot business unit ID (data-businessunit-id).', 'legal-nurse-core' ),
			]
		);

		$this->add_control(
			'review_url',
			[
				'label'       => esc_html__( 'Review Page URL', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::URL,
				'default'     => [ 'url' => 'https://www.trustpilot.com/review/legalnurse.com' ],
				'options'     => [ 'target', 'nofollow' ],
				'description' => esc_html__( 'Fallback link shown before the widget hydrates.', 'legal-nurse-core' ),
			]
		);

		$this->add_control(
			'locale',
			[
				'label'   => esc_html__( 'Locale', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'en-US',
			]
		);

		$this->add_control(
			'theme',
			[
				'label'   => esc_html__( 'Theme', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'light',
				'options' => [
					''      => esc_html__( 'Default', 'legal-nurse-core' ),
					'light' => esc_html__( 'Light', 'legal-nurse-core' ),
					'dark'  => esc_html__( 'Dark', 'legal-nurse-core' ),
				],
			]
		);

		$this->add_responsive_control(
			'align',
			[
				'label'     => esc_html__( 'Alignment', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::CHOOSE,
				'options'   => [
					'left'   => [ 'title' => esc_html__( 'Left', 'legal-nurse-core' ), 'icon' => 'eicon-text-align-left' ],
					'center' => [ 'title' => esc_html__( 'Center', 'legal-nurse-core' ), 'icon' => 'eicon-text-align-center' ],
					'right'  => [ 'title' => esc_html__( 'Right', 'legal-nurse-core' ), 'icon' => 'eicon-text-align-right' ],
				],
				'default'         => 'left',
				'selectors_dictionary' => [
					'left'   => 'margin-right:auto;margin-left:0;',
					'center' => 'margin-left:auto;margin-right:auto;',
					'right'  => 'margin-left:auto;margin-right:0;',
				],
				'selectors' => [ '{{WRAPPER}} .trustpilot-widget' => '{{VALUE}}' ],
			]
		);

		$this->add_responsive_control(
			'height',
			[
				'label'       => esc_html__( 'Height', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::SLIDER,
				'size_units'  => [ 'px' ],
				'range'       => [ 'px' => [ 'min' => 20, 'max' => 800 ] ],
				'default'     => [ 'size' => 52, 'unit' => 'px' ],
				'description' => esc_html__( 'Match the chosen TrustBox template height (Micro ~24px, compact ~52px, Mini ~150px).', 'legal-nurse-core' ),
			]
		);

		$this->add_responsive_control(
			'max_width',
			[
				'label'      => esc_html__( 'Max Width', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range'      => [ 'px' => [ 'min' => 100, 'max' => 1200 ], '%' => [ 'min' => 10, 'max' => 100 ] ],
				'default'    => [ 'size' => 240, 'unit' => 'px' ],
				'selectors'  => [ '{{WRAPPER}} .trustpilot-widget' => 'max-width:{{SIZE}}{{UNIT}};' ],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		wp_enqueue_script( 'trustpilot-bootstrap' );

		$settings = $this->get_settings_for_display();

		$template_id = trim( (string) ( $settings['template_id'] ?? '' ) );
		$business_id = trim( (string) ( $settings['businessunit_id'] ?? '' ) );

		if ( '' === $template_id || '' === $business_id ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="elementor-alert elementor-alert-warning">' . esc_html__( 'Trustpilot: set both Template ID and Business Unit ID.', 'legal-nurse-core' ) . '</div>';
			}
			return;
		}

		$review_url = ! empty( $settings['review_url']['url'] ) ? $settings['review_url']['url'] : '';
		$target     = ! empty( $settings['review_url']['is_external'] ) ? ' target="_blank"' : '';
		$nofollow   = ! empty( $settings['review_url']['nofollow'] ) ? ' rel="noopener nofollow"' : ' rel="noopener"';
		$height     = isset( $settings['height']['size'] ) && '' !== $settings['height']['size'] ? (int) $settings['height']['size'] . 'px' : '140px';

		$attrs = [
			'class'                 => 'trustpilot-widget',
			'data-locale'           => (string) ( $settings['locale'] ?? 'en-US' ),
			'data-template-id'      => $template_id,
			'data-businessunit-id'  => $business_id,
			'data-style-height'     => $height,
			'data-style-width'      => '100%',
		];

		if ( ! empty( $settings['theme'] ) ) {
			$attrs['data-theme'] = (string) $settings['theme'];
		}

		$html = '<div';
		foreach ( $attrs as $key => $value ) {
			$html .= ' ' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
		}
		$html .= '>';
		if ( '' !== $review_url ) {
			$html .= '<a href="' . esc_url( $review_url ) . '"' . $target . $nofollow . '>Trustpilot</a>';
		}
		$html .= '</div>';

		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
