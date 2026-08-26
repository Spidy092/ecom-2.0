<?php
/**
 * Theme-facing storefront enhancements.
 *
 * @package BhaivaTechStorefrontCore
 */

namespace BhaivaTech\Storefront;

defined( 'ABSPATH' ) || exit;

/**
 * Registers shortcodes and small presentation hooks without owning WooCommerce
 * cart, checkout, shipping, or product data.
 */
final class Storefront_Frontend {
	private Buy_Again_Service $buy_again_service;
	private ?array $buy_again_cache = null;

	/**
	 * @param Buy_Again_Service $buy_again_service Repeat-purchase service.
	 */
	public function __construct( Buy_Again_Service $buy_again_service ) {
		$this->buy_again_service = $buy_again_service;
	}

	/**
	 * Register public presentation seams.
	 *
	 * @return void
	 */
	public function register(): void {
		add_shortcode( 'bhaivatech_delivery_checker', array( $this, 'render_delivery_checker' ) );
		add_shortcode( 'bhaivatech_shopping_list', array( $this, 'render_shopping_list' ) );
		add_shortcode( 'bhaivatech_buy_again', array( $this, 'render_buy_again' ) );
		add_shortcode( 'bhaivatech_buy_again_link', array( $this, 'render_buy_again_link' ) );
		add_shortcode( 'bhaivatech_cart_feedback', array( $this, 'render_cart_feedback' ) );
		add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'append_shopping_list_button' ), 20, 3 );
	}

	/**
	 * Render a delivery checker backed by the canonical Core endpoint.
	 *
	 * @return string
	 */
	public function render_delivery_checker(): string {
		$this->enqueue_script();
		$postcode_id = wp_unique_id( 'storefront-postcode-' );
		$status_id   = wp_unique_id( 'storefront-delivery-result-' );

		ob_start();
		?>
		<section id="grovia-delivery-checker" class="grovia-delivery-checker storefront-delivery-checker" data-delivery-checker>
			<div class="grovia-delivery-checker__copy">
				<p class="grovia-kicker"><?php echo esc_html__( 'Delivery certainty', 'bhaivatech-storefront-alpha' ); ?></p>
				<h2><?php echo esc_html__( 'Know before you fill the basket.', 'bhaivatech-storefront-alpha' ); ?></h2>
				<p><?php echo esc_html__( 'Enter your postcode to see whether delivery is available in your area.', 'bhaivatech-storefront-alpha' ); ?></p>
			</div>
			<form class="storefront-delivery-form grovia-delivery-checker__form" id="storefront-delivery-form" data-delivery-form data-endpoint="<?php echo esc_attr( esc_url( rest_url( 'storefront-core/v1/delivery/check' ) ) ); ?>">
				<label for="<?php echo esc_attr( $postcode_id ); ?>"><?php echo esc_html__( 'Postcode', 'bhaivatech-storefront-alpha' ); ?></label>
				<div class="grovia-delivery-checker__controls">
					<input id="<?php echo esc_attr( $postcode_id ); ?>" name="postcode" type="text" inputmode="text" autocomplete="postal-code" maxlength="20" required aria-describedby="<?php echo esc_attr( $status_id ); ?>">
					<button type="submit"><?php echo esc_html__( 'Check delivery', 'bhaivatech-storefront-alpha' ); ?></button>
				</div>
				<p id="<?php echo esc_attr( $status_id ); ?>" class="storefront-delivery-form__result grovia-delivery-checker__status" data-delivery-status role="status" aria-live="polite"></p>
			</form>
		</section>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Render a Shopping List widget consumed by the theme module.
	 *
	 * @return string
	 */
	public function render_shopping_list(): string {
		if ( ! is_user_logged_in() ) {
			return '<p class="grovia-list-empty"><a href="' . esc_url( wp_login_url( get_permalink() ) ) . '">' . esc_html__( 'Sign in to view your Shopping List.', 'bhaivatech-storefront-alpha' ) . '</a></p>';
		}

		ob_start();
		?>
		<section class="storefront-shopping-list" id="storefront-shopping-list" aria-label="<?php echo esc_attr__( 'Shopping list', 'bhaivatech-storefront-alpha' ); ?>">
			<div class="storefront-shopping-list__header"><h2 class="storefront-shopping-list__title"><?php echo esc_html__( 'Your list', 'bhaivatech-storefront-alpha' ); ?></h2></div>
			<div class="storefront-shopping-list__body" id="storefront-shopping-list-body" aria-live="polite" aria-atomic="false"><p class="storefront-shopping-list__loading"><?php echo esc_html__( 'Loading your list…', 'bhaivatech-storefront-alpha' ); ?></p></div>
			<div class="storefront-shopping-list__footer" id="storefront-shopping-list-footer" hidden><button type="button" class="storefront-shopping-list__add-all wp-element-button" id="storefront-shopping-list-add-all"><?php echo esc_html__( 'Add to basket', 'bhaivatech-storefront-alpha' ); ?></button></div>
			<template id="storefront-list-item-tpl"><div class="storefront-shopping-list__item" data-product-id="" data-variation-id=""><label class="storefront-shopping-list__item-label"><input type="checkbox" class="storefront-shopping-list__item-check"><span class="storefront-shopping-list__item-name"></span></label><button type="button" class="storefront-shopping-list__item-remove" aria-label="<?php echo esc_attr__( 'Remove item', 'bhaivatech-storefront-alpha' ); ?>">×</button></div></template>
		</section>
		<?php
		$this->enqueue_theme_script();
		return (string) ob_get_clean();
	}

	/**
	 * Render server-truth repeat purchases.
	 *
	 * @return string
	 */
	public function render_buy_again(): string {
		if ( ! is_user_logged_in() ) {
			return '<section class="grovia-buy-again"><p class="grovia-kicker">' . esc_html__( 'Buy Again', 'bhaivatech-storefront-alpha' ) . '</p><h2>' . esc_html__( 'Bring back the usuals.', 'bhaivatech-storefront-alpha' ) . '</h2><p><a href="' . esc_url( wp_login_url( get_permalink() ) ) . '">' . esc_html__( 'Sign in to see products from your orders.', 'bhaivatech-storefront-alpha' ) . '</a></p></section>';
		}

		$products = $this->get_buy_again_products();
		ob_start();
		?>
		<section class="grovia-buy-again" data-buy-again>
			<p class="grovia-kicker"><?php echo esc_html__( 'Buy Again', 'bhaivatech-storefront-alpha' ); ?></p>
			<h2><?php echo esc_html__( 'Bring back the usuals.', 'bhaivatech-storefront-alpha' ); ?></h2>
			<?php if ( empty( $products ) ) : ?>
				<p class="grovia-list-empty"><?php echo esc_html__( 'Your repeat products will appear here after your first completed order.', 'bhaivatech-storefront-alpha' ); ?></p>
			<?php else : ?>
				<ul class="grovia-buy-again__items">
					<?php foreach ( $products as $product ) : ?>
						<li class="grovia-buy-again__item">
							<div>
								<a href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
								<div class="grovia-shopping-list__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
							</div>
							<?php if ( ! $product->is_in_stock() || ! $product->is_purchasable() ) : ?>
								<span class="grovia-buy-again__action"><?php echo esc_html__( 'Unavailable', 'bhaivatech-storefront-alpha' ); ?></span>
							<?php elseif ( $product->is_type( 'simple' ) ) : ?>
								<a class="grovia-buy-again__action" href="<?php echo esc_url( $product->add_to_cart_url() ); ?>"><?php echo esc_html__( 'Add again', 'bhaivatech-storefront-alpha' ); ?></a>
							<?php else : ?>
								<a class="grovia-buy-again__action" href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html__( 'Choose options', 'bhaivatech-storefront-alpha' ); ?></a>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</section>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Render a canonical, account-page-aware Buy Again entry point.
	 *
	 * @return string
	 */
	public function render_buy_again_link(): string {
		$account_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : get_permalink();
		$target      = function_exists( 'wc_get_endpoint_url' ) ? wc_get_endpoint_url( 'buy-again', '', $account_url ) : $account_url;
		$href        = is_user_logged_in() ? $target : wp_login_url( $target );

		return sprintf(
			'<a class="wp-element-button" href="%s">%s</a>',
			esc_url( $href ),
			esc_html__( 'Open Buy Again →', 'bhaivatech-storefront-alpha' )
		);
	}

	/**
	 * Append a private save action to classic Woo product loops.
	 *
	 * @param string $html Existing markup.
	 * @param mixed  $product Product object.
	 * @param array  $args Loop arguments.
	 * @return string
	 */
	public function append_shopping_list_button( string $html, $product, array $args = array() ): string {
		if ( ! is_user_logged_in() || ! is_object( $product ) || ! is_callable( array( $product, 'get_id' ) ) ) {
			return $html;
		}

		$this->enqueue_script();
		$endpoint = rest_url( 'storefront-core/v1/shopping-list/items' );
		$button   = sprintf( '<button type="button" class="grovia-save-list-button" data-shopping-list-button data-product-id="%1$d" data-endpoint="%2$s" data-nonce="%3$s" aria-pressed="false">%4$s</button>', absint( $product->get_id() ), esc_url( $endpoint ), esc_attr( wp_create_nonce( 'wp_rest' ) ), esc_html__( 'Save to list', 'bhaivatech-storefront-alpha' ) );

		return $html . $button;
	}

	/**
	 * Render cart state backed by WooCommerce Store API.
	 *
	 * @return string
	 */
	public function render_cart_feedback(): string {
		$this->enqueue_script();
		$cart_url = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' );
		return sprintf( '<aside class="grovia-cart-feedback" data-cart-feedback data-cart-endpoint="%1$s" data-cart-url="%2$s" hidden><p role="status" aria-live="polite"><strong data-cart-count>0</strong> <span data-cart-count-label>%3$s</span><span data-cart-total></span></p><a href="%2$s">%4$s</a></aside>', esc_attr( esc_url( rest_url( 'wc/store/v1/cart' ) ) ), esc_attr( esc_url( $cart_url ) ), esc_html__( 'items', 'bhaivatech-storefront-alpha' ), esc_html__( 'View basket', 'bhaivatech-storefront-alpha' ) );
	}

	/**
	 * @return array<int, object>
	 */
	private function get_buy_again_products(): array {
		if ( null === $this->buy_again_cache ) {
			$this->buy_again_cache = $this->buy_again_service->get_products_for_customer( get_current_user_id() );
		}
		return $this->buy_again_cache;
	}

	/**
	 * @return void
	 */
	private function enqueue_script(): void {
		wp_register_script(
			'bhaivatech-storefront',
			plugins_url( 'assets/js/storefront.js', dirname( __DIR__ ) . '/storefront-core.php' ),
			array(),
			defined( 'BHAIVATECH_STOREFRONT_CORE_VERSION' ) ? BHAIVATECH_STOREFRONT_CORE_VERSION : '0.0.1-alpha',
			true
		);
		wp_enqueue_script( 'bhaivatech-storefront' );
	}

	/**
	 * @return void
	 */
	private function enqueue_theme_script(): void {
		wp_enqueue_script_module( 'storefront-interactions' );
	}
}
