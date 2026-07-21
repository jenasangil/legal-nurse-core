<?php
/**
 * Elementor Social Share Widget
 *
 * Sticky-style share buttons (Facebook, X, LinkedIn, Pinterest, Copy link).
 * Share URLs and the copy-to-clipboard action are wired up on the front end,
 * scoped per instance so multiple widgets can appear on a page.
 *
 * @package LegalNurseCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LNC_Social_Share_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'lnc_social_share';
	}

	public function get_title() {
		return esc_html__( 'LN - Social Share', 'legal-nurse-core' );
	}

	public function get_icon() {
		return 'eicon-share';
	}

	public function get_categories() {
		return [ 'legal-nurse' ];
	}

	public function get_keywords() {
		return [ 'social', 'share', 'facebook', 'twitter', 'x', 'linkedin', 'pinterest', 'copy' ];
	}

	public function get_script_depends() {
		return [ 'lnc-social-share' ];
	}

	public function get_style_depends() {
		return [ 'lnc-social-share' ];
	}

	/**
	 * Network definitions: key => [ label, inline SVG (no hardcoded fills) ].
	 *
	 * @return array<string,array{label:string,svg:string}>
	 */
	public static function networks() {
		return [
			'facebook'  => [
				'label' => esc_html__( 'Facebook', 'legal-nurse-core' ),
				'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="10" height="18" viewBox="0 0 10 18"><path d="M6.06741 18V9.78996H8.82207L9.23536 6.58941H6.06741V4.54632C6.06741 3.61998 6.32359 2.98869 7.65347 2.98869L9.34686 2.98799V0.125307C9.05401 0.0872508 8.04877 0 6.87877 0C4.43564 0 2.76302 1.49127 2.76302 4.22934V6.58941H0V9.78996H2.76302V18H6.06741Z"/></svg>',
			],
			'x'         => [
				'label' => esc_html__( 'X', 'legal-nurse-core' ),
				'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18 18"><path d="M10.6756 7.62177L17.2324 0H15.6786L9.98536 6.61788L5.43815 0H0.193481L7.06976 10.0074L0.193481 18H1.74733L7.75958 11.0113L12.5618 18H17.8064L10.6752 7.62177Z"/></svg>',
			],
			'linkedin'  => [
				'label' => esc_html__( 'LinkedIn', 'legal-nurse-core' ),
				'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18"><path d="M15.3369 15.3372H12.6698V11.1604C12.6698 10.1644 12.652 8.88219 11.2827 8.88219C9.8936 8.88219 9.6811 9.9674 9.6811 11.0879V15.3369H7.01408V6.74767H9.57445V7.92148H9.61029C10.1324 7.02882 11.1031 6.49566 12.1365 6.534C14.8396 6.534 15.3381 8.31207 15.3381 10.6253L15.3369 15.3372ZM4.00472 5.57357C3.14993 5.57374 2.45688 4.88087 2.45672 4.0261C2.45655 3.17128 3.14939 2.4782 4.00414 2.47804C4.85893 2.47787 5.55198 3.17074 5.55214 4.02551C5.55231 4.88033 4.85951 5.57345 4.00472 5.57357ZM5.33823 15.3372H2.66843V6.74767H5.33823V15.3372ZM16.6665 0.00123909H1.32823C0.603328 -0.00695191 0.00885596 0.573736 0 1.29866V16.7011C0.00856492 17.4264 0.602953 18.0076 1.32823 17.9999H16.6665C17.3932 18.009 17.9899 17.4278 18 16.7011V1.29758C17.9896 0.5712 17.3928 -0.00944664 16.6665 0.00011645V0.00123909Z"/></svg>',
			],
			'pinterest' => [
				'label' => esc_html__( 'Pinterest', 'legal-nurse-core' ),
				'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18"><path d="M9.24446 0C4.31112 0 1.6875 3.16139 1.6875 6.60855C1.6875 8.20724 2.58079 10.2008 4.01073 10.8331C4.22786 10.931 4.34599 10.8894 4.39437 10.688C4.43712 10.535 4.62501 9.79807 4.71614 9.45042C4.74426 9.33904 4.72964 9.24229 4.63963 9.13766C4.16486 8.58864 3.78797 7.58847 3.78797 6.65017C3.78797 4.24594 5.69943 1.91146 8.95195 1.91146C11.7646 1.91146 13.7323 3.73854 13.7323 6.35204C13.7323 9.30529 12.1696 11.3484 10.1389 11.3484C9.01495 11.3484 8.17792 10.4663 8.44343 9.37505C8.76407 8.07561 9.39297 6.6783 9.39297 5.74113C9.39297 4.90072 8.9182 4.20544 7.94841 4.20544C6.80423 4.20544 5.87606 5.33837 5.87606 6.85943C5.87606 7.82585 6.21808 8.47838 6.21808 8.47838C6.21808 8.47838 5.08628 13.0506 4.87589 13.9045C4.52038 15.3502 4.92427 17.6914 4.95915 17.8928C4.98052 18.0042 5.1054 18.0391 5.17516 17.9479C5.28654 17.8017 6.6546 15.8497 7.03824 14.4389C7.17775 13.9248 7.7504 11.84 7.7504 11.84C8.12729 12.5207 9.21521 13.0911 10.374 13.0911C13.8212 13.0911 16.312 10.0613 16.312 6.30141C16.2997 2.69675 13.2148 0 9.24446 0Z"/></svg>',
			],
			'copy'      => [
				'label' => esc_html__( 'Copy link', 'legal-nurse-core' ),
				'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18 18"><path d="M9.53357 4.26783C4.25292 4.54282 0.00622559 8.92586 0.00622559 14.2734V18L1.33953 14.8961C2.91642 11.7428 6.04508 9.72664 9.53357 9.54127V13.8064L17.9938 6.89062L9.53357 0V4.26783Z"/></svg>',
			],
		];
	}

	protected function register_controls() {

		// CONTENT.
		$this->start_controls_section( 'section_content', [ 'label' => esc_html__( 'Networks', 'legal-nurse-core' ) ] );

		foreach ( self::networks() as $key => $net ) {
			$this->add_control(
				'show_' . $key,
				[
					/* translators: %s: network name */
					'label'        => sprintf( esc_html__( 'Show %s', 'legal-nurse-core' ), $net['label'] ),
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'yes',
					'default'      => 'yes',
				]
			);
		}

		$this->add_control(
			'open_new_tab',
			[
				'label'        => esc_html__( 'Open in New Tab', 'legal-nurse-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'separator'    => 'before',
			]
		);

		$this->add_control(
			'copied_text',
			[
				'label'   => esc_html__( 'Copied Message', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Link copied', 'legal-nurse-core' ),
			]
		);

		$this->add_control(
			'hide_on_mobile',
			[
				'label'        => esc_html__( 'Hide on Mobile', 'legal-nurse-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$this->end_controls_section();

		// STYLE.
		$this->start_controls_section(
			'section_style',
			[ 'label' => esc_html__( 'Buttons', 'legal-nurse-core' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ]
		);

		$this->add_responsive_control(
			'direction',
			[
				'label'     => esc_html__( 'Direction', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::CHOOSE,
				'options'   => [
					'column' => [ 'title' => esc_html__( 'Vertical', 'legal-nurse-core' ), 'icon' => 'eicon-navigation-vertical' ],
					'row'    => [ 'title' => esc_html__( 'Horizontal', 'legal-nurse-core' ), 'icon' => 'eicon-navigation-horizontal' ],
				],
				'default'   => 'column',
				'selectors' => [ '{{WRAPPER}} .lnc-socials' => 'flex-direction:{{VALUE}};' ],
			]
		);

		$this->add_responsive_control(
			'gap',
			[
				'label'      => esc_html__( 'Gap', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 40 ] ],
				'default'    => [ 'size' => 10, 'unit' => 'px' ],
				'selectors'  => [ '{{WRAPPER}} .lnc-socials' => 'gap:{{SIZE}}{{UNIT}};' ],
			]
		);

		$this->add_responsive_control(
			'button_size',
			[
				'label'      => esc_html__( 'Button Size', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 24, 'max' => 80 ] ],
				'default'    => [ 'size' => 48, 'unit' => 'px' ],
				'selectors'  => [ '{{WRAPPER}} .lnc-social-btn' => 'width:{{SIZE}}{{UNIT}};height:{{SIZE}}{{UNIT}};' ],
			]
		);

		$this->add_responsive_control(
			'icon_size',
			[
				'label'      => esc_html__( 'Icon Size', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 8, 'max' => 40 ] ],
				'default'    => [ 'size' => 18, 'unit' => 'px' ],
				'selectors'  => [ '{{WRAPPER}} .lnc-social-btn svg' => 'width:{{SIZE}}{{UNIT}};height:{{SIZE}}{{UNIT}};' ],
			]
		);

		$this->add_control(
			'radius',
			[
				'label'      => esc_html__( 'Border Radius', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 50 ], '%' => [ 'min' => 0, 'max' => 50 ] ],
				'default'    => [ 'size' => 50, 'unit' => '%' ],
				'selectors'  => [ '{{WRAPPER}} .lnc-social-btn' => 'border-radius:{{SIZE}}{{UNIT}};' ],
			]
		);

		$this->start_controls_tabs( 'btn_tabs' );

		$this->start_controls_tab( 'btn_normal', [ 'label' => esc_html__( 'Normal', 'legal-nurse-core' ) ] );
		$this->add_control(
			'icon_color',
			[
				'label'     => esc_html__( 'Icon Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#0A0A0A',
				'selectors' => [ '{{WRAPPER}} .lnc-social-btn' => 'color:{{VALUE}};' ],
			]
		);
		$this->add_control(
			'btn_bg',
			[
				'label'     => esc_html__( 'Background', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#F3F3F3',
				'selectors' => [ '{{WRAPPER}} .lnc-social-btn' => 'background:{{VALUE}};' ],
			]
		);
		$this->end_controls_tab();

		$this->start_controls_tab( 'btn_hover', [ 'label' => esc_html__( 'Hover', 'legal-nurse-core' ) ] );
		$this->add_control(
			'icon_color_hover',
			[
				'label'     => esc_html__( 'Icon Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .lnc-social-btn:hover' => 'color:{{VALUE}};' ],
			]
		);
		$this->add_control(
			'btn_bg_hover',
			[
				'label'     => esc_html__( 'Background', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .lnc-social-btn:hover' => 'background:{{VALUE}};' ],
			]
		);
		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$networks = self::networks();
		$new_tab  = 'yes' === ( $settings['open_new_tab'] ?? 'yes' );

		$classes = 'lnc-social-share';
		if ( 'yes' === ( $settings['hide_on_mobile'] ?? '' ) ) {
			$classes .= ' lnc-social-share--hide-mobile';
		}
		?>
		<div class="<?php echo esc_attr( $classes ); ?>" data-copied="<?php echo esc_attr( $settings['copied_text'] ? $settings['copied_text'] : esc_html__( 'Link copied', 'legal-nurse-core' ) ); ?>">
			<ul class="lnc-socials">
				<?php
				foreach ( $networks as $key => $net ) :
					if ( 'yes' !== ( $settings[ 'show_' . $key ] ?? 'yes' ) ) {
						continue;
					}
					$is_copy = ( 'copy' === $key );
					$target  = ( ! $is_copy && $new_tab ) ? ' target="_blank" rel="noopener noreferrer"' : '';
					?>
					<li>
						<a class="lnc-social-btn lnc-social-btn--<?php echo esc_attr( $key ); ?>"<?php echo $target; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> aria-label="<?php echo esc_attr( $net['label'] ); ?>">
							<?php echo $net['svg']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
	}
}
