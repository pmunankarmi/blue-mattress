# Blue Mattress WordPress Theme

Custom bilingual WooCommerce theme for Blue Mattress.

## Theme structure

`functions.php` is intentionally only a bootstrap. Feature code lives in `inc/` and is grouped by responsibility:

- `bilingual.php` handles the current language, translated links, menus and translated WooCommerce text.
- `polylang-slugs.php` lets linked English and Arabic pages share a slug safely.
- `setup.php` registers theme support, assets, menus and the small set of theme-supplied pages.
- `acf.php` defines Theme Options and editable content fields.
- `woocommerce.php`, `shipping.php` and `paymob.php` contain storefront integrations.
- `media-assets.php` resolves images stored in the WordPress Media Library.
- `theme-updater.php` connects tagged GitHub releases to WordPress theme updates.
- `contact-submissions.php` stores and manages contact-form enquiries.

Page templates stay in the theme root, WooCommerce template overrides are under `woocommerce/`, front-end styles are under `assets/css/`, and browser code is under `assets/js/`.

## Bilingual page URLs

Arabic is the default language and English uses the `/en/` prefix. Linked pages use the same slug in both languages, for example:

- Arabic: `/our-story/`
- English: `/en/our-story/`

The shared-slug support is part of this theme in `inc/polylang-slugs.php`; do not install the standalone Polylang Slug plugin as well. The module is adapted from the GPL-licensed [Polylang Slug 0.2.3 project](https://github.com/grappler/polylang-slug), but is deliberately restricted to pages and public page lookups. It does not alter product, order, coupon or email behavior.

After a theme update, an administrator visit runs the one-time page-slug migration and refreshes rewrite rules. Old suffixed Arabic URLs recorded during that migration redirect permanently to their clean URL.

When adding a page programmatically, assign its Polylang language before changing it to a shared slug. Use `blue_get_page_by_slug_in_language()` when code must find one translation by slug.

## Required plugins

- WooCommerce
- Polylang Free
- Advanced Custom Fields Pro

Polylang for WooCommerce is not required. Products, variations, categories and attributes are shared commerce records; their Arabic display content comes from the theme's ACF fields. Do not create duplicate Arabic products.

## Site setup

1. Activate the required plugins and add Arabic and English in Polylang. Arabic should be the default language, with English under `/en/`.
2. Let WooCommerce create its Shop, Cart, Checkout and My Account pages. The theme links their Arabic translations during the next administrator request.
3. Create linked Arabic and English home pages, assign the **Home Page** template, and select the Arabic page as the static front page.
4. Review the theme-created Mattress Finder, Our Story, STARK and Contact pages and fill their ACF content.
5. Assign a Primary Menu and Footer Menu for each language. The theme intentionally has no hard-coded menu fallback.
6. Configure global copy, bilingual Store Notice text, media, contact details, legal links, analytics and checkout settings under **Theme Options**.

## Products and translations

Create each product once in WooCommerce. Prices, stock, variations, coupons, cart totals and orders always remain native WooCommerce data. The theme adds bilingual ACF fields for customer-facing product copy, category copy, attribute labels and option labels.

Use WooCommerce variations for mattress sizes and prices. Keep product, variation, category and attribute slugs stable because cart and order records depend on them. Arabic copy may fall back to English while content is being completed, but language-specific pages and menus should always be linked through Polylang.

Common storefront text lives in `languages/ui.json`. Update official WordPress and WooCommerce Arabic language packs for core strings; payment and shipping extensions may need their own translations.

## Maintenance guidelines

- Keep `functions.php` as a loader. Add feature code to the closest existing file under `inc/`, or create a clearly named module when the responsibility is new.
- Keep business data in WooCommerce and editable presentation content in ACF. Do not hard-code prices, stock, coupons, addresses or product IDs into templates.
- Prefer named functions for reusable hooks. Short closures are acceptable only when their purpose is obvious beside the hook.
- Preserve WooCommerce behavior when making visual changes. The theme does not replace or restyle WooCommerce emails; manage them through WooCommerce itself.
- Put third-party code in an isolated module, retain its attribution and narrow it to the exact behavior the theme needs.
- Test both languages, both color modes and a mobile viewport before tagging a release.

## Saudi Short Address lookup

Cart and Checkout can resolve an eight-character Saudi Short Address (for example, `JEZC7519`) through Google Maps and fill WooCommerce's standard address fields.

1. In Google Cloud, enable **Maps JavaScript API**, **Places API (New)**, and **Geocoding API**.
2. Create a browser API key and restrict its HTTP referrers to `https://blue-mattress.com/*` and `https://bluemattress.primedigital.dev/*`.
3. In WordPress, enter the key under **Theme Options → Checkout address → Google Maps browser API key**.

The key may instead be supplied as the `BLUE_GOOGLE_MAPS_API_KEY` constant in `wp-config.php`.

## Contact submissions

The Contact Page validates and sanitizes submissions, stores a private record under **Contact Submissions**, and sends a plain-text notification to the address configured in Theme Options. Production mail delivery still requires a properly configured SMTP or transactional mail service.

## Releasing an update

1. Update the version in `style.css` and `BLUE_THEME_VERSION` in `functions.php`.
2. Commit and push the changes to `master`.
3. Create and push a matching tag, for example `v2.7.18`.

GitHub Actions will publish an installable `blue-mattress.zip` release. WordPress checks the latest release automatically and displays it under **Dashboard → Updates** and **Appearance → Themes**.

Always install and test an update on staging before applying it to the live site.
