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

class JoomlaPlatform extends AbstractPlatform
{
    public function getSlug(): string { return 'joomla'; }
    public function getLabel(): string { return 'Joomla'; }
    public function getExtension(): string { return 'xml'; }
    public function supportsChangelog(): bool { return true; }

    private function getTypePrefix(array $product): string
    {
        $prefixes = ['template' => 'tpl_', 'component' => 'com_', 'module' => 'mod_', 'plugin' => 'plg_', 'library' => 'lib_', 'package' => 'pkg_', 'file' => 'file_', 'language' => 'lang_'];
        return $prefixes[strtolower(trim((string) ($product['type'] ?? '')))] ?? '';
    }

    public function getFilename(array $product): string
    {
        return $this->sanitizeFilename($this->getTypePrefix($product) . ($product['element'] ?? 'product')) . '.xml';
    }

    public function getChangelogFilename(array $product): string
    {
        return $this->sanitizeFilename($this->getTypePrefix($product) . ($product['element'] ?? 'product') . '_changelog') . '.xml';
    }

    public function generate(array $product): string
    {
        $xml = $this->newWriter('utf-8', 'updates');
        foreach ($this->getVersionRowsForPlatform($product, $this->getSlug()) as $row) {
            if (!empty($row['tag'])) { $xml->writeComment(sprintf(' Joomla %s - %s ', $row['target_version'] ?? '', $row['version'] ?? '')); }
            $xml->startElement('update');
            $xml->writeElement('name', (string) ($product['title'] ?? ''));
            $xml->writeElement('description', (string) ($row['description'] ?? $product['description'] ?? ''));
            $xml->writeElement('element', (string) ($product['element'] ?? ''));
            $xml->writeElement('type', (string) ($product['type'] ?? 'template'));
            $xml->writeElement('version', (string) ($row['version'] ?? ''));
            if (!empty($row['info_url'])) {
                $xml->startElement('infourl');
                if (!empty($row['info_title'])) { $xml->writeAttribute('title', (string) $row['info_title']); }
                $xml->text((string) $row['info_url']);
                $xml->endElement();
            }
            $changelogMode = (string) ($row['changelog_mode'] ?? 'generated');
            $changelogUrl = $changelogMode === 'custom' ? (string) ($row['changelog_url'] ?? '') : '';
            if ($changelogMode === 'generated') { $changelogUrl = ProductUpdaterHelper::getPublicUrl(ProductUpdaterHelper::getProductChangelogFilePath($product, $this)) ?? ''; }
            if ($changelogUrl !== '') { $xml->writeElement('changelogurl', $changelogUrl); }
            if (!empty($row['sha512'])) { $xml->writeElement('sha512', strtoupper((string) $row['sha512'])); }
            if (!empty($row['download_url'])) {
                $xml->startElement('downloads');
                $xml->startElement('downloadurl');
                $xml->writeAttribute('type', 'full');
                $xml->writeAttribute('format', 'zip');
                $xml->text((string) $row['download_url']);
                $xml->endElement();
                $xml->endElement();
            }
            $xml->startElement('tags');
            $xml->writeElement('tag', (string) ($row['tag'] ?? 'stable'));
            $xml->endElement();
            if (!empty($product['maintainer'])) { $xml->writeElement('maintainer', (string) $product['maintainer']); }
            if (!empty($product['maintainer_url'])) { $xml->writeElement('maintainerurl', (string) $product['maintainer_url']); }
            $xml->startElement('targetplatform');
            $xml->writeAttribute('name', 'joomla');
            $xml->writeAttribute('version', !empty($row['target_version']) ? (string) $row['target_version'] : '*');
            $xml->endElement();
            if (!empty($row['requires_php'])) { $xml->writeElement('php_minimum', (string) $row['requires_php']); }
            if (!empty($row['requires'])) { $xml->writeElement('requires', (string) $row['requires']); }
            if (!empty($row['tested'])) { $xml->writeElement('tested', (string) $row['tested']); }
            $xml->endElement();
        }
        $xml->endElement();
        $xml->endDocument();
        return $xml->outputMemory();
    }

    public function generateChangelog(array $changelog, array $product): ?string
    {
        $xml = $this->newWriter('UTF-8', 'changelogs');
        foreach ($this->getChangelogEntries($changelog) as $entry) {
            $xml->startElement('changelog');
            $xml->writeElement('element', (string) ($product['element'] ?? ''));
            $xml->writeElement('type', (string) ($product['type'] ?? 'template'));
            $xml->writeElement('version', $entry['version']);
            foreach ($entry['categories'] as $category => $items) {
                if (!$items) { continue; }
                $xml->startElement($category);
                foreach ($items as $item) { $xml->writeElement('item', $item); }
                $xml->endElement();
            }
            $xml->endElement();
        }
        $xml->endElement();
        $xml->endDocument();
        return $xml->outputMemory();
    }

    private function newWriter(string $encoding, string $root): \XMLWriter
    {
        $xml = new \XMLWriter();
        $xml->openMemory();
        $xml->setIndent(true);
        $xml->setIndentString('    ');
        $xml->startDocument('1.0', $encoding);
        $xml->startElement($root);
        return $xml;
    }
}
