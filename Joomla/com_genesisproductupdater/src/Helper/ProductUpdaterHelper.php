<?php
/**
 * @package   Genesis Product Updater
 * @author    Dazzle Software https://dazzlesoftware.org
 * @copyright Copyright (C) 2026 Dazzle Software, LLC
 * @license   GNU/GPLv3 and later
 */

namespace Joomla\Component\Genesisproductupdater\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\Component\Genesisproductupdater\Administrator\Platform\PlatformRegistry;
use Joomla\Component\Genesisproductupdater\Administrator\Platform\PlatformInterface;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;

/**
 * Orchestrates generation of static update-feed files for products and
 * changelogs, delegating the actual per-platform content generation to
 * whichever PlatformInterface implementations are registered.
 *
 * This is the functional replacement for the WordPress plugin's
 * "generate files to disk" behaviour.
 */
class ProductUpdaterHelper
{
    /**
     * @var  PlatformRegistry|null
     */
    private static ?PlatformRegistry $registry = null;

    /**
     * Returns the shared platform registry instance.
     *
     * @return  PlatformRegistry
     */
    public static function getRegistry(): PlatformRegistry
    {
        if (self::$registry === null) {
            self::$registry = new PlatformRegistry();
        }

        return self::$registry;
    }

    /**
     * Resolves the absolute base output directory from the component
     * configuration. A relative "output_path" resolves under JPATH_ROOT,
     * an absolute path is used as-is - mirrors the original WP plugin's
     * flexibility for where generated files are written.
     *
     * @return  string  Absolute path, no trailing slash.
     */
    public static function getOutputBasePath(): string
    {
        $params = ComponentHelper::getParams('com_genesisproductupdater');
        $path   = trim((string) $params->get('output_path', 'updates'));

        if ($path === '') {
            $path = 'updates';
        }

        $isAbsolute = (bool) preg_match('#^(/|[A-Za-z]:[\\\\/])#', $path);

        return $isAbsolute ? rtrim($path, '\\/') : JPATH_ROOT . '/' . trim($path, '\\/');
    }

    /**
     * Resolves the base public URL used to build absolute links to
     * generated files, if configured. Empty string if not configured.
     *
     * @return  string  Base URL without trailing slash, or ''.
     */
    public static function getBaseUrl(): string
    {
        $params = ComponentHelper::getParams('com_genesisproductupdater');

        $baseUrl = rtrim((string) $params->get('base_url', ''), '/');

        if ($baseUrl !== '') {
            return $baseUrl;
        }

        $path = trim((string) $params->get('output_path', 'updates'));
        if ($path !== '' && !preg_match('#^(/|[A-Za-z]:[\\\\/])#', $path)) {
            return rtrim((string) \Joomla\CMS\Uri\Uri::root(), '/') . '/' . trim(str_replace('\\', '/', $path), '/');
        }

        return '';
    }

    /**
     * Computes the relative (from the output base path) directory for a product.
     *
     * @param   array  $product  Product record (must contain "element").
     *
     * @return  string
     */
    public static function getProductRelativeDir(array $product): string
    {
        $element = self::sanitizeElement((string) ($product['element'] ?? ('product-' . ($product['id'] ?? '0'))));

        return $element;
    }

    /**
     * Computes the absolute file path for a product's platform feed file.
     *
     * @param   array   $product  Product record.
     * @param   string  $slug     Platform slug.
     * @param   string  $ext      File extension (no dot).
     *
     * @return  string
     */
    public static function getProductFilePath(array $product, PlatformInterface $platform): string
    {
        $filename = method_exists($platform, 'getFilename')
            ? $platform->getFilename($product)
            : $platform->getSlug() . '.' . $platform->getExtension();

        return self::getOutputBasePath() . '/' . self::sanitizeElement($platform->getSlug()) . '/' . self::getProductRelativeDir($product) . '/' . $filename;
    }

