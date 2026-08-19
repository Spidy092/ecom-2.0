<?php
/**
 * Setup and privacy-safe system status for the engineering alpha.
 *
 * @package BhaivaTechStorefrontAlpha
 */

defined( 'ABSPATH' ) || exit;

/**
 * Collect active plugin names/versions without filesystem paths or secrets.
 *
 * @return array<int, array{name:string,version:string}>
 */
function bhaivatech_storefront_status_active_plugins(): array {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';

	$plugins = get_plugins();
	$active  = array();

	foreach ( $plugins as $plugin_file => $plugin ) {
		if ( ! is_plugin_active( $plugin_file ) ) {
			continue;
		}

		$active[] = array(
			'name'    => isset( $plugin['Name'] ) ? wp_strip_all_tags( (string) $plugin['Name'] ) : 'Unknown plugin',
			'version' => isset( $plugin['Version'] ) ? wp_strip_all_tags( (string) $plugin['Version'] ) : 'Unknown',
		);
	}

	usort(
		$active,
		static fn( array $a, array $b ): int => strcasecmp( $a['name'], $b['name'] )
	);

	return $active;
}

/**
 * List theme-owned WooCommerce template overrides using relative paths only.
 *
 * @return string[]
 */
function bhaivatech_storefront_status_template_overrides(): array {
	$root = trailingslashit( get_stylesheet_directory() ) . 'woocommerce';
	if ( ! is_dir( $root ) ) {
		return array();
	}

	$files    = array();
	$iterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS )
	);

	foreach ( $iterator as $file ) {
		if ( ! $file->isFile() || 'php' !== strtolower( $file->getExtension() ) ) {
			continue;
		}

		$files[] = ltrim( str_replace( '\\', '/', substr( $file->getPathname(), strlen( $root ) ) ), '/' );
	}

	sort( $files, SORT_NATURAL | SORT_FLAG_CASE );
	return $files;
}

/**
 * Build the privacy-reviewed diagnostic payload used by both UI and export.
 *
 * Deliberately excluded: site URL/domain, usernames/emails, orders/customer
 * data, cookies/nonces, database credentials, filesystem paths and license
 * secrets.
 *
 * @return array<string, mixed>
 */
function bhaivatech_storefront_collect_system_status(): array {
	global $wp_version;

	$theme              = wp_get_theme();
	$home_scheme        = wp_parse_url( home_url( '/' ), PHP_URL_SCHEME );
	$memory_limit       = defined( 'WP_MEMORY_LIMIT' ) ? WP_MEMORY_LIMIT : ini_get( 'memory_limit' );
	$cron_disabled      = defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON;
	$template_overrides = bhaivatech_storefront_status_template_overrides();
	$starter_state      = bhaivatech_storefront_get_starter_import_state();

	return array(
		'generated_at_utc' => gmdate( 'c' ),
		'product'          => array(
			'core_version'         => BHAIVATECH_STOREFRONT_CORE_VERSION,
			'active_theme'         => $theme->get( 'Name' ),
			'active_theme_version' => $theme->get( 'Version' ),
			'product_theme_active' => 'storefront-theme' === get_template(),
		),
		'platform'         => array(
			'wordpress_version'   => (string) $wp_version,
			'woocommerce_version' => defined( 'WC_VERSION' ) ? WC_VERSION : 'Not active',
			'php_version'         => PHP_VERSION,
		),
		'environment'      => array(
			'https_home'          => 'https' === $home_scheme,
			'rest_api_configured' => function_exists( 'rest_get_server' ) && '' !== rest_url(),
			'wp_cron_enabled'     => ! $cron_disabled,
			'wp_memory_limit'     => (string) $memory_limit,
		),
		'starter_import'   => array(
			'status'           => (string) $starter_state['status'],
			'manifest_id'      => (string) $starter_state['manifest_id'],
			'manifest_version' => (string) $starter_state['manifest_version'],
			'attempts'         => (int) $starter_state['attempts'],
			'current_step'     => (string) $starter_state['current_step'],
			'failed_step'      => (string) $starter_state['failed_step'],
			'last_error_code'  => (string) $starter_state['last_error_code'],
		),
		'active_plugins'     => bhaivatech_storefront_status_active_plugins(),
		'template_overrides' => $template_overrides,
		'privacy_scope'      => 'No URLs, customer/order data, credentials, cookies/nonces or license secrets are included.',
	);
}

/**
 * Register the product setup/status page under WooCommerce.
 */
function bhaivatech_storefront_register_setup_status_page(): void {
	add_submenu_page(
		'woocommerce',
		__( 'Store Setup & Status', 'bhaivatech-storefront-alpha' ),
		__( 'Store Setup', 'bhaivatech-storefront-alpha' ),
		'manage_woocommerce',
		'bhaivatech-storefront-setup',
		'bhaivatech_storefront_render_setup_status_page'
	);
}

