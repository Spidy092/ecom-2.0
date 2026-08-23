# Disposable WordPress validation

This folder contains a WordPress Playground Blueprint for a fresh WooCommerce environment with the internal Modern Grocery theme/Core packages mounted from the repository.

From the repository root:

```bash
npx @wp-playground/cli@latest server \
  --port=9400 \
  --blueprint=tools/playground/blueprint.json \
  --mount-before-install="$PWD/packages/storefront-core:/wordpress/wp-content/plugins/bhaivatech-storefront-core" \
  --mount-before-install="$PWD/packages/storefront-theme:/wordpress/wp-content/themes/bhaivatech-grocery-alpha" \
  --login
```

The environment is disposable and SQLite-backed. It creates sample products and pages only for local validation; it must never be pointed at production data.

Expected manual/browser checks:

- the Modern Grocery home renders without a PHP fatal;
- delivery checker returns available for `560001` and unavailable for an unknown postcode;
- the Shop page renders WooCommerce products and cart links;
- a signed-in user can save/remove a product from Shopping List;
- Buy Again is empty before an order and does not expose another user's order data;
- WooCommerce Cart/Checkout Blocks remain the checkout authority;
- WooCommerce > Storefront setup is visible only to users with `manage_woocommerce`.
