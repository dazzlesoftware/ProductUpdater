<?php
/** @package Genesis Product Updater @author Dazzle Software https://dazzlesoftware.org @copyright Copyright (C) 2026 Dazzle Software, LLC @license GNU/GPLv3 and later */
namespace Joomla\Component\Genesisproductupdater\Administrator\View\Bundles;
\defined('_JEXEC') or die;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\MVC\View\ListView;
class HtmlView extends ListView
{
    public function __construct(array $config){$config['option']=$config['option']??'com_genesisproductupdater';$config['supports_batch']=false;parent::__construct($config);}
    protected function initializeView(){parent::initializeView();$this->canDo=ContentHelper::getActions('com_genesisproductupdater');}
    protected function addToolbar(){parent::addToolbar();if($this->canDo->get('core.edit')){$this->getDocument()->getToolbar()->standardButton('generate-all')->text('COM_GENESISPRODUCTUPDATER_TOOLBAR_GENERATE_ALL_BUNDLES')->icon('icon-refresh')->task('bundles.generateAll');}}
}
