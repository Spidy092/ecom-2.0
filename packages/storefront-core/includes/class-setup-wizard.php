<?php
/**
 * Small, capability-protected store setup flow.
 *
 * @package BhaivaTechStorefrontCore
 */

namespace BhaivaTech\Storefront;

defined( 'ABSPATH' ) || exit;

/**
 * Guides a merchant through Grovia-owned basics without replacing WooCommerce setup.
 */
final class Setup_Wizard {
	private const OPTION = 'bhaivatech_storefront_setup';

	/**
	 * Register admin hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_menu', array( $this, 'add_page' ), 30 );
		add_action( 'admin_post_bhaivatech_storefront_setup', array( $this, 'handle_submission' ) );
	}

	/**
	 * Add the wizard under WooCommerce.
	 *
	 * @return void
	 */
	public function add_page(): void {
		add_submenu_page(
			'woocommerce',
			esc_html__( 'Storefront setup', 'bhaivatech-storefront-alpha' ),
			esc_html__( 'Storefront setup', 'bhaivatech-storefront-alpha' ),
			'manage_woocommerce',
			'bhaivatech-storefront-setup',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Render the current setup step.
	 *
	 * @return void
	 */
	public function render_page(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have permission to run storefront setup.', 'bhaivatech-storefront-alpha' ) );
		}

		$state = $this->get_state();
		$step  = isset( $_GET['setup_step'] ) && is_scalar( $_GET['setup_step'] ) ? absint( wp_unslash( $_GET['setup_step'] ) ) : ( $state['completed'] ? 3 : $state['step'] );
		$step  = max( 1, min( 3, $step ) );
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'Storefront setup', 'bhaivatech-storefront-alpha' ); ?></h1>
			<p><?php echo esc_html( sprintf( __( 'Step %1$d of 3', 'bhaivatech-storefront-alpha' ), $step ) ); ?></p>
			<?php if ( 1 === $step ) : ?>
				<h2><?php echo esc_html__( 'Start with the store basics', 'bhaivatech-storefront-alpha' ); ?></h2>
				<p><?php echo esc_html__( 'Choose the kind of grocery store you are launching and the name shoppers should see. WooCommerce remains responsible for products, taxes, shipping and payments.', 'bhaivatech-storefront-alpha' ); ?></p>
				<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
					<?php wp_nonce_field( 'bhaivatech_storefront_setup' ); ?>
					<input type="hidden" name="action" value="bhaivatech_storefront_setup">
					<input type="hidden" name="setup_step" value="1">
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="bhaivatech-store-name"><?php echo esc_html__( 'Store name', 'bhaivatech-storefront-alpha' ); ?></label></th>
							<td><input class="regular-text" id="bhaivatech-store-name" name="store_name" type="text" maxlength="80" value="<?php echo esc_attr( $state['store_name'] ); ?>" required></td>
						</tr>
						<tr>
							<th scope="row"><label for="bhaivatech-store-type"><?php echo esc_html__( 'Store type', 'bhaivatech-storefront-alpha' ); ?></label></th>
							<td>
								<select id="bhaivatech-store-type" name="store_type">
									<option value="grocery" <?php selected( $state['store_type'], 'grocery' ); ?>><?php echo esc_html__( 'Everyday grocery', 'bhaivatech-storefront-alpha' ); ?></option>
									<option value="organic" <?php selected( $state['store_type'], 'organic' ); ?>><?php echo esc_html__( 'Organic food', 'bhaivatech-storefront-alpha' ); ?></option>
									<option value="farm" <?php selected( $state['store_type'], 'farm' ); ?>><?php echo esc_html__( 'Farm produce', 'bhaivatech-storefront-alpha' ); ?></option>
								</select>
							</td>
						</tr>
					</table>
					<?php submit_button( esc_html__( 'Continue to delivery', 'bhaivatech-storefront-alpha' ) ); ?>
				</form>
			<?php elseif ( 2 === $step ) : ?>
				<h2><?php echo esc_html__( 'Set the delivery area', 'bhaivatech-storefront-alpha' ); ?></h2>
				<p><?php echo esc_html__( 'Configure delivery areas in WooCommerce Shipping Zones. The storefront checker reads those zones directly, so shoppers and checkout use one delivery truth.', 'bhaivatech-storefront-alpha' ); ?></p>
				<p><a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=shipping' ) ); ?>"><?php echo esc_html__( 'Open Shipping Zones', 'bhaivatech-storefront-alpha' ); ?></a></p>
				<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
					<?php wp_nonce_field( 'bhaivatech_storefront_setup' ); ?>
					<input type="hidden" name="action" value="bhaivatech_storefront_setup">
					<input type="hidden" name="setup_step" value="2">
					<?php submit_button( esc_html__( 'Finish setup', 'bhaivatech-storefront-alpha' ) ); ?>
				</form>
			<?php else : ?>
				<h2><?php echo esc_html__( 'Your storefront foundation is ready', 'bhaivatech-storefront-alpha' ); ?></h2>
				<p><?php echo esc_html__( 'The Modern Grocery theme is ready for your products. Complete WooCommerce product, tax, shipping and payment configuration before accepting orders.', 'bhaivatech-storefront-alpha' ); ?></p>
				<ul>
					<li><?php echo esc_html( sprintf( __( 'Store: %s', 'bhaivatech-storefront-alpha' ), $state['store_name'] ) ); ?></li>
					<li><?php echo esc_html__( 'Delivery: WooCommerce Shipping Zones', 'bhaivatech-storefront-alpha' ); ?></li>
				</ul>
				<p><a class="button button-primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html__( 'View storefront', 'bhaivatech-storefront-alpha' ); ?></a></p>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Handle a wizard submission with explicit capability and nonce checks.
	 *
	 * @return void
	 */
	public function handle_submission(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have permission to update storefront setup.', 'bhaivatech-storefront-alpha' ) );
		}

		check_admin_referer( 'bhaivatech_storefront_setup' );
		$step  = isset( $_POST['setup_step'] ) && is_scalar( $_POST['setup_step'] ) ? absint( wp_unslash( $_POST['setup_step'] ) ) : 0;
		$state = $this->get_state();

		if ( 1 === $step ) {
			$store_name = isset( $_POST['store_name'] ) && is_scalar( $_POST['store_name'] ) ? sanitize_text_field( wp_unslash( $_POST['store_name'] ) ) : '';
			$store_type = isset( $_POST['store_type'] ) && is_scalar( $_POST['store_type'] ) ? sanitize_key( wp_unslash( $_POST['store_type'] ) ) : 'grocery';
			$allowed    = array( 'grocery', 'organic', 'farm' );

			$state['store_name'] = substr( $store_name, 0, 80 );
			$state['store_type'] = in_array( $store_type, $allowed, true ) ? $store_type : 'grocery';
			$state['step']       = 2;
		} elseif ( 2 === $step ) {
			$state['completed'] = true;
			$state['step']      = 3;
		} else {
			wp_die( esc_html__( 'That setup step is not available.', 'bhaivatech-storefront-alpha' ) );
		}

		update_option( self::OPTION, $state, false );
		wp_safe_redirect( add_query_arg( array( 'page' => 'bhaivatech-storefront-setup', 'setup_step' => $state['step'], 'updated' => '1' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	/**
	 * Return normalized wizard state.
	 *
	 * @return array<string, mixed>
	 */
	private function get_state(): array {
		$stored = get_option( self::OPTION, array() );
		$stored = is_array( $stored ) ? $stored : array();

		return array(
			'step'               => ! empty( $stored['completed'] ) ? 3 : max( 1, min( 2, absint( $stored['step'] ?? 1 ) ) ),
			'completed'          => ! empty( $stored['completed'] ),
			'store_name'         => isset( $stored['store_name'] ) && is_string( $stored['store_name'] ) ? $stored['store_name'] : '',
			'store_type'         => isset( $stored['store_type'] ) && is_string( $stored['store_type'] ) ? $stored['store_type'] : 'grocery',
		);
	}
}
