<?php
/**
 * @package   Genesis Updater
 * @author    Dazzle Software https://dazzlesoftware.org
 * @copyright Copyright (C) 2026 Dazzle Software, LLC
 * @license   GNU/GPLv3 and later
 */

namespace Joomla\Component\Genesisupdater\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\Component\Genesisupdater\Administrator\Helper\GenesisUpdaterHelper;

/**
 * Product (single record) controller class.
 */
class ProductController extends FormController
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
     * Regenerates the update-feed files for the currently edited/existing product.
     *
     * @return  void
     */
    public function generate()
    {
        $this->checkToken();

        $id = $this->input->getInt('id');

        if ($id > 0) {
            GenesisUpdaterHelper::generateProduct($id);
        }

        $this->setRedirect(
            Route::_('index.php?option=com_genesisupdater&view=products', false),
            Text::_('COM_GENESISUPDATER_GENERATE_SUCCESS')
        );
    }
}
