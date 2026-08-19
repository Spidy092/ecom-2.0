<?php
/**
 * Provider-independent Modern Grocery starter import transaction contract.
 *
 * This file intentionally owns transaction state only. It does not create,
 * update, delete or import customer content yet.
 *
 * @package BhaivaTechStorefrontAlpha
 */

defined( 'ABSPATH' ) || exit;

const BHAIVATECH_STOREFRONT_STARTER_IMPORT_OPTION      = 'bhaivatech_storefront_starter_import_state';
const BHAIVATECH_STOREFRONT_STARTER_IMPORT_LOCK_OPTION = 'bhaivatech_storefront_starter_import_lock';
const BHAIVATECH_STOREFRONT_STARTER_SCHEMA             = 1;

/**
 * Ordered business-level phases for a starter-store import.
 *
 * @return string[]
 */
function bhaivatech_storefront_starter_import_steps(): array {
	return array( 'preflight', 'content', 'configuration', 'verification' );
}

/**
 * Return the provider-independent Modern Grocery manifest identity.
 *
 * The eventual commercial download/update provider may supply the package,
 * but it must not redefine this transaction contract.
 *
 * @return array<string, mixed>
 */
function bhaivatech_storefront_modern_grocery_manifest(): array {
	return array(
		'schema'      => BHAIVATECH_STOREFRONT_STARTER_SCHEMA,
		'id'          => 'modern-grocery',
		'version'     => '0.1.0-alpha',
		'description' => 'Modern Grocery starter-store transaction manifest.',
		'steps'       => bhaivatech_storefront_starter_import_steps(),
	);
}

/**
 * Validate only the immutable transaction metadata needed before import work.
 *
 * @param mixed $manifest Candidate manifest.
 * @return true|WP_Error
 */
function bhaivatech_storefront_validate_starter_manifest( $manifest ) {
	if ( ! is_array( $manifest ) ) {
		return new WP_Error( 'starter_manifest_invalid', 'Starter manifest must be an array.' );
	}

	$required = array( 'schema', 'id', 'version', 'steps' );
	foreach ( $required as $key ) {
		if ( ! array_key_exists( $key, $manifest ) ) {
			return new WP_Error( 'starter_manifest_missing_field', 'Starter manifest is missing required metadata.' );
		}
	}

	if ( BHAIVATECH_STOREFRONT_STARTER_SCHEMA !== (int) $manifest['schema'] ) {
		return new WP_Error( 'starter_manifest_schema_unsupported', 'Starter manifest schema is not supported.' );
	}

	if ( ! is_string( $manifest['id'] ) || ! preg_match( '/^[a-z0-9-]+$/', $manifest['id'] ) ) {
		return new WP_Error( 'starter_manifest_id_invalid', 'Starter manifest ID is invalid.' );
	}

	if ( ! is_string( $manifest['version'] ) || '' === trim( $manifest['version'] ) ) {
		return new WP_Error( 'starter_manifest_version_invalid', 'Starter manifest version is invalid.' );
	}

	if ( bhaivatech_storefront_starter_import_steps() !== array_values( (array) $manifest['steps'] ) ) {
		return new WP_Error( 'starter_manifest_steps_invalid', 'Starter manifest step order is invalid.' );
	}

	return true;
}

/**
 * Create a stable identity for the manifest used by retry/idempotency checks.
 *
 * @param array<string, mixed> $manifest Validated manifest.
 */
function bhaivatech_storefront_starter_manifest_digest( array $manifest ): string {
	$identity = array(
		'schema'  => (int) $manifest['schema'],
		'id'      => (string) $manifest['id'],
		'version' => (string) $manifest['version'],
		'steps'   => array_values( (array) $manifest['steps'] ),
	);

	return hash( 'sha256', (string) wp_json_encode( $identity ) );
}

/**
 * Return normalized transaction state.
 *
 * @return array<string, mixed>
 */
