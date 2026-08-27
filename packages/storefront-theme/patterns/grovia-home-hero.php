<?php
/**
 * Title: Grovia Fresh Dispatch hero
 * Slug: storefront/grovia-home-hero
 * Categories: storefront-grocery
 * Description: Search-led grocery hero with an original local market-crate image.
 *
 * @package StorefrontTheme
 */

$hero_image = get_theme_file_uri( 'assets/images/grovia-market-crate.webp' );
?>
<!-- wp:html -->
<section class="grovia-hero" aria-labelledby="grovia-hero-heading">
	<div class="grovia-hero__aisles" aria-label="Featured aisles">
		<a href="/product-category/grovia-produce"><strong>01</strong><span>Produce</span></a>
		<a href="/product-category/grovia-dairy"><strong>02</strong><span>Dairy</span></a>
		<a href="/product-category/grovia-bakery"><strong>03</strong><span>Bakery</span></a>
		<a href="/product-category/grovia-pantry"><strong>04</strong><span>Pantry</span></a>
	</div>
	<div class="grovia-hero__copy">
		<h1 id="grovia-hero-heading">Groceries,<br>without the runaround.</h1>
		<p>Search every aisle, see current stock, and build the weekly basket without losing your place.</p>
		<a class="grovia-hero__primary" href="/shop">Shop the market <span aria-hidden="true">→</span></a>
	</div>
	<div class="grovia-hero__visual">
		<img src="<?php echo esc_url( $hero_image ); ?>" alt="A red market crate filled with tomatoes, bananas, milk, bread, spinach, lettuce, and broccoli" width="1672" height="941" fetchpriority="high" />
	</div>
</section>
<!-- /wp:html -->
