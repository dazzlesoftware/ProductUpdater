<?php
/**
 * @package   Genesis Product Updater
 * @author    Dazzle Software https://dazzlesoftware.org
 * @copyright Copyright (C) 2026 Dazzle Software, LLC
 * @license   GNU/GPLv3 and later
 */

namespace Joomla\Component\Genesisproductupdater\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\Component\Genesisproductupdater\Administrator\Helper\ProductUpdaterHelper;

/**
 * Changelog (single record) controller class.
 */
class ChangelogController extends FormController
{
    /**
     * @inheritDoc
     */
    protected function allowAdd($data = [])
    {
        return $this->app->getIdentity()->authorise('core.create', $this->option);
    }

    /**
     * @inheritDoc
     */
    protected function allowEdit($data = [], $key = 'id')
    {
        return $this->app->getIdentity()->authorise('core.edit', $this->option);
    }

    /**
     * Regenerates the update-feed changelog files for the currently edited/existing changelog.
     *
     * @return  void
     */
    public function generate()
    {
        $this->checkToken();

        $id = $this->input->getInt('id');

        if ($id > 0) {
            ProductUpdaterHelper::generateChangelogForProduct($id);
        }

        $this->setRedirect(
            Route::_('index.php?option=com_genesisproductupdater&view=changelogs', false),
            Text::_('COM_GENESISPRODUCTUPDATER_GENERATE_SUCCESS')
        );
    }
}
