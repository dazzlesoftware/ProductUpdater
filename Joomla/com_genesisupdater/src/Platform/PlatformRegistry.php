<?php
/**
 * @package   Genesis Updater
 * @author    Dazzle Software https://dazzlesoftware.org
 * @copyright Copyright (C) 2026 Dazzle Software, LLC
 * @license   GNU/GPLv3 and later
 */

namespace Joomla\Component\Genesisupdater\Administrator\Platform;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Event\Event;

/**
 * Collects the platform generators registered by the "genesisupdaterplatform"
 * plugin group. Each plugin listens for the `onGenesisUpdaterRegisterPlatforms`
 * event and calls back into `register()` on the instance handed to it, so new
 * platforms can be added by simply installing a new plugin - no core changes
 * required.
 */
class PlatformRegistry
{
    /**
     * @var  PlatformInterface[]
     */
    private array $platforms = [];

    /**
     * @var  bool
     */
    private bool $discovered = false;

    /**
     * Registers a platform generator instance.
     *
     * @param   PlatformInterface  $platform  The platform to register.
     *
     * @return  void
     */
    public function register(PlatformInterface $platform): void
    {
        $this->platforms[$platform->getSlug()] = $platform;
    }

    /**
     * Returns all registered platforms, discovering them via the plugin
     * event on first use.
     *
     * @return  PlatformInterface[]
     */
    public function getPlatforms(): array
    {
        $this->discover();

        return $this->platforms;
    }

    /**
     * Returns a single platform by slug, or null if not registered.
     *
     * @param   string  $slug  The platform slug.
     *
     * @return  PlatformInterface|null
     */
    public function getPlatform(string $slug): ?PlatformInterface
    {
        $this->discover();

        return $this->platforms[$slug] ?? null;
    }

    /**
     * Imports and dispatches the registration event to every installed
     * "genesisupdaterplatform" plugin exactly once per request.
     *
     * @return  void
     */
    private function discover(): void
    {
        if ($this->discovered) {
            return;
        }

        $this->discovered = true;

        // Match the generators bundled with the WordPress plugin. A Joomla
        // plugin can still replace any slug by registering it afterwards.
        $this->register(new JoomlaPlatform());
        $this->register(new WordPressPlatform());
        $this->register(new MobilePlatform());
        $this->register(new FabPlatform());

        $app = Factory::getApplication();

		if ($app instanceof CMSApplicationInterface) {
			PluginHelper::importPlugin('genesisupdaterplatform');
			$app->getDispatcher()->dispatch(
				'onGenesisUpdaterRegisterPlatforms',
				new Event('onGenesisUpdaterRegisterPlatforms', ['registry' => $this])
			);
		}
    }
}
