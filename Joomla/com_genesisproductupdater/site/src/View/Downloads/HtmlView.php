<?php
/**
 * @package   Genesis Product Updater
 * @author    Dazzle Software https://dazzlesoftware.org
 * @copyright Copyright (C) 2026 Dazzle Software, LLC
 * @license   GNU/GPLv3 and later
 */
namespace Joomla\Component\Genesisproductupdater\Site\View\Downloads;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;

class HtmlView extends BaseHtmlView
{
    public array $categories = [];

    public function display($tpl = null)
    {
        $active = Factory::getApplication()->getMenu()->getActive();
		$categoryFilter = $active ? (int) $active->getParams()->get('category_id', 0) : 0;
        foreach ($this->getModel()->getItems($categoryFilter) as $item) {
            if (empty($item['current_version'])) continue;
			$category = trim((string) $item['category_title']) ?: 'Downloads';
            $this->categories[$category][] = $item;
        }
        parent::display($tpl);
    }
}
