<?php
/**
 * @package   Genesis Updater
 * @author    Dazzle Software https://dazzlesoftware.org
 * @copyright Copyright (C) 2026 Dazzle Software, LLC
 * @license   GNU/GPLv3 and later
 */

namespace Joomla\Component\Genesisupdater\Administrator\View\Changelogs;

\defined('_JEXEC') or die;

use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\MVC\View\ListView;

/**
 * View class for a list of changelogs.
 */
class HtmlView extends ListView
{
    /**
     * @var  string
     */
    protected $helpLink = 'Changelogs';

    /**
     * @inheritDoc
     */
    public function __construct(array $config)
    {
        if (empty($config['option'])) {
            $config['option'] = 'com_genesisupdater';
        }

        $config['toolbar_icon']   = 'generic';
        $config['supports_batch'] = false;

        parent::__construct($config);
    }

    /**
     * @inheritDoc
     */
    protected function initializeView()
    {
        parent::initializeView();

        $this->canDo = ContentHelper::getActions('com_genesisupdater');
    }

    protected function addToolbar()
    {
        parent::addToolbar();
        if ($this->canDo->get('core.edit')) {
            $this->getDocument()->getToolbar()->standardButton('generate-all')
                ->text('COM_GENESISUPDATER_TOOLBAR_GENERATE_ALL_CHANGELOGS')
                ->icon('icon-refresh')
                ->task('changelogs.generateAll');
        }
    }
}
