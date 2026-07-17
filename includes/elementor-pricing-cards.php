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
			'wc_button_text',
			[
				'label'   => esc_html__( 'Button Text', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Compare Details', 'legal-nurse-core' ),
			]
		);

		// Per-item card list: each row selects a product and carries its own styling.
		$item = new \Elementor\Repeater();

		$item->add_control(
			'product_id',
			[
				'label'       => esc_html__( 'Product', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'label_block' => true,
				'options'     => $this->get_product_options(),
				'description' => esc_html__( 'Title & price come from the product; note & features from ACF (pricing_note, features).', 'legal-nurse-core' ),
			]
		);

		$item->add_control(
			'bg_color',
			[
				'label'   => esc_html__( 'Background', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::COLOR,
				'default' => '#F1ECE1',
			]
		);

		$item->add_control(
			'border_color',
			[
				'label'   => esc_html__( 'Border Color', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::COLOR,
				'default' => 'transparent',
			]
		);

		$item->add_control(
			'border_width',
			[
				'label'      => esc_html__( 'Border Width', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 12 ] ],
				'default'    => [ 'size' => 0, 'unit' => 'px' ],
			]
		);

		$item->add_control(
			'title_color',
			[
				'label'   => esc_html__( 'Title Color', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::COLOR,
				'default' => '#2E4057',
			]
		);

		$item->add_control(
			'price_color',
			[
				'label'   => esc_html__( 'Price Color', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::COLOR,
				'default' => '#2E4057',
			]
		);

		$item->add_control(
			'text_color',
			[
				'label'   => esc_html__( 'Text Color', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::COLOR,
				'default' => '#4A4A4A',
			]
		);

		$item->add_control(
			'check_color',
			[
				'label'   => esc_html__( 'Check Icon Color', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::COLOR,
				'default' => '#1BA39C',
			]
		);

		$item->add_control(
			'button_color',
			[
				'label'   => esc_html__( 'Button Text/Border', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::COLOR,
				'default' => '#1BA39C',
			]
		);

		$item->add_control(
			'button_hover_bg',
			[
				'label'   => esc_html__( 'Button Hover Background', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::COLOR,
				'default' => '#1BA39C',
			]
		);

		$item->add_control(
			'button_hover_color',
			[
				'label'   => esc_html__( 'Button Hover Text', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::COLOR,
				'default' => '#FFFFFF',
			]
		);

		$item->add_control(
			'hover_heading',
			[
				'label'     => esc_html__( 'Card Hover', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$item->add_control(
			'hover_border_color',
			[
				'label'   => esc_html__( 'Hover Border Color', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::COLOR,
				'default' => '',
			]
		);

		$item->add_control(
			'hover_border_width',
			[
				'label'      => esc_html__( 'Hover Border Width', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 12 ] ],
				'default'    => [ 'size' => 2, 'unit' => 'px' ],
			]
		);

		$item->add_control(
			'hover_padding',
			[
				'label'      => esc_html__( 'Hover Padding', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'description' => esc_html__( 'Leave empty to keep the base padding on hover.', 'legal-nurse-core' ),
			]
		);

		$this->add_control(
			'card_items',
			[
				'label'         => esc_html__( 'Cards', 'legal-nurse-core' ),
				'type'          => \Elementor\Controls_Manager::REPEATER,
				'fields'        => $item->get_controls(),
				'prevent_empty' => false,
				'title_field'   => '{{{ product_id }}}',
				'default'       => [
					[ 'bg_color' => '#F1ECE1', 'title_color' => '#2E4057', 'price_color' => '#2E4057', 'text_color' => '#4A4A4A', 'check_color' => '#1BA39C', 'button_color' => '#1BA39C' ],
					[ 'bg_color' => '#8FE3D0', 'title_color' => '#2E4057', 'price_color' => '#2E4057', 'text_color' => '#2E4057', 'check_color' => '#2E4057', 'button_color' => '#2E4057' ],
					[ 'bg_color' => '#6C63C7', 'title_color' => '#FFFFFF', 'price_color' => '#FFFFFF', 'text_color' => '#F0EEFF', 'check_color' => '#FFFFFF', 'button_color' => '#FFFFFF' ],
				],
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
	 * Read the pricing note for a product from the ACF field "pricing_note".
	 *
	 * @param int $id Product ID.
	 * @return string
	 */
	private function get_product_note( $id ) {
		if ( function_exists( 'get_field' ) ) {
			$note = get_field( 'pricing_note', $id );
			if ( is_string( $note ) ) {
				return $note;
			}
		}
		return '';
	}

	/**
	 * Read the features list for a product from the ACF field "features".
	 * Handles both a plain array of strings and an ACF repeater (array of rows).
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

		$items = $settings['card_items'] ?? [];
		if ( ! is_array( $items ) || ! function_exists( 'wc_get_product' ) ) {
			return $cards;
		}

		$button_text = $settings['wc_button_text'] ?? esc_html__( 'Compare Details', 'legal-nurse-core' );

		foreach ( $items as $item ) {
			$id      = $item['product_id'] ?? 0;
			$product = $id ? wc_get_product( $id ) : null;
			if ( ! $product ) {
				continue;
			}

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
				'style'          => [
					'bg_color'           => $item['bg_color'] ?? '',
					'border_color'       => $item['border_color'] ?? '',
					'border_width'       => isset( $item['border_width']['size'] ) ? (float) $item['border_width']['size'] : 0,
					'title_color'        => $item['title_color'] ?? '',
					'price_color'        => $item['price_color'] ?? '',
					'text_color'         => $item['text_color'] ?? '',
					'check_color'        => $item['check_color'] ?? '',
					'button_color'       => $item['button_color'] ?? '',
					'button_hover_bg'    => $item['button_hover_bg'] ?? '',
					'button_hover_color' => $item['button_hover_color'] ?? '',
					'hover_border_color' => $item['hover_border_color'] ?? '',
					'hover_border_width' => isset( $item['hover_border_width']['size'] ) ? (float) $item['hover_border_width']['size'] : null,
					'hover_padding'      => $item['hover_padding'] ?? [],
				],
			];
		}

		return $cards;
	}

	/**
	 * Keep a value safe to drop inside a <style> block (no break-out chars).
	 *
	 * @param string $value
	 * @return string
	 */
	private function css_safe( $value ) {
		return trim( str_replace( [ '<', '>', '{', '}', ';', '"', "'", '\\' ], '', (string) $value ) );
	}

	/**
	 * Build the scoped <style> for a card's hover states (card border/padding
	 * and button hover background/text).
	 *
	 * @param string $uid   Unique per-card class.
	 * @param array  $style Card style data.
	 * @return string
	 */
	private function hover_style( $uid, $style ) {
		$card_rules = [];

		$hbw = $style['hover_border_width'];
		$hbc = $this->css_safe( $style['hover_border_color'] ?? '' );
		if ( null !== $hbw && '' !== $hbc ) {
			$card_rules[] = sprintf( 'border:%dpx solid %s !important;', (int) $hbw, $hbc );
		} elseif ( '' !== $hbc ) {
			$card_rules[] = sprintf( 'border-color:%s !important;', $hbc );
		}

		$pad = $style['hover_padding'] ?? [];
		if ( is_array( $pad ) && isset( $pad['top'] ) && '' !== $pad['top'] ) {
			$unit = preg_replace( '/[^a-z%]/i', '', (string) ( $pad['unit'] ?? 'px' ) );
			$card_rules[] = sprintf(
				'padding:%s%5$s %s%5$s %s%5$s %s%5$s !important;',
				(float) $pad['top'],
				(float) ( $pad['right'] ?? 0 ),
				(float) ( $pad['bottom'] ?? 0 ),
				(float) ( $pad['left'] ?? 0 ),
				$unit ? $unit : 'px'
			);
		}

		$btn_rules = [];
		$bh_bg = $this->css_safe( $style['button_hover_bg'] ?? '' );
		$bh_c  = $this->css_safe( $style['button_hover_color'] ?? '' );
		if ( '' !== $bh_bg ) {
			$btn_rules[] = sprintf( 'background:%1$s !important;border-color:%1$s !important;', $bh_bg );
		}
		if ( '' !== $bh_c ) {
			$btn_rules[] = sprintf( 'color:%s !important;', $bh_c );
		}

		$out = '';
		if ( $card_rules ) {
			$out .= sprintf( '.%1$s.lnc-pcard:hover{%2$s}', $uid, implode( '', $card_rules ) );
		}
		if ( $btn_rules ) {
			$out .= sprintf(
				'.%1$s .lnc-pcard__button:hover,.%1$s .lnc-pcard__button:focus{%2$s}',
				$uid,
				implode( '', $btn_rules )
			);
		}

		return '' === $out ? '' : '<style>' . $out . '</style>';
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

		$show_check   = 'yes' === ( $settings['show_check_icon'] ?? 'yes' );
		$feature_icon = $settings['feature_icon'] ?? [];

		$widget_id = $this->get_id();

		echo '<div class="lnc-pcards">';

		foreach ( $cards as $index => $card ) {
			$style = $card['style'];

			$bg     = $style['bg_color'] ?? '';
			$bc     = $style['border_color'] ?? '';
			$bw     = (float) ( $style['border_width'] ?? 0 );
			$t_col  = $style['title_color'] ?? '';
			$p_col  = $style['price_color'] ?? '';
			$txt    = $style['text_color'] ?? '';
			$chk    = $style['check_color'] ?? '';
			$btn    = $style['button_color'] ?? '';

			// Unique per-card class so hover states can be scoped via inline <style>.
			$uid = 'lnc-pcard--' . $widget_id . '-' . $index;

			$card_style = sprintf(
				'background:%s;border:%dpx solid %s;',
				esc_attr( $bg ),
				(int) $bw,
				esc_attr( $bc )
			);

			echo $this->hover_style( $uid, $style ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			echo '<div class="lnc-pcard ' . esc_attr( $uid ) . '" style="' . esc_attr( $card_style ) . '">';

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
					'<p class="lnc-pcard__note" style="color:%s"><span class="lnc-pcard__note-prefix">%s</span> %s</p>',
					esc_attr( $txt ),
					esc_html__( 'Free mentoring', 'legal-nurse-core' ),
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
				$btn_style = $btn
					? sprintf( '--lnc-accent:%1$s;color:%1$s;border-color:%1$s;', esc_attr( $btn ) )
					: '';
				printf(
					'<a class="lnc-pcard__button" href="%s"%s style="%s">%s</a>',
					$href,
					$target,
					$btn_style,
					esc_html( $card['button_text'] )
				);
			}

			echo '</div>'; // .lnc-pcard
		}

		echo '</div>'; // .lnc-pcards
	}
}
