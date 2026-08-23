<?php
/**
 * @package   Genesis Product Updater
 * @author    Dazzle Software https://dazzlesoftware.org
 * @copyright Copyright (C) 2026 Dazzle Software, LLC
 * @license   GNU/GPLv3 and later
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$wa = $this->getDocument()->getWebAssetManager();
$wa->registerAndUseStyle('com_genesisproductupdater.downloads', 'com_genesisproductupdater/downloads.css');
$wa->registerAndUseScript('com_genesisproductupdater.downloads', 'com_genesisproductupdater/downloads.js');
$params = ComponentHelper::getParams('com_genesisproductupdater');
$style = sprintf('--pu-accent:%s;--pu-button-text:%s;--pu-download-hover-bg:%s;--pu-download-hover-text:%s;--pu-radius:%dpx;--pu-ios-bg:%s;--pu-ios-text:%s;--pu-ios-hover-bg:%s;--pu-ios-hover-text:%s;--pu-android-bg:%s;--pu-android-text:%s;--pu-android-hover-bg:%s;--pu-android-hover-text:%s', $params->get('download_accent', '#914dad'), $params->get('download_text', '#ffffff'), $params->get('download_hover_accent', '#743d8a'), $params->get('download_hover_text', '#ffffff'), (int) $params->get('download_radius', 3), $params->get('ios_bg', '#000000'), $params->get('ios_text', '#ffffff'), $params->get('ios_hover_bg', '#333333'), $params->get('ios_hover_text', '#ffffff'), $params->get('android_bg', '#01875f'), $params->get('android_text', '#ffffff'), $params->get('android_hover_bg', '#016b4b'), $params->get('android_hover_text', '#ffffff'));
$style .= sprintf(';--pu-fab-bg:%s;--pu-fab-text:%s;--pu-fab-hover-bg:%s;--pu-fab-hover-text:%s', $params->get('fab_bg', '#252525'), $params->get('fab_text', '#ffffff'), $params->get('fab_hover_bg', '#3b3b3b'), $params->get('fab_hover_text', '#ffffff'));
$icons = (bool) $params->get('download_icons', 1);
$bootstrap = (bool) $params->get('download_bootstrap', 0);
$downloadIcon = '<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 3v12m0 0 5-5m-5 5-5-5M5 21h14"/></svg>';
$changelogIcon = '<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M3 12a9 9 0 1 0 3-6.7L3 8m0 0h5M12 7v5l3 2"/></svg>';
$appleIcon = '<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M16.7 13.2c0-2.4 2-3.6 2.1-3.7-1.1-1.7-2.9-1.9-3.6-1.9-1.5-.2-3 .9-3.8.9-.8 0-2-.9-3.3-.9-1.7 0-3.3 1-4.2 2.5-1.8 3.1-.5 7.8 1.3 10.3.9 1.2 1.9 2.6 3.3 2.5 1.3-.1 1.8-.8 3.4-.8 1.6 0 2 .8 3.4.8 1.4 0 2.3-1.3 3.2-2.5 1-1.4 1.4-2.9 1.4-3-.1 0-3.2-1.2-3.2-4.2zM14.2 6c.7-.9 1.2-2.1 1.1-3.3-1.1 0-2.4.7-3.2 1.6-.7.8-1.3 2-1.1 3.2 1.2.1 2.4-.6 3.2-1.5z"/></svg>';
$androidIcon = '<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M3 2.7v18.6L14.8 12 3 2.7zm13.2 8.2 2.7-2.1-3.4-2-2.1 3.3 2.8.8zm-2.8 3 2.1 3.3 3.4-2-2.7-2.1-2.8.8z"/></svg>';
$formatSize = static function (int $bytes): string { if ($bytes <= 0) return ''; $units = ['B','KB','MB','GB']; $i = min(3, (int) floor(log($bytes, 1024))); return round($bytes / (1024 ** $i), 2) . ' ' . $units[$i]; };
?>
<section class="pu-downloads<?php echo $bootstrap ? ' pu-use-bootstrap' : ''; ?>" style="<?php echo $this->escape($style); ?>">
    <?php foreach ($this->categories as $category => $products) : ?>
        <section class="pu-download-category">
            <h2><?php echo $this->escape($category); ?></h2>
            <div class="pu-download-grid<?php echo $bootstrap ? ' row g-4' : ''; ?>">
                <?php foreach ($products as $product) : $row = $product['current_version']; ?>
                    <article class="pu-download-card<?php echo $bootstrap ? ' col-md-6 col-xl-4 card h-100' : ''; ?>">
                        <h3><?php echo $this->escape($product['title']); ?></h3>
						<?php if (!empty($product['preview_image'])) : $preview = preg_match('#^https?://#i', $product['preview_image']) ? $product['preview_image'] : Uri::root() . ltrim($product['preview_image'], '/'); ?><img class="pu-download-card__preview" src="<?php echo $this->escape($preview); ?>" alt="<?php echo $this->escape($product['title']); ?>"><?php endif; ?>
                        <p class="pu-download-card__version">Version <?php echo $this->escape($row['version'] ?? ''); ?><?php if (!empty($row['release_date'])) : ?> &middot; <?php echo $this->escape($row['release_date']); ?><?php endif; ?></p>
                        <?php if ($product['description']) : ?><p><?php echo $this->escape($product['description']); ?></p><?php endif; ?>
						<?php if ($product['platform'] === 'fab' && !empty($row['url_fab'])) : ?><div class="pu-download-card__actions"><a class="pu-store-button pu-store-button--fab<?php echo $bootstrap ? ' btn' : ''; ?>" href="<?php echo $this->escape($row['url_fab']); ?>" target="_blank" rel="noopener"><?php if ($icons) : ?><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 2 22 8v8l-10 6L2 16V8l10-6zm0 4.1L6 9.6v4.8l6 3.5 6-3.5V9.6l-6-3.5zm0 2.3 4 2.3v2.6l-4 2.3-4-2.3v-2.6l4-2.3z"/></svg><?php endif; ?>Fab</a></div><?php endif; ?>
                        <div class="pu-download-card__actions">
							<?php if ($product['platform'] === 'mobile') : ?><?php if (!empty($row['url_ios'])) : ?><a class="pu-store-button pu-store-button--ios<?php echo $bootstrap ? ' btn' : ''; ?>" href="<?php echo $this->escape($row['url_ios']); ?>" target="_blank" rel="noopener"><?php if ($icons) echo $appleIcon; ?>App Store</a><?php endif; ?><?php if (!empty($row['url_android'])) : ?><a class="pu-store-button pu-store-button--android<?php echo $bootstrap ? ' btn' : ''; ?>" href="<?php echo $this->escape($row['url_android']); ?>" target="_blank" rel="noopener"><?php if ($icons) echo $androidIcon; ?>Google Play</a><?php endif; ?><?php elseif (!empty($row['download_url'])) : ?><a class="pu-download-button<?php echo $bootstrap ? ' btn btn-primary' : ''; ?>" href="<?php echo Route::_('index.php?option=com_genesisproductupdater&task=download.track&id=' . (int) $product['id']); ?>"><?php if ($icons) echo $downloadIcon; ?>Download</a><?php endif; ?>
							<?php if ($product['changelog_entries']) : ?><button class="pu-changelog-button<?php echo $bootstrap ? ' btn btn-outline-primary' : ''; ?>" type="button" data-pu-dialog="pu-changelog-<?php echo (int) $product['id']; ?>"><?php if ($icons) echo $changelogIcon; ?>Changelog</button><?php endif; ?>
                        </div>
						<?php if ($product['platform'] !== 'mobile' && !empty($row['download_url'])) : ?><p class="pu-download-card__stats"><?php if ($product['download_size']) : ?><?php echo $this->escape($formatSize((int) $product['download_size'])); ?> / <?php endif; ?><?php echo (int) $product['download_count']; ?> download<?php echo (int) $product['download_count'] === 1 ? '' : 's'; ?></p><?php endif; ?>
                    </article>
					<?php if ($product['changelog_entries']) : ?><dialog class="pu-changelog-dialog" id="pu-changelog-<?php echo (int) $product['id']; ?>"><button class="pu-dialog-close" type="button" aria-label="Close changelog">&times;</button><h2><?php echo $this->escape($product['title']); ?> Changelog</h2><div class="pu-changelog-dialog__content"><?php foreach ($product['changelog_entries'] as $entry) : ?><h4><?php echo $this->escape($entry['version']); ?><?php if ($entry['date']) : ?> &mdash; <?php echo $this->escape($entry['date']); ?><?php endif; ?></h4><?php foreach ($entry['categories'] as $category => $groups) : ?><p><strong><?php echo $this->escape(ucfirst($category)); ?></strong></p><ul><?php foreach ($groups as $group) : foreach (preg_split('/\r\n|\r|\n/', $group) as $line) : if (trim($line) !== '') : ?><li><?php echo $this->escape(trim($line)); ?></li><?php endif; endforeach; endforeach; ?></ul><?php endforeach; endforeach; ?></div></dialog><?php endif; ?>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>
</section>
