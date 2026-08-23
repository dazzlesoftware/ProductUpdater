<?php
/**
 * @package   Genesis Product Updater
 * @author    Dazzle Software https://dazzlesoftware.org
 * @copyright Copyright (C) 2026 Dazzle Software, LLC
 * @license   GNU/GPLv3 and later
 */

namespace Joomla\Component\Genesisproductupdater\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;

/**
 * Methods supporting a list of Product records.
 */
class ProductsModel extends ListModel
{
    /**
     * Constructor.
     *
     * @param   array                     $config   An optional associative array of configuration settings.
     * @param   \Joomla\CMS\MVC\Factory\MVCFactoryInterface|null  $factory  The factory.
     */
    public function __construct($config = [], ?\Joomla\CMS\MVC\Factory\MVCFactoryInterface $factory = null)
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id', 'a.id',
                'title', 'a.title',
                'element', 'a.element',
                'type', 'a.type',
                'state', 'a.state',
                'ordering', 'a.ordering',
            ];
        }

        parent::__construct($config, $factory);
    }

    /**
     * @inheritDoc
     */
    protected function populateState($ordering = 'a.title', $direction = 'asc')
    {
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $type = $this->getUserStateFromRequest($this->context . '.filter.type', 'filter_type');
        $this->setState('filter.type', $type);

        $published = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '');
        $this->setState('filter.state', $published);

        parent::populateState($ordering, $direction);
    }

    /**
     * @inheritDoc
     */
    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.type');
        $id .= ':' . $this->getState('filter.state');

        return parent::getStoreId($id);
    }

    /**
     * @inheritDoc
     */
    protected function getListQuery()
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.element, a.title, a.type, a.state, a.ordering, a.created, a.modified'
            )
        );
        $query->from($db->quoteName('#__productupdater_products', 'a'));

        $search = (string) $this->getState('filter.search', '');

        if ($search !== '') {
            if (stripos($search, 'id:') === 0) {
				$searchId = (int) substr($search, 3);
                $query->where('a.id = :searchid')->bind(':searchid', $searchId, ParameterType::INTEGER);
            } else {
                $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $search) . '%';
                $query->extendWhere(
                    'AND',
                    [
                        $db->quoteName('a.title') . ' LIKE :search1',
                        $db->quoteName('a.element') . ' LIKE :search2',
                    ],
                    'OR'
                );
                $query->bind(':search1', $like)->bind(':search2', $like);
            }
        }

        $type = (string) $this->getState('filter.type', '');

        if ($type !== '') {
            $query->where($db->quoteName('a.type') . ' = :type')->bind(':type', $type);
        }

        $published = $this->getState('filter.state');

        if (is_numeric($published)) {
			$publishedState = (int) $published;
            $query->where('a.state = :state')->bind(':state', $publishedState, ParameterType::INTEGER);
        } elseif ($published === '') {
            $query->where('a.state IN (0, 1)');
        }

        $orderCol = $this->state->get('list.ordering', 'a.title');
        $orderDir = $this->state->get('list.direction', 'ASC');
        $query->order($db->escape($orderCol . ' ' . $orderDir));

        return $query;
    }
}
