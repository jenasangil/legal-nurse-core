<?php
/**
 * Elementor Web Lead Form Widget
 *
 * Select a Gravity Form and render it with the Legal Nurse styling, plus an
 * optional Creatio tracking integration (Form ID, Thank-You URL, and Creatio
 * service/landing are configurable per widget).
 *
 * @package LegalNurseCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LNC_Web_Lead_Form_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'lnc_web_lead_form';
	}

	public function get_title() {
		return esc_html__( 'LN - Web Lead Form', 'legal-nurse-core' );
	}

	public function get_icon() {
		return 'eicon-form-horizontal';
	}

	public function get_categories() {
		return [ 'legal-nurse' ];
	}

	public function get_keywords() {
		return [ 'gravity', 'form', 'lead', 'creatio', 'tracking', 'contact' ];
	}

	public function get_style_depends() {
		return [ 'lnc-web-lead-form' ];
	}

	/**
	 * Available Gravity Forms as id => title.
	 *
	 * @return array<int|string,string>
	 */
	private function get_form_options() {
		$options = [ '' => esc_html__( '— Select a form —', 'legal-nurse-core' ) ];

		if ( class_exists( 'GFAPI' ) ) {
			$forms = \GFAPI::get_forms();
			if ( is_array( $forms ) ) {
				foreach ( $forms as $form ) {
					$options[ $form['id'] ] = $form['title'] . ' (#' . $form['id'] . ')';
				}
			}
		}

		return $options;
	}

	protected function register_controls() {

		$this->start_controls_section( 'section_form', [ 'label' => esc_html__( 'Form', 'legal-nurse-core' ) ] );

		if ( ! class_exists( 'GFAPI' ) ) {
			$this->add_control(
				'gf_missing',
				[
					'type'            => \Elementor\Controls_Manager::RAW_HTML,
					'raw'             => esc_html__( 'Gravity Forms is not active. Activate it to select a form.', 'legal-nurse-core' ),
					'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
				]
			);
		}

		$this->add_control(
			'form_id',
			[
				'label'   => esc_html__( 'Gravity Form', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => $this->get_form_options(),
				'default' => '',
			]
		);

		$this->add_control(
			'show_title',
			[
				'label'        => esc_html__( 'Show Form Title', 'legal-nurse-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$this->add_control(
			'show_description',
			[
				'label'        => esc_html__( 'Show Form Description', 'legal-nurse-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$this->add_control(
			'ajax',
			[
				'label'        => esc_html__( 'AJAX Submit', 'legal-nurse-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
				'description'  => esc_html__( 'Leave off to match the Creatio tracking (non-AJAX) flow.', 'legal-nurse-core' ),
			]
		);

		$this->end_controls_section();

		// Tracking / Creatio.
		$this->start_controls_section( 'section_tracking', [ 'label' => esc_html__( 'Tracking & Creatio', 'legal-nurse-core' ) ] );

		$this->add_control(
			'enable_tracking',
			[
				'label'        => esc_html__( 'Enable Creatio Tracking', 'legal-nurse-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
				'description'  => esc_html__( 'Loads UTM/click tracking and sends the lead to Creatio on submit.', 'legal-nurse-core' ),
			]
		);

		$this->add_control(
			'thank_you_url',
			[
				'label'     => esc_html__( 'Thank You URL', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => '/thank-you',
				'condition' => [ 'enable_tracking' => 'yes' ],
			]
		);

		$this->add_control(
			'creatio_service_url',
			[
				'label'     => esc_html__( 'Creatio Service URL', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => 'https://legalnurse.creatio.com/0/ServiceModel/GeneratedObjectWebFormService.svc/SaveWebFormObjectData',
				'condition' => [ 'enable_tracking' => 'yes' ],
			]
		);

		$this->add_control(
			'creatio_landing_id',
			[
				'label'     => esc_html__( 'Creatio Landing ID', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => '5eb21031-3a9b-47ac-979b-5e3f1b61e846',
				'condition' => [ 'enable_tracking' => 'yes' ],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		wp_enqueue_style( 'lnc-web-lead-form' );

		$settings = $this->get_settings_for_display();
		$form_id  = $settings['form_id'] ?? '';

		if ( '' === $form_id || ! function_exists( 'gravity_form' ) ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="elementor-alert elementor-alert-info">'
					. esc_html__( 'Web Lead Form: select a Gravity Form (and make sure Gravity Forms is active).', 'legal-nurse-core' )
					. '</div>';
			}
			return;
		}

		$show_title = 'yes' === ( $settings['show_title'] ?? '' ) ? 'true' : 'false';
		$show_desc  = 'yes' === ( $settings['show_description'] ?? '' ) ? 'true' : 'false';
		$ajax       = 'yes' === ( $settings['ajax'] ?? '' ) ? 'true' : 'false';

		// Optional Creatio tracking.
		if ( 'yes' === ( $settings['enable_tracking'] ?? '' ) ) {
			wp_enqueue_script( 'creatio-track-cookies' );
			wp_enqueue_script( 'creatio-create-object' );
			wp_enqueue_script( 'lnc-web-lead-form' );
			wp_add_inline_script(
				'lnc-web-lead-form',
				'window.LNC_LEAD_FORM_CONFIG = ' . wp_json_encode(
					[
						'formId'           => (int) $form_id,
						'thankYouUrl'      => $settings['thank_you_url'] ?? '/thank-you',
						'creatioServiceUrl' => $settings['creatio_service_url'] ?? '',
						'creatioLandingId' => $settings['creatio_landing_id'] ?? '',
					]
				) . ';',
				'before'
			);
		}

		echo '<div class="lnc-web-lead-form">';
		echo do_shortcode(
			sprintf(
				'[gravityform id="%d" title="%s" description="%s" ajax="%s"]',
				(int) $form_id,
				esc_attr( $show_title ),
				esc_attr( $show_desc ),
				esc_attr( $ajax )
			)
		);
		echo '</div>';
	}
}
