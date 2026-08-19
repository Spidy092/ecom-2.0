<?php
/**
 * Seed a deterministic administrator used only by setup/status E2E coverage.
 */

$login    = 'alpha-setup-admin';
$password = 'alpha-setup-pass';
$user_id  = username_exists( $login );

if ( ! $user_id ) {
	$user_id = wp_create_user( $login, $password, $login . '@example.test' );
}

if ( is_wp_error( $user_id ) ) {
	WP_CLI::error( 'Could not create setup/status E2E administrator.' );
}

wp_set_password( $password, (int) $user_id );
$user = new WP_User( (int) $user_id );
$user->set_role( 'administrator' );

WP_CLI::success( 'Seeded setup/status E2E administrator.' );
