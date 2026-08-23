# Genesis Product Updater for Joomla

Genesis Product Updater is a Joomla administrator component for managing self-hosted update feeds for Joomla extensions, WordPress plugins and themes, and mobile applications. It also provides a normal Joomla Downloads menu item with styled product cards, changelog popups, download statistics, preview images, and mobile store links.

> This project is currently a work in progress.

## Installation

1. Create or obtain an installable ZIP containing the component package.
2. In Joomla Administrator, open **System > Install > Extensions** and install the ZIP.
3. Open **Components > Genesis Product Updater**.
4. Select **Options** and configure the generated-file output path and public base URL.

The default relative output folder is `updates`, beneath the Joomla installation. The web server must be able to write to the selected location.

## Create a product

1. Open **Components > Genesis Product Updater > Products** and select **New**.
2. Enter the title and Element/Slug.
3. Select one Platform. A product belongs to one platform only.
4. Select the Product Type. Choices and version fields change to match the platform.
5. Optionally add a description, maintainer details, preview image, Download Category, and an imported starting download count.
6. Add at least one version row and mark the current release.
7. Set the product to Published and save it.

Supported bundled platforms are:

- **Joomla:** component, module, plugin, template, library, package, file, and language.
- **WordPress:** plugin, theme, and custom.
- **Mobile:** mobile application releases with iOS and Android store details.
- **Fab:** Unreal Engine marketplace products with a Fab URL.

## Version rows

Common fields include the release version, current-release flag, download URL, release date, changelog selection, and release notes. Platform-specific fields are enabled only when relevant.

- Joomla and WordPress releases can include compatibility requirements, information URLs, hashes, and downloadable package URLs.
- Mobile releases can include build number, Force Update, App Store URL, Google Play URL, and mobile release notes.

Use only one current version per product. If none is explicitly selected, the component uses the highest version when displaying the product.

## Changelogs

1. Open **Components > Genesis Product Updater > Changelogs** and select **New**.
2. Link the changelog to a product.
3. Add one row for each version and change category combination.
4. In the product version row, choose the generated changelog option.
5. Publish and save the changelog.

Rows sharing the same version are combined into one release. The Downloads page uses these CMS records in a popup; it does not show visitors the generated XML or JSON document.

## Generated update files

Saving supported content generates platform-specific update files in the configured output folder. The public base URL must point to the web-accessible form of that folder. Configure it explicitly when files are served from another domain, a CDN, or a path Joomla cannot derive automatically.

Files are grouped as `updates/{platform}/{product-element}/{filename}`, for example `updates/joomla/g5_helium/tpl_g5_helium.xml`.

## Create a Downloads page

1. Open **Menus** and choose the required site menu.
2. Create a new menu item.
3. Set **Menu Item Type** to **Genesis Product Updater > Downloads**.
4. Enter the page title and save the menu item.

By default, the page shows all non-empty product categories.

### Make a category-specific page

In the Downloads menu item settings, set **Download Category** to one category. Leave it on **All Categories** for the combined page.

Create additional menu items with different category choices to publish separate Downloads pages for Joomla products, mobile applications, launchers, or any other category in your product data.

### Product cards

Published products with a current version appear on the page. A product card can contain:

- Product title and optional preview image.
- Current version, release date, and description.
- Download and Changelog buttons.
- Package file size and tracked download total.
- App Store and Google Play buttons for Mobile products.

The Download button passes through the component's tracking task before redirecting to the package. Mobile store buttons link directly to their respective stores and are not counted as package downloads.

## Download statistics and migration

The product editor contains a **Download Count** field. Enter the total from an older extension before migration. New tracked package downloads continue from that number.

The component displays a file size when it can determine the size of the current package. The remote package host must expose the required size information.

## Styling

Open **Components > Genesis Product Updater > Options** and use the Downloads settings to configure:

- Download button background and text colors.
- Download button hover background and hover text colors.
- App Store normal and hover background/text colors.
- Google Play normal and hover background/text colors.
- Button corner radius and icons.
- Optional Bootstrap styling for Bootstrap-enabled templates.

Configured text colors also apply to button icons. The component's button state rules take precedence over generic template link colors.

## Updating a release

For a normal release workflow:

1. Add the new product version row.
2. Mark it current and clear the old current flag.
3. Update its package or store URLs, compatibility data, release date, and other platform fields.
4. Add the matching changelog entries.
5. Save the changelog and product.
6. Confirm the generated files and Downloads page before announcing the release.

## Troubleshooting

- **Files are not generated:** confirm the output directory exists or can be created and is writable by PHP.
- **Generated URLs are incorrect:** set the Public Base URL explicitly in component Options.
- **A product is missing from Downloads:** publish it, provide a Download Category, and add a usable current version.
- **A category is absent from the menu setting:** save at least one product with that exact Download Category, then reopen the menu item.
- **No package size appears:** confirm the current package URL is reachable and exposes its size.
- **Old button styles remain:** clear Joomla/template caches and perform a hard browser refresh.

## Version

Current package version: **1.5.1**.
