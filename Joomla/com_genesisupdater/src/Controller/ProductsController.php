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
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Router\Route;
use Joomla\Component\Genesisupdater\Administrator\Helper\GenesisUpdaterHelper;

/**
 * Products list controller class.
 */
class ProductsController extends AdminController
{
    /**
     * @inheritDoc
     */
    public function getModel($name = 'Product', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Generates update-feed files for every product.
     *
     * @return  void
     */
    public function generateAll()
    {
        $this->checkToken();

        $results = GenesisUpdaterHelper::generateAll();

        $count = \count($results);

        $this->setRedirect(
            Route::_('index.php?option=com_genesisupdater&view=products', false),
            Text::sprintf('COM_GENESISUPDATER_GENERATE_ALL_SUCCESS', $count)
        );
    }
}
