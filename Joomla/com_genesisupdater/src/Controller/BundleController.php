<?php
/** @package Genesis Updater @author Dazzle Software https://dazzlesoftware.org @copyright Copyright (C) 2026 Dazzle Software, LLC @license GNU/GPLv3 and later */
namespace Joomla\Component\Genesisupdater\Administrator\Controller;
\defined('_JEXEC') or die;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\Component\Genesisupdater\Administrator\Helper\GenesisUpdaterHelper;
class BundleController extends FormController
{
    protected function allowAdd($data=[]){return $this->app->getIdentity()->authorise('core.create',$this->option);}
    protected function allowEdit($data=[],$key='id'){return $this->app->getIdentity()->authorise('core.edit',$this->option);}
    public function generate(){ $this->checkToken();$id=$this->input->getInt('id');if($id){GenesisUpdaterHelper::generateBundle($id);} $this->setRedirect(Route::_('index.php?option=com_genesisupdater&view=bundles',false),Text::_('COM_GENESISUPDATER_GENERATE_SUCCESS')); }
}
