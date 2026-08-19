<?php
/**
 * Provider-independent starter-store resource preflight.
 *
 * @package BhaivaTechStorefrontAlpha
 */

defined( 'ABSPATH' ) || exit;

/**
 * Return the minimal resources the current Modern Grocery alpha expects.
 *
 * These are verification targets only. This manifest does not authorize
 * creating or overwriting customer content.
 *
 * @return array<int, array{key:string,type:string,lookup:string}>
 */
function bhaivatech_storefront_modern_grocery_required_resources(): array {
	return array(
		array(
			'key'    => 'modern-grocery/woocommerce/page/shop',
			'type'   => 'woocommerce_page',
			'lookup' => 'shop',
		),
		array(
			'key'    => 'modern-grocery/woocommerce/page/cart',
			'type'   => 'woocommerce_page',
			'lookup' => 'cart',
		),
		array(
			'key'    => 'modern-grocery/woocommerce/page/checkout',
			'type'   => 'woocommerce_page',
			'lookup' => 'checkout',
		),
		array(
			'key'    => 'modern-grocery/woocommerce/page/myaccount',
			'type'   => 'woocommerce_page',
			'lookup' => 'myaccount',
		),
		array(
			'key'    => 'modern-grocery/theme/template/front-page',
			'type'   => 'theme_file',
			'lookup' => 'templates/front-page.html',
		),
		array(
			'key'    => 'modern-grocery/theme/part/footer',
			'type'   => 'theme_file',
			'lookup' => 'parts/footer.html',
		),
		array(
			'key'    => 'modern-grocery/core/block/product-workspace',
			'type'   => 'block',
			'lookup' => 'bhaivatech-storefront/product-workspace',
		),
		array(
			'key'    => 'modern-grocery/core/block/mobile-shopping-nav',
			'type'   => 'block',
			'lookup' => 'bhaivatech-storefront/mobile-shopping-nav',
		),
	);
}

/**
 * Validate resource-manifest structure and stable identities.
 *
 * @param mixed $resources Candidate resources.
 * @return true|WP_Error
 */
function bhaivatech_storefront_validate_required_resources( $resources ) {
	if ( ! is_array( $resources ) || empty( $resources ) ) {
		return new WP_Error( 'starter_resources_invalid', 'Starter resource manifest must be a non-empty array.' );
	}

	$allowed_types = array( 'woocommerce_page', 'theme_file', 'block' );
	$keys          = array();

	foreach ( $resources as $resource ) {
		if ( ! is_array( $resource ) || ! isset( $resource['key'], $resource['type'], $resource['lookup'] ) ) {
			return new WP_Error( 'starter_resource_missing_field', 'Starter resource manifest contains incomplete metadata.' );
		}

		$key = (string) $resource['key'];
		if ( ! preg_match( '#^modern-grocery/[a-z0-9/_-]+$#', $key ) ) {
			return new WP_Error( 'starter_resource_key_invalid', 'Starter resource key is invalid.' );
		}

		if ( isset( $keys[ $key ] ) ) {
			return new WP_Error( 'starter_resource_key_duplicate', 'Starter resource keys must be unique.' );
		}
		$keys[ $key ] = true;

		if ( ! in_array( (string) $resource['type'], $allowed_types, true ) ) {
			return new WP_Error( 'starter_resource_type_invalid', 'Starter resource type is invalid.' );
		}

		if ( '' === trim( (string) $resource['lookup'] ) ) {
			return new WP_Error( 'starter_resource_lookup_invalid', 'Starter resource lookup is invalid.' );
		}
	}

	return true;
}

/**
 * Check a single verification-only resource.
 *
 * @param array{key:string,type:string,lookup:string} $resource Resource definition.
 * @return array{key:string,type:string,ready:bool,code:string}
 */
function bhaivatech_storefront_check_required_resource( array $resource ): array {
	$ready = false;
	$code  = 'unknown_resource';

	if ( 'woocommerce_page' === $resource['type'] ) {
		$page_id = function_exists( 'wc_get_page_id' ) ? (int) wc_get_page_id( $resource['lookup'] ) : 0;
		$ready   = $page_id > 0 && 'publish' === get_post_status( $page_id );
		$code    = $ready ? 'ready' : 'woocommerce_page_missing';
	} elseif ( 'theme_file' === $resource['type'] ) {
		$relative = ltrim( str_replace( '\\', '/', $resource['lookup'] ), '/' );
		$safe     = ! str_contains( $relative, '../' ) && ! str_starts_with( $relative, '/' );
		$ready    = $safe && is_file( trailingslashit( get_stylesheet_directory() ) . $relative );
		$code     = $ready ? 'ready' : 'theme_resource_missing';
	} elseif ( 'block' === $resource['type'] ) {
		$registry = WP_Block_Type_Registry::get_instance();
		$ready    = $registry->is_registered( $resource['lookup'] );
		$code     = $ready ? 'ready' : 'block_not_registered';
	}

	return array(
		'key'   => $resource['key'],
		'type'  => $resource['type'],
		'ready' => $ready,
		'code'  => $code,
	);
}

/**
 * Run the verification-only resource preflight.
 *
 * @return array{ready:int,total:int,all_ready:bool,checks:array<int,array{key:string,type:string,ready:bool,code:string}>}|WP_Error
 */
function bhaivatech_storefront_run_starter_resource_preflight() {
	$resources = bhaivatech_storefront_modern_grocery_required_resources();
	$valid     = bhaivatech_storefront_validate_required_resources( $resources );
	if ( is_wp_error( $valid ) ) {
		return $valid;
	}

	$checks = array_map( 'bhaivatech_storefront_check_required_resource', $resources );
	$ready  = count(
		array_filter(
			$checks,
			static fn( array $check ): bool => true === $check['ready']
		)
	);
	$total = count( $checks );

	return array(
		'ready'     => $ready,
		'total'     => $total,
		'all_ready' => $ready === $total,
		'checks'    => $checks,
	);
}
