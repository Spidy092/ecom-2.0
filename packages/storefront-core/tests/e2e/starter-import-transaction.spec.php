<?php
/**
 * Deterministic transaction-state and verification-preflight checks for the provider-independent starter importer.
 */

/**
 * Fail the E2E script when a condition is false.
 *
 * @param bool   $condition Condition.
 * @param string $message Failure message.
 */
function bhaivatech_starter_import_assert( bool $condition, string $message ): void {
	if ( ! $condition ) {
		WP_CLI::error( $message );
	}
}

$resources = bhaivatech_storefront_modern_grocery_required_resources();
bhaivatech_starter_import_assert( true === bhaivatech_storefront_validate_required_resources( $resources ), 'Starter required-resource manifest should validate.' );
bhaivatech_starter_import_assert( 8 === count( $resources ), 'Starter required-resource manifest should have eight current verification targets.' );

$resource_keys = array_column( $resources, 'key' );
bhaivatech_starter_import_assert( count( $resource_keys ) === count( array_unique( $resource_keys ) ), 'Starter resource keys should be unique.' );

$duplicate_resources   = $resources;
$duplicate_resources[] = $resources[0];
$duplicate_result      = bhaivatech_storefront_validate_required_resources( $duplicate_resources );
bhaivatech_starter_import_assert( is_wp_error( $duplicate_result ) && 'starter_resource_key_duplicate' === $duplicate_result->get_error_code(), 'Duplicate starter resource identity should fail validation.' );

$preflight = bhaivatech_storefront_run_starter_resource_preflight();
bhaivatech_starter_import_assert( ! is_wp_error( $preflight ), 'Starter verification preflight should run.' );
bhaivatech_starter_import_assert( 8 === $preflight['total'], 'Starter preflight should report all verification targets.' );
bhaivatech_starter_import_assert( count( $preflight['checks'] ) === $preflight['total'], 'Starter preflight checks should match total.' );

foreach ( $preflight['checks'] as $check ) {
	bhaivatech_starter_import_assert( str_starts_with( $check['key'], 'modern-grocery/' ), 'Starter preflight key should remain namespaced.' );
	bhaivatech_starter_import_assert( in_array( $check['code'], array( 'ready', 'woocommerce_page_missing', 'theme_resource_missing', 'block_not_registered' ), true ), 'Starter preflight should expose only bounded result codes.' );

	if ( in_array( $check['type'], array( 'theme_file', 'block' ), true ) ) {
		bhaivatech_starter_import_assert( true === $check['ready'], 'Active product theme/Core verification target should be ready: ' . $check['key'] );
	}
}

bhaivatech_storefront_reset_starter_import_state();
$manifest = bhaivatech_storefront_modern_grocery_manifest();

bhaivatech_starter_import_assert( true === bhaivatech_storefront_validate_starter_manifest( $manifest ), 'Modern Grocery manifest should validate.' );

$invalid           = $manifest;
$invalid['schema'] = 999;
bhaivatech_starter_import_assert( is_wp_error( bhaivatech_storefront_validate_starter_manifest( $invalid ) ), 'Unsupported manifest schema should fail.' );

$started = bhaivatech_storefront_begin_starter_import( $manifest );
bhaivatech_starter_import_assert( ! is_wp_error( $started ), 'First transaction should start.' );
bhaivatech_starter_import_assert( 'running' === $started['status'], 'Started transaction should be running.' );
bhaivatech_starter_import_assert( 'preflight' === $started['current_step'], 'First transaction step should be preflight.' );
bhaivatech_starter_import_assert( 1 === (int) $started['attempts'], 'First transaction should record one attempt.' );
bhaivatech_starter_import_assert( 'started' === $started['result'], 'First transaction should report started.' );

$parallel = bhaivatech_storefront_begin_starter_import( $manifest );
bhaivatech_starter_import_assert( is_wp_error( $parallel ) && 'starter_import_in_progress' === $parallel->get_error_code(), 'Parallel transaction should be blocked.' );

$out_of_order = bhaivatech_storefront_complete_starter_import_step( 'configuration' );
bhaivatech_starter_import_assert( is_wp_error( $out_of_order ) && 'starter_import_step_out_of_order' === $out_of_order->get_error_code(), 'Out-of-order phase completion should fail.' );

$after_preflight = bhaivatech_storefront_complete_starter_import_step( 'preflight' );
bhaivatech_starter_import_assert( ! is_wp_error( $after_preflight ), 'Preflight should complete.' );
bhaivatech_starter_import_assert( 'content' === $after_preflight['current_step'], 'Content should follow preflight.' );

$failed = bhaivatech_storefront_fail_starter_import( 'content', 'network-timeout' );
bhaivatech_starter_import_assert( ! is_wp_error( $failed ), 'Current phase should be fail-able.' );
bhaivatech_starter_import_assert( 'failed' === $failed['status'], 'Failure should persist failed status.' );
bhaivatech_starter_import_assert( 'content' === $failed['failed_step'], 'Failure should persist the current phase only.' );
bhaivatech_starter_import_assert( 'network-timeout' === $failed['last_error_code'], 'Failure should persist only a bounded error code.' );

$resumed = bhaivatech_storefront_begin_starter_import( $manifest );
bhaivatech_starter_import_assert( ! is_wp_error( $resumed ), 'Same manifest should resume after failure.' );
bhaivatech_starter_import_assert( 'running' === $resumed['status'], 'Resumed transaction should be running.' );
bhaivatech_starter_import_assert( 'content' === $resumed['current_step'], 'Retry should resume from failed content phase.' );
bhaivatech_starter_import_assert( 2 === (int) $resumed['attempts'], 'Retry should increment attempt count.' );
bhaivatech_starter_import_assert( 'resumed' === $resumed['result'], 'Retry should report resumed.' );
bhaivatech_starter_import_assert( array( 'preflight' ) === $resumed['completed_steps'], 'Retry should preserve completed phases.' );

foreach ( array( 'content', 'configuration', 'verification' ) as $step ) {
	$result = bhaivatech_storefront_complete_starter_import_step( $step );
	bhaivatech_starter_import_assert( ! is_wp_error( $result ), 'Remaining transaction phase should complete: ' . $step );
}

$complete = bhaivatech_storefront_get_starter_import_state();
bhaivatech_starter_import_assert( 'complete' === $complete['status'], 'Verification should complete the transaction.' );
bhaivatech_starter_import_assert( bhaivatech_storefront_starter_import_steps() === $complete['completed_steps'], 'All transaction phases should be recorded once.' );

$already_complete = bhaivatech_storefront_begin_starter_import( $manifest );
bhaivatech_starter_import_assert( ! is_wp_error( $already_complete ), 'Rerun of completed identical manifest should be non-destructive.' );
bhaivatech_starter_import_assert( 'complete' === $already_complete['status'], 'Completed rerun should stay complete.' );
bhaivatech_starter_import_assert( 'already_complete' === $already_complete['result'], 'Completed rerun should report already complete.' );

$changed            = $manifest;
$changed['version'] = '0.2.0-alpha';
$changed_result      = bhaivatech_storefront_begin_starter_import( $changed );
bhaivatech_starter_import_assert( is_wp_error( $changed_result ) && 'starter_import_manifest_changed' === $changed_result->get_error_code(), 'Different manifest version must not silently reuse old transaction state.' );

bhaivatech_storefront_reset_starter_import_state();
bhaivatech_starter_import_assert( 'idle' === bhaivatech_storefront_get_starter_import_state()['status'], 'Transaction reset should restore idle state.' );

WP_CLI::success( 'Starter preflight + transaction retry/idempotency contract passed.' );
