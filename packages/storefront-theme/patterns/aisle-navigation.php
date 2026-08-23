<?php
/**
 * Title: Aisle Navigation
 * Slug: storefront/aisle-navigation
 * Categories: storefront-grocery
 * Description: Numbered aisle navigation pattern for use in page content areas. For template composition use the aisle-rail template part.
 */
?>
<!-- wp:html -->
<nav class="storefront-aisle-rail" aria-label="<?php esc_attr_e( 'Browse aisles', 'bhaivatech-grocery-alpha' ); ?>">
	<ul class="storefront-aisle-rail__list" role="list">
		<?php
		$aisles = [
			'all'       => [ 'num' => '00', 'label' => __( 'All', 'bhaivatech-grocery-alpha' ), 'href' => '/shop' ],
			'produce'   => [ 'num' => '01', 'label' => __( 'Produce', 'bhaivatech-grocery-alpha' ), 'href' => '/product-category/produce' ],
			'dairy'     => [ 'num' => '02', 'label' => __( 'Dairy', 'bhaivatech-grocery-alpha' ), 'href' => '/product-category/dairy' ],
			'bakery'    => [ 'num' => '03', 'label' => __( 'Bakery', 'bhaivatech-grocery-alpha' ), 'href' => '/product-category/bakery' ],
			'staples'   => [ 'num' => '04', 'label' => __( 'Staples', 'bhaivatech-grocery-alpha' ), 'href' => '/product-category/staples' ],
			'snacks'    => [ 'num' => '05', 'label' => __( 'Snacks', 'bhaivatech-grocery-alpha' ), 'href' => '/product-category/snacks' ],
			'household' => [ 'num' => '06', 'label' => __( 'Household', 'bhaivatech-grocery-alpha' ), 'href' => '/product-category/household' ],
		];

		foreach ( $aisles as $slug => $aisle ) {
			printf(
				'<li class="storefront-aisle-rail__item"><a href="%s" class="storefront-aisle-rail__link" data-aisle="%s"><span class="storefront-aisle-rail__num">%s</span><span class="storefront-aisle-rail__name">%s</span></a></li>',
				esc_url( $aisle['href'] ),
				esc_attr( $slug ),
				esc_html( $aisle['num'] ),
				esc_html( $aisle['label'] )
			);
		}
		?>
	</ul>
</nav>
<!-- /wp:html -->
