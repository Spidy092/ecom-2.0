<?php
/**
 * Title: Grovia basket brief hero
 * Slug: storefront/grovia-home-hero
 * Categories: storefront-grocery
 * Description: Split grocery-first homepage hero with local demo imagery and direct shopping actions.
 */

$milk_image  = get_theme_file_uri( 'assets/demo/milk.webp' );
$bread_image = get_theme_file_uri( 'assets/demo/bread.webp' );
?>
<!-- wp:html -->
<section class="grovia-hero" aria-labelledby="grovia-hero-heading">
	<div class="grovia-hero__copy">
		<p class="grovia-kicker">Modern Grocery / 01</p>
		<h1 id="grovia-hero-heading">Build your basket without losing your place.</h1>
		<p class="grovia-hero__lede">Search, scan, and adjust everyday groceries in one calm workspace. Delivery context stays visible before the basket grows.</p>
		<div class="grovia-hero__actions" aria-label="Primary shopping actions">
			<a class="grovia-hero__primary" href="/shop">Shop the full aisle<span aria-hidden="true">→</span></a>
			<a class="grovia-hero__secondary" href="#grovia-delivery-checker">Check delivery availability</a>
		</div>
		<ul class="grovia-hero__signals" aria-label="Grovia shopping signals">
			<li><strong>01</strong><span>Search-first discovery</span></li>
			<li><strong>02</strong><span>Current stock and price</span></li>
			<li><strong>03</strong><span>Quantity controls on the shelf</span></li>
		</ul>
	</div>
	<div class="grovia-hero__visual" aria-label="Basket brief">
		<div class="grovia-hero__image grovia-hero__image--main">
			<img src="<?php echo esc_url( $milk_image ); ?>" alt="Farm fresh milk" width="640" height="640" fetchpriority="high" />
		</div>
		<div class="grovia-hero__image grovia-hero__image--secondary">
			<img src="<?php echo esc_url( $bread_image ); ?>" alt="Sourdough bread" width="640" height="640" loading="lazy" />
		</div>
		<div class="grovia-hero__brief">
			<p class="grovia-kicker">Basket brief</p>
			<strong>Fresh staples, ready to add.</strong>
			<span>Live stock · current prices · postcode-aware delivery</span>
		</div>
	</div>
</section>
<!-- /wp:html -->
