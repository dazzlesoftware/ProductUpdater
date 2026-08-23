<?php
/**
 * @package   Genesis Product Updater
 * @author    Dazzle Software https://dazzlesoftware.org
 * @copyright Copyright (C) 2026 Dazzle Software, LLC
 * @license   GNU/GPLv3 and later
 */
namespace Joomla\Component\Genesisproductupdater\Administrator\Platform;

\defined('_JEXEC') or die;

use Joomla\Component\Genesisproductupdater\Administrator\Helper\ProductUpdaterHelper;

class WordPressPlatform extends AbstractPlatform
{
    public function getSlug(): string { return 'wordpress'; }
    public function getLabel(): string { return 'WordPress'; }
    public function getExtension(): string { return 'json'; }
    public function supportsChangelog(): bool { return true; }

    public function generate(array $product): string
    {
        $row = $this->getCurrentVersionRow($product) ?? [];
        $data = [
            'name' => (string) ($product['title'] ?? ''),
			'type' => (string) ($product['type'] ?? ''),
            'slug' => (string) ($product['element'] ?? ''),
            'version' => (string) ($row['version'] ?? ''),
            'details_url' => (string) ($row['info_url'] ?? ''),
            'download_url' => (string) ($row['download_url'] ?? ''),
        ];
        foreach (['requires', 'tested', 'requires_php'] as $field) {
            if (!empty($row[$field])) { $data[$field] = $row[$field]; }
        }
        if (!empty($row['release_date'])) { $data['last_updated'] = $row['release_date']; }
        if (!empty($product['maintainer'])) { $data['author'] = $product['maintainer']; }
        if (!empty($product['maintainer_url'])) { $data['homepage'] = $product['maintainer_url']; }

        $changelogMode = (string) ($row['changelog_mode'] ?? 'generated');
        $changelogUrl = $changelogMode === 'custom' ? (string) ($row['changelog_url'] ?? '') : '';
        if ($changelogMode === 'generated') {
            $changelogUrl = ProductUpdaterHelper::getPublicUrl(ProductUpdaterHelper::getProductChangelogFilePath($product, $this)) ?? '';
        }
        if ($changelogUrl !== '') { $data['changelog_url'] = $changelogUrl; }

        $changelog = ProductUpdaterHelper::loadChangelogForProduct((int) ($product['id'] ?? 0));
        $sections = [];
        if (!empty($product['description'])) { $sections['description'] = '<p>' . htmlspecialchars((string) $product['description'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>'; }
        if ($changelog !== null) { $sections['changelog'] = $this->renderChangelogHtml($this->getChangelogEntries($changelog)); }
        if ($sections) { $data['sections'] = $sections; }

        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    }

    public function generateChangelog(array $changelog, array $product): ?string
    {
        $entries = $this->getChangelogEntries($changelog);
        $data = [
            'name' => (string) ($product['title'] ?? ''),
			'type' => (string) ($product['type'] ?? ''),
            'slug' => (string) ($product['element'] ?? ''),
            'entries' => $entries,
            'changelog_html' => $this->renderChangelogHtml($entries),
        ];

        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    }

    private function renderChangelogHtml(array $entries): string
    {
        $labels = ['addition' => 'Addition', 'change' => 'Change', 'fix' => 'Fix', 'remove' => 'Remove', 'security' => 'Security', 'deprecated' => 'Deprecated', 'language' => 'Language', 'note' => 'Note'];
        $html = '';
        foreach ($entries as $entry) {
            $heading = $entry['version'] . (!empty($entry['date']) ? ' &#8212; ' . $entry['date'] : '');
            $html .= '<h4>' . htmlspecialchars($heading, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</h4>\n";
            foreach ($entry['categories'] as $category => $items) {
                if (!$items) { continue; }
                $label = $labels[$category] ?? ucfirst($category);
                $html .= '<p><strong>' . htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</strong></p>\n<ul>\n";
                foreach ($items as $item) { $html .= '<li>' . htmlspecialchars($item, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</li>\n"; }
                $html .= "</ul>\n";
            }
        }
        return $html;
    }
}
