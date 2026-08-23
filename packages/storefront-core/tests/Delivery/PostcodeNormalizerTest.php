<?php
/**
 * Unit tests for PostcodeNormalizer.
 *
 * Pure PHP — no WordPress stubs required.
 *
 * @package StorefrontCore\Tests\Delivery
 */

declare( strict_types=1 );

namespace StorefrontCore\Tests\Delivery;

use PHPUnit\Framework\TestCase;
use StorefrontCore\Delivery\PostcodeNormalizer;

final class PostcodeNormalizerTest extends TestCase {

	private PostcodeNormalizer $normalizer;

	protected function setUp(): void {
		$this->normalizer = new PostcodeNormalizer();
	}

	/** @test */
	public function it_normalizes_a_simple_numeric_postcode(): void {
		$this->assertSame( '560001', $this->normalizer->normalize( '560001' ) );
	}

	/** @test */
	public function it_strips_leading_and_trailing_whitespace(): void {
		$this->assertSame( '560001', $this->normalizer->normalize( '  560001  ' ) );
	}

	/** @test */
	public function it_collapses_internal_whitespace(): void {
		// UK-style postcode with a space.
		$this->assertSame( 'SW1A2AA', $this->normalizer->normalize( 'SW1A 2AA' ) );
	}

	/** @test */
	public function it_uppercases_lowercase_input(): void {
		$this->assertSame( 'SW1A2AA', $this->normalizer->normalize( 'sw1a2aa' ) );
	}

	/** @test */
	public function it_accepts_hyphens(): void {
		$this->assertSame( '12345-6789', $this->normalizer->normalize( '12345-6789' ) );
	}

	/** @test */
	public function it_returns_null_for_input_exceeding_max_length(): void {
		$this->assertNull( $this->normalizer->normalize( '12345678901' ) ); // 11 chars.
	}

	/** @test */
	public function it_returns_null_for_empty_input(): void {
		$this->assertNull( $this->normalizer->normalize( '' ) );
		$this->assertNull( $this->normalizer->normalize( '   ' ) );
	}

	/** @test */
	public function it_returns_null_for_input_with_special_characters(): void {
		$this->assertNull( $this->normalizer->normalize( '560001!' ) );
		$this->assertNull( $this->normalizer->normalize( '560 001@' ) );
	}

	/** @test */
	public function it_returns_null_for_non_ascii_input(): void {
		$this->assertNull( $this->normalizer->normalize( '５６０００１' ) ); // Full-width digits.
	}

	/** @test */
	public function it_accepts_a_ten_character_postcode_at_max_boundary(): void {
		$this->assertSame( '1234567890', $this->normalizer->normalize( '1234567890' ) );
	}

	/** @test */
	public function it_returns_null_for_eleven_characters(): void {
		$this->assertNull( $this->normalizer->normalize( '12345678901' ) );
	}
}
