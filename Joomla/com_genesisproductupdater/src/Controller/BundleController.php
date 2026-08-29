<?php
/** @package Genesis Product Updater @author Dazzle Software https://dazzlesoftware.org @copyright Copyright (C) 2026 Dazzle Software, LLC @license GNU/GPLv3 and later */
namespace Joomla\Component\Genesisproductupdater\Administrator\Controller;
\defined('_JEXEC') or die;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\Component\Genesisproductupdater\Administrator\Helper\ProductUpdaterHelper;
class BundleController extends FormController
{
    protected function allowAdd($data=[]){return $this->app->getIdentity()->authorise('core.create',$this->option);}
    protected function allowEdit($data=[],$key='id'){return $this->app->getIdentity()->authorise('core.edit',$this->option);}
    public function generate(){ $this->checkToken();$id=$this->input->getInt('id');if($id){ProductUpdaterHelper::generateBundle($id);} $this->setRedirect(Route::_('index.php?option=com_genesisproductupdater&view=bundles',false),Text::_('COM_GENESISPRODUCTUPDATER_GENERATE_SUCCESS')); }
}
