<?php
/**
 * @package   Genesis Product Updater
 * @author    Dazzle Software https://dazzlesoftware.org
 * @copyright Copyright (C) 2026 Dazzle Software, LLC
 * @license   GNU/GPLv3 and later
 */
namespace Joomla\Component\Genesisproductupdater\Administrator\Table;
\defined('_JEXEC') or die;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\DispatcherInterface;

class BundleTable extends Table
{
    public function __construct(DatabaseInterface $db, ?DispatcherInterface $dispatcher = null) { parent::__construct('#__productupdater_bundles', 'id', $db, $dispatcher); }
    public function check()
    {
        $this->title = trim((string) $this->title);
        if ($this->title === '') { $this->setError('COM_GENESISPRODUCTUPDATER_ERROR_BUNDLE_TITLE_REQUIRED'); return false; }
        $this->output_slug = trim((string) $this->output_slug) ?: strtolower(preg_replace('/[^a-z0-9]+/i', '-', $this->title));
		$this->platform = in_array((string) ($this->platform ?? ''), ['joomla', 'wordpress', 'mobile', 'fab'], true) ? $this->platform : 'joomla';
		$extension = $this->platform === 'joomla' ? 'xml' : 'json';
		$this->filename = preg_replace('/\.(xml|json)$/i', '', trim((string) $this->filename) ?: 'list') . '.' . $extension;
        if (!\is_string($this->product_ids) || $this->product_ids === '') { $this->product_ids = '[]'; }
        return true;
    }
    public function store($updateNulls = true)
    {
        $date = \Joomla\CMS\Factory::getDate()->toSql(); $user = \Joomla\CMS\Factory::getApplication()->getIdentity();
        if ($this->id) { $this->modified = $date; $this->modified_by = $user ? $user->id : 0; }
        else { $this->created = $this->created ?: $date; $this->created_by = $this->created_by ?: ($user ? $user->id : 0); }
        return parent::store($updateNulls);
    }
}
