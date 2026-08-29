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
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\Component\Genesisupdater\Administrator\Helper\GenesisUpdaterHelper;
use Joomla\Database\ParameterType;

/**
 * Changelog model, handling a single changelog record.
 */
class ChangelogModel extends AdminModel
{
    /**
     * @var  string
     */
    public $typeAlias = 'com_genesisupdater.changelog';

    /**
     * @var  string
     */
    protected $text_prefix = 'COM_GENESISUPDATER_CHANGELOG';

    /**
     * @inheritDoc
     */
    public function getForm($data = [], $loadData = true)
    {
        $form = $this->loadForm('com_genesisupdater.changelog', 'changelog', ['control' => 'jform', 'load_data' => $loadData]);

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
        $data = $app->getUserState('com_genesisupdater.edit.changelog.data', []);

        if (empty($data)) {
            $data = (array) $this->getItem();
        }

        if (isset($data['entries']) && \is_string($data['entries'])) {
            $data['entries'] = json_decode($data['entries'], true) ?: [];
        }

        $this->preprocessData('com_genesisupdater.changelog', $data);

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function getItem($pk = null)
    {
        $item = parent::getItem($pk);

        if ($item && isset($item->entries) && \is_string($item->entries)) {
            $item->entries = json_decode($item->entries, true) ?: [];
        }

        return $item;
    }

    /**
     * @inheritDoc
     */
    public function save($data)
    {
        if (isset($data['entries']) && \is_array($data['entries'])) {
            $data['entries'] = json_encode(array_values($data['entries']));
        } elseif (!isset($data['entries'])) {
            $data['entries'] = '[]';
        }

        // Auto-derive the title from the linked product.
        if (!empty($data['product_id'])) {
			$productId = (int) $data['product_id'];
            $db    = $this->getDatabase();
            $query = $db->getQuery(true)
                ->select($db->quoteName('title'))
                ->from($db->quoteName('#__genesisupdater_products'))
                ->where($db->quoteName('id') . ' = :pid')
                ->bind(':pid', $productId, ParameterType::INTEGER);
            $db->setQuery($query);
            $productTitle = $db->loadResult();

            if ($productTitle) {
                $data['title'] = $productTitle . ' ' . \Joomla\CMS\Language\Text::_('COM_GENESISUPDATER_CHANGELOG_TITLE_SUFFIX');
            }
        }

        $result = parent::save($data);

        if ($result) {
            $id = (int) $this->getState($this->getName() . '.id');

            if ($id > 0) {
                try {
                    GenesisUpdaterHelper::generateChangelogForProduct($id);
                    GenesisUpdaterHelper::generateProduct((int) $data['product_id']);
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
}