    /**
     * Computes the absolute file path for a product's platform changelog file.
     *
     * @param   array   $product  Product record.
     * @param   string  $slug     Platform slug.
     * @param   string  $ext      File extension (no dot).
     *
     * @return  string
     */
    public static function getProductChangelogFilePath(array $product, PlatformInterface $platform): string
    {
        $filename = method_exists($platform, 'getChangelogFilename')
            ? $platform->getChangelogFilename($product)
            : $platform->getSlug() . '_changelog.' . $platform->getExtension();

        return self::getOutputBasePath() . '/' . self::sanitizeElement($platform->getSlug()) . '/' . self::getProductRelativeDir($product) . '/' . $filename;
    }

    /**
     * Returns the public URL for a previously generated file, if a base_url
     * is configured; otherwise null.
     *
     * @param   string  $absolutePath  Absolute path as returned by getProductFilePath().
     *
     * @return  string|null
     */
    public static function getPublicUrl(string $absolutePath): ?string
    {
        $baseUrl = self::getBaseUrl();

        if ($baseUrl === '') {
            return null;
        }

        $base = self::getOutputBasePath();

        if (strpos($absolutePath, $base) !== 0) {
            return null;
        }

        $relative = ltrim(str_replace('\\', '/', substr($absolutePath, \strlen($base))), '/');

        return $baseUrl . '/' . $relative;
    }

    /**
     * Generates every registered platform's feed file (and changelog file,
     * where a changelog exists for the product) for a single product.
     *
     * @param   int  $productId  The product id.
     *
     * @return  array  Map of platform-slug => ['file' => path|null, 'changelog' => path|null, 'error' => string|null]
     */
    public static function generateProduct(int $productId): array
    {
        $product = self::loadProduct($productId);
        $results = [];

        if ($product === null) {
            return $results;
        }

        $changelog = self::loadChangelogForProduct($productId);

        foreach (self::getRegistry()->getPlatforms() as $slug => $platform) {
            $hasRows = false;
            foreach (($product['versions'] ?? []) as $row) {
                if (($row['platform'] ?? '') === $slug) {
                    $hasRows = true;
                    break;
                }
            }
            if (!$hasRows) {
                continue;
            }

            $entry = ['file' => null, 'changelog' => null, 'error' => null];

            try {
                $contents = $platform->generate($product);
                $path     = self::getProductFilePath($product, $platform);
                self::writeFile($path, $contents);
                $entry['file'] = $path;
            } catch (\Throwable $e) {
                $entry['error'] = $e->getMessage();
            }

            if ($changelog !== null && $platform->supportsChangelog()) {
                try {
                    $clContents = $platform->generateChangelog($changelog, $product);

                    if ($clContents !== null) {
                        $clPath = self::getProductChangelogFilePath($product, $platform);
                        self::writeFile($clPath, $clContents);
                        $entry['changelog'] = $clPath;
                    }
                } catch (\Throwable $e) {
                    $entry['error'] = $e->getMessage();
                }
            }

            $results[$slug] = $entry;
        }

        return $results;
    }

    /**
     * Regenerates only the changelog files for the product linked to the
     * given changelog id.
     *
     * @param   int  $changelogId  The changelog id.
     *
     * @return  array  Map of platform-slug => path|null
     */
    public static function generateChangelogForProduct(int $changelogId): array
    {
        $changelog = self::loadChangelog($changelogId);
        $results   = [];

        if ($changelog === null) {
            return $results;
        }

        $product = self::loadProduct((int) $changelog['product_id']);

        if ($product === null) {
            return $results;
        }

        foreach (self::getRegistry()->getPlatforms() as $slug => $platform) {
			if ($slug !== ($product['platform'] ?? '')) {
				continue;
			}
            if (!$platform->supportsChangelog()) {
                continue;
            }

            try {
                $contents = $platform->generateChangelog($changelog, $product);

                if ($contents !== null) {
                    $path = self::getProductChangelogFilePath($product, $platform);
                    self::writeFile($path, $contents);
                    $results[$slug] = $path;
                }
            } catch (\Throwable $e) {
                $results[$slug] = null;
            }
        }

        return $results;
    }

