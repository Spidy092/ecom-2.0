# Compatibility evidence policy

Status: engineering gate

A platform combination is customer-supportable only when all of the following are true on the same commit:

1. `release/compatibility-matrix.json` contains the exact WordPress, WooCommerce and PHP row.
2. The matrix workflow builds the generated Theme/Core customer ZIPs.
3. Those ZIPs install and activate on the exact row.
4. Runtime assertions confirm the exact WordPress, WooCommerce and PHP versions.
5. WooCommerce remains active and the expected Theme/Core are active.
6. HPOS is enabled in the clean new-store test environment.
7. Woo Store API products responds successfully.
8. Storefront Core serviceability REST responds without a server error.
9. The storefront returns HTTP 200.
10. The reference row additionally passes the full engineering regression suite.

Passing one row never implies support for a neighboring patch/minor/major version. When an upstream release ships, the support table changes only after a new exact row passes.

## Evidence freshness

Version choices must be rechecked against official upstream release/security sources before changing the matrix. A plugin-directory `Tested up to` value is compatibility metadata, not proof that our package passed the referenced WordPress release.

As of 2026-08-21, the proposed V1 support window is WordPress 7.1 and 7.0.4, WooCommerce 11.0.1, and PHP 8.3/8.4. WordPress 7.1 is the latest stable release from August 19, 2026; WordPress 7.0.4 is the current 7.0 security line. WooCommerce 11.0.1 is the current 11.0 patch observed from WordPress.org plugin metadata and includes a WordPress 7.1 compatibility fix.

WordPress 6.9 remains the package installation floor but is not an advertised V1 support row in this gate. PHP 8.5, WooCommerce 10.x and third-party extension/page-builder compatibility remain outside the V1 claim until exact product evidence is added.
