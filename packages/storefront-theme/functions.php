<?php
/**
 * Storefront Theme — functions.php
 *
 * Registers only presentation-level theme support and assets.
 * Business/product functionality belongs in storefront-core.
 *
 * @package StorefrontTheme
 */

defined( 'ABSPATH' ) || exit;

// ---------------------------------------------------------------------------
// 1. Theme setup
// ---------------------------------------------------------------------------

/**
 * Register theme support declarations.
 */
function storefront_theme_setup(): void {
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'custom-logo', [
		'height'               => 60,
		'width'                => 200,
		'flex-height'          => true,
		'flex-width'           => true,
		'unlink-homepage-logo' => false,
	] );

	// WooCommerce declarations.
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	// Cart/Checkout Blocks compatibility — declared after validating block behavior.
	add_theme_support( 'woocommerce-blocks', [ 'cart-checkout-blocks' ] );

	load_theme_textdomain( 'bhaivatech-grocery-alpha', get_template_directory() . '/languages' );
}
add_action( 'after_setup_theme', 'storefront_theme_setup' );

// ---------------------------------------------------------------------------
// 2. Image sizes
// ---------------------------------------------------------------------------

/**
 * Register grocery-optimized image sizes.
 *
 * - ledger-thumb: compact row image (Product Ledger).
 * - card-grid:    2-column grid card.
 * - card-wide:    full-width product card (single-product header).
 */
function storefront_theme_image_sizes(): void {
	add_image_size( 'storefront-ledger-thumb', 80,  80,  true );
	add_image_size( 'storefront-card-grid',    400, 400, true );
	add_image_size( 'storefront-card-wide',    800, 600, false );
}
add_action( 'after_setup_theme', 'storefront_theme_image_sizes' );

// ---------------------------------------------------------------------------
// 3. Pattern categories
// ---------------------------------------------------------------------------

/**
 * Register Storefront block pattern categories.
 */
function storefront_theme_pattern_categories(): void {
	register_block_pattern_category(
		'storefront-grocery',
		[ 'label' => __( 'Storefront — Grocery', 'bhaivatech-grocery-alpha' ) ]
	);
}
add_action( 'init', 'storefront_theme_pattern_categories' );

/**
 * Register the small product-unit block used by ledger rows.
 *
 * The block reads only current WooCommerce product presentation data. It is
 * intentionally theme-owned so catalog behavior remains in Storefront Core.
 */
function storefront_theme_product_unit_block(): void {
	register_block_type(
		'storefront/product-unit',
		array(
			'render_callback' => 'storefront_theme_render_product_unit',
		)
	);
}
add_action( 'init', 'storefront_theme_product_unit_block' );

/**
 * Render a product's bounded grocery unit label inside a query-loop card.
 *
 * @param array<string,mixed> $attributes Block attributes.
 * @return string
 */
function storefront_theme_render_product_unit( array $attributes = array() ): string {
	global $product;
	if ( ! is_object( $product ) || ! is_callable( array( $product, 'get_meta' ) ) ) {
		return '';
	}

	$raw_unit = $product->get_meta( '_grovia_unit', true );
	$unit     = is_scalar( $raw_unit ) ? (string) $raw_unit : '';
	if ( '' === $unit && is_callable( array( $product, 'get_short_description' ) ) ) {
		$unit = wp_strip_all_tags( (string) $product->get_short_description() );
	}
	if ( '' === $unit ) {
		return '';
	}

	$class_name = 'storefront-ledger-row__unit';
	if ( ! empty( $attributes['className'] ) && is_string( $attributes['className'] ) ) {
		$class_name .= ' ' . $attributes['className'];
	}

	return '<span class="' . esc_attr( $class_name ) . '">' . esc_html( $unit ) . '</span>';
}

// ---------------------------------------------------------------------------
// 4. Assets
// ---------------------------------------------------------------------------

/**
 * Enqueue front-end theme assets.
 *
 * JavaScript is loaded as a native ES module (wp_enqueue_script_module).
 *
 * IMPORTANT: wp_add_inline_script() is NOT compatible with script modules —
 * it only works with wp_enqueue_script() handles. Instead, we output a
 * standard <script> tag on wp_head that populates window.storefrontConfig
 * before the module executes.
 *
 * @see https://developer.wordpress.org/reference/functions/wp_enqueue_script_module/
 */
function storefront_theme_assets(): void {
	$ver = wp_get_theme()->get( 'Version' ) ?: '0.0.1';
	$js  = get_template_directory_uri() . '/assets/js/storefront-interactions.js';

	// Interactions module — not loaded on admin, cart, checkout (Woo owns those).
	if ( ! is_cart() && ! is_checkout() && ! is_admin() ) {
		wp_enqueue_script_module(
			'storefront-interactions',
			$js,
			[],
			$ver
		);
	}
}
add_action( 'wp_enqueue_scripts', 'storefront_theme_assets' );

/**
 * Attach the theme's scoped presentation styles to the Core-owned blocks.
 *
 * The block markup and interaction behavior belong to Storefront Core; the
 * visual system belongs here. Using wp_enqueue_block_style keeps these files
 * conditional so account, cart, and checkout pages do not pay for browse UI.
 *
 * @return void
 */
