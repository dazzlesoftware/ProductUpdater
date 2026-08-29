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

class BundlesModel extends ListModel
{
    public function __construct($config = [], ?\Joomla\CMS\MVC\Factory\MVCFactoryInterface $factory = null) { if (empty($config['filter_fields'])) { $config['filter_fields'] = ['id','a.id','title','a.title','state','a.state','ordering','a.ordering']; } parent::__construct($config, $factory); }
    protected function populateState($ordering = 'a.title', $direction = 'asc') { $this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search')); $this->setState('filter.state', $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '')); parent::populateState($ordering, $direction); }
    protected function getListQuery()
    {
        $db=$this->getDatabase(); $query=$db->getQuery(true)->select('a.*')->from($db->quoteName('#__productupdater_bundles','a'));
        $search=(string)$this->getState('filter.search',''); if($search!==''){ $like='%'.str_replace(['%','_'],['\%','\_'],$search).'%'; $query->where($db->quoteName('a.title').' LIKE :search')->bind(':search',$like); }
        $state=$this->getState('filter.state'); if(is_numeric($state)){ $state=(int)$state;$query->where('a.state = :state')->bind(':state',$state,ParameterType::INTEGER); } elseif($state===''){ $query->where('a.state IN (0,1)'); }
        return $query->order($db->escape($this->state->get('list.ordering','a.title').' '.$this->state->get('list.direction','ASC')));
    }
}
