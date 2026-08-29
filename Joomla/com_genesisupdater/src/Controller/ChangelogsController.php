<?php
/**
 * @package   Genesis Updater
 * @author    Dazzle Software https://dazzlesoftware.org
 * @copyright Copyright (C) 2026 Dazzle Software, LLC
 * @license   GNU/GPLv3 and later
 */

namespace Joomla\Component\Genesisupdater\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Genesisupdater\Administrator\Helper\GenesisUpdaterHelper;

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
        $results = GenesisUpdaterHelper::generateAllChangelogs();
        $this->setRedirect(
            Route::_('index.php?option=com_genesisupdater&view=changelogs', false),
            Text::sprintf('COM_GENESISUPDATER_GENERATE_ALL_CHANGELOGS_SUCCESS', \count($results))
        );
    }
}
