<?php
/**
 * WP-CLI entry point for the disposable Grovia demo.
 *
 * @package StorefrontCore
 */

namespace StorefrontCore\Demo;

defined( 'ABSPATH' ) || exit;

final class Command {

	/**
	 * Register the `wp grovia seed-demo` command.
	 */
	public static function register(): void {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			call_user_func( array( 'WP_CLI', 'add_command' ), 'grovia seed-demo', array( self::class, 'seed_demo' ) );
		}
	}

	/**
	 * Seed the deterministic Modern Grocery demo.
	 *
	 * ## OPTIONS
	 *
	 * [--reset]
	 * : Remove only records previously marked as Grovia demo content.
	 *
	 * ## EXAMPLES
	 *
	 *     wp grovia seed-demo
	 *     wp grovia seed-demo --reset
	 *
	 * @param array<string,mixed> $args Positional arguments.
	 * @param array<string,mixed> $assoc_args Associative arguments.
	 */
	public static function seed_demo( array $args, array $assoc_args ): void {
		if ( ! empty( $args ) ) {
			call_user_func( array( 'WP_CLI', 'error' ), 'seed-demo does not accept positional arguments.' );
			return;
		}

		try {
			$result = ( new DemoSeeder() )->seed( ! empty( $assoc_args['reset'] ) );
		} catch ( \Throwable $exception ) {
			call_user_func( array( 'WP_CLI', 'error' ), $exception->getMessage() );
			return;
		}

		call_user_func(
			array( 'WP_CLI', 'success' ),
			sprintf(
				'Seeded Grovia demo: %d products, %d departments, %d pages.',
				$result['products'],
				$result['categories'],
				$result['pages']
			)
		);
	}
}
