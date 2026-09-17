# Blue Mattress WordPress Theme

Custom bilingual WooCommerce theme for Blue Mattress.

## Saudi Short Address lookup

Cart and Checkout can resolve an eight-character Saudi Short Address (for example, `JEZC7519`) through Google Maps and fill WooCommerce's standard address fields.

1. In Google Cloud, enable **Maps JavaScript API**, **Places API (New)**, and **Geocoding API**.
2. Create a browser API key and restrict its HTTP referrers to `https://blue-mattress.com/*` and `https://bluemattress.primedigital.dev/*`.
3. In WordPress, enter the key under **Theme Options → Checkout address → Google Maps browser API key**.

The key may instead be supplied as the `BLUE_GOOGLE_MAPS_API_KEY` constant in `wp-config.php`.

## Releasing an update

1. Update the version in `style.css` and `BLUE_THEME_VERSION` in `functions.php`.
2. Commit and push the changes to `master`.
3. Create and push a matching tag, for example `v2.7.18`.

GitHub Actions will publish an installable `blue-mattress.zip` release. WordPress checks the latest release automatically and displays it under **Dashboard → Updates** and **Appearance → Themes**.

Always install and test an update on staging before applying it to the live site.
