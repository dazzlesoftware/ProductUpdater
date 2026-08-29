# Genesis Updater

Genesis Updater is maintained as two CMS implementations with the same features and generated-feed behavior. Changes to shared functionality should normally be applied to both versions at the same time.

## Source folders

### Joomla

Source: [`Joomla/com_genesisupdater`](Joomla/com_genesisupdater)

- Joomla component identifier: `com_genesisupdater`
- PHP namespace: `Joomla\Component\Genesisupdater`
- Language prefix: `COM_GENESISUPDATER`
- Install package format: `com_genesisupdater-{version}.zip`

The Joomla component provides Products, Categories, Changelogs, generated update feeds, download statistics, and a configurable Downloads menu item.

### WordPress

Source: [`Wordpress/genesis-updater`](Wordpress/genesis-updater)

- Plugin folder and slug: `genesis-updater`
- Main plugin file: `genesis-updater.php`
- Text domain: `genesis-updater`
- Install package format: `genesis-updater-{version}.zip`

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

Current package version: **1.8.1**

Both packages are authored by [Dazzle Software](https://dazzlesoftware.org), Copyright (C) 2026 Dazzle Software, LLC, and licensed under GNU/GPLv3 and later.
