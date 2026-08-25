<?php
/**
 * Lightweight system-status page for store owners.
 *
 * Surfaces key health signals without duplicating WooCommerce's system status.
 * Focus: is the storefront operating correctly, and what requires attention.
 *
 * @package BhaivaTechStorefrontCore
 */

namespace BhaivaTech\Storefront;

defined( 'ABSPATH' ) || exit;

/**
 * Registers a WooCommerce submenu page showing storefront health checks.
 */
final class System_Status {

	/**
	 * Register admin hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_menu', array( $this, 'add_page' ), 35 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * Add the status page under WooCommerce.
	 *
	 * @return void
	 */
	public function add_page(): void {
		add_submenu_page(
			'woocommerce',
			esc_html__( 'Storefront status', 'bhaivatech-storefront-alpha' ),
			esc_html__( 'Storefront status', 'bhaivatech-storefront-alpha' ),
			'manage_woocommerce',
			'bhaivatech-storefront-status',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Conditionally enqueue admin CSS on this page.
	 *
	 * @param string $hook_suffix The current admin page hook.
	 * @return void
	 */
	public function enqueue_admin_assets( string $hook_suffix ): void {
		if ( 'woocommerce_page_bhaivatech-storefront-status' !== $hook_suffix ) {
			return;
		}

		wp_enqueue_style(
			'bhaivatech-storefront-admin',
			plugins_url( 'assets/css/admin-style.css', dirname( __DIR__ ) . '/storefront-core.php' ),
			array(),
			defined( 'BHAIVATECH_STOREFRONT_CORE_VERSION' ) ? BHAIVATECH_STOREFRONT_CORE_VERSION : '0.0.1-alpha'
		);
	}

	/**
	 * Render the system status page.
	 *
	 * @return void
	 */
	public function render_page(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have permission to view storefront status.', 'bhaivatech-storefront-alpha' ) );
		}

		$checks = $this->run_checks();
		?>
		<div class="wrap">
			<div class="grovia-admin-wrap">
				<div class="grovia-admin-header">
					<h1><?php echo esc_html__( 'Storefront Status', 'bhaivatech-storefront-alpha' ); ?></h1>
					<p><?php echo esc_html__( 'Health checks for your grocery storefront. Resolve any warnings before accepting orders.', 'bhaivatech-storefront-alpha' ); ?></p>
				</div>

				<table class="widefat striped" role="presentation">
					<thead>
						<tr>
							<th scope="col"><?php echo esc_html__( 'Check', 'bhaivatech-storefront-alpha' ); ?></th>
							<th scope="col"><?php echo esc_html__( 'Status', 'bhaivatech-storefront-alpha' ); ?></th>
							<th scope="col"><?php echo esc_html__( 'Detail', 'bhaivatech-storefront-alpha' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $checks as $check ) : ?>
							<tr>
								<td><strong><?php echo esc_html( $check['label'] ); ?></strong></td>
								<td>
									<?php if ( 'pass' === $check['status'] ) : ?>
										<span class="dashicons dashicons-yes-alt" style="color:#1a6b35;" aria-hidden="true"></span>
										<span class="screen-reader-text"><?php echo esc_html__( 'Pass', 'bhaivatech-storefront-alpha' ); ?></span>
									<?php elseif ( 'warn' === $check['status'] ) : ?>
										<span class="dashicons dashicons-warning" style="color:#dba617;" aria-hidden="true"></span>
										<span class="screen-reader-text"><?php echo esc_html__( 'Warning', 'bhaivatech-storefront-alpha' ); ?></span>
									<?php else : ?>
										<span class="dashicons dashicons-dismiss" style="color:#b53d2f;" aria-hidden="true"></span>
										<span class="screen-reader-text"><?php echo esc_html__( 'Fail', 'bhaivatech-storefront-alpha' ); ?></span>
									<?php endif; ?>
								</td>
								<td><?php echo esc_html( $check['detail'] ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<h2 style="margin-top:2rem;"><?php echo esc_html__( 'Environment', 'bhaivatech-storefront-alpha' ); ?></h2>
				<table class="widefat striped" role="presentation">
					<tbody>
						<?php foreach ( $this->get_environment_info() as $key => $value ) : ?>
							<tr>
								<td><strong><?php echo esc_html( $key ); ?></strong></td>
								<td><?php echo esc_html( $value ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<h2 style="margin-top:2rem;"><?php echo esc_html__( 'Quick links', 'bhaivatech-storefront-alpha' ); ?></h2>
				<ul>
					<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=shipping' ) ); ?>"><?php echo esc_html__( 'WooCommerce Shipping Zones', 'bhaivatech-storefront-alpha' ); ?></a></li>
					<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=bhaivatech-storefront-setup' ) ); ?>"><?php echo esc_html__( 'Storefront Setup Wizard', 'bhaivatech-storefront-alpha' ); ?></a></li>
					<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-status' ) ); ?>"><?php echo esc_html__( 'WooCommerce System Status', 'bhaivatech-storefront-alpha' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html__( 'View storefront', 'bhaivatech-storefront-alpha' ); ?></a></li>
				</ul>
			</div>
		</div>
		<?php
	}

	/**
	 * Run storefront health checks.
	 *
	 * @return array<int, array{label: string, status: string, detail: string}>
	 */
	private function run_checks(): array {
		$checks = array();

		// 1. WooCommerce active and version.
		$checks[] = $this->check_woocommerce();

		// 2. Theme compatibility.
		$checks[] = $this->check_theme();

		// 3. Shipping zones configured.
		$checks[] = $this->check_shipping_zones();

		// 4. Shopping list table exists.
		$checks[] = $this->check_shopping_list_table();

		// 5. Setup wizard completed.
		$checks[] = $this->check_setup_completed();

		// 6. PHP version.
		$checks[] = $this->check_php_version();

		return $checks;
	}

	/**
	 * Check WooCommerce is active with a supported version.
	 *
	 * @return array{label: string, status: string, detail: string}
	 */
	private function check_woocommerce(): array {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return array(
				'label'  => __( 'WooCommerce', 'bhaivatech-storefront-alpha' ),
				'status' => 'fail',
				'detail' => __( 'WooCommerce is not active. The storefront requires WooCommerce.', 'bhaivatech-storefront-alpha' ),
			);
		}

		$version = defined( 'WC_VERSION' ) ? WC_VERSION : '0.0.0';

		if ( version_compare( $version, '9.0', '<' ) ) {
			return array(
				'label'  => __( 'WooCommerce', 'bhaivatech-storefront-alpha' ),
				'status' => 'warn',
				'detail' => sprintf(
					/* translators: %s WooCommerce version number */
					__( 'WooCommerce %s detected. Version 9.0+ is recommended.', 'bhaivatech-storefront-alpha' ),
					$version
				),
			);
		}

		return array(
			'label'  => __( 'WooCommerce', 'bhaivatech-storefront-alpha' ),
			'status' => 'pass',
			'detail' => sprintf(
				/* translators: %s WooCommerce version number */
				__( 'Version %s active.', 'bhaivatech-storefront-alpha' ),
				$version
			),
		);
	}

	/**
	 * Check the active theme is the expected storefront theme.
	 *
	 * @return array{label: string, status: string, detail: string}
	 */
	private function check_theme(): array {
		$theme      = wp_get_theme();
		$stylesheet = get_stylesheet();

		// Accept by text domain or known stylesheet slug.
		$compatible = (
			'bhaivatech-grocery-alpha' === $theme->get( 'TextDomain' )
			|| str_contains( $stylesheet, 'storefront-theme' )
			|| str_contains( $stylesheet, 'bhaivatech' )
			|| str_contains( $stylesheet, 'grovia' )
		);

		if ( $compatible ) {
			return array(
				'label'  => __( 'Theme', 'bhaivatech-storefront-alpha' ),
				'status' => 'pass',
				'detail' => sprintf(
					/* translators: %s theme name */
					__( '%s active and compatible.', 'bhaivatech-storefront-alpha' ),
					$theme->get( 'Name' )
				),
			);
		}

		return array(
			'label'  => __( 'Theme', 'bhaivatech-storefront-alpha' ),
			'status' => 'warn',
			'detail' => sprintf(
				/* translators: %s active theme name */
				__( '%s is active. The storefront blocks and patterns are designed for the BhaivaTech Grocery theme.', 'bhaivatech-storefront-alpha' ),
				$theme->get( 'Name' )
			),
		);
	}

	/**
	 * Check at least one named shipping zone exists.
	 *
	 * @return array{label: string, status: string, detail: string}
	 */
	private function check_shipping_zones(): array {
		if ( ! class_exists( 'WC_Shipping_Zones' ) ) {
			return array(
				'label'  => __( 'Shipping Zones', 'bhaivatech-storefront-alpha' ),
				'status' => 'warn',
				'detail' => __( 'Could not check shipping zones. WooCommerce shipping may not be loaded.', 'bhaivatech-storefront-alpha' ),
			);
		}

		$zones = \WC_Shipping_Zones::get_zones();
		$count = is_array( $zones ) ? count( $zones ) : 0;

		if ( 0 === $count ) {
			return array(
				'label'  => __( 'Shipping Zones', 'bhaivatech-storefront-alpha' ),
				'status' => 'warn',
				'detail' => __( 'No named shipping zones configured. The delivery checker will show all postcodes as unavailable. Configure zones in WooCommerce → Shipping.', 'bhaivatech-storefront-alpha' ),
			);
		}

		return array(
			'label'  => __( 'Shipping Zones', 'bhaivatech-storefront-alpha' ),
			'status' => 'pass',
			'detail' => sprintf(
				/* translators: %d number of zones */
				_n( '%d shipping zone configured.', '%d shipping zones configured.', $count, 'bhaivatech-storefront-alpha' ),
				$count
			),
		);
	}

	/**
	 * Check the shopping list custom table exists.
	 *
	 * @return array{label: string, status: string, detail: string}
	 */
	private function check_shopping_list_table(): array {
		global $wpdb;

		$table = $wpdb->prefix . 'storefront_shopping_list';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );

		if ( $exists ) {
			return array(
				'label'  => __( 'Shopping List table', 'bhaivatech-storefront-alpha' ),
				'status' => 'pass',
				'detail' => sprintf(
					/* translators: %s table name */
					__( 'Table %s exists.', 'bhaivatech-storefront-alpha' ),
					$table
				),
			);
		}

		return array(
			'label'  => __( 'Shopping List table', 'bhaivatech-storefront-alpha' ),
			'status' => 'fail',
			'detail' => __( 'Custom table missing. Deactivate and reactivate Storefront Core to create it.', 'bhaivatech-storefront-alpha' ),
		);
	}

	/**
	 * Check the setup wizard has been completed.
	 *
	 * @return array{label: string, status: string, detail: string}
	 */
	private function check_setup_completed(): array {
		$state = get_option( 'bhaivatech_storefront_setup', array() );
		$state = is_array( $state ) ? $state : array();

		if ( ! empty( $state['completed'] ) ) {
			return array(
				'label'  => __( 'Setup wizard', 'bhaivatech-storefront-alpha' ),
				'status' => 'pass',
				'detail' => sprintf(
					/* translators: %s store name */
					__( 'Completed. Store: %s.', 'bhaivatech-storefront-alpha' ),
					isset( $state['store_name'] ) && is_string( $state['store_name'] ) ? $state['store_name'] : '—'
				),
			);
		}

		return array(
			'label'  => __( 'Setup wizard', 'bhaivatech-storefront-alpha' ),
			'status' => 'warn',
			'detail' => __( 'Not completed. Run setup from WooCommerce → Storefront setup.', 'bhaivatech-storefront-alpha' ),
		);
	}

	/**
	 * Check PHP version meets the recommended minimum.
	 *
	 * @return array{label: string, status: string, detail: string}
	 */
	private function check_php_version(): array {
		$version = PHP_VERSION;

		if ( version_compare( $version, '8.3', '<' ) ) {
			return array(
				'label'  => __( 'PHP version', 'bhaivatech-storefront-alpha' ),
				'status' => 'warn',
				'detail' => sprintf(
					/* translators: %s PHP version */
					__( 'PHP %s detected. PHP 8.3+ is recommended for optimal performance and security.', 'bhaivatech-storefront-alpha' ),
					$version
				),
			);
		}

		return array(
			'label'  => __( 'PHP version', 'bhaivatech-storefront-alpha' ),
			'status' => 'pass',
			'detail' => sprintf(
				/* translators: %s PHP version */
				__( 'PHP %s.', 'bhaivatech-storefront-alpha' ),
				$version
			),
		);
	}

	/**
	 * Return key environment information.
	 *
	 * @return array<string, string>
	 */
	private function get_environment_info(): array {
		$info = array();

		$info[ __( 'WordPress version', 'bhaivatech-storefront-alpha' ) ]       = get_bloginfo( 'version' );
		$info[ __( 'WooCommerce version', 'bhaivatech-storefront-alpha' ) ]     = defined( 'WC_VERSION' ) ? WC_VERSION : __( 'Not active', 'bhaivatech-storefront-alpha' );
		$info[ __( 'PHP version', 'bhaivatech-storefront-alpha' ) ]             = PHP_VERSION;
		$info[ __( 'Storefront Core version', 'bhaivatech-storefront-alpha' ) ] = defined( 'BHAIVATECH_STOREFRONT_CORE_VERSION' ) ? BHAIVATECH_STOREFRONT_CORE_VERSION : '—';
		$info[ __( 'Active theme', 'bhaivatech-storefront-alpha' ) ]            = wp_get_theme()->get( 'Name' ) . ' ' . wp_get_theme()->get( 'Version' );
		$info[ __( 'HPOS enabled', 'bhaivatech-storefront-alpha' ) ]            = $this->is_hpos_enabled() ? __( 'Yes', 'bhaivatech-storefront-alpha' ) : __( 'No / Unknown', 'bhaivatech-storefront-alpha' );
		$info[ __( 'Block theme', 'bhaivatech-storefront-alpha' ) ]             = wp_is_block_theme() ? __( 'Yes', 'bhaivatech-storefront-alpha' ) : __( 'No', 'bhaivatech-storefront-alpha' );

		return $info;
	}

	/**
	 * Detect whether HPOS (High-Performance Order Storage) is active.
	 *
	 * @return bool
	 */
	private function is_hpos_enabled(): bool {
		if ( ! class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) ) {
			return false;
		}

		if ( method_exists( '\Automattic\WooCommerce\Utilities\OrderUtil', 'custom_orders_table_usage_is_enabled' ) ) {
			return \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
		}

		return false;
	}
}
