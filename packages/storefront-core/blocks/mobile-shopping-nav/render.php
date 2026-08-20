<?php
/**
 * Server-rendered mobile shopping navigation.
 *
 * @package BhaivaTechStorefrontCore
 */

defined( 'ABSPATH' ) || exit;

if ( is_admin() ) {
	return;
}

if ( function_exists( 'is_checkout' ) && is_checkout() ) {
	return;
}

if ( function_exists( 'is_cart' ) && is_cart() ) {
	return;
}

$home_url    = home_url( '/' );
$search_url  = $home_url . '#grocery-search';
$browse_url  = $home_url . '#grocery-browse';
$cart_url    = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : $home_url;
$account_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : $home_url;
$cart_count  = WC()->cart ? (int) WC()->cart->get_cart_contents_count() : 0;
$is_account_current = function_exists( 'is_account_page' ) && is_account_page();
?>
<nav class="bt-mobile-shopping-nav" aria-label="<?php echo esc_attr_x( 'Shopping', 'mobile storefront navigation label', 'bhaivatech-storefront-alpha' ); ?>" data-bt-mobile-shopping-nav>
	<a class="bt-mobile-shopping-nav__item" href="<?php echo esc_url( $home_url ); ?>"<?php echo is_front_page() ? ' aria-current="page"' : ''; ?>>
		<span><?php esc_html_e( 'Home', 'bhaivatech-storefront-alpha' ); ?></span>
	</a>

	<a class="bt-mobile-shopping-nav__item" href="<?php echo esc_url( $search_url ); ?>" data-bt-mobile-search-link>
		<span><?php esc_html_e( 'Search', 'bhaivatech-storefront-alpha' ); ?></span>
	</a>

	<a class="bt-mobile-shopping-nav__item" href="<?php echo esc_url( $browse_url ); ?>" data-bt-mobile-browse-link>
		<span><?php esc_html_e( 'Browse', 'bhaivatech-storefront-alpha' ); ?></span>
	</a>

	<a class="bt-mobile-shopping-nav__item" href="<?php echo esc_url( $cart_url ); ?>" data-bt-mobile-cart-link>
		<span><?php esc_html_e( 'Cart', 'bhaivatech-storefront-alpha' ); ?></span>
		<span
			class="bt-mobile-shopping-nav__badge"
			data-bt-mobile-cart-count
			data-label-one="<?php echo esc_attr__( '1 item in cart', 'bhaivatech-storefront-alpha' ); ?>"
			data-label-many="<?php echo esc_attr__( '%d items in cart', 'bhaivatech-storefront-alpha' ); ?>"
			aria-label="<?php echo esc_attr( sprintf( _n( '%d item in cart', '%d items in cart', $cart_count, 'bhaivatech-storefront-alpha' ), $cart_count ) ); ?>"
		>
			<?php echo esc_html( (string) $cart_count ); ?>
		</span>
	</a>

	<a class="bt-mobile-shopping-nav__item" href="<?php echo esc_url( $account_url ); ?>"<?php echo $is_account_current ? ' aria-current="page"' : ''; ?>>
		<span><?php esc_html_e( 'Account', 'bhaivatech-storefront-alpha' ); ?></span>
	</a>
</nav>
