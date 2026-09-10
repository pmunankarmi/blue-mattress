# Blue Mattress WordPress Theme

Custom bilingual WooCommerce theme for Blue Mattress.

## Releasing an update

1. Update the version in `style.css` and `BLUE_THEME_VERSION` in `functions.php`.
2. Commit and push the changes to `master`.
3. Create and push a matching tag, for example `v2.7.18`.

GitHub Actions will publish an installable `blue-mattress.zip` release. WordPress checks the latest release automatically and displays it under **Dashboard → Updates** and **Appearance → Themes**.

Always install and test an update on staging before applying it to the live site.
