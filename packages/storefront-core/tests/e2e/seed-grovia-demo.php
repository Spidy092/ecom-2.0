<?php
/**
 * Thin E2E wrapper for the reusable Grovia demo seeder.
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit;
}

WP_CLI::runcommand( 'grovia seed-demo --reset' );