function storefront_theme_register_block_styles(): void {
	$version = wp_get_theme()->get( 'Version' ) ?: '0.0.1';
	$styles  = array(
		'bhaivatech-storefront/product-workspace' => array(
			array(
				'handle' => 'bhaivatech-grocery-alpha-product-workspace',
				'file'   => 'assets/css/product-workspace.css',
			),
			array(
				'handle' => 'bhaivatech-grocery-alpha-department-browse',
				'file'   => 'assets/css/department-browse.css',
			),
			array(
				'handle' => 'bhaivatech-grocery-alpha-product-filters',
				'file'   => 'assets/css/product-filters.css',
			),
		),
		'bhaivatech-storefront/mobile-shopping-nav' => array(
			array(
				'handle' => 'bhaivatech-grocery-alpha-mobile-shopping-nav',
				'file'   => 'assets/css/mobile-shopping-nav.css',
			),
		),
		'storefront-core/product-quick-add' => array(
			array(
				'handle' => 'bhaivatech-grocery-alpha-product-quick-add',
				'file'   => 'assets/css/product-quick-add.css',
			),
		),
	);

	foreach ( $styles as $block_name => $block_styles ) {
		foreach ( $block_styles as $style ) {
			$file = get_theme_file_path( $style['file'] );
			if ( ! file_exists( $file ) ) {
				continue;
			}

			wp_enqueue_block_style(
				$block_name,
				array(
					'handle' => $style['handle'],
					'src'    => get_theme_file_uri( $style['file'] ),
					'path'   => $file,
					'ver'    => $version,
				)
			);
		}
	}
}
add_action( 'init', 'storefront_theme_register_block_styles', 20 );

/**
 * Load the small shell styles used by template parts and fallback patterns.
 *
 * @return void
 */
function storefront_theme_enqueue_shell_style(): void {
	$version = wp_get_theme()->get( 'Version' ) ?: '0.0.1';

	// The root stylesheet is the theme's canonical public design layer. Block
	// themes may omit it when installed from a ZIP, so enqueue it explicitly
	// before the conditional block styles and shell helpers.
	wp_enqueue_style(
		'bhaivatech-grocery-alpha-style',
		get_stylesheet_uri(),
		array(),
		$version
	);

	wp_enqueue_style(
		'bhaivatech-grocery-alpha-storefront-shell',
		get_theme_file_uri( 'assets/css/storefront-shell.css' ),
		array(),
		$version
	);
}
add_action( 'wp_enqueue_scripts', 'storefront_theme_enqueue_shell_style', 15 );

/**
 * Load Cart & Checkout block presentation styles on cart/checkout pages.
 *
 * @return void
 */
function storefront_theme_cart_checkout_assets(): void {
	if ( ! function_exists( 'is_cart' ) || ! function_exists( 'is_checkout' ) ) {
		return;
	}

	if ( ! is_cart() && ! is_checkout() ) {
		return;
	}

	wp_enqueue_style(
		'bhaivatech-grocery-alpha-cart-checkout',
		get_theme_file_uri( 'assets/css/cart-checkout.css' ),
		array(),
		wp_get_theme()->get( 'Version' ) ?: '0.0.1'
	);
}
add_action( 'wp_enqueue_scripts', 'storefront_theme_cart_checkout_assets', 20 );

/**
 * Load Buy Again presentation styles only on the dedicated account endpoint.
 *
 * @return void
 */
function storefront_theme_buy_again_assets(): void {
	if (
		! function_exists( 'is_account_page' )
		|| ! is_account_page()
		|| ! function_exists( 'is_wc_endpoint_url' )
		|| ! is_wc_endpoint_url( 'buy-again' )
	) {
		return;
	}

	wp_enqueue_style(
		'bhaivatech-grocery-alpha-buy-again',
		get_theme_file_uri( 'assets/css/buy-again.css' ),
		array(),
		wp_get_theme()->get( 'Version' )
	);
}
add_action( 'wp_enqueue_scripts', 'storefront_theme_buy_again_assets', 20 );

/**
 * Output storefrontConfig before body — readable by the ES module via window.storefrontConfig.
 *
 * wp_add_inline_script() does not work with script modules (type="module" tags),
 * so we use wp_head to output a plain <script> that runs before the module loads.
 * The module reads from window.storefrontConfig.
 */
function storefront_theme_inline_config(): void {
	if ( is_admin() ) {
		return;
	}
	$config = wp_json_encode(
		[
			'restUrl'     => esc_url_raw( rest_url() ),
			'storeApiUrl' => esc_url_raw( rest_url( 'wc/store/v1/' ) ),
			'nonce'       => wp_create_nonce( 'wp_rest' ),
			'currency'    => get_woocommerce_currency_symbol(),
			'isUser'      => is_user_logged_in(),
		],
		JSON_HEX_TAG | JSON_HEX_AMP
	);
	echo '<script>window.storefrontConfig=' . $config . ';</script>' . "\n";
}
add_action( 'wp_head', 'storefront_theme_inline_config', 5 );

// ---------------------------------------------------------------------------
// 5. Editor assets
// ---------------------------------------------------------------------------

/**
 * Add editor stylesheet so block editor inherits base design tokens.
 */
function storefront_theme_editor_assets(): void {
	add_editor_style( 'style.css' );
}
add_action( 'after_setup_theme', 'storefront_theme_editor_assets' );
