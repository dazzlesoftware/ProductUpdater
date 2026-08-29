<?php
/** @package Genesis Updater @author Dazzle Software https://dazzlesoftware.org @copyright Copyright (C) 2026 Dazzle Software, LLC @license GNU/GPLv3 and later */
namespace Joomla\Component\Genesisupdater\Administrator\Controller;
\defined('_JEXEC') or die;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\Component\Genesisupdater\Administrator\Helper\GenesisUpdaterHelper;
class BundlesController extends AdminController
{
    public function getModel($name='Bundle',$prefix='Administrator',$config=['ignore_request'=>true]){return parent::getModel($name,$prefix,$config);}
    public function generateAll(){ $this->checkToken();$results=GenesisUpdaterHelper::generateAllBundles();$this->setRedirect(Route::_('index.php?option=com_genesisupdater&view=bundles',false),Text::sprintf('COM_GENESISUPDATER_GENERATE_ALL_BUNDLES_SUCCESS',count($results))); }
}
