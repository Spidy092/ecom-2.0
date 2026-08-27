<?php
/**
 * Deterministic, namespaced Modern Grocery demo content.
 *
 * This service is intentionally opt-in through WP-CLI. It never runs during
 * normal plugin bootstrap and only manages records carrying the Grovia demo
 * marker.
 *
 * @package StorefrontCore
 */

namespace StorefrontCore\Demo;

use RuntimeException;
use WC_Product_Simple;
use WC_Product_Variable;
use WC_Product_Variation;

defined( 'ABSPATH' ) || exit;

final class DemoSeeder {

	public const MARKER_KEY   = '_grovia_demo_fixture';
	public const MARKER_VALUE = '1';

	/**
	 * Seed the local Modern Grocery demo.
	 *
	 * @param bool $reset Remove only previously marked demo content first.
	 * @return array{products:int,categories:int,pages:int}
	 */
	public function seed( bool $reset = false ): array {
		$this->assert_dependencies();

		if ( $reset ) {
			$this->reset_marked_content();
		}

		$this->configure_store();
		$categories = $this->seed_categories();
		$products   = $this->seed_products( $categories );
		$pages      = $this->seed_pages();
		$this->remove_default_content();

		return array(
			'products'   => count( $products ),
			'categories' => count( $categories ),
			'pages'      => count( $pages ),
		);
	}

