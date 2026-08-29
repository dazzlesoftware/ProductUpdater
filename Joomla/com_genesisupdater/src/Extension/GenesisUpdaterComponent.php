<?php
/**
 * @package   Genesis Updater
 * @author    Dazzle Software https://dazzlesoftware.org
 * @copyright Copyright (C) 2026 Dazzle Software, LLC
 * @license   GNU/GPLv3 and later
 */

namespace Joomla\Component\Genesisupdater\Administrator\Extension;

\defined('_JEXEC') or die;

use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Psr\Container\ContainerInterface;

/**
 * Component class for com_genesisupdater.
 */
class GenesisUpdaterComponent extends MVCComponent implements BootableExtensionInterface
{
    /**
     * Booting the extension. This is the point to attach event listeners and
     * do other things such as loading language files, registering routes, etc.
     *
     * @param   ContainerInterface  $container  The container to get extensions from.
     *
     * @return  void
     */
    public function boot(ContainerInterface $container)
    {
        // Nothing extra to boot at this time; platform registration is
        // handled lazily by Platform\PlatformRegistry when generation runs.
    }
}