    /**
     * Regenerates every file for every published product (and its changelog,
     * if any). Used by the toolbar "Generate All Files" action.
     *
     * @return  array  Map of productId => generateProduct() result
     */
    public static function generateAll(): array
    {
        $db = Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);

        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__productupdater_products'))
            ->where($db->quoteName('state') . ' = 1');

        $db->setQuery($query);
        $ids = $db->loadColumn();

        $all = [];

        foreach ($ids as $id) {
            $all[$id] = self::generateProduct((int) $id);
        }

        return $all;
    }

    /**
     * Loads a product record from the database with its "versions" JSON
     * column decoded into a plain array.
     *
     * @param   int  $productId  The product id.
     *
     * @return  array|null
     */
    public static function loadProduct(int $productId): ?array
    {
        $db    = Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__productupdater_products'))
            ->where($db->quoteName('id') . ' = :id')
            ->bind(':id', $productId, \Joomla\Database\ParameterType::INTEGER);

        $db->setQuery($query);
        $row = $db->loadAssoc();

        if (!$row) {
            return null;
        }

        $row['versions'] = json_decode((string) $row['versions'], true) ?: [];

        return $row;
    }

    /**
     * Loads a changelog record with its "entries" JSON column decoded.
     *
     * @param   int  $changelogId  The changelog id.
     *
     * @return  array|null
     */
    public static function loadChangelog(int $changelogId): ?array
    {
        $db    = Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__productupdater_changelogs'))
            ->where($db->quoteName('id') . ' = :id')
            ->bind(':id', $changelogId, \Joomla\Database\ParameterType::INTEGER);

        $db->setQuery($query);
        $row = $db->loadAssoc();

        if (!$row) {
            return null;
        }

        $row['entries'] = json_decode((string) $row['entries'], true) ?: [];

        return $row;
    }

    /**
     * Loads the (first published, or first any-state) changelog linked to a product.
     *
     * @param   int  $productId  The product id.
     *
     * @return  array|null
     */
    public static function loadChangelogForProduct(int $productId): ?array
    {
        $db    = Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select('id')
            ->from($db->quoteName('#__productupdater_changelogs'))
            ->where($db->quoteName('product_id') . ' = :pid')
            ->where($db->quoteName('state') . ' = 1')
            ->bind(':pid', $productId, \Joomla\Database\ParameterType::INTEGER)
            ->order($db->quoteName('id') . ' DESC');

        $db->setQuery($query, 0, 1);
        $id = (int) $db->loadResult();

        return $id > 0 ? self::loadChangelog($id) : null;
    }

    /**
     * Writes a file to disk, creating parent directories as needed.
     *
     * @param   string  $absolutePath  Destination path.
     * @param   string  $contents      File contents.
     *
     * @return  void
     *
     * @throws  \RuntimeException  If the directory could not be created or the file could not be written.
     */
    public static function writeFile(string $absolutePath, string $contents): void
    {
        $dir = \dirname($absolutePath);

        if (!is_dir($dir) && !Folder::create($dir)) {
            throw new \RuntimeException('Unable to create output directory: ' . $dir);
        }

        if (!File::write($absolutePath, $contents)) {
            throw new \RuntimeException('Unable to write file: ' . $absolutePath);
        }
    }

    /**
     * Normalises an "element" slug so it is always safe to use as a folder name.
     *
     * @param   string  $element  Raw element value.
     *
     * @return  string
     */
    public static function sanitizeElement(string $element): string
    {
        $element = strtolower(trim($element));
        $element = preg_replace('/[^a-z0-9_\-]+/', '-', $element);

        return trim((string) $element, '-') ?: 'product';
    }
}
