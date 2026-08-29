<?php
/**
 * @package   Genesis Updater
 * @author    Dazzle Software https://dazzlesoftware.org
 * @copyright Copyright (C) 2026 Dazzle Software, LLC
 * @license   GNU/GPLv3 and later
 */
namespace Joomla\Component\Genesisupdater\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;

class DownloadController extends BaseController
{
	public function track()
	{
		$id = $this->input->getInt('id');
		$db = Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);
		$query = $db->getQuery(true)->select($db->quoteName('versions'))->from($db->quoteName('#__genesisupdater_products'))->where($db->quoteName('id') . ' = ' . $id)->where($db->quoteName('state') . ' = 1');
		$db->setQuery($query);
		$versions = json_decode((string) $db->loadResult(), true) ?: [];
		$current = null;
		foreach ($versions as $row) if (!empty($row['is_current'])) { $current = $row; break; }
		if ($current === null) { usort($versions, static fn ($a, $b) => version_compare($b['version'] ?? '0', $a['version'] ?? '0')); $current = $versions[0] ?? []; }
		$url = (string) ($current['download_url'] ?? '');
		if ($url === '') throw new \RuntimeException('Download not found.', 404);
		$query = $db->getQuery(true)->update($db->quoteName('#__genesisupdater_products'))->set($db->quoteName('download_count') . ' = ' . $db->quoteName('download_count') . ' + 1')->where($db->quoteName('id') . ' = ' . $id);
		$db->setQuery($query)->execute();
		$this->setRedirect($url);
	}
}
