=== Genesis Product Updater ===
Contributors: dazzlesoftware
Requires at least: 5.5
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.7.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Self-hosted update feed manager for multiple products across multiple
platforms (Joomla, WordPress, and anything else you add).

== Description ==

Genesis Product Updater lets you manage one "Product" per item you ship (a Joomla
template, a WordPress plugin, etc.), record every version you've released
per platform, and generate the update feed file each platform's updater
expects:

* Joomla: `<updates>` XML, one `<update>` block per version/target row.
* WordPress: flat `{version, details_url, download_url}` JSON.

Files are written to a configurable folder (default: `/updates` at the site
root), grouped as `{output folder}/{product-slug}/{platform}.{ext}`, e.g.:

    /updates/g5_helium/joomla.xml
    /updates/g5_hydrogen/wordpress.json

Change the folder any time under **Genesis Product Updater > Settings**.

= Changelogs =

**Genesis Product Updater > Changelogs** is a separate screen from Products: pick
the product a changelog belongs to, then add version history rows (version
+ date + category — Addition/Change/Fix/Remove/Security/Deprecated/
Language/Note — + one item per line). Rows sharing a version are combined
into a single release entry, newest first, and written as:

    /updates/g5_helium/tpl_g5_helium_changelog.xml   (Joomla schema, matches tpl_g5_helium_changelog.xml)
    /updates/g5_hydrogen/wordpress_changelog.json     (structured entries + rendered HTML)

Two integrations happen automatically:
* If a product's `Changelog URL` row field is left blank, the main Joomla/
  WordPress update feed links straight to the generated changelog file.
* The WordPress update feed also embeds the changelog as HTML under
  `sections.changelog`, the field most self-hosted WP update checkers read.

Joomla files are also named with the standard extension-type prefix
(`tpl_`, `com_`, `mod_`, `plg_`, `lib_`, `pkg_`, `file_`, `lang_`) based on
the product's Type field. The WordPress feed additionally carries
`requires` / `tested` / `requires_php` / `last_updated` / `author` /
`homepage` when you fill in those (optional) row fields — the fields a
self-hosted WP updater typically needs to gate compatibility.

= Adding a new platform =

Platforms are plain PHP classes extending `Product_Updater_Platform_Base`
(`includes/platforms/class-genesis-product-updater-platform-base.php`... see
`class-genesis-product-updater-platform-joomla.php` for a full example). Drop a new class in
`includes/platforms/`, require it from the main plugin file, and register
it with the `product_updater_register_platforms` filter. It then automatically appears
in the product editor's platform dropdown and gets included whenever files
are (re)generated — no other code needs to change.

== Changelog ==

= 1.3.0 =
* New "Mobile (iOS & Android)" platform: one JSON feed per product shared by both apps (latest_version, build_number, url_ios, url_android, force_update, changelog), matching the current row marked "Current" (or highest version).
* New row fields Build Number / URL (iOS) / URL (Android) / Force Update / Release Notes, shown only for rows on a platform that opts in via Product_Updater_Platform_Base::supports_mobile_fields() (Requires/Tested/Requires PHP are hidden on those rows instead, since mobile releases don't gate on a platform-core version).

= 1.2.0 =
* The Requires/Tested up to/Requires PHP fields on each version row now show/hide and relabel per-row based on that row's Platform (e.g. "Requires Joomla Version" vs "Requires WordPress Version"). Platforms can opt out entirely via Product_Updater_Platform_Base::supports_compatibility_fields().

= 1.1.1 =
* Fix "Invalid post type" on the Products/Changelogs screens: both post type slugs (product_updater_product, product_updater_changelog) were over WordPress's 20-character limit, so register_post_type() silently refused to register them. Shortened to pu_product / pu_changelog.

= 1.1.0 =
* Row fields Requires/Tested/Requires PHP are no longer labeled WordPress-only; relabeled to Requires Platform Version / Tested up to Platform Version / Requires PHP.
* Joomla update feed now includes requires, tested, and php_minimum as custom elements when those row fields are filled in.

= 1.0.0 =
* Initial release.
