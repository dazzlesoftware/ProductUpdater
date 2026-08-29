<?php
/** @package Genesis Product Updater @author Dazzle Software https://dazzlesoftware.org @copyright Copyright (C) 2026 Dazzle Software, LLC @license GNU/GPLv3 and later */
namespace Joomla\Component\Genesisproductupdater\Administrator\Controller;
\defined('_JEXEC') or die;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\Component\Genesisproductupdater\Administrator\Helper\ProductUpdaterHelper;
class BundlesController extends AdminController
{
    public function getModel($name='Bundle',$prefix='Administrator',$config=['ignore_request'=>true]){return parent::getModel($name,$prefix,$config);}
    public function generateAll(){ $this->checkToken();$results=ProductUpdaterHelper::generateAllBundles();$this->setRedirect(Route::_('index.php?option=com_genesisproductupdater&view=bundles',false),Text::sprintf('COM_GENESISPRODUCTUPDATER_GENERATE_ALL_BUNDLES_SUCCESS',count($results))); }
}
