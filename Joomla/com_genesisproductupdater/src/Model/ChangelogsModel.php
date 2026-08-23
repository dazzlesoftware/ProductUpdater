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
 * Methods supporting a list of Changelog records.
 */
class ChangelogsModel extends ListModel
{
    /**
     * Constructor.
     *
     * @param   array                                              $config   Configuration array.
     * @param   \Joomla\CMS\MVC\Factory\MVCFactoryInterface|null    $factory  The factory.
     */
    public function __construct($config = [], ?\Joomla\CMS\MVC\Factory\MVCFactoryInterface $factory = null)
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id', 'a.id',
                'title', 'a.title',
                'product_id', 'a.product_id',
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
                'a.id, a.product_id, a.title, a.state, a.ordering, a.created, a.modified, p.title AS product_title, p.element AS product_element'
            )
        );
        $query->from($db->quoteName('#__productupdater_changelogs', 'a'));
        $query->join(
            'LEFT',
            $db->quoteName('#__productupdater_products', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('a.product_id')
        );

        $search = (string) $this->getState('filter.search', '');

        if ($search !== '') {
            $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $search) . '%';
            $query->extendWhere(
                'AND',
                [
                    $db->quoteName('a.title') . ' LIKE :search1',
                    $db->quoteName('p.title') . ' LIKE :search2',
                ],
                'OR'
            );
            $query->bind(':search1', $like)->bind(':search2', $like);
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
