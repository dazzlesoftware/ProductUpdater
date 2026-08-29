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
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\DispatcherInterface;

/**
 * Product table class.
 */
class ProductTable extends Table
{
    /**
     * Constructor.
     *
     * @param   DatabaseInterface      $db          Database connector object.
     * @param   DispatcherInterface|null  $dispatcher  Event dispatcher for this table.
     */
    public function __construct(DatabaseInterface $db, ?DispatcherInterface $dispatcher = null)
    {
		$this->ensureProductColumns($db);
        parent::__construct('#__genesisupdater_products', 'id', $db, $dispatcher);
    }

	private function ensureProductColumns(DatabaseInterface $db): void
	{
		$table = $db->replacePrefix('#__genesisupdater_products');
		$columns = array_change_key_case($db->getTableColumns($table, false), CASE_LOWER);
		$required = [
			'platform' => "VARCHAR(32) NOT NULL DEFAULT 'joomla'",
			'catid' => 'INT UNSIGNED NOT NULL DEFAULT 0',
			'download_category' => "VARCHAR(255) NOT NULL DEFAULT ''",
			'preview_image' => "VARCHAR(1024) NOT NULL DEFAULT ''",
			'download_size' => 'BIGINT UNSIGNED NOT NULL DEFAULT 0',
			'download_count' => 'BIGINT UNSIGNED NOT NULL DEFAULT 0',
		];
		foreach ($required as $name => $definition) {
			if (!isset($columns[$name])) {
				$db->setQuery('ALTER TABLE ' . $db->quoteName($table) . ' ADD ' . $db->quoteName($name) . ' ' . $definition)->execute();
			}
		}
	}

	public function bind($src, $ignore = [])
	{
		if (is_array($src)) {
			$platform = strtolower(trim((string) ($src['platform'] ?? 'joomla')));
			$src['platform'] = in_array($platform, ['joomla', 'wordpress', 'mobile', 'fab'], true) ? $platform : 'joomla';
		}

		return parent::bind($src, $ignore);
	}

    /**
     * Overloaded check method to ensure data integrity.
     *
     * @return  boolean  True on success.
     */
    public function check()
    {
        if (trim((string) $this->title) === '') {
            $this->setError(Text::_('COM_GENESISUPDATER_ERROR_TITLE_REQUIRED'));

            return false;
        }

        if (trim((string) $this->element) === '') {
            $this->setError(Text::_('COM_GENESISUPDATER_ERROR_ELEMENT_REQUIRED'));

            return false;
        }

		if (trim((string) $this->platform) === '') {
			$this->platform = 'joomla';
		}

        if (!in_array((string) $this->platform, ['joomla', 'wordpress', 'mobile', 'fab'], true)) {
			$this->setError(Text::_('COM_GENESISUPDATER_ERROR_PLATFORM_REQUIRED'));

			return false;
		}

        if (!\is_string($this->versions) || $this->versions === '') {
            $this->versions = '[]';
        }

		$this->download_count = max(0, (int) $this->download_count);

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
