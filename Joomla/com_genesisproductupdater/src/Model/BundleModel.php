<?php
/**
 * @package   Genesis Product Updater
 * @author    Dazzle Software https://dazzlesoftware.org
 * @copyright Copyright (C) 2026 Dazzle Software, LLC
 * @license   GNU/GPLv3 and later
 */
namespace Joomla\Component\Genesisproductupdater\Administrator\Model;
\defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\Component\Genesisproductupdater\Administrator\Helper\ProductUpdaterHelper;

class BundleModel extends AdminModel
{
    public $typeAlias = 'com_genesisproductupdater.bundle';
    protected $text_prefix = 'COM_GENESISPRODUCTUPDATER_BUNDLE';
    public function getForm($data = [], $loadData = true) { return $this->loadForm('com_genesisproductupdater.bundle', 'bundle', ['control' => 'jform', 'load_data' => $loadData]); }
    protected function loadFormData()
    {
        $data = Factory::getApplication()->getUserState('com_genesisproductupdater.edit.bundle.data', []);
        if (empty($data)) { $data = (array) $this->getItem(); }
        if (isset($data['product_ids']) && \is_string($data['product_ids'])) { $data['product_ids'] = json_decode($data['product_ids'], true) ?: []; }
        $this->preprocessData('com_genesisproductupdater.bundle', $data); return $data;
    }
    public function getItem($pk = null) { $item = parent::getItem($pk); if ($item && \is_string($item->product_ids ?? null)) { $item->product_ids = json_decode($item->product_ids, true) ?: []; } return $item; }
    public function save($data)
    {
        $data['product_ids'] = json_encode(array_values(array_unique(array_map('intval', (array) ($data['product_ids'] ?? [])))));
        $result = parent::save($data);
        if ($result) { $id = (int) $this->getState($this->getName() . '.id'); if ($id) { try { ProductUpdaterHelper::generateBundle($id); } catch (\Throwable $e) { Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning'); } } }
        return $result;
    }
}
