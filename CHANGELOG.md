# Changelog

## 2.8.0

- Added a resumable Theme Media migration that imports all bundled images and videos into the WordPress Media Library.
- Resolved logos, payment badges, page fallbacks and mattress cutaway videos from Media Library attachment IDs after migration.
- Removed the CSS-level hero image dependency so page media remains fully dynamic.

## 2.7.41

- Kept Arabic Paymob gateway, place-order and terms labels localized after WooCommerce AJAX refreshes.
- Localized the empty Paymob Saved Cards message while leaving the secure provider iframe untouched.

## 2.7.40

- Added clean Arabic routing for Paymob's Saved Cards account endpoint.
- Localized the Paymob account label, gateway title, checkout button and terms agreement, including its translated terms link.

## 2.7.39

- Redirected legacy Arabic page slugs, including WooCommerce endpoints, to their clean canonical URLs.

## 2.7.38

- Fixed the Arabic footer terms link so it resolves to the translated WooCommerce terms page.
- Contained decorative cart and checkout glows within their cards to remove RTL horizontal overflow.

## 2.7.37

- Made translated WooCommerce core pages resolve as Shop, Cart, Checkout and My Account so their Arabic layouts load the correct theme templates and styles.
- Added clean canonical Arabic routes for every Polylang page translation without exposing internal `-ar` or localized database slugs.
- Added clean Arabic Shop pagination plus Checkout and My Account endpoint routes.
- Removed off-screen skip-link and contact honeypot positioning that caused extreme horizontal overflow on RTL pages.

## 2.7.36

- Squared the embedded Paymob card frame's bottom corners so it joins the cardholder-name row cleanly.
- Added the missing right-edge border to the card number, expiry and CVV frame.
- Added a rate-limited automatic update refresh during normal WordPress admin use, so new GitHub releases appear without pressing **Check again**.

## 2.7.35

- Reliably removed Paymob's payment-method label and Card selector even when its SDK renders duplicate Card choices.
- Added 20px inline padding around the complete Card Information field group to match the checkout reference.

## 2.7.34

- Removed Paymob's redundant payment-method selector when Card is the only available embedded method.
- Clipped the embedded card-details frame to remove the white top-corner artifact.

## 2.7.33

- Changed product size-option cards to display each variation's regular price instead of its discounted price.
- Kept the selected discounted price in the main price area and add-to-cart button.

## 2.7.32

- Restyled the single-product sale badge as the compact navy pill used by the reference design.
- Moved the sale badge fully inside the main image, added RTL-safe positioning and removed WooCommerce's exclamation mark.

## 2.7.31

- Fixed the WooCommerce empty-cart icon glyph overlapping the message text.
- Added a responsive, centered empty-cart panel with a consistent theme icon and tighter return-button spacing.
- Made the product add-to-cart button show only the selected variation's payable price instead of repeating the crossed-out regular price.

## 2.7.30

- Made GitHub theme updates appear even when the host's WordPress.org theme check fails before custom providers run.
- Added an authenticated, iframe-safe WordPress page for the View version details link.
- Added Blue Mattress metadata support for WordPress's standard theme information API.
- Reset the release cache schema so the repaired updater starts with fresh GitHub metadata.

## 2.7.29

- Added optional ACF image uploads for homepage benefit and social icons.
- Existing preset icon dropdowns remain the automatic fallback when no image is selected.
- Added preset previews in the ACF editor and native uploaded-image previews.
- Applied custom social icons consistently in the footer, contact page and floating WhatsApp button.

## 2.7.28

- Fixed WooCommerce clearfix pseudo-elements occupying cells in the My Account address grid.
- Billing and Shipping addresses now render as an aligned, equal-width row on desktop and stack cleanly on mobile.

## 2.7.27

- Removed WooCommerce's purple dismissible Store Notice overlay after its frontend hooks are registered.
- Added a defensive style fallback so only the custom slim navy header notice is visible.
- Published as a follow-up release for verifying the repaired WordPress theme updater on live.

## 2.7.26

- Replaced the legacy ACF announcement with WooCommerce's Store Notice setting.
- Styled the notice as the requested slim navy delivery bar and added the default Arabic equivalent.
- Added WordPress's supported custom Update URI response so GitHub releases appear reliably on every installation.
- Added a public repository fallback for hosts that cannot reach or are rate-limited by the GitHub API.

## 2.7.25

- Fixed the reversed desktop My Account columns.
- The account navigation now stays in the narrow start-side column and account content uses the wide column.
- Preserved the mirrored RTL layout and single-column mobile layout.

## 2.7.24

- Added an optional mobile-specific homepage hero banner image.
- Phones use the mobile banner instead of the desktop image or video when the mobile field is populated.
- Mobile falls back automatically to the existing desktop hero when no mobile image is selected.

## 2.7.23

- Added a Homepage Content toggle for showing or hiding the hero banner caption.
- Refined every WooCommerce My Account endpoint, including orders, downloads, addresses, account details, saved-card empty states and individual order details.
- Added responsive account form grids, address cards, mobile order tables and dark-mode-compatible Select2 controls.

## 2.7.22

- Added an editable homepage hero banner image.
- The hero video is now optional; when empty, the banner image is rendered instead.
- The banner image also remains underneath a configured video as its loading and playback fallback.

## 2.7.21

- Redesigned the WooCommerce order-received summary and order details.
- Placed Billing and Shipping addresses in one responsive row, with Shipping on the right.
- Added mobile stacking and dark-theme support for the confirmation layout.

## 2.7.20

- Redesigned checkout billing and shipping addresses as responsive two-column forms.
- Matched the My Account address editor to the same rounded field and card styling.

## 2.7.19

- Constrained the dynamic footer logo with a responsive maximum height.
- Restyled WooCommerce success, information and error bars for light and dark themes.
- Rounded and aligned the notice action button.
- Removed the newsletter email placeholder and its unused ACF settings.
- Added immediate GitHub release refresh support from WordPress **Check again**.

## 2.7.18

- Replaced the legacy non-WordPress repository contents with the Blue Mattress WooCommerce theme.
- Added GitHub-tagged release ZIP generation.
- Added native WordPress theme update checks using GitHub releases.
- Included the Paymob checkout and duplicate Card-option fixes from version 2.7.17.
