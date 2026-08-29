<?php
/**
 * @package   Genesis Updater
 * @author    Dazzle Software https://dazzlesoftware.org
 * @copyright Copyright (C) 2026 Dazzle Software, LLC
 * @license   GNU/GPLv3 and later
 */

namespace Joomla\Component\Genesisupdater\Administrator\Platform;

\defined('_JEXEC') or die;

/**
 * Convenience base class for platform generators providing sane defaults.
 */
abstract class AbstractPlatform implements PlatformInterface
{
    /**
     * @inheritDoc
     */
    public function supportsChangelog(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function supportsCompatibilityFields(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function supportsMobileFields(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function generateChangelog(array $changelog, array $product): ?string
    {
        return null;
    }

    public function getFilename(array $product): string
    {
        return $this->sanitizeFilename($this->getSlug()) . '.' . $this->getExtension();
    }

    public function getChangelogFilename(array $product): string
    {
        return $this->sanitizeFilename($this->getSlug() . '_changelog') . '.' . $this->getExtension();
    }

    protected function sanitizeFilename(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9_.-]+/', '-', $value);

        return trim((string) $value, '-');
    }

    /**
     * Returns the "current" version row of a product: the row explicitly
     * flagged is_current, or otherwise the row with the highest version number.
     *
     * @param   array  $product  Product record with a decoded "versions" array.
     *
     * @return  array|null
     */
    protected function getCurrentVersionRow(array $product): ?array
    {
        $versions = $this->getVersionRowsForPlatform($product, $this->getSlug());

        if (!\is_array($versions) || empty($versions)) {
            return null;
        }

        foreach ($versions as $row) {
            if (!empty($row['is_current'])) {
                return $row;
            }
        }

        $best = null;

        foreach ($versions as $row) {
            if ($best === null || version_compare((string) ($row['version'] ?? '0'), (string) ($best['version'] ?? '0'), '>')) {
                $best = $row;
            }
        }

        return $best;
    }

    /**
     * Filters the version rows of a product down to a specific platform slug.
     *
     * @param   array   $product  Product record with a decoded "versions" array.
     * @param   string  $slug     Platform slug to filter on.
     *
     * @return  array
     */
    protected function getVersionRowsForPlatform(array $product, string $slug): array
    {
        $versions = $product['versions'] ?? [];

        if (!\is_array($versions)) {
            return [];
        }

        return array_values(array_filter($versions, static function ($row) use ($slug) {
            return isset($row['platform']) && $row['platform'] === $slug;
        }));
    }

    protected function getChangelogEntries(array $changelog): array
    {
        $grouped = [];

        foreach (($changelog['entries'] ?? []) as $row) {
            $version = trim((string) ($row['version'] ?? ''));
            $category = strtolower(trim((string) ($row['category'] ?? '')));

            if ($version === '' || $category === '') {
                continue;
            }

            if (!isset($grouped[$version])) {
                $grouped[$version] = [
                    'version' => $version,
                    'date' => (string) ($row['date'] ?? $row['release_date'] ?? ''),
                    'categories' => [],
                ];
            }

            $items = preg_split('/\r\n|\r|\n/', (string) ($row['items'] ?? ''));
            $items = array_values(array_filter(array_map('trim', $items), static fn ($item) => $item !== ''));
            $grouped[$version]['categories'][$category] = array_merge($grouped[$version]['categories'][$category] ?? [], $items);
        }

        $entries = array_values($grouped);
        usort($entries, static fn ($a, $b) => version_compare($b['version'], $a['version']));

        $order = ['addition', 'change', 'fix', 'remove', 'security', 'deprecated', 'language', 'note'];
        foreach ($entries as &$entry) {
            $ordered = [];
            foreach ($order as $category) {
                if (!empty($entry['categories'][$category])) {
                    $ordered[$category] = $entry['categories'][$category];
                }
            }
            foreach ($entry['categories'] as $category => $items) {
                if (!isset($ordered[$category]) && $items) {
                    $ordered[$category] = $items;
                }
            }
            $entry['categories'] = $ordered;
        }
        unset($entry);

        return $entries;
    }
}
