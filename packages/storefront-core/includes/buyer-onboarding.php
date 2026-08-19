<?php
/**
 * Buyer-facing personalization and launch-review guidance.
 *
 * This file deliberately provides navigation only. It does not mutate Global
 * Styles, template parts, WooCommerce products or merchant configuration.
 *
 * @package BhaivaTechStorefrontAlpha
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the guided personalization page beneath WooCommerce.
 */
function bhaivatech_storefront_register_buyer_onboarding_page(): void {
	add_submenu_page(
		'woocommerce',
		__( 'Personalize Your Store', 'bhaivatech-storefront-alpha' ),
		__( 'Personalize Store', 'bhaivatech-storefront-alpha' ),
		'manage_woocommerce',
		'bhaivatech-storefront-personalize',
		'bhaivatech_storefront_render_buyer_onboarding_page'
	);
}

/**
 * Render one guided onboarding action.
 *
 * @param string $title Action title.
 * @param string $description Plain-language explanation.
 * @param string $where Stable WordPress/WooCommerce surface name.
 * @param string $url Admin/front-end URL.
 * @param string $label Link label.
 */
function bhaivatech_storefront_render_onboarding_action( string $title, string $description, string $where, string $url, string $label ): void {
	?>
	<li style="margin:0 0 1.25rem">
		<strong><?php echo esc_html( $title ); ?></strong>
		<p style="margin:.25rem 0"><?php echo esc_html( $description ); ?></p>
		<p style="margin:.25rem 0"><span class="description"><?php echo esc_html( $where ); ?></span></p>
		<a class="button button-secondary" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a>
	</li>
	<?php
}

/**
 * Render the buyer-facing, non-destructive onboarding guide.
 */
function bhaivatech_storefront_render_buyer_onboarding_page(): void {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die( esc_html__( 'You do not have permission to personalize this store.', 'bhaivatech-storefront-alpha' ) );
	}

	$site_editor_url = admin_url( 'site-editor.php' );
	$products_url    = admin_url( 'edit.php?post_type=product' );
	$woo_settings    = admin_url( 'admin.php?page=wc-settings' );
	$setup_url       = admin_url( 'admin.php?page=bhaivatech-storefront-setup' );
	$front_url       = home_url( '/' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Personalize your store', 'bhaivatech-storefront-alpha' ); ?></h1>
		<p><?php esc_html_e( 'Use this guided checklist to turn the starter storefront into your own brand. These links open normal WordPress and WooCommerce tools; this screen does not silently rewrite your design, products or commerce settings.', 'bhaivatech-storefront-alpha' ); ?></p>
		<p><strong><?php esc_html_e( 'No settings or customer content are changed from these onboarding links.', 'bhaivatech-storefront-alpha' ); ?></strong></p>

		<h2><?php esc_html_e( 'Brand and presentation', 'bhaivatech-storefront-alpha' ); ?></h2>
		<ol>
			<?php
			bhaivatech_storefront_render_onboarding_action(
				__( 'Add or change logo and store name', 'bhaivatech-storefront-alpha' ),
				__( 'Use the native Site Logo and Site Title blocks. Your changes remain normal WordPress site content.', 'bhaivatech-storefront-alpha' ),
				__( 'Appearance → Editor', 'bhaivatech-storefront-alpha' ),
				$site_editor_url,
				__( 'Open Site Editor', 'bhaivatech-storefront-alpha' )
			);
			bhaivatech_storefront_render_onboarding_action(
				__( 'Choose a visual style', 'bhaivatech-storefront-alpha' ),
				__( 'Start with the default look, Fresh Grove or Minimal Market, then adjust only what your brand needs.', 'bhaivatech-storefront-alpha' ),
				__( 'Appearance → Editor → Styles', 'bhaivatech-storefront-alpha' ),
				$site_editor_url,
				__( 'Choose style', 'bhaivatech-storefront-alpha' )
			);
			bhaivatech_storefront_render_onboarding_action(
				__( 'Adjust colors and typography', 'bhaivatech-storefront-alpha' ),
				__( 'Use Global Styles so storefront components continue to share the same semantic brand tokens.', 'bhaivatech-storefront-alpha' ),
				__( 'Appearance → Editor → Styles', 'bhaivatech-storefront-alpha' ),
				$site_editor_url,
				__( 'Edit styles', 'bhaivatech-storefront-alpha' )
			);
			bhaivatech_storefront_render_onboarding_action(
				__( 'Edit header, navigation and footer', 'bhaivatech-storefront-alpha' ),
				__( 'Edit the registered Store Header and Store Footer template parts. Keep navigation short and store-owner specific.', 'bhaivatech-storefront-alpha' ),
				__( 'Appearance → Editor', 'bhaivatech-storefront-alpha' ),
				$site_editor_url,
				__( 'Edit store shell', 'bhaivatech-storefront-alpha' )
			);
			?>
		</ol>

		<h2><?php esc_html_e( 'Products and demo content', 'bhaivatech-storefront-alpha' ); ?></h2>
		<ol start="5">
			<?php
			bhaivatech_storefront_render_onboarding_action(
				__( 'Replace demo products and images', 'bhaivatech-storefront-alpha' ),
				__( 'The generated grocery images are starter defaults, not locked theme assets. Replace names, prices, stock and images with your real catalog.', 'bhaivatech-storefront-alpha' ),
				__( 'WooCommerce → Products', 'bhaivatech-storefront-alpha' ),
				$products_url,
				__( 'Review products', 'bhaivatech-storefront-alpha' )
			);
			?>
		</ol>

		<h2><?php esc_html_e( 'Review launch readiness', 'bhaivatech-storefront-alpha' ); ?></h2>
		<p><?php esc_html_e( 'Presentation being complete does not mean commerce is production-ready. Before launch, review products, delivery/shipping, cart/checkout, payments, taxes where applicable, store policies/legal copy and the responsive storefront.', 'bhaivatech-storefront-alpha' ); ?></p>
		<p>
			<a class="button button-primary" href="<?php echo esc_url( $front_url ); ?>"><?php esc_html_e( 'Open storefront', 'bhaivatech-storefront-alpha' ); ?></a>
			<a class="button" href="<?php echo esc_url( $woo_settings ); ?>"><?php esc_html_e( 'Review WooCommerce settings', 'bhaivatech-storefront-alpha' ); ?></a>
			<a class="button" href="<?php echo esc_url( $setup_url ); ?>"><?php esc_html_e( 'Review technical status', 'bhaivatech-storefront-alpha' ); ?></a>
		</p>

		<p class="description"><?php esc_html_e( 'This guide does not certify legal, tax, payment, shipping or business compliance.', 'bhaivatech-storefront-alpha' ); ?></p>
	</div>
	<?php
}