/**
 * Render one WordPress-native status row.
 *
 * @param string $label Row label.
 * @param string $value Current value.
 * @param string $state Ready, Review or Info.
 * @param string $note Supporting note.
 */
function bhaivatech_storefront_render_status_row( string $label, string $value, string $state, string $note ): void {
	?>
	<tr>
		<th scope="row"><?php echo esc_html( $label ); ?></th>
		<td><strong><?php echo esc_html( $state ); ?></strong></td>
		<td><code><?php echo esc_html( $value ); ?></code></td>
		<td><?php echo esc_html( $note ); ?></td>
	</tr>
	<?php
}

/**
 * Render the non-destructive setup/status alpha experience.
 */
function bhaivatech_storefront_render_setup_status_page(): void {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die( esc_html__( 'You do not have permission to view store setup.', 'bhaivatech-storefront-alpha' ) );
	}

	$status          = bhaivatech_storefront_collect_system_status();
	$wp_ready        = version_compare( $status['platform']['wordpress_version'], '6.9', '>=' );
	$php_ready       = version_compare( $status['platform']['php_version'], '8.3', '>=' );
	$woo_ready       = 'Not active' !== $status['platform']['woocommerce_version'];
	$theme_ready     = (bool) $status['product']['product_theme_active'];
	$shop_page_id    = function_exists( 'wc_get_page_id' ) ? (int) wc_get_page_id( 'shop' ) : 0;
	$shop_page_ready = $shop_page_id > 0 && 'publish' === get_post_status( $shop_page_id );
	$starter_status  = (string) $status['starter_import']['status'];
	$starter_label   = 'complete' === $starter_status ? 'Ready' : ( 'failed' === $starter_status || 'running' === $starter_status ? 'Review' : 'Info' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Store Setup & Status', 'bhaivatech-storefront-alpha' ); ?></h1>
		<p><?php esc_html_e( 'A non-destructive engineering-alpha checklist for getting the grocery storefront ready and producing a safe support report.', 'bhaivatech-storefront-alpha' ); ?></p>

		<h2><?php esc_html_e( 'Setup path', 'bhaivatech-storefront-alpha' ); ?></h2>
		<ol>
			<li><strong><?php esc_html_e( 'Platform requirements', 'bhaivatech-storefront-alpha' ); ?></strong> — <?php echo esc_html( $wp_ready && $php_ready && $woo_ready ? __( 'ready', 'bhaivatech-storefront-alpha' ) : __( 'review the checks below', 'bhaivatech-storefront-alpha' ) ); ?></li>
			<li><strong><?php esc_html_e( 'Theme + Core', 'bhaivatech-storefront-alpha' ); ?></strong> — <?php echo esc_html( $theme_ready ? __( 'product theme and Core are active', 'bhaivatech-storefront-alpha' ) : __( 'Core is active; activate the product theme before importing a starter store', 'bhaivatech-storefront-alpha' ) ); ?></li>
			<li><strong><?php esc_html_e( 'WooCommerce store basics', 'bhaivatech-storefront-alpha' ); ?></strong> — <a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings' ) ); ?>"><?php esc_html_e( 'review WooCommerce settings', 'bhaivatech-storefront-alpha' ); ?></a></li>
			<li><strong><?php esc_html_e( 'Modern Grocery starter store', 'bhaivatech-storefront-alpha' ); ?></strong> — <?php esc_html_e( 'the transaction/retry contract now exists internally, but content import remains disabled until the commercial package provider and content-operation verification are complete; no content is changed from this screen.', 'bhaivatech-storefront-alpha' ); ?></li>
			<li><strong><?php esc_html_e( 'Verify storefront', 'bhaivatech-storefront-alpha' ); ?></strong> — <?php if ( $shop_page_ready ) : ?><a href="<?php echo esc_url( get_permalink( $shop_page_id ) ); ?>"><?php esc_html_e( 'open Shop', 'bhaivatech-storefront-alpha' ); ?></a><?php else : ?><?php esc_html_e( 'Shop page needs review.', 'bhaivatech-storefront-alpha' ); ?><?php endif; ?></li>
		</ol>

		<h2><?php esc_html_e( 'Environment checks', 'bhaivatech-storefront-alpha' ); ?></h2>
		<table class="widefat striped" style="max-width:1100px">
			<thead><tr><th><?php esc_html_e( 'Check', 'bhaivatech-storefront-alpha' ); ?></th><th><?php esc_html_e( 'State', 'bhaivatech-storefront-alpha' ); ?></th><th><?php esc_html_e( 'Detected', 'bhaivatech-storefront-alpha' ); ?></th><th><?php esc_html_e( 'Why it matters', 'bhaivatech-storefront-alpha' ); ?></th></tr></thead>
			<tbody>
				<?php bhaivatech_storefront_render_status_row( 'WordPress', $status['platform']['wordpress_version'], $wp_ready ? 'Ready' : 'Review', 'Core currently requires WordPress 6.9 or newer.' ); ?>
				<?php bhaivatech_storefront_render_status_row( 'WooCommerce', $status['platform']['woocommerce_version'], $woo_ready ? 'Ready' : 'Review', 'WooCommerce owns products, cart, checkout, payments, shipping and taxes.' ); ?>
				<?php bhaivatech_storefront_render_status_row( 'PHP', $status['platform']['php_version'], $php_ready ? 'Ready' : 'Review', 'Core currently requires PHP 8.3 or newer.' ); ?>
				<?php bhaivatech_storefront_render_status_row( 'Product theme', $status['product']['active_theme'] . ' ' . $status['product']['active_theme_version'], $theme_ready ? 'Ready' : 'Review', 'The Core plugin remains functional independently; the product theme supplies the intended storefront presentation.' ); ?>
				<?php bhaivatech_storefront_render_status_row( 'HTTPS', $status['environment']['https_home'] ? 'HTTPS' : 'HTTP', $status['environment']['https_home'] ? 'Ready' : 'Review', 'Production commerce should use HTTPS.' ); ?>
				<?php bhaivatech_storefront_render_status_row( 'WordPress REST API', $status['environment']['rest_api_configured'] ? 'Configured' : 'Unavailable', $status['environment']['rest_api_configured'] ? 'Ready' : 'Review', 'This confirms REST is configured; network reachability will be checked by the final importer preflight.' ); ?>
				<?php bhaivatech_storefront_render_status_row( 'WP-Cron', $status['environment']['wp_cron_enabled'] ? 'Enabled' : 'Disabled', $status['environment']['wp_cron_enabled'] ? 'Ready' : 'Review', 'If WP-Cron is disabled, the host should provide a real cron runner.' ); ?>
				<?php bhaivatech_storefront_render_status_row( 'WordPress memory limit', $status['environment']['wp_memory_limit'], 'Info', 'Recorded for support diagnosis. The importer memory threshold is not finalized yet.' ); ?>
				<?php bhaivatech_storefront_render_status_row( 'Woo template overrides', (string) count( $status['template_overrides'] ), 0 === count( $status['template_overrides'] ) ? 'Ready' : 'Review', 'Zero is preferred. Every override becomes an explicit WooCommerce compatibility obligation.' ); ?>
				<?php bhaivatech_storefront_render_status_row( 'Starter import transaction', $starter_status, $starter_label, 'Transaction state is technical only; the customer-facing destructive importer is still disabled in this alpha.' ); ?>
			</tbody>
		</table>

		<h2><?php esc_html_e( 'Plugin compatibility', 'bhaivatech-storefront-alpha' ); ?></h2>
		<p><?php esc_html_e( 'The product is designed to coexist with WordPress and WooCommerce extensions that use supported public APIs. WooCommerce is required; Elementor and other page builders are not required for the core storefront. A plugin is only advertised as validated compatibility after we test it.', 'bhaivatech-storefront-alpha' ); ?></p>
		<p><strong><?php esc_html_e( 'Active plugins detected:', 'bhaivatech-storefront-alpha' ); ?></strong> <?php echo esc_html( (string) count( $status['active_plugins'] ) ); ?></p>
		<ul>
			<?php foreach ( $status['active_plugins'] as $plugin ) : ?>
				<li><?php echo esc_html( $plugin['name'] . ' — ' . $plugin['version'] ); ?></li>
			<?php endforeach; ?>
		</ul>

		<h2><?php esc_html_e( 'Support report', 'bhaivatech-storefront-alpha' ); ?></h2>
		<p><?php esc_html_e( 'Export only the technical data needed for setup/compatibility diagnosis. The report excludes site URLs, customer/order data, credentials, cookies/nonces and license secrets.', 'bhaivatech-storefront-alpha' ); ?></p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="bhaivatech_storefront_export_status" />
			<?php wp_nonce_field( 'bhaivatech_storefront_export_status' ); ?>
			<?php submit_button( __( 'Download safe system report', 'bhaivatech-storefront-alpha' ), 'secondary', 'submit', false ); ?>
		</form>
	</div>
	<?php
}

/**
 * Download the privacy-safe status payload as JSON.
 */
function bhaivatech_storefront_export_system_status(): void {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die(
			esc_html__( 'You do not have permission to export store status.', 'bhaivatech-storefront-alpha' ),
			esc_html__( 'Forbidden', 'bhaivatech-storefront-alpha' ),
			array( 'response' => 403 )
		);
	}

	check_admin_referer( 'bhaivatech_storefront_export_status' );

	$payload = bhaivatech_storefront_collect_system_status();
	$json    = wp_json_encode( $payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );

	if ( false === $json ) {
		wp_die( esc_html__( 'The system report could not be generated.', 'bhaivatech-storefront-alpha' ) );
	}

	nocache_headers();
	header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
	header( 'Content-Disposition: attachment; filename="storefront-system-status.json"' );
	echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON download after wp_json_encode().
	exit;
}
