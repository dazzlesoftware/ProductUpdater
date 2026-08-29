<?php
/**
 * @package   Genesis Updater
 * @author    Dazzle Software https://dazzlesoftware.org
 * @copyright Copyright (C) 2026 Dazzle Software, LLC
 * @license   GNU/GPLv3 and later
 */

namespace Joomla\Component\Genesisupdater\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\DispatcherInterface;

/**
 * Changelog table class.
 */
class ChangelogTable extends Table
{
    /**
     * Constructor.
     *
     * @param   DatabaseInterface         $db          Database connector object.
     * @param   DispatcherInterface|null  $dispatcher  Event dispatcher for this table.
     */
    public function __construct(DatabaseInterface $db, ?DispatcherInterface $dispatcher = null)
    {
        parent::__construct('#__genesisupdater_changelogs', 'id', $db, $dispatcher);
    }

    /**
     * Overloaded check method to ensure data integrity.
     *
     * @return  boolean  True on success.
     */
    public function check()
    {
        if ((int) $this->product_id <= 0) {
            $this->setError('COM_GENESISUPDATER_ERROR_PRODUCT_REQUIRED');

            return false;
        }

        if (!\is_string($this->entries) || $this->entries === '') {
            $this->entries = '[]';
        }

        return true;
    }

    /**
     * Overloaded store method to keep created/modified fields in sync.
     *
     * @param   boolean  $updateNulls  True to update fields even if they are null.
     *
     * @return  boolean  True on success.
     */
    public function store($updateNulls = true)
    {
        $date = \Joomla\CMS\Factory::getDate()->toSql();
        $user = \Joomla\CMS\Factory::getApplication()->getIdentity();

        if ($this->id) {
            $this->modified    = $date;
            $this->modified_by = $user ? $user->id : 0;
        } else {
            if (empty($this->created)) {
                $this->created = $date;
            }

            if (empty($this->created_by)) {
                $this->created_by = $user ? $user->id : 0;
            }
        }

        if (empty($this->ordering)) {
            $this->ordering = 0;
        }

        return parent::store($updateNulls);
    }
}