	/**
	 * Ensure WooCommerce is available before any write occurs.
	 */
	private function assert_dependencies(): void {
		if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'wc_get_products' ) ) {
			throw new RuntimeException( 'WooCommerce must be active before seeding the Grovia demo.' );
		}

		if ( function_exists( 'wp_get_environment_type' ) && ! in_array( wp_get_environment_type(), array( 'local', 'development', 'staging' ), true ) ) {
			throw new RuntimeException( 'The Grovia demo may only be seeded in a local, development, or staging environment.' );
		}
	}

	/**
	 * Set only the options required by the disposable demo.
	 */
	private function configure_store(): void {
		update_option( 'blogname', 'Grovia Modern Market' );
		update_option( 'blogdescription', 'A faster way to build the everyday basket.' );
		update_option( 'woocommerce_store_address', '12 Market Lane' );
		update_option( 'woocommerce_store_city', 'Mumbai' );
		update_option( 'woocommerce_default_country', 'IN:MH' );
		update_option( 'woocommerce_currency', 'INR' );
		// The disposable Blueprint is a working storefront demo, not a
		// private coming-soon preview. Keep the catalog visible to logged-out
		// shoppers as well as the auto-logged-in Playground administrator.
		update_option( 'woocommerce_coming_soon', 'no' );
		update_option( 'woocommerce_store_pages_only', 'no' );
		update_option( 'bhaivatech_storefront_delivery_postcodes', "400001\n400050\n560001\n560034\n560038" );
		update_option( 'permalink_structure', '/%postname%/' );
	}

	/**
	 * @return array<string,int>
	 */
	private function seed_categories(): array {
		$definitions = array(
			'produce'   => 'Fresh Produce',
			'dairy'     => 'Dairy & Eggs',
			'bakery'    => 'Bakery',
			'pantry'    => 'Pantry Staples',
			'breakfast' => 'Breakfast',
		);
		$categories  = array();

		foreach ( $definitions as $slug => $name ) {
			$term          = term_exists( 'grovia-' . $slug, 'product_cat' );
			$existing_term = (bool) $term;
			if ( ! $term ) {
				$term = wp_insert_term( $name, 'product_cat', array( 'slug' => 'grovia-' . $slug ) );
			}
			if ( is_wp_error( $term ) ) {
				throw new RuntimeException( 'Could not create demo department: ' . $term->get_error_message() );
			}

			if ( is_array( $term ) ) {
				$term_id = (int) $term['term_id'];
				// @phpstan-ignore-next-line
			} elseif ( is_numeric( $term ) ) {
				$term_id = (int) $term;
				// @phpstan-ignore-next-line
			} else {
				throw new RuntimeException( 'Could not resolve the demo department ID.' );
			}
			if ( $existing_term && self::MARKER_VALUE !== (string) get_term_meta( $term_id, self::MARKER_KEY, true ) ) {
				throw new RuntimeException( 'The Grovia department slug is already reserved by unrelated content.' );
			}
			wp_update_term( $term_id, 'product_cat', array( 'name' => $name ) );
			update_term_meta( $term_id, self::MARKER_KEY, self::MARKER_VALUE );
			$categories[ $slug ] = $term_id;
		}

		return $categories;
	}

	/**
	 * @param array<string,int> $categories
	 * @return array<int,int>
	 */
	private function seed_products( array $categories ): array {
		$definitions = array(
			array(
				'sku'      => 'grovia-milk',
				'name'     => 'Farm Fresh Milk',
				'price'    => '68.00',
				'unit'     => '1 litre',
				'category' => 'dairy',
				'image'    => 'milk.webp',
				'stock'    => 40,
			),
			array(
				'sku'      => 'grovia-bread',
				'name'     => 'Sourdough Bread',
				'price'    => '95.00',
				'unit'     => '400 g',
				'category' => 'bakery',
				'image'    => 'bread.webp',
				'stock'    => 24,
			),
			array(
				'sku'      => 'grovia-tomatoes',
				'name'     => 'Vine Tomatoes',
				'price'    => '42.00',
				'unit'     => '500 g',
				'category' => 'produce',
				'image'    => 'tomatoes.webp',
				'stock'    => 0,
			),
			array(
				'sku'      => 'grovia-apples',
				'name'     => 'Crisp Red Apples',
				'price'    => '120.00',
				'unit'     => '4 pieces',
				'category' => 'produce',
				'image'    => 'apples.webp',
				'stock'    => 30,
			),
			array(
				'sku'      => 'grovia-bananas',
				'name'     => 'Robusta Bananas',
				'price'    => '55.00',
				'unit'     => '6 pieces',
				'category' => 'produce',
				'image'    => 'bananas.webp',
				'stock'    => 28,
			),
			array(
				'sku'      => 'grovia-spinach',
				'name'     => 'Baby Spinach',
				'price'    => '35.00',
				'unit'     => '200 g',
				'category' => 'produce',
				'image'    => 'spinach.webp',
				'stock'    => 18,
			),
			array(
				'sku'      => 'grovia-eggs',
				'name'     => 'Free Range Eggs',
				'price'    => '120.00',
				'unit'     => '6 pieces',
				'category' => 'dairy',
				'image'    => 'eggs.webp',
				'stock'    => 20,
			),
			array(
				'sku'      => 'grovia-yogurt',
				'name'     => 'Plain Greek Yogurt',
				'price'    => '110.00',
				'unit'     => '400 g',
				'category' => 'dairy',
				'image'    => 'yogurt.webp',
				'stock'    => 16,
			),
			array(
				'sku'      => 'grovia-rice',
				'name'     => 'Everyday Basmati Rice',
				'price'    => '249.00',
				'unit'     => '5 kg',
				'category' => 'pantry',
				'image'    => 'rice.webp',
				'stock'    => 12,
				'variable' => true,
			),
			array(
				'sku'      => 'grovia-lentils',
				'name'     => 'Red Lentils',
				'price'    => '145.00',
				'unit'     => '1 kg',
				'category' => 'pantry',
				'image'    => 'lentils.webp',
				'stock'    => 22,
			),
			array(
				'sku'      => 'grovia-oil',
				'name'     => 'Cold Pressed Groundnut Oil',
				'price'    => '210.00',
				'unit'     => '1 litre',
				'category' => 'pantry',
				'image'    => 'oil.webp',
				'stock'    => 14,
			),
			array(
				'sku'      => 'grovia-cereal',
				'name'     => 'Toasted Oat Cereal',
				'price'    => '185.00',
				'unit'     => '500 g',
				'category' => 'breakfast',
				'image'    => 'cereal.webp',
				'stock'    => 18,
			),
		);
		$product_ids = array();

		foreach ( $definitions as $definition ) {
			$product = $this->find_or_create_product( $definition );
			$product->set_name( $definition['name'] );
			$product->set_status( 'publish' );
			$product->set_catalog_visibility( 'visible' );
			$product->set_description( $definition['name'] . '. A dependable everyday grocery staple with clear unit pricing.' );
			$product->set_short_description( $definition['unit'] );
			$product->update_meta_data( '_grovia_demo_fixture', self::MARKER_VALUE );
			$product->update_meta_data( '_grovia_unit', $definition['unit'] );

			if ( empty( $definition['variable'] ) ) {
				$product->set_regular_price( $definition['price'] );
				$product->set_manage_stock( true );
				$product->set_stock_quantity( $definition['stock'] );
				$product->set_stock_status( $definition['stock'] > 0 ? 'instock' : 'outofstock' );
			}

			$product->save();
			wp_set_object_terms( $product->get_id(), array( $categories[ $definition['category'] ] ), 'product_cat' );
			$this->attach_demo_image( $product->get_id(), $definition['image'] );

			if ( ( $definition['variable'] ?? false ) && $product instanceof WC_Product_Variable ) {
				$this->seed_rice_variations( $product );
			}

			$product_ids[] = $product->get_id();
		}

		return $product_ids;
	}

	/**
	 * @param array<string,mixed> $definition
	 * @return WC_Product_Simple|WC_Product_Variable
	 */
	private function find_or_create_product( array $definition ) {
		$products = wc_get_products(
			array(
				'sku'   => $definition['sku'],
				'limit' => 1,
			)
		);
		if ( ! empty( $products ) && ( $products[0] instanceof WC_Product_Variable || $products[0] instanceof WC_Product_Simple ) ) {
			$existing = $products[0];
			if ( self::MARKER_VALUE !== (string) $existing->get_meta( self::MARKER_KEY, true ) ) {
				throw new RuntimeException( 'A non-Grovia product already uses the reserved demo SKU ' . $definition['sku'] . '.' );
			}
			return $existing;
		}

		$product = ( $definition['variable'] ?? false ) ? new WC_Product_Variable() : new WC_Product_Simple();
		$product->set_sku( $definition['sku'] );
		return $product;
	}

	private function seed_rice_variations( WC_Product_Variable $product ): void {
		$attribute = new \WC_Product_Attribute();
		$attribute->set_name( 'Pack' );
		$attribute->set_options( array( '1 kg', '5 kg' ) );
		$attribute->set_visible( true );
		$attribute->set_variation( true );
		$product->set_attributes( array( $attribute ) );
		$product->save();

		foreach ( array(
			'1 kg' => '59.00',
			'5 kg' => '249.00',
		) as $pack => $price ) {
			$variation = null;
			foreach ( $product->get_children() as $child_id ) {
				$candidate = wc_get_product( $child_id );
				if ( $candidate instanceof WC_Product_Variation && $candidate->get_attribute( 'pack' ) === $pack ) {
					$variation = $candidate;
					break;
				}
			}
			if ( ! $variation instanceof WC_Product_Variation ) {
				$variation = new WC_Product_Variation();
				$variation->set_parent_id( $product->get_id() );
			}
			$variation->set_attributes( array( 'pack' => $pack ) );
			$variation->set_regular_price( $price );
			$variation->set_manage_stock( true );
			$variation->set_stock_quantity( 12 );
			$variation->set_stock_status( 'instock' );
			$variation->update_meta_data( self::MARKER_KEY, self::MARKER_VALUE );
			$variation->save();
		}

		WC_Product_Variable::sync( $product->get_id() );
		wc_delete_product_transients( $product->get_id() );
	}

	private function attach_demo_image( int $product_id, string $filename ): void {
		$path = get_theme_file_path( 'assets/demo/' . $filename );
		if ( ! file_exists( $path ) ) {
			return;
		}

		$existing      = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'posts_per_page' => 1,
				'meta_key'       => '_grovia_demo_asset',
				'meta_value'     => $filename,
			)
		);
		$attachment_id = ! empty( $existing ) ? (int) $existing[0]->ID : 0;
		// A previous Playground build could persist the mounted theme path as
		// the attachment file. Remove only that marked fixture so the next run
		// can place the image in WordPress's real uploads directory.
		if ( $attachment_id && ! file_exists( (string) get_attached_file( $attachment_id ) ) ) {
			wp_delete_attachment( $attachment_id, true );
			$attachment_id = 0;
		}

		if ( ! $attachment_id ) {
			$bits = file_get_contents( $path );
			if ( false === $bits ) {
				return;
			}
			$upload = wp_upload_bits( $filename, null, $bits );
			if ( ! empty( $upload['error'] ) || empty( $upload['file'] ) ) {
				return;
			}
			$mime              = wp_check_filetype( $filename, null );
			$attachment_result = wp_insert_attachment(
				array(
					'post_title'     => sanitize_text_field( pathinfo( $filename, PATHINFO_FILENAME ) ),
					'post_status'    => 'inherit',
					'post_mime_type' => ! empty( $mime['type'] ) ? $mime['type'] : 'image/png',
				),
				$upload['file']
			);
			// @phpstan-ignore-next-line
			if ( is_wp_error( $attachment_result ) ) {
				return;
			}
			$attachment_id = (int) $attachment_result;
			if ( $attachment_id <= 0 ) {
				return;
			}
			update_post_meta( $attachment_id, self::MARKER_KEY, self::MARKER_VALUE );
			update_post_meta( $attachment_id, '_grovia_demo_asset', $filename );
			require_once ABSPATH . 'wp-admin/includes/image.php';
			$metadata = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );
			if ( ! empty( $metadata ) ) {
				wp_update_attachment_metadata( $attachment_id, $metadata );
			}
		}

		update_post_meta( $product_id, '_thumbnail_id', $attachment_id );
	}

	/**
	 * @return array<int,int>
	 */
	private function seed_pages(): array {
		$pages    = array(
			'home'     => array(
				'title'   => 'Home',
				'content' => '',
			),
			'shop'     => array(
				'title'   => 'Shop',
				'content' => '',
			),
			'cart'     => array(
				'title'   => 'Cart',
				'content' => '<!-- wp:woocommerce/cart /-->',
			),
			'checkout' => array(
				'title'   => 'Checkout',
				'content' => '<!-- wp:woocommerce/checkout /-->',
			),
			'account'  => array(
				'title'   => 'My Account',
				'content' => '[woocommerce_my_account]',
			),
		);
		$page_ids = array();

		foreach ( $pages as $key => $definition ) {
			$page                        = get_page_by_path( sanitize_title( $definition['title'] ), OBJECT, 'page' );
			$woocommerce_page_keys       = array(
				'shop'     => 'shop',
				'cart'     => 'cart',
				'checkout' => 'checkout',
				'account'  => 'myaccount',
			);
			$can_manage_woocommerce_page = $page
				&& isset( $woocommerce_page_keys[ $key ] )
				&& function_exists( 'wc_get_page_id' )
				&& absint( wc_get_page_id( $woocommerce_page_keys[ $key ] ) ) === (int) $page->ID;
			$post                        = array(
				'post_title'   => $definition['title'],
				'post_name'    => sanitize_title( $definition['title'] ),
				'post_content' => $definition['content'],
				'post_status'  => 'publish',
				'post_type'    => 'page',
			);
			if ( $page && ( $can_manage_woocommerce_page || self::MARKER_VALUE === (string) get_post_meta( $page->ID, self::MARKER_KEY, true ) ) ) {
				$post['ID'] = $page->ID;
			}
			$page_id = wp_insert_post( $post, true );
			if ( is_wp_error( $page_id ) ) {
				throw new RuntimeException( 'Could not create demo page: ' . $page_id->get_error_message() );
			}
			update_post_meta( $page_id, self::MARKER_KEY, self::MARKER_VALUE );
			$page_ids[ $key ] = (int) $page_id;
		}

		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $page_ids['home'] );
		update_option( 'woocommerce_shop_page_id', $page_ids['shop'] );
		update_option( 'woocommerce_cart_page_id', $page_ids['cart'] );
		update_option( 'woocommerce_checkout_page_id', $page_ids['checkout'] );
		update_option( 'woocommerce_myaccount_page_id', $page_ids['account'] );
		$this->seed_menu( $page_ids );
		flush_rewrite_rules();

		return array_values( $page_ids );
	}

	/**
	 * Create the small primary menu used by the disposable storefront shell.
	 *
	 * @param array<string,int> $page_ids Seeded page IDs keyed by purpose.
	 */
	private function seed_menu( array $page_ids ): void {
		$menu = wp_get_nav_menu_object( 'Grovia Primary' );
		if ( $menu && self::MARKER_VALUE !== (string) get_term_meta( $menu->term_id, self::MARKER_KEY, true ) ) {
			throw new RuntimeException( 'A non-Grovia navigation menu already uses the reserved name Grovia Primary.' );
		}
		if ( ! $menu ) {
			$menu_id = wp_create_nav_menu( 'Grovia Primary' );
			if ( is_wp_error( $menu_id ) ) {
				throw new RuntimeException( 'Could not create the Grovia primary menu: ' . $menu_id->get_error_message() );
			}
			$menu_id = (int) $menu_id;
		} else {
			$menu_id = (int) $menu->term_id;
		}
		update_term_meta( $menu_id, self::MARKER_KEY, self::MARKER_VALUE );

		$items             = array(
			'Home'       => get_permalink( $page_ids['home'] ),
			'Shop'       => get_permalink( $page_ids['shop'] ),
			'Cart'       => get_permalink( $page_ids['cart'] ),
			'Checkout'   => get_permalink( $page_ids['checkout'] ),
			'My Account' => get_permalink( $page_ids['account'] ),
		);
		$existing_items    = wp_get_nav_menu_items( $menu_id );
		$existing_by_title = array();
		foreach ( is_array( $existing_items ) ? $existing_items : array() as $item ) {
			$existing_by_title[ (string) $item->title ] = (int) $item->ID;
		}

		foreach ( $items as $title => $url ) {
			$item_id = wp_update_nav_menu_item(
				$menu_id,
				$existing_by_title[ $title ] ?? 0,
				array(
					'menu-item-title'  => $title,
					'menu-item-url'    => esc_url_raw( (string) $url ),
					'menu-item-status' => 'publish',
				)
			);
			if ( ! is_wp_error( $item_id ) && $item_id ) {
				update_post_meta( (int) $item_id, self::MARKER_KEY, self::MARKER_VALUE );
			}
		}

		$locations            = get_theme_mod( 'nav_menu_locations', array() );
		$locations            = is_array( $locations ) ? $locations : array();
		$locations['primary'] = $menu_id;
		set_theme_mod( 'nav_menu_locations', $locations );
	}

	private function remove_default_content(): void {
		$hello = get_page_by_path( 'hello-world', OBJECT, 'post' );
		if ( $hello && 'Hello world!' === $hello->post_title ) {
			wp_delete_post( $hello->ID, true );
		}
		$sample = get_page_by_path( 'sample-page', OBJECT, 'page' );
		if ( $sample && 'Sample Page' === $sample->post_title && ! get_post_meta( $sample->ID, self::MARKER_KEY, true ) ) {
			wp_delete_post( $sample->ID, true );
		}
	}

	private function reset_marked_content(): void {
		$marked = get_posts(
			array(
				'post_type'      => array( 'product', 'product_variation', 'page', 'attachment' ),
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_key'       => self::MARKER_KEY,
				'meta_value'     => self::MARKER_VALUE,
			)
		);
		foreach ( $marked as $post_id ) {
			$post_type = get_post_type( (int) $post_id );
			if ( in_array( $post_type, array( 'product', 'product_variation' ), true ) && function_exists( 'wc_get_product' ) ) {
				$product = wc_get_product( (int) $post_id );
				if ( $product && is_callable( array( $product, 'delete' ) ) ) {
					$product->delete( true );
					continue;
				}
			}
			wp_delete_post( (int) $post_id, true );
		}

		$terms = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
				'fields'     => 'ids',
				'meta_query' => array(
					array(
						'key'   => self::MARKER_KEY,
						'value' => self::MARKER_VALUE,
					),
				),
			)
		);
		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term_id ) {
				wp_delete_term( (int) $term_id, 'product_cat' );
			}
		}

		$menus = get_terms(
			array(
				'taxonomy'   => 'nav_menu',
				'hide_empty' => false,
				'fields'     => 'ids',
				'meta_query' => array(
					array(
						'key'   => self::MARKER_KEY,
						'value' => self::MARKER_VALUE,
					),
				),
			)
		);
		if ( ! is_wp_error( $menus ) ) {
			foreach ( $menus as $menu_id ) {
				wp_delete_nav_menu( (int) $menu_id );
			}
		}
	}
}
