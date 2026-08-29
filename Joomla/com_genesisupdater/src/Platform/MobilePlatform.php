<?php
/**
 * @package   Genesis Updater
 * @author    Dazzle Software https://dazzlesoftware.org
 * @copyright Copyright (C) 2026 Dazzle Software, LLC
 * @license   GNU/GPLv3 and later
 */
namespace Joomla\Component\Genesisupdater\Administrator\Platform;

\defined('_JEXEC') or die;

use Joomla\Component\Genesisupdater\Administrator\Helper\GenesisUpdaterHelper;

class MobilePlatform extends AbstractPlatform
{
    public function getSlug(): string { return 'mobile'; }
    public function getLabel(): string { return 'Mobile (iOS & Android)'; }
    public function getExtension(): string { return 'json'; }
    public function supportsCompatibilityFields(): bool { return false; }
    public function supportsMobileFields(): bool { return true; }
    public function supportsChangelog(): bool { return true; }

    public function generate(array $product): string
    {
        $row = $this->getCurrentVersionRow($product) ?? [];
        $data = [
			'type' => (string) ($product['type'] ?? ''),
            'latest_version' => (string) ($row['version'] ?? ''),
            'build_number' => isset($row['build_number']) && $row['build_number'] !== '' ? (int) $row['build_number'] : 0,
            'url_ios' => (string) ($row['url_ios'] ?? ''),
            'url_android' => (string) ($row['url_android'] ?? ''),
            'force_update' => !empty($row['force_update']),
            'changelog' => !empty($row['release_notes']) ? $row['release_notes'] : ($product['description'] ?? ''),
        ];

		$changelogMode = (string) ($row['changelog_mode'] ?? 'generated');
		if ($changelogMode === 'custom' && !empty($row['changelog_url'])) {
			$data['changelog_url'] = $row['changelog_url'];
		} elseif ($changelogMode === 'generated') {
			$data['changelog_url'] = GenesisUpdaterHelper::getPublicUrl(GenesisUpdaterHelper::getProductChangelogFilePath($product, $this)) ?? '';
		}

        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    }

    public function generateChangelog(array $changelog, array $product): ?string
    {
        return json_encode([
            'name' => (string) ($product['title'] ?? ''),
            'type' => (string) ($product['type'] ?? ''),
            'entries' => $this->getChangelogEntries($changelog),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    }
}
