<?php
/**
 * @package   Genesis Product Updater
 * @author    Dazzle Software https://dazzlesoftware.org
 * @copyright Copyright (C) 2026 Dazzle Software, LLC
 * @license   GNU/GPLv3 and later
 */
namespace Joomla\Component\Genesisproductupdater\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Component\Genesisproductupdater\Administrator\Helper\ProductUpdaterHelper;

class PlatformListField extends ListField
{
    protected $type = 'PlatformList';

    protected function getOptions()
    {
        $options = [];

        foreach (ProductUpdaterHelper::getRegistry()->getPlatforms() as $slug => $platform) {
            $options[] = HTMLHelper::_('select.option', $slug, $platform->getLabel());
        }

        return array_merge(parent::getOptions(), $options);
    }
}