function bhaivatech_storefront_get_starter_import_state(): array {
	$state = get_option( BHAIVATECH_STOREFRONT_STARTER_IMPORT_OPTION, array() );
	if ( ! is_array( $state ) ) {
		$state = array();
	}

	return wp_parse_args(
		$state,
		array(
			'status'           => 'idle',
			'manifest_id'      => '',
			'manifest_version' => '',
			'manifest_digest'  => '',
			'attempts'         => 0,
			'current_step'     => '',
			'completed_steps'  => array(),
			'failed_step'      => '',
			'last_error_code'  => '',
			'updated_at_utc'   => '',
		)
	);
}

/**
 * Persist bounded technical transaction state.
 *
 * @param array<string, mixed> $state State to persist.
 */
function bhaivatech_storefront_save_starter_import_state( array $state ): void {
	$state['updated_at_utc'] = gmdate( 'c' );
	update_option( BHAIVATECH_STOREFRONT_STARTER_IMPORT_OPTION, $state, false );
}

/**
 * Acquire a database-backed begin lock using WordPress's unique option key.
 *
 * add_option() is used rather than get/update so two concurrent begin requests
 * cannot both acquire the same lock key.
 */
function bhaivatech_storefront_acquire_starter_import_lock( string $digest ): bool {
	return add_option(
		BHAIVATECH_STOREFRONT_STARTER_IMPORT_LOCK_OPTION,
		array(
			'manifest_digest' => $digest,
			'acquired_at_utc' => gmdate( 'c' ),
		),
		'',
		false
	);
}

/**
 * Release the transaction lock after failure, completion or explicit reset.
 */
function bhaivatech_storefront_release_starter_import_lock(): void {
	delete_option( BHAIVATECH_STOREFRONT_STARTER_IMPORT_LOCK_OPTION );
}

/**
 * Whether the transaction currently owns a persisted lock.
 */
function bhaivatech_storefront_has_starter_import_lock(): bool {
	return false !== get_option( BHAIVATECH_STOREFRONT_STARTER_IMPORT_LOCK_OPTION, false );
}

/**
 * Begin or resume the same manifest transaction.
 *
 * @param array<string, mixed> $manifest Starter manifest.
 * @return array<string, mixed>|WP_Error
 */
function bhaivatech_storefront_begin_starter_import( array $manifest ) {
	$valid = bhaivatech_storefront_validate_starter_manifest( $manifest );
	if ( is_wp_error( $valid ) ) {
		return $valid;
	}

	$digest = bhaivatech_storefront_starter_manifest_digest( $manifest );
	$state  = bhaivatech_storefront_get_starter_import_state();

	if ( 'running' === $state['status'] ) {
		return new WP_Error( 'starter_import_in_progress', 'A starter import transaction is already running.' );
	}

	if ( '' !== $state['manifest_digest'] && $digest !== $state['manifest_digest'] && 'idle' !== $state['status'] ) {
		return new WP_Error( 'starter_import_manifest_changed', 'Existing starter import state belongs to another manifest version.' );
	}

	if ( 'complete' === $state['status'] && $digest === $state['manifest_digest'] ) {
		$state['result'] = 'already_complete';
		return $state;
	}

	if ( ! bhaivatech_storefront_acquire_starter_import_lock( $digest ) ) {
		return new WP_Error( 'starter_import_lock_unavailable', 'Another starter import begin request owns the transaction lock.' );
	}

	// Re-read after acquiring the atomic lock so a stale pre-lock read cannot
	// overwrite a transaction that changed and released the lock in between.
	$state = bhaivatech_storefront_get_starter_import_state();

	if ( 'running' === $state['status'] ) {
		bhaivatech_storefront_release_starter_import_lock();
		return new WP_Error( 'starter_import_in_progress', 'A starter import transaction is already running.' );
	}

	if ( '' !== $state['manifest_digest'] && $digest !== $state['manifest_digest'] && 'idle' !== $state['status'] ) {
		bhaivatech_storefront_release_starter_import_lock();
		return new WP_Error( 'starter_import_manifest_changed', 'Existing starter import state belongs to another manifest version.' );
	}

	if ( 'complete' === $state['status'] && $digest === $state['manifest_digest'] ) {
		bhaivatech_storefront_release_starter_import_lock();
		$state['result'] = 'already_complete';
		return $state;
	}

	$was_failed      = 'failed' === $state['status'];
	$steps           = bhaivatech_storefront_starter_import_steps();
	$completed_steps = array_values( array_intersect( $steps, (array) $state['completed_steps'] ) );
	$next_step       = $steps[ count( $completed_steps ) ] ?? '';

	$state = array(
		'status'           => 'running',
		'manifest_id'      => (string) $manifest['id'],
		'manifest_version' => (string) $manifest['version'],
		'manifest_digest'  => $digest,
		'attempts'         => (int) $state['attempts'] + 1,
		'current_step'     => $next_step,
		'completed_steps'  => $completed_steps,
		'failed_step'      => '',
		'last_error_code'  => '',
		'result'           => $was_failed ? 'resumed' : 'started',
	);

	bhaivatech_storefront_save_starter_import_state( $state );
	return bhaivatech_storefront_get_starter_import_state();
}

