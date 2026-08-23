# Genesis Product Updater

Genesis Product Updater is maintained as two CMS implementations with the same features and generated-feed behavior. Changes to shared functionality should normally be applied to both versions at the same time.

## Source folders

### Joomla

Source: [`Joomla/com_genesisproductupdater`](Joomla/com_genesisproductupdater)

- Joomla component identifier: `com_genesisproductupdater`
- PHP namespace: `Joomla\Component\Genesisproductupdater`
- Language prefix: `COM_GENESISPRODUCTUPDATER`
- Install package format: `com_genesisproductupdater-{version}.zip`

The Joomla component provides Products, Categories, Changelogs, generated update feeds, download statistics, and a configurable Downloads menu item.

### WordPress

Source: [`Wordpress/genesis-product-updater`](Wordpress/genesis-product-updater)

- Plugin folder and slug: `genesis-product-updater`
- Main plugin file: `genesis-product-updater.php`
- Text domain: `genesis-product-updater`
- Install package format: `genesis-product-updater-{version}.zip`

The WordPress plugin provides the matching Products, Categories, Changelogs, generated update feeds, download statistics, and Downloads page shortcode.

## Keeping both versions aligned

The Joomla and WordPress administration interfaces use their CMS-native APIs, but their product fields and output behavior should remain equivalent. When changing a shared feature, review both implementations for:

- Platforms and product types
- Product and version fields
- Categories and filtered Downloads pages
- Changelog records, generated files, and changelog URLs
- Joomla XML and JSON feed fields
- WordPress, Mobile, and Fab JSON feed fields
- Preview images, download counts, file sizes, and store links
- Downloads-page button styling and Bootstrap support
- Validation, language strings, documentation, and version numbers

Platform-specific behavior can differ where required by Joomla or WordPress, but generated data should use the same field names and values wherever the formats allow it.

## Current release

Current package version: **1.5.0**

Both packages are authored by [Dazzle Software](https://dazzlesoftware.org), Copyright (C) 2026 Dazzle Software, LLC, and licensed under GNU/GPLv3 and later.
