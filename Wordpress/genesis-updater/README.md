# Genesis Updater for WordPress

Genesis Updater manages self-hosted update feeds for Joomla extensions, WordPress plugins and themes, and mobile applications from the WordPress administrator. It can also publish a styled Downloads page with changelog popups, download counts, file sizes, preview images, and mobile store links.

> This project is currently a work in progress.

## Installation

1. Copy the `genesis-updater` directory to `wp-content/plugins/`, or install a ZIP of the directory from **Plugins > Add New > Upload Plugin**.
2. Activate **Genesis Updater**.
3. Open **Genesis Updater > Settings**.
4. Configure the generated-file output folder and, if required, its public base URL.

The default relative output folder is `updates`, beneath the WordPress installation. The web server must be able to write to the selected location.

## Create a product

1. Open **Genesis Updater > Products > Add New Product**.
2. Enter the product title and Element/Slug.
3. Select one Platform. A product belongs to one platform only.
4. Select the Product Type. The choices and version fields change to match the selected platform.
5. Optionally add a description, maintainer details, preview image, Downloads category, and an imported starting download count.
6. Add at least one version row and mark the current release.
7. Publish or update the product.

Supported bundled platforms are:

- **Joomla:** component, module, plugin, template, library, package, file, and language.
- **WordPress:** plugin, theme, and custom.
- **Mobile:** mobile application releases with iOS and Android store details.
- **Fab:** Unreal Engine marketplace products with a Fab URL.

## Version rows

Common fields include the release version, current-release flag, download URL, release date, changelog selection, and release notes. Platform-specific fields are shown only when relevant.

- Joomla and WordPress releases can include compatibility requirements, information URLs, hashes, and downloadable package URLs.
- Mobile releases can include build number, Force Update, App Store URL, Google Play URL, and mobile release notes.

Use only one current version per product. If none is explicitly selected, the plugin uses the highest version when displaying the product.

## Changelogs

1. Open **Genesis Updater > Changelogs** and create a changelog.
2. Link it to a product.
3. Add one row for each version and change category combination.
4. In the product version row, choose the generated changelog option.

Rows sharing the same version are combined into one release. On a Downloads page, the Changelog button opens the CMS changelog in a popup instead of displaying the generated XML or JSON file.

## Generated update files

Saving supported content generates the appropriate update files in the configured output folder. Use **Genesis Updater > Settings > Regenerate All Files Now** after changing the output location or when rebuilding every feed.

Files are grouped as `updates/{platform}/{product-element}/{filename}`, for example `updates/joomla/g5_helium/tpl_g5_helium.xml`.

The public base URL must point to the web-accessible form of the output folder. Set it explicitly when the files are served from a CDN, another domain, or a filesystem location that cannot be derived from the WordPress URL.

## Downloads page

Create a normal WordPress page and add:

```text
[genesis_updater_downloads]
```

This displays every non-empty Downloads category. Optional `title` and `intro` attributes add a header:

```text
[genesis_updater_downloads title="Downloads" intro="Get the latest releases."]
```

To make a page for one category, use its category slug:

```text
[genesis_updater_downloads category="mobile-apps"]
```

You can create multiple WordPress pages with different category slugs.

### Product cards

Published products with a current version appear on the page. A product card can contain:

- Product title and optional preview image.
- Current version, release date, and description.
- Download and Changelog buttons.
- Package file size and tracked download total.
- App Store and Google Play buttons for Mobile products.

The Download button passes through the plugin's tracking endpoint before redirecting to the package. Mobile store buttons link directly to their respective stores and are not counted as package downloads.

## Download statistics and migration

The product editor contains a **Download Count** field. Enter the total from an older system before launch or migration. New tracked downloads continue from that number.

The file size is obtained from the current package URL when possible. The remote server must provide a `Content-Length` response for a size to appear.

## Styling

Open **Genesis Updater > Settings** to configure:

- Download button background and text colors.
- Download button hover background and hover text colors.
- App Store normal and hover background/text colors.
- Google Play normal and hover background/text colors.
- Button corner radius and icons.
- Optional Bootstrap classes for Bootstrap-enabled themes.

Configured text colors also apply to button icons. The plugin's button state rules take precedence over generic theme link colors.

## Updating a release

For a normal release workflow:

1. Add the new product version row.
2. Mark it current and clear the old current flag.
3. Update its download URL, compatibility data, release date, and other platform fields.
4. Add the matching changelog entries.
5. Save the changelog and product.
6. Confirm the generated files and Downloads page before announcing the release.

## Troubleshooting

- **Files are not generated:** confirm the output directory exists or can be created and is writable by PHP.
- **Generated URLs are incorrect:** set the Public Base URL explicitly in Settings.
- **A product is missing from Downloads:** publish it, assign a Downloads category, and add a usable current version.
- **A filtered page is empty:** use the category slug, not its display name.
- **No package size appears:** confirm the package URL is reachable and returns `Content-Length`.
- **Old button styles remain:** clear page/cache plugins and perform a hard browser refresh.

## Version

Current package version: **1.8.1**.

## Joomla extension-set bundles

Open **Genesis Updater > Bundles** to group products into platform-specific indexes. Joomla bundles generate `updates/joomla/{bundle-slug}/list.xml`; WordPress, Mobile, and Fab bundles generate `updates/{platform}/{bundle-slug}/list.json`. Select one platform per bundle, then select products from that platform. Each entry links to the product's individual generated feed.
