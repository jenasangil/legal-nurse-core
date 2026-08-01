<?php
/**
 * Elementor ACF Content / Video Widget
 *
 * Outputs the HTML from an ACF WYSIWYG field (e.g. "right_box_text" holding a
 * YouTube <iframe>) for the current post, with an option to make embedded
 * iframes/videos responsive.
 *
 * @package LegalNurseCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LNC_ACF_Content_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'lnc_acf_content';
	}

	public function get_title() {
		return esc_html__( 'LN - ACF Content / Video', 'legal-nurse-core' );
	}

	public function get_icon() {
		return 'eicon-video-camera';
	}

	public function get_categories() {
		return [ 'legal-nurse' ];
	}

	public function get_keywords() {
		return [ 'acf', 'video', 'wysiwyg', 'iframe', 'youtube', 'embed', 'content' ];
	}

	public function get_style_depends() {
		return [ 'lnc-acf-content' ];
	}

	protected function register_controls() {

		$this->start_controls_section( 'section_content', [ 'label' => esc_html__( 'Content', 'legal-nurse-core' ) ] );

		$this->add_control(
			'field_name',
			[
				'label'       => esc_html__( 'ACF Field Name', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => 'right_box_text',
				'description' => esc_html__( 'The ACF WYSIWYG field to output (from the current post).', 'legal-nurse-core' ),
			]
		);

		$this->add_control(
			'post_id',
			[
				'label'       => esc_html__( 'Post ID (optional)', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Leave empty for the current post', 'legal-nurse-core' ),
			]
		);

		$this->add_control(
			'responsive_video',
			[
				'label'        => esc_html__( 'Responsive Video', 'legal-nurse-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'description'  => esc_html__( 'Make embedded iframes scale to the container (16:9).', 'legal-nurse-core' ),
			]
		);

		$this->add_control(
			'aspect_ratio',
			[
				'label'     => esc_html__( 'Aspect Ratio', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => '16 / 9',
				'options'   => [
					'16 / 9' => '16:9',
					'4 / 3'  => '4:3',
					'21 / 9' => '21:9',
					'1 / 1'  => '1:1',
				],
				'selectors' => [ '{{WRAPPER}} .lnc-acf-content--responsive iframe' => 'aspect-ratio:{{VALUE}};' ],
				'condition' => [ 'responsive_video' => 'yes' ],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section( 'section_style', [ 'label' => esc_html__( 'Style', 'legal-nurse-core' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ] );

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
				'selectors' => [ '{{WRAPPER}} .lnc-acf-content' => 'text-align:{{VALUE}};' ],
			]
		);

		$this->add_responsive_control(
			'max_width',
			[
				'label'      => esc_html__( 'Max Width', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range'      => [ 'px' => [ 'min' => 200, 'max' => 1200 ], '%' => [ 'min' => 10, 'max' => 100 ] ],
				'selectors'  => [ '{{WRAPPER}} .lnc-acf-content' => 'max-width:{{SIZE}}{{UNIT}};margin-left:auto;margin-right:auto;' ],
			]
		);

		$this->add_responsive_control(
			'radius',
			[
				'label'      => esc_html__( 'Video Radius', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 40 ] ],
				'selectors'  => [ '{{WRAPPER}} .lnc-acf-content iframe' => 'border-radius:{{SIZE}}{{UNIT}};' ],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		wp_enqueue_style( 'lnc-acf-content' );

		$settings = $this->get_settings_for_display();
		$field    = $settings['field_name'] ? $settings['field_name'] : 'right_box_text';
		$post_id  = $settings['post_id'] ? $settings['post_id'] : get_the_ID();

		$content = function_exists( 'get_field' ) ? get_field( $field, $post_id ) : '';

		if ( empty( $content ) ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="elementor-alert elementor-alert-info">'
					. sprintf(
						/* translators: %s: field name */
						esc_html__( 'ACF Content: no value found in field "%s" for this post.', 'legal-nurse-core' ),
						esc_html( $field )
					)
					. '</div>';
			}
			return;
		}

		$class = 'lnc-acf-content';
		if ( 'yes' === ( $settings['responsive_video'] ?? 'yes' ) ) {
			$class .= ' lnc-acf-content--responsive';
		}

		// WYSIWYG content is authored by an editor; run shortcodes and keep the
		// markup (including the iframe embed) intact.
		echo '<div class="' . esc_attr( $class ) . '">' . do_shortcode( $content ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
