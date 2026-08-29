<?php
/**
 * @package   Genesis Updater
 * @author    Dazzle Software https://dazzlesoftware.org
 * @copyright Copyright (C) 2026 Dazzle Software, LLC
 * @license   GNU/GPLv3 and later
 */

namespace Joomla\Component\Genesisupdater\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\Component\Genesisupdater\Administrator\Helper\GenesisUpdaterHelper;
use Joomla\CMS\Http\HttpFactory;

/**
 * Product model, handling a single product record.
 */
class ProductModel extends AdminModel
{
    /**
     * @var  string
     */
    public $typeAlias = 'com_genesisupdater.product';

    /**
     * The prefix to use with controller messages.
     *
     * @var  string
     */
    protected $text_prefix = 'COM_GENESISUPDATER_PRODUCT';

    /**
     * @inheritDoc
     */
    public function getForm($data = [], $loadData = true)
    {
        $form = $this->loadForm('com_genesisupdater.product', 'product', ['control' => 'jform', 'load_data' => $loadData]);

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * @inheritDoc
     */
    protected function loadFormData()
    {
        $app  = Factory::getApplication();
        $data = $app->getUserState('com_genesisupdater.edit.product.data', []);

        if (empty($data)) {
            $data = (array) $this->getItem();
        }

        if (isset($data['versions']) && \is_string($data['versions'])) {
            $data['versions'] = json_decode($data['versions'], true) ?: [];
        }

        $this->preprocessData('com_genesisupdater.product', $data);

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function getItem($pk = null)
    {
        $item = parent::getItem($pk);

        if ($item && isset($item->versions) && \is_string($item->versions)) {
            $item->versions = json_decode($item->versions, true) ?: [];
        }

        return $item;
    }

    /**
     * @inheritDoc
     */
	public function save($data)
    {
		$platform = strtolower(trim((string) ($data['platform'] ?? 'joomla')));
		if (!in_array($platform, ['joomla', 'wordpress', 'mobile', 'fab'], true)) {
			$platform = 'joomla';
		}
		$data['platform'] = $platform;
		if (isset($data['versions']) && \is_array($data['versions'])) {
			foreach ($data['versions'] as &$version) {
				$version['platform'] = $platform;
			}
			unset($version);
		}
		if (isset($data['versions']) && \is_array($data['versions'])) {
            $data['versions'] = json_encode(array_values($data['versions']));
        } elseif (!isset($data['versions'])) {
            $data['versions'] = '[]';
        }

        $result = parent::save($data);

        if ($result) {
            $id = (int) $this->getState($this->getName() . '.id');

            if ($id > 0) {
                try {
                    GenesisUpdaterHelper::generateProduct($id);
					$this->refreshDownloadSize($id, json_decode((string) $data['versions'], true) ?: []);
                } catch (\Throwable $e) {
                    Factory::getApplication()->enqueueMessage(
                        \Joomla\CMS\Language\Text::sprintf('COM_GENESISUPDATER_ERROR_GENERATE_FAILED', $e->getMessage()),
                        'warning'
                    );
                }
            }
        }

        return $result;
    }

	private function refreshDownloadSize(int $id, array $versions): void
	{
		$current = null;
		foreach ($versions as $row) if (!empty($row['is_current'])) { $current = $row; break; }
		if ($current === null) { usort($versions, static fn ($a, $b) => version_compare($b['version'] ?? '0', $a['version'] ?? '0')); $current = $versions[0] ?? []; }
		$size = 0;
		if (!empty($current['download_url'])) {
			try {
				$response = HttpFactory::getHttp()->head((string) $current['download_url'], [], 4);
				$size = (int) ($response->headers['Content-Length'] ?? $response->headers['content-length'] ?? 0);
			} catch (\Throwable $e) {}
		}
		$db = $this->getDatabase();
		$query = $db->getQuery(true)->update($db->quoteName('#__genesisupdater_products'))->set($db->quoteName('download_size') . ' = ' . $size)->where($db->quoteName('id') . ' = ' . $id);
		$db->setQuery($query)->execute();
	}

    /**
     * @inheritDoc
     */
    protected function preprocessForm(Form $form, $data, $group = 'content')
    {
        parent::preprocessForm($form, $data, $group);
    }
}
