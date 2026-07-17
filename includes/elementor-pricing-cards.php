<?php
/**
 * Elementor Pricing Cards Widget
 *
 * Renders a row of pricing/plan cards (Basic / Executive / VIP style).
 * Cards are built from selected WooCommerce products (title + price from the
 * product; note + features from ACF fields pricing_note / features).
 * Each card supports its own background, border, and text styling via a
 * cycled style palette (index-based), so every card can look different.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LNC_Pricing_Cards_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'lnc_pricing_cards';
	}

	public function get_title() {
		return esc_html__( 'LN - Pricing Cards', 'legal-nurse-core' );
	}

	public function get_icon() {
		return 'eicon-price-table';
	}

	public function get_categories() {
		return [ 'legal-nurse' ];
	}

	public function get_keywords() {
		return [ 'pricing', 'card', 'plan', 'woocommerce', 'product', 'legal nurse' ];
	}

	/**
	 * WooCommerce product options for the SELECT2 control.
	 *
	 * @return array<int,string>
	 */
	private function get_product_options() {
		$options = [];

		if ( ! function_exists( 'wc_get_products' ) ) {
			return $options;
		}

		$products = get_posts(
			[
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => 100,
				'orderby'        => 'title',
				'order'          => 'ASC',
			]
		);

		foreach ( $products as $product ) {
			$options[ $product->ID ] = $product->post_title;
		}

		return $options;
	}

	protected function register_controls() {

		// ---------------------------------------------------------------
		// CONTENT: Source
		// ---------------------------------------------------------------
		$this->start_controls_section(
			'section_source',
			[ 'label' => esc_html__( 'Source', 'legal-nurse-core' ) ]
		);

		$this->add_control(
			'wc_products',
			[
				'label'       => esc_html__( 'Select Products', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'options'     => $this->get_product_options(),
				'description' => esc_html__( 'Order of selection = display order. Title, price come from the product; note & features come from ACF (pricing_note, features).', 'legal-nurse-core' ),
			]
		);

		$this->add_control(
			'wc_button_text',
			[
				'label'   => esc_html__( 'Button Text', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Compare Details', 'legal-nurse-core' ),
			]
		);

		$this->end_controls_section();

		// ---------------------------------------------------------------
		// CONTENT: Layout
		// ---------------------------------------------------------------
		$this->start_controls_section(
			'section_layout',
			[ 'label' => esc_html__( 'Layout', 'legal-nurse-core' ) ]
		);

		$this->add_responsive_control(
			'columns',
			[
				'label'          => esc_html__( 'Columns', 'legal-nurse-core' ),
				'type'           => \Elementor\Controls_Manager::SELECT,
				'default'        => '3',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'options'        => [ '1' => '1', '2' => '2', '3' => '3', '4' => '4' ],
				'selectors'      => [
					'{{WRAPPER}} .lnc-pcards' => 'grid-template-columns:repeat({{VALUE}},1fr);',
				],
			]
		);

		$this->add_responsive_control(
			'gap',
			[
				'label'      => esc_html__( 'Gap', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 80 ] ],
				'default'    => [ 'size' => 24, 'unit' => 'px' ],
				'selectors'  => [ '{{WRAPPER}} .lnc-pcards' => 'gap:{{SIZE}}{{UNIT}};' ],
			]
		);

		$this->add_control(
			'show_check_icon',
			[
				'label'        => esc_html__( 'Show Feature Icons', 'legal-nurse-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'feature_icon',
			[
				'label'       => esc_html__( 'Feature Icon', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::ICONS,
				'description' => esc_html__( 'Choose an icon or upload your own SVG. Used before each feature.', 'legal-nurse-core' ),
				'default'     => [
					'value'   => 'fas fa-check',
					'library' => 'fa-solid',
				],
				'condition'   => [ 'show_check_icon' => 'yes' ],
			]
		);

		$this->add_responsive_control(
			'feature_icon_size',
			[
				'label'      => esc_html__( 'Icon Size', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [ 'min' => 8, 'max' => 40 ],
					'em' => [ 'min' => 0.5, 'max' => 3, 'step' => 0.1 ],
				],
				'default'    => [ 'size' => 1, 'unit' => 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .lnc-pcard__check' => 'font-size:{{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .lnc-pcard__check svg' => 'width:{{SIZE}}{{UNIT}};height:{{SIZE}}{{UNIT}};',
				],
				'condition'  => [ 'show_check_icon' => 'yes' ],
			]
		);

		$this->end_controls_section();

		// ---------------------------------------------------------------
		// STYLE: Per-card palette (cycled by index)
		// ---------------------------------------------------------------
		$this->start_controls_section(
			'section_card_styles',
			[
				'label' => esc_html__( 'Card Styles (cycled)', 'legal-nurse-core' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'card_styles_hint',
			[
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'Each style below is applied to cards in order and cycles. E.g. 3 styles → card 1/2/3 use style 1/2/3, card 4 reuses style 1.', 'legal-nurse-core' ),
				'content_classes' => 'elementor-descriptor',
			]
		);

		$style_rep = new \Elementor\Repeater();

		$style_rep->add_control(
			'bg_color',
			[
				'label'   => esc_html__( 'Background', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::COLOR,
				'default' => '#F1ECE1',
			]
		);

		$style_rep->add_control(
			'border_color',
			[
				'label'   => esc_html__( 'Border Color', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::COLOR,
				'default' => 'transparent',
			]
		);

		$style_rep->add_control(
			'border_width',
			[
				'label'      => esc_html__( 'Border Width', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 12 ] ],
				'default'    => [ 'size' => 0, 'unit' => 'px' ],
			]
		);

		$style_rep->add_control(
			'title_color',
			[
				'label'   => esc_html__( 'Title Color', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::COLOR,
				'default' => '#2E4057',
			]
		);

		$style_rep->add_control(
			'price_color',
			[
				'label'   => esc_html__( 'Price Color', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::COLOR,
				'default' => '#2E4057',
			]
		);

		$style_rep->add_control(
			'text_color',
			[
				'label'   => esc_html__( 'Text Color', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::COLOR,
				'default' => '#4A4A4A',
			]
		);

		$style_rep->add_control(
			'check_color',
			[
				'label'   => esc_html__( 'Check Icon Color', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::COLOR,
				'default' => '#1BA39C',
			]
		);

		$style_rep->add_control(
			'button_color',
			[
				'label'   => esc_html__( 'Button Text/Border', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::COLOR,
				'default' => '#1BA39C',
			]
		);

		$this->add_control(
			'card_styles',
			[
				'label'       => esc_html__( 'Styles', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $style_rep->get_controls(),
				'title_field' => '{{{ bg_color }}}',
				'default'     => [
					[ 'bg_color' => '#F1ECE1', 'title_color' => '#2E4057', 'price_color' => '#2E4057', 'text_color' => '#4A4A4A', 'check_color' => '#1BA39C', 'button_color' => '#1BA39C' ],
					[ 'bg_color' => '#8FE3D0', 'title_color' => '#2E4057', 'price_color' => '#2E4057', 'text_color' => '#2E4057', 'check_color' => '#2E4057', 'button_color' => '#2E4057' ],
					[ 'bg_color' => '#6C63C7', 'title_color' => '#FFFFFF', 'price_color' => '#FFFFFF', 'text_color' => '#F0EEFF', 'check_color' => '#FFFFFF', 'button_color' => '#FFFFFF' ],
				],
			]
		);

		$this->end_controls_section();

		// ---------------------------------------------------------------
		// STYLE: Card box
		// ---------------------------------------------------------------
		$this->start_controls_section(
			'section_card_box',
			[
				'label' => esc_html__( 'Card Box', 'legal-nurse-core' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'card_padding',
			[
				'label'      => esc_html__( 'Padding', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'default'    => [ 'top' => 28, 'right' => 28, 'bottom' => 28, 'left' => 28, 'unit' => 'px' ],
				'selectors'  => [ '{{WRAPPER}} .lnc-pcard' => 'padding:{{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
			]
		);

		$this->add_responsive_control(
			'card_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 60 ] ],
				'default'    => [ 'size' => 16, 'unit' => 'px' ],
				'selectors'  => [ '{{WRAPPER}} .lnc-pcard' => 'border-radius:{{SIZE}}{{UNIT}};' ],
			]
		);

		$this->end_controls_section();

		// ---------------------------------------------------------------
		// STYLE: Typography
		// ---------------------------------------------------------------
		$this->start_controls_section(
			'section_typography',
			[
				'label' => esc_html__( 'Typography', 'legal-nurse-core' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typography',
				'label'    => esc_html__( 'Title', 'legal-nurse-core' ),
				'selector' => '{{WRAPPER}} .lnc-pcard__title',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'price_typography',
				'label'    => esc_html__( 'Price', 'legal-nurse-core' ),
				'selector' => '{{WRAPPER}} .lnc-pcard__price',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'note_typography',
				'label'    => esc_html__( 'Note', 'legal-nurse-core' ),
				'selector' => '{{WRAPPER}} .lnc-pcard__note',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'feature_typography',
				'label'    => esc_html__( 'Features', 'legal-nurse-core' ),
				'selector' => '{{WRAPPER}} .lnc-pcard__feature',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Read the pricing note for a product from ACF (pricing_note), falling
	 * back to the plugin's own meta box value.
	 *
	 * @param int $id Product ID.
	 * @return string
	 */
	private function get_product_note( $id ) {
		if ( function_exists( 'get_field' ) ) {
			$note = get_field( 'pricing_note', $id );
			if ( is_string( $note ) && '' !== $note ) {
				return $note;
			}
		}
		return (string) get_post_meta( $id, LNC_META_PRICING_NOTE, true );
	}

	/**
	 * Read the features list for a product from ACF (features repeater),
	 * falling back to the plugin's own meta box value. Handles both a plain
	 * array of strings and an ACF repeater (array of rows).
	 *
	 * @param int $id Product ID.
	 * @return array<int,string>
	 */
	private function get_product_features( $id ) {
		$features = [];

		if ( function_exists( 'get_field' ) ) {
			$rows = get_field( 'features', $id );
			if ( is_array( $rows ) ) {
				foreach ( $rows as $row ) {
					if ( is_array( $row ) ) {
						// ACF repeater row: prefer a "feature" sub-field, else first value.
						$value = $row['feature'] ?? reset( $row );
					} else {
						$value = $row;
					}
					$value = is_string( $value ) ? trim( $value ) : '';
					if ( '' !== $value ) {
						$features[] = $value;
					}
				}
			}
		}

		if ( empty( $features ) ) {
			$meta = get_post_meta( $id, LNC_META_FEATURES, true );
			if ( is_array( $meta ) ) {
				$features = array_values( array_filter( array_map( 'trim', $meta ) ) );
			}
		}

		return $features;
	}

	/**
	 * Build the list of card data arrays from the selected WooCommerce products.
	 *
	 * @param array $settings
	 * @return array<int,array>
	 */
	private function get_cards( $settings ) {
		$cards = [];

		$ids = $settings['wc_products'] ?? [];
		if ( ! is_array( $ids ) || ! function_exists( 'wc_get_product' ) ) {
			return $cards;
		}

		$button_text = $settings['wc_button_text'] ?? esc_html__( 'Compare Details', 'legal-nurse-core' );

		foreach ( $ids as $id ) {
			$product = wc_get_product( $id );
			if ( ! $product ) {
				continue;
			}

			$colors = get_post_meta( $id, LNC_META_COLORS, true );
			$colors = is_array( $colors ) ? array_filter( $colors ) : [];

			$regular = $product->get_regular_price();
			$active  = $product->get_price();

			$cards[] = [
				'title'          => $product->get_name(),
				'price_original' => ( $regular && $regular !== $active ) ? wc_price( $regular ) : '',
				'price'          => wc_price( $active ),
				'note'           => $this->get_product_note( $id ),
				'features'       => $this->get_product_features( $id ),
				'button_text'    => $button_text,
				'button_url'     => $product->get_permalink(),
				'button_target'  => '',
				'color_override' => $colors,
			];
		}

		return $cards;
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$cards    = $this->get_cards( $settings );

		if ( empty( $cards ) ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="elementor-alert elementor-alert-info">'
					. esc_html__( 'Pricing Cards: select one or more WooCommerce products.', 'legal-nurse-core' )
					. '</div>';
			}
			return;
		}

		$styles       = $settings['card_styles'] ?? [];
		$style_count  = count( $styles );
		$show_check   = 'yes' === ( $settings['show_check_icon'] ?? 'yes' );
		$feature_icon = $settings['feature_icon'] ?? [];

		echo '<div class="lnc-pcards">';

		foreach ( $cards as $index => $card ) {
			// Cycle the palette by index, then let per-product colors override.
			$style = $style_count ? $styles[ $index % $style_count ] : [];
			if ( ! empty( $card['color_override'] ) ) {
				$style = array_merge( $style, $card['color_override'] );
			}

			$bg     = $style['bg_color'] ?? '';
			$bc     = $style['border_color'] ?? '';
			$bw     = isset( $style['border_width']['size'] ) ? (float) $style['border_width']['size'] : 0;
			$t_col  = $style['title_color'] ?? '';
			$p_col  = $style['price_color'] ?? '';
			$txt    = $style['text_color'] ?? '';
			$chk    = $style['check_color'] ?? '';
			$btn    = $style['button_color'] ?? '';

			$card_style = sprintf(
				'background:%s;border:%dpx solid %s;',
				esc_attr( $bg ),
				(int) $bw,
				esc_attr( $bc )
			);

			echo '<div class="lnc-pcard" style="' . esc_attr( $card_style ) . '">';

			// Title.
			if ( '' !== $card['title'] ) {
				printf(
					'<h3 class="lnc-pcard__title" style="color:%s">%s</h3>',
					esc_attr( $t_col ),
					esc_html( $card['title'] )
				);
			}

			// Price row.
			echo '<div class="lnc-pcard__price" style="color:' . esc_attr( $p_col ) . '">';
			if ( '' !== $card['price_original'] ) {
				echo '<span class="lnc-pcard__price-original">' . wp_kses_post( $card['price_original'] ) . '</span> ';
			}
			echo '<span class="lnc-pcard__price-current">' . wp_kses_post( $card['price'] ) . '</span>';
			echo '</div>';

			// Note.
			if ( '' !== $card['note'] ) {
				printf(
					'<p class="lnc-pcard__note" style="color:%s">%s</p>',
					esc_attr( $txt ),
					wp_kses_post( $card['note'] )
				);
			}

			// Features.
			if ( ! empty( $card['features'] ) ) {
				echo '<ul class="lnc-pcard__features">';
				foreach ( $card['features'] as $feature ) {
					echo '<li class="lnc-pcard__feature" style="color:' . esc_attr( $txt ) . '">';
					if ( $show_check ) {
						echo '<span class="lnc-pcard__check" style="color:' . esc_attr( $chk ) . '" aria-hidden="true">';
						if ( ! empty( $feature_icon['value'] ) ) {
							\Elementor\Icons_Manager::render_icon( $feature_icon, [ 'aria-hidden' => 'true' ] );
						} else {
							echo '&#10003;';
						}
						echo '</span> ';
					}
					echo esc_html( $feature );
					echo '</li>';
				}
				echo '</ul>';
			}

			// Button.
			if ( '' !== $card['button_text'] ) {
				$target = $card['button_target'] ? ' target="' . esc_attr( $card['button_target'] ) . '"' : '';
				$href   = $card['button_url'] ? esc_url( $card['button_url'] ) : '#';
				printf(
					'<a class="lnc-pcard__button" href="%s"%s style="--lnc-accent:%s">%s</a>',
					$href,
					$target,
					esc_attr( $btn ),
					esc_html( $card['button_text'] )
				);
			}

			echo '</div>'; // .lnc-pcard
		}

		echo '</div>'; // .lnc-pcards
	}
}
