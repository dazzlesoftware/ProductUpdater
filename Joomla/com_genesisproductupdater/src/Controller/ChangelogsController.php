<?php
/**
 * @package   Genesis Product Updater
 * @author    Dazzle Software https://dazzlesoftware.org
 * @copyright Copyright (C) 2026 Dazzle Software, LLC
 * @license   GNU/GPLv3 and later
 */

namespace Joomla\Component\Genesisproductupdater\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Genesisproductupdater\Administrator\Helper\ProductUpdaterHelper;

/**
 * Changelogs list controller class.
 */
class ChangelogsController extends AdminController
{
    /**
     * @inheritDoc
     */
    public function getModel($name = 'Changelog', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function generateAll()
    {
        $this->checkToken();
        $results = ProductUpdaterHelper::generateAllChangelogs();
        $this->setRedirect(
            Route::_('index.php?option=com_genesisproductupdater&view=changelogs', false),
            Text::sprintf('COM_GENESISPRODUCTUPDATER_GENERATE_ALL_CHANGELOGS_SUCCESS', \count($results))
        );
    }
}
