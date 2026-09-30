<?php
/**
 * Elementor Social Share Widget
 *
 * Sticky-style share buttons (Facebook, X, LinkedIn, Instagram, Copy link).
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
		return [ 'social', 'share', 'facebook', 'twitter', 'x', 'linkedin', 'instagram', 'copy' ];
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
			'instagram' => [
				'label' => esc_html__( 'Instagram', 'legal-nurse-core' ),
				'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"><path d="M12 2.16c3.2 0 3.58.01 4.85.07 1.17.05 1.8.25 2.23.41.56.22.96.48 1.38.9.42.42.68.82.9 1.38.16.42.36 1.06.41 2.23.06 1.27.07 1.65.07 4.85s-.01 3.58-.07 4.85c-.05 1.17-.25 1.8-.41 2.23-.22.56-.48.96-.9 1.38-.42.42-.82.68-1.38.9-.42.16-1.06.36-2.23.41-1.27.06-1.65.07-4.85.07s-3.58-.01-4.85-.07c-1.17-.05-1.8-.25-2.23-.41-.56-.22-.96-.48-1.38-.9-.42-.42-.68-.82-.9-1.38-.16-.42-.36-1.06-.41-2.23-.06-1.27-.07-1.65-.07-4.85s.01-3.58.07-4.85c.05-1.17.25-1.8.41-2.23.22-.56.48-.96.9-1.38.42-.42.82-.68 1.38-.9.42-.16 1.06-.36 2.23-.41C8.42 2.17 8.8 2.16 12 2.16zm0 1.44c-3.15 0-3.52.01-4.76.07-1.15.05-1.77.24-2.19.4-.55.21-.94.47-1.35.88-.41.41-.67.8-.88 1.35-.16.42-.35 1.04-.4 2.19-.06 1.24-.07 1.61-.07 4.76s.01 3.52.07 4.76c.05 1.15.24 1.77.4 2.19.21.55.47.94.88 1.35.41.41.8.67 1.35.88.42.16 1.04.35 2.19.4 1.24.06 1.61.07 4.76.07s3.52-.01 4.76-.07c1.15-.05 1.77-.24 2.19-.4.55-.21.94-.47 1.35-.88.41-.41.67-.8.88-1.35.16-.42.35-1.04.4-2.19.06-1.24.07-1.61.07-4.76s-.01-3.52-.07-4.76c-.05-1.15-.24-1.77-.4-2.19-.21-.55-.47-.94-.88-1.35-.41-.41-.8-.67-1.35-.88-.42-.16-1.04-.35-2.19-.4-1.24-.06-1.61-.07-4.76-.07zm0 2.45a5.95 5.95 0 110 11.9 5.95 5.95 0 010-11.9zm0 9.82a3.87 3.87 0 100-7.74 3.87 3.87 0 000 7.74zm7.58-10.01a1.39 1.39 0 11-2.78 0 1.39 1.39 0 012.78 0z"/></svg>',
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

			$this->add_control(
				'icon_' . $key,
				[
					/* translators: %s: network name */
					'label'       => sprintf( esc_html__( '%s Icon', 'legal-nurse-core' ), $net['label'] ),
					'type'        => \Elementor\Controls_Manager::ICONS,
					'description' => esc_html__( 'Optional. Choose an icon or upload an SVG to override the default.', 'legal-nurse-core' ),
					'condition'   => [ 'show_' . $key => 'yes' ],
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
				'selectors'  => [
					'{{WRAPPER}} .lnc-social-btn svg' => 'width:{{SIZE}}{{UNIT}};height:{{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .lnc-social-btn i'   => 'font-size:{{SIZE}}{{UNIT}};',
				],
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

	/**
	 * Current page URL and title for share links.
	 *
	 * @return array{url:string,title:string}
	 */
	private function get_share_context() {
		$url = get_permalink();
		if ( ! $url ) {
			global $wp;
			$url = home_url( add_query_arg( [], isset( $wp->request ) ? $wp->request : '' ) );
		}

		$title = get_the_title();
		if ( '' === $title ) {
			$title = wp_get_document_title();
		}

		return [ 'url' => $url, 'title' => $title ];
	}

	/**
	 * Build the share href for a network (server-side).
	 *
	 * @param string $key   Network key.
	 * @param string $url   Page URL.
	 * @param string $title Page title.
	 * @return string
	 */
	private function share_url( $key, $url, $title ) {
		$e_url   = rawurlencode( $url );
		$e_title = rawurlencode( $title );

		switch ( $key ) {
			case 'facebook':
				return 'https://www.facebook.com/sharer/sharer.php?u=' . $e_url;
			case 'x':
				return 'https://twitter.com/intent/tweet?text=' . $e_title . '&url=' . $e_url;
			case 'linkedin':
				return 'https://www.linkedin.com/sharing/share-offsite/?url=' . $e_url;
			case 'instagram':
				return 'https://www.instagram.com/legal.nurse/';
			default:
				return '';
		}
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$networks = self::networks();
		$new_tab  = 'yes' === ( $settings['open_new_tab'] ?? 'yes' );
		$context  = $this->get_share_context();

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

					// Custom icon override, else the built-in SVG.
					$custom = $settings[ 'icon_' . $key ] ?? [];
					if ( ! empty( $custom['value'] ) ) {
						ob_start();
						\Elementor\Icons_Manager::render_icon( $custom, [ 'aria-hidden' => 'true' ] );
						$icon = ob_get_clean();
					} else {
						$icon = $net['svg'];
					}

					// Server-side href + attributes.
					if ( $is_copy ) {
						$href_attr = ' href="#"';
						$target    = '';
					} else {
						$href_attr = ' href="' . esc_url( $this->share_url( $key, $context['url'], $context['title'] ) ) . '"';
						$target    = $new_tab ? ' target="_blank" rel="noopener noreferrer"' : '';
					}
					?>
					<li>
						<a class="lnc-social-btn lnc-social-btn--<?php echo esc_attr( $key ); ?>"<?php echo $href_attr . $target; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> aria-label="<?php echo esc_attr( $net['label'] ); ?>">
							<?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
	}
}
