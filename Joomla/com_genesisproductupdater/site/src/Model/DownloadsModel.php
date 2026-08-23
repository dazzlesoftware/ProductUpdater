<?php
/**
 * @package   Genesis Product Updater
 * @author    Dazzle Software https://dazzlesoftware.org
 * @copyright Copyright (C) 2026 Dazzle Software, LLC
 * @license   GNU/GPLv3 and later
 */
namespace Joomla\Component\Genesisproductupdater\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class DownloadsModel extends BaseDatabaseModel
{
    public function getItems(int $categoryId = 0): array
    {
        $db = $this->getDatabase();
        $categoryTitle = 'COALESCE(NULLIF(' . $db->quoteName('c.title') . ", ''), NULLIF(" . $db->quoteName('p.download_category') . ", ''), 'Downloads')";
		$query = $db->getQuery(true)
			->select($db->quoteName('p') . '.*')
			->select($categoryTitle . ' AS ' . $db->quoteName('category_title'))
			->from($db->quoteName('#__productupdater_products', 'p'))
			->leftJoin($db->quoteName('#__categories', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('p.catid') . ' AND ' . $db->quoteName('c.extension') . ' = ' . $db->quote('com_genesisproductupdater'))
			->where($db->quoteName('p.state') . ' = 1')
			->order($categoryTitle . ' ASC, ' . $db->quoteName('p.ordering') . ' ASC, ' . $db->quoteName('p.title') . ' ASC');
        if ($categoryId > 0) {
            $query->where($db->quoteName('p.catid') . ' = ' . $categoryId);
        }
        $db->setQuery($query);
        $items = $db->loadAssocList();
        foreach ($items as &$item) {
            $versions = json_decode((string) $item['versions'], true) ?: [];
            $versions = array_values(array_filter($versions, static fn ($row) => ($row['platform'] ?? '') === $item['platform']));
            $current = null;
            foreach ($versions as $row) { if (!empty($row['is_current'])) { $current = $row; break; } }
            if ($current === null) { usort($versions, static fn ($a, $b) => version_compare($b['version'] ?? '0', $a['version'] ?? '0')); $current = $versions[0] ?? []; }
            $item['current_version'] = $current;
            $item['changelog_url'] = $this->getChangelogUrl($item, $current);
			$item['changelog_entries'] = $this->getChangelogEntries((int) $item['id']);
        }
        unset($item);
        return $items;
    }

	private function getChangelogEntries(int $productId): array
	{
		$db = $this->getDatabase();
		$query = $db->getQuery(true)->select($db->quoteName('entries'))->from($db->quoteName('#__productupdater_changelogs'))->where($db->quoteName('product_id') . ' = ' . $productId)->where($db->quoteName('state') . ' = 1')->order($db->quoteName('id') . ' DESC');
		$db->setQuery($query, 0, 1);
		$rows = json_decode((string) $db->loadResult(), true) ?: [];
		$grouped = [];
		foreach ($rows as $row) {
			$version = trim((string) ($row['version'] ?? ''));
			if ($version === '') continue;
			$grouped[$version]['version'] = $version;
			$grouped[$version]['date'] = $row['date'] ?? $row['release_date'] ?? '';
			$grouped[$version]['categories'][strtolower((string) ($row['category'] ?? 'change'))][] = (string) ($row['items'] ?? '');
		}
		$entries = array_values($grouped);
		usort($entries, static fn ($a, $b) => version_compare($b['version'], $a['version']));
		return $entries;
	}

    private function getChangelogUrl(array $product, array $row): string
    {
        $mode = $row['changelog_mode'] ?? 'generated';
        if ($mode === 'none') return '';
        if ($mode === 'custom') return (string) ($row['changelog_url'] ?? '');
        $base = rtrim((string) ComponentHelper::getParams('com_genesisproductupdater')->get('base_url', ''), '/');
        if ($base === '') return '';
        $element = preg_replace('/[^a-z0-9_-]+/', '-', strtolower((string) $product['element']));
        $filename = $product['platform'] === 'joomla' ? $this->joomlaPrefix($product['type']) . $element . '_changelog.xml' : $product['platform'] . '_changelog.json';
        $platform = preg_replace('/[^a-z0-9_-]+/', '-', strtolower((string) $product['platform']));
        return $base . '/' . $platform . '/' . $element . '/' . $filename;
    }

    private function joomlaPrefix(string $type): string
    {
        return ['template'=>'tpl_','component'=>'com_','module'=>'mod_','plugin'=>'plg_','library'=>'lib_','package'=>'pkg_','file'=>'file_','language'=>'lang_'][$type] ?? '';
    }
}
