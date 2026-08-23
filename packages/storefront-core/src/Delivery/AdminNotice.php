<?php
/**
 * Admin notice for delivery configuration.
 *
 * Guides store owners to configure WooCommerce Shipping Zones rather than
 * a parallel postcode list, since the delivery checker reads from those zones.
 *
 * @package StorefrontCore\Delivery
 */

declare( strict_types=1 );

namespace StorefrontCore\Delivery;

/**
 * Shows a one-time dismissible admin notice on plugin activation.
 */
final class AdminNotice {

	private const TRANSIENT_KEY = 'storefront_delivery_notice_shown';

	/**
	 * Register hooks.
	 */
	public function register(): void {
		add_action( 'admin_notices', [ $this, 'maybe_show_notice' ] );
		add_action( 'wp_ajax_storefront_dismiss_delivery_notice', [ $this, 'dismiss' ] );
	}

	/**
	 * Show the notice if it has not been dismissed and user can manage WooCommerce.
	 */
	public function maybe_show_notice(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		if ( get_transient( self::TRANSIENT_KEY ) ) {
			return;
		}

		$zones_url = esc_url( admin_url( 'admin.php?page=wc-settings&tab=shipping' ) );

		echo '<div class="notice notice-info is-dismissible storefront-delivery-notice" data-dismiss-nonce="'
			. esc_attr( wp_create_nonce( 'storefront_dismiss_delivery_notice' ) ) . '">';
		echo '<p><strong>' . esc_html__( 'Storefront Delivery Checker', 'bhaivatech-storefront-alpha' ) . '</strong> — ';
		printf(
			/* translators: %s = WooCommerce Shipping Zones settings URL */
			esc_html__( 'The delivery checker reads your WooCommerce Shipping Zones. Configure delivery areas at: %s', 'bhaivatech-storefront-alpha' ),
			'<a href="' . $zones_url . '">' . esc_html__( 'WooCommerce → Settings → Shipping', 'bhaivatech-storefront-alpha' ) . '</a>'
		);
		echo '</p></div>';
		echo '<script>
		(function(){
			var btn = document.querySelector(".storefront-delivery-notice .notice-dismiss");
			if(!btn) return;
			btn.addEventListener("click", function(){
				fetch("' . esc_url( admin_url( 'admin-ajax.php' ) ) . '", {
					method:"POST",
					headers:{"Content-Type":"application/x-www-form-urlencoded"},
					body:"action=storefront_dismiss_delivery_notice&_ajax_nonce=" + encodeURIComponent(btn.closest("[data-dismiss-nonce]").dataset.dismissNonce)
				});
			});
		})();
		</script>';
	}

	/**
	 * AJAX handler — marks the notice as dismissed.
	 */
	public function dismiss(): void {
		check_ajax_referer( 'storefront_dismiss_delivery_notice' );
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( '', '', [ 'response' => 403 ] );
		}
		set_transient( self::TRANSIENT_KEY, true, YEAR_IN_SECONDS );
		wp_die();
	}

	/**
	 * Set the transient on plugin activation so the notice appears once.
	 */
	public static function on_activate(): void {
		delete_transient( self::TRANSIENT_KEY );
	}
}
