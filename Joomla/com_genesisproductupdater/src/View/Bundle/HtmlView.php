<?php
/** @package Genesis Product Updater @author Dazzle Software https://dazzlesoftware.org @copyright Copyright (C) 2026 Dazzle Software, LLC @license GNU/GPLv3 and later */
namespace Joomla\Component\Genesisproductupdater\Administrator\View\Bundle;
\defined('_JEXEC') or die;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\MVC\View\FormView;
class HtmlView extends FormView { public function __construct(array $config){$config['option']=$config['option']??'com_genesisproductupdater';parent::__construct($config);} protected function initializeView(){parent::initializeView();$this->canDo=ContentHelper::getActions('com_genesisproductupdater');$this->form->addControlField('task');} }
