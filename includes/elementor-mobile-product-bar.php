<?php
/**
 * Elementor Mobile Product Bar Widget
 *
 * A mobile-only bar that shows a WooCommerce product's price (sale or
 * regular) with a checkout button. The bar relocates itself to appear right
 * after a chosen element on the page (e.g. .hero-section) via a small script,
 * and is hidden on tablet/desktop.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LNC_Mobile_Product_Bar_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'lnc_mobile_product_bar';
	}

	public function get_title() {
		return esc_html__( 'LN - Mobile Product Bar', 'legal-nurse-core' );
	}

	public function get_icon() {
		return 'eicon-product-price';
	}

	public function get_categories() {
		return [ 'legal-nurse' ];
	}

	public function get_keywords() {
		return [ 'product', 'price', 'checkout', 'mobile', 'sticky', 'woocommerce', 'enroll', 'legal nurse' ];
	}

	public function get_style_depends() {
		return [ 'lnc-mobile-product-bar' ];
	}

	public function get_script_depends() {
		return [ 'lnc-mobile-product-bar' ];
	}

	/**
	 * WooCommerce product options for the SELECT2 control.
	 *
	 * @return array<int,string>
	 */
	private function get_product_options() {
		$options = [];

		// Only needed to populate the editor dropdown — skip on the front end,
		// where render() uses the saved product ID, not this list.
		$is_editor = is_admin()
			|| \Elementor\Plugin::$instance->editor->is_edit_mode()
			|| ( isset( $_GET['action'] ) && 'elementor' === $_GET['action'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! $is_editor ) {
			return $options;
		}

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

		// ============ CONTENT ============
		$this->start_controls_section( 'section_content', [ 'label' => esc_html__( 'Product Bar', 'legal-nurse-core' ), 'tab' => \Elementor\Controls_Manager::TAB_CONTENT ] );

		$this->add_control( 'enabled', [
			'label'        => esc_html__( 'Enable Bar', 'legal-nurse-core' ),
			'type'         => \Elementor\Controls_Manager::SWITCHER,
			'label_on'     => esc_html__( 'On', 'legal-nurse-core' ),
			'label_off'    => esc_html__( 'Off', 'legal-nurse-core' ),
			'return_value' => 'yes',
			'default'      => 'yes',
		] );

		$this->add_control( 'product_id', [
			'label'   => esc_html__( 'Product', 'legal-nurse-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT2,
			'options' => $this->get_product_options(),
			'label_block' => true,
		] );

		$this->add_control( 'offset_selector', [
			'label'       => esc_html__( 'Insert After (CSS selector)', 'legal-nurse-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => '.hero-section',
			'description' => esc_html__( 'The bar is moved to appear right after this element (e.g. .hero-section). Leave empty to keep it where placed.', 'legal-nurse-core' ),
		] );

		$this->add_control( 'price_source', [
			'label'   => esc_html__( 'Price to Show', 'legal-nurse-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'active',
			'options' => [
				'active'  => esc_html__( 'Sale / Current Price', 'legal-nurse-core' ),
				'regular' => esc_html__( 'Original / Regular Price', 'legal-nurse-core' ),
			],
		] );

		$this->add_control( 'price_caption', [
			'label'       => esc_html__( 'Price Caption', 'legal-nurse-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => esc_html__( 'SALE PRICE', 'legal-nurse-core' ),
			'description' => esc_html__( 'Small label above/beside the price (e.g. SALE PRICE or ORIGINAL PRICE).', 'legal-nurse-core' ),
		] );

		$this->add_control( 'button_text', [
			'label'   => esc_html__( 'Button Text', 'legal-nurse-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => esc_html__( 'Enroll Now', 'legal-nurse-core' ),
		] );

		$this->add_control( 'checkout_type', [
			'label'   => esc_html__( 'Button Action', 'legal-nurse-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'checkout',
			'options' => [
				'checkout' => esc_html__( 'Add to Cart → Checkout', 'legal-nurse-core' ),
				'product'  => esc_html__( 'Go to Product Page', 'legal-nurse-core' ),
			],
		] );

		$this->end_controls_section();

		// ============ STYLE: BAR ============
		$this->start_controls_section( 'section_bar_style', [ 'label' => esc_html__( 'Bar', 'legal-nurse-core' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ] );

		$this->add_control( 'bar_bg', [
			'label'     => esc_html__( 'Background', 'legal-nurse-core' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .lnc-mpbar' => 'background-color:{{VALUE}};' ],
		] );

		$this->add_responsive_control( 'bar_padding', [
			'label'      => esc_html__( 'Padding', 'legal-nurse-core' ),
			'type'       => \Elementor\Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', 'em' ],
			'default'    => [ 'top' => 12, 'right' => 16, 'bottom' => 12, 'left' => 16, 'unit' => 'px' ],
			'selectors'  => [ '{{WRAPPER}} .lnc-mpbar' => 'padding:{{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
		] );

		$this->add_responsive_control( 'bar_gap', [
			'label'      => esc_html__( 'Gap', 'legal-nurse-core' ),
			'type'       => \Elementor\Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [ 'px' => [ 'min' => 0, 'max' => 60 ] ],
			'default'    => [ 'size' => 16, 'unit' => 'px' ],
			'selectors'  => [ '{{WRAPPER}} .lnc-mpbar' => 'gap:{{SIZE}}{{UNIT}};' ],
		] );

		$this->end_controls_section();

		// ============ STYLE: CAPTION ============
		$this->start_controls_section( 'section_caption_style', [ 'label' => esc_html__( 'Price Caption', 'legal-nurse-core' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ] );

		$this->add_control( 'caption_color', [
			'label'     => esc_html__( 'Color', 'legal-nurse-core' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .lnc-mpbar__caption' => 'color:{{VALUE}};' ],
		] );

		$this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [ 'name' => 'caption_typography', 'selector' => '{{WRAPPER}} .lnc-mpbar__caption' ] );

		$this->end_controls_section();

		// ============ STYLE: PRICE ============
		$this->start_controls_section( 'section_price_style', [ 'label' => esc_html__( 'Price', 'legal-nurse-core' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ] );

		$this->add_control( 'price_color', [
			'label'     => esc_html__( 'Color', 'legal-nurse-core' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .lnc-mpbar__price' => 'color:{{VALUE}};' ],
		] );

		$this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [ 'name' => 'price_typography', 'selector' => '{{WRAPPER}} .lnc-mpbar__price' ] );

		$this->end_controls_section();

		// ============ STYLE: BUTTON ============
		$this->start_controls_section( 'section_button_style', [ 'label' => esc_html__( 'Button', 'legal-nurse-core' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ] );

		$this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [ 'name' => 'button_typography', 'selector' => '{{WRAPPER}} .lnc-mpbar__btn' ] );

		$this->add_responsive_control( 'button_padding', [
			'label'      => esc_html__( 'Padding', 'legal-nurse-core' ),
			'type'       => \Elementor\Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', 'em' ],
			'default'    => [ 'top' => 14, 'right' => 32, 'bottom' => 14, 'left' => 32, 'unit' => 'px' ],
			'selectors'  => [ '{{WRAPPER}} .lnc-mpbar__btn' => 'padding:{{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
		] );

		$this->add_responsive_control( 'button_radius', [
			'label'      => esc_html__( 'Border Radius', 'legal-nurse-core' ),
			'type'       => \Elementor\Controls_Manager::SLIDER,
			'size_units' => [ 'px', '%' ],
			'range'      => [ 'px' => [ 'min' => 0, 'max' => 100 ], '%' => [ 'min' => 0, 'max' => 100 ] ],
			'default'    => [ 'size' => 999, 'unit' => 'px' ],
			'selectors'  => [ '{{WRAPPER}} .lnc-mpbar__btn' => 'border-radius:{{SIZE}}{{UNIT}};' ],
		] );

		$this->start_controls_tabs( 'button_tabs' );

		$this->start_controls_tab( 'button_normal', [ 'label' => esc_html__( 'Normal', 'legal-nurse-core' ) ] );
		$this->add_control( 'button_color', [ 'label' => esc_html__( 'Text', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#ffffff', 'selectors' => [ '{{WRAPPER}} .lnc-mpbar__btn' => 'color:{{VALUE}};' ] ] );
		$this->add_control( 'button_bg', [ 'label' => esc_html__( 'Background', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#25797c', 'selectors' => [ '{{WRAPPER}} .lnc-mpbar__btn' => 'background-color:{{VALUE}};' ] ] );
		$this->end_controls_tab();

		$this->start_controls_tab( 'button_hover', [ 'label' => esc_html__( 'Hover', 'legal-nurse-core' ) ] );
		$this->add_control( 'button_color_h', [ 'label' => esc_html__( 'Text', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} .lnc-mpbar__btn:hover' => 'color:{{VALUE}};' ] ] );
		$this->add_control( 'button_bg_h', [ 'label' => esc_html__( 'Background', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#1f6f72', 'selectors' => [ '{{WRAPPER}} .lnc-mpbar__btn:hover' => 'background-color:{{VALUE}};' ] ] );
		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$is_editor = \Elementor\Plugin::$instance->editor->is_edit_mode();

		if ( 'yes' !== ( $settings['enabled'] ?? '' ) ) {
			if ( $is_editor ) {
				echo '<div class="lnc-mpbar-notice">' . esc_html__( 'Mobile Product Bar is disabled.', 'legal-nurse-core' ) . '</div>';
			}
			return;
		}

		$product_id = (int) ( $settings['product_id'] ?? 0 );

		if ( ! $product_id || ! function_exists( 'wc_get_product' ) ) {
			if ( $is_editor ) {
				echo '<div class="lnc-mpbar-notice">' . esc_html__( 'Select a WooCommerce product for the Mobile Product Bar.', 'legal-nurse-core' ) . '</div>';
			}
			return;
		}

		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			if ( $is_editor ) {
				echo '<div class="lnc-mpbar-notice">' . esc_html__( 'Selected product not found.', 'legal-nurse-core' ) . '</div>';
			}
			return;
		}

		// Which price to display.
		$source = ( 'regular' === ( $settings['price_source'] ?? 'active' ) ) ? 'regular' : 'active';
		$raw    = ( 'regular' === $source ) ? $product->get_regular_price() : $product->get_price();
		$price_html = ( '' !== $raw && null !== $raw ) ? wc_price( $raw ) : $product->get_price_html();

		// Button link.
		if ( 'product' === ( $settings['checkout_type'] ?? 'checkout' ) ) {
			$button_url = $product->get_permalink();
		} elseif ( function_exists( 'wc_get_checkout_url' ) ) {
			$button_url = add_query_arg( 'add-to-cart', $product_id, wc_get_checkout_url() );
		} else {
			$button_url = $product->add_to_cart_url();
		}

		$caption = trim( (string) ( $settings['price_caption'] ?? '' ) );
		$btn_txt = $settings['button_text'] ?? esc_html__( 'Enroll Now', 'legal-nurse-core' );
		$offset  = trim( (string) ( $settings['offset_selector'] ?? '' ) );

		?>
		<div class="lnc-mpbar" data-offset="<?php echo esc_attr( $offset ); ?>">
			<div class="lnc-mpbar__price-wrap">
				<?php if ( '' !== $caption ) : ?>
					<span class="lnc-mpbar__caption"><?php echo esc_html( $caption ); ?></span>
				<?php endif; ?>
				<span class="lnc-mpbar__price"><?php echo wp_kses_post( $price_html ); ?></span>
			</div>
			<a class="lnc-mpbar__btn" href="<?php echo esc_url( $button_url ); ?>"><?php echo esc_html( $btn_txt ); ?></a>
		</div>
		<?php
	}
}