/**
 * Record successful completion of exactly the current phase.
 *
 * @param string $step Completed phase.
 * @return array<string, mixed>|WP_Error
 */
function bhaivatech_storefront_complete_starter_import_step( string $step ) {
	$state = bhaivatech_storefront_get_starter_import_state();
	$steps = bhaivatech_storefront_starter_import_steps();

	if ( 'running' !== $state['status'] ) {
		return new WP_Error( 'starter_import_not_running', 'Starter import transaction is not running.' );
	}

	if ( ! bhaivatech_storefront_has_starter_import_lock() ) {
		return new WP_Error( 'starter_import_lock_missing', 'Starter import transaction lock is missing.' );
	}

	if ( $step !== $state['current_step'] || ! in_array( $step, $steps, true ) ) {
		return new WP_Error( 'starter_import_step_out_of_order', 'Starter import step was completed out of order.' );
	}

	$completed = array_values( array_unique( array_merge( (array) $state['completed_steps'], array( $step ) ) ) );
	$next      = $steps[ count( $completed ) ] ?? '';

	$state['completed_steps'] = $completed;
	$state['current_step']    = $next;
	$state['failed_step']     = '';
	$state['last_error_code'] = '';

	if ( '' === $next ) {
		$state['status'] = 'complete';
	}

	bhaivatech_storefront_save_starter_import_state( $state );

	if ( 'complete' === $state['status'] ) {
		bhaivatech_storefront_release_starter_import_lock();
	}

	return bhaivatech_storefront_get_starter_import_state();
}

/**
 * Fail the current phase using a bounded machine-readable code only.
 *
 * Customer content, exception traces and secrets must never be persisted here.
 *
 * @param string $step Current phase.
 * @param string $error_code Bounded diagnostic code.
 * @return array<string, mixed>|WP_Error
 */
function bhaivatech_storefront_fail_starter_import( string $step, string $error_code ) {
	$state = bhaivatech_storefront_get_starter_import_state();

	if ( 'running' !== $state['status'] || $step !== $state['current_step'] ) {
		return new WP_Error( 'starter_import_failure_out_of_order', 'Starter import failure does not match the current transaction step.' );
	}

	if ( ! bhaivatech_storefront_has_starter_import_lock() ) {
		return new WP_Error( 'starter_import_lock_missing', 'Starter import transaction lock is missing.' );
	}

	$error_code = sanitize_key( $error_code );
	if ( '' === $error_code ) {
		$error_code = 'unknown_failure';
	}

	$state['status']          = 'failed';
	$state['failed_step']     = $step;
	$state['last_error_code'] = $error_code;

	bhaivatech_storefront_save_starter_import_state( $state );
	bhaivatech_storefront_release_starter_import_lock();
	return bhaivatech_storefront_get_starter_import_state();
}

/**
 * Reset transaction state before any customer-facing importer is enabled.
 *
 * This function is intentionally not wired to an admin action yet.
 */
function bhaivatech_storefront_reset_starter_import_state(): void {
	delete_option( BHAIVATECH_STOREFRONT_STARTER_IMPORT_OPTION );
	bhaivatech_storefront_release_starter_import_lock();
}
