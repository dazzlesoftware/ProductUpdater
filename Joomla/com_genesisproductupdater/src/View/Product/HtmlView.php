<?php
/**
 * @package   Genesis Product Updater
 * @author    Dazzle Software https://dazzlesoftware.org
 * @copyright Copyright (C) 2026 Dazzle Software, LLC
 * @license   GNU/GPLv3 and later
 */

namespace Joomla\Component\Genesisproductupdater\Administrator\View\Product;

\defined('_JEXEC') or die;

use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\MVC\View\FormView;
use Joomla\Component\Genesisproductupdater\Administrator\Helper\ProductUpdaterHelper;

/**
 * View class for a single product (add/edit form).
 */
class HtmlView extends FormView
{
    /**
     * Per-platform generated-file status, keyed by platform slug.
     *
     * @var  array
     */
    public $platformStatus = [];

    /**
     * @inheritDoc
     */
    public function __construct(array $config)
    {
        if (empty($config['option'])) {
            $config['option'] = 'com_genesisproductupdater';
        }

        $config['toolbar_icon'] = 'generic';

        parent::__construct($config);
    }

    /**
     * @inheritDoc
     */
    protected function initializeView()
    {
        parent::initializeView();

        $this->canDo = ContentHelper::getActions('com_genesisproductupdater');

        $this->form->addControlField('task');

        $product = (array) $this->item;

        if (isset($product['versions']) && \is_string($product['versions'])) {
            $product['versions'] = json_decode($product['versions'], true) ?: [];
        }

        if (!empty($product['id'])) {
            foreach (ProductUpdaterHelper::getRegistry()->getPlatforms() as $slug => $platform) {
				if ($slug !== ($product['platform'] ?? '')) {
					continue;
				}
                $path   = ProductUpdaterHelper::getProductFilePath($product, $platform);
                $exists = is_file($path);

                $this->platformStatus[$slug] = [
                    'label'  => $platform->getLabel(),
                    'exists' => $exists,
                    'path'   => $path,
                    'url'    => $exists ? ProductUpdaterHelper::getPublicUrl($path) : null,
                ];
            }
        }
    }
}
