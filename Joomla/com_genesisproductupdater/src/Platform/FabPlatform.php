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
class FabPlatform extends AbstractPlatform
{
    public function getSlug(): string { return 'fab'; }
    public function getLabel(): string { return 'Fab'; }
    public function getExtension(): string { return 'json'; }
    public function supportsCompatibilityFields(): bool { return false; }
    public function supportsChangelog(): bool { return true; }
    public function generate(array $product): string
    {
        $row = $this->getCurrentVersionRow($product) ?? [];
		$type = (string) ($product['type'] ?? '');
		$changelogText = trim((string) ($row['release_notes'] ?? ($product['description'] ?? '')));
		if ($changelogText === '' && !empty($product['id'])) {
			$changelog = ProductUpdaterHelper::loadChangelogForProduct((int) $product['id']);
			if ($changelog !== null) {
				foreach ($this->getChangelogEntries($changelog) as $entry) {
					if ((string) ($entry['version'] ?? '') !== (string) ($row['version'] ?? '')) {
						continue;
					}
					$items = [];
					foreach (($entry['categories'] ?? []) as $categoryItems) {
						$items = array_merge($items, (array) $categoryItems);
					}
					$changelogText = implode("\n", array_filter(array_map('trim', $items)));
					break;
				}
			}
		}
		$data = [
			'type' => $type,
			'category' => preg_replace('/^fab-/', '', $type),
			'download_category' => (string) ($product['download_category'] ?? ''),
			'preview_image' => $this->getPreviewImageUrl($product),
            'latest_version' => (string) ($row['version'] ?? ''),
            'fab_url' => (string) ($row['url_fab'] ?? ''),
            'changelog' => $changelogText,
        ];
		$changelogMode = (string) ($row['changelog_mode'] ?? 'generated');
		if ($changelogMode === 'custom' && !empty($row['changelog_url'])) {
			$data['changelog_url'] = (string) $row['changelog_url'];
		} elseif ($changelogMode === 'generated') {
			$data['changelog_url'] = ProductUpdaterHelper::getPublicUrl(ProductUpdaterHelper::getProductChangelogFilePath($product, $this)) ?? '';
		}
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    }

    public function generateChangelog(array $changelog, array $product): ?string
    {
		$type = (string) ($product['type'] ?? '');
        return json_encode([
			'name' => (string) ($product['title'] ?? ''),
			'type' => $type,
			'category' => preg_replace('/^fab-/', '', $type),
			'preview_image' => $this->getPreviewImageUrl($product),
			'entries' => $this->getChangelogEntries($changelog),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    }

	private function getPreviewImageUrl(array $product): string
	{
		$image = trim((string) ($product['preview_image'] ?? ''));
		$image = explode('#', $image, 2)[0];
		if ($image === '' || preg_match('#^https?://#i', $image)) {
			return $image;
		}

		return rtrim((string) \Joomla\CMS\Uri\Uri::root(), '/') . '/' . ltrim($image, '/');
	}
}
