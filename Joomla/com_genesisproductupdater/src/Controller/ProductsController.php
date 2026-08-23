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
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Router\Route;
use Joomla\Component\Genesisproductupdater\Administrator\Helper\ProductUpdaterHelper;

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

        $results = ProductUpdaterHelper::generateAll();

        $count = \count($results);

        $this->setRedirect(
            Route::_('index.php?option=com_genesisproductupdater&view=products', false),
            Text::sprintf('COM_GENESISPRODUCTUPDATER_GENERATE_ALL_SUCCESS', $count)
        );
    }
}
