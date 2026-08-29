<?php
/**
 * @package   Genesis Updater
 * @author    Dazzle Software https://dazzlesoftware.org
 * @copyright Copyright (C) 2026 Dazzle Software, LLC
 * @license   GNU/GPLv3 and later
 */

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var \Joomla\Component\Genesisupdater\Administrator\View\Product\HtmlView $this */

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')->useScript('form.validate');
$adminScript = @file_get_contents(JPATH_ROOT . '/media/com_genesisupdater/js/admin.js');
if ($adminScript !== false) {
	$wa->addInlineScript($adminScript);
}

$input = $this->item->id ? 'index.php?option=com_genesisupdater&task=product.edit&id=' . (int) $this->item->id : 'index.php?option=com_genesisupdater&task=product.add';
?>
<form action="<?php echo Route::_($input); ?>" method="post" name="adminForm" id="product-form" aria-label="<?php echo Text::_('COM_GENESISUPDATER_PRODUCT_FORM_ARIA_LABEL', true); ?>" class="form-validate">
	<div class="row">
		<div class="col-lg-9">
			<div class="card">
				<div class="card-body">
					<?php echo $this->form->renderFieldset('details'); ?>
					<hr>
					<?php echo $this->form->renderFieldset('versions'); ?>
				</div>
			</div>
		</div>
		<div class="col-lg-3">
			<?php if (!empty($this->item->id)) : ?>
				<div class="card">
					<div class="card-header">
						<?php echo Text::_('COM_GENESISUPDATER_GENERATED_FILES'); ?>
					</div>
					<div class="card-body">
						<?php if (empty($this->platformStatus)) : ?>
							<p class="text-muted"><?php echo Text::_('COM_GENESISUPDATER_NO_PLATFORMS_REGISTERED'); ?></p>
						<?php else : ?>
							<ul class="list-unstyled">
								<?php foreach ($this->platformStatus as $slug => $status) : ?>
									<li class="mb-2">
										<strong><?php echo $this->escape($status['label']); ?></strong>
										<?php if ($status['exists']) : ?>
											<span class="badge bg-success"><?php echo Text::_('COM_GENESISUPDATER_FILE_EXISTS'); ?></span>
											<?php if (!empty($status['url'])) : ?>
												<br><a href="<?php echo $this->escape($status['url']); ?>" target="_blank" rel="noopener"><?php echo Text::_('COM_GENESISUPDATER_VIEW_FILE'); ?></a>
											<?php else : ?>
												<br><small class="text-muted"><?php echo $this->escape($status['path']); ?></small>
											<?php endif; ?>
										<?php else : ?>
											<span class="badge bg-secondary"><?php echo Text::_('COM_GENESISUPDATER_FILE_MISSING'); ?></span>
										<?php endif; ?>
									</li>
								<?php endforeach; ?>
							</ul>
							<button type="button" class="btn btn-primary btn-sm" onclick="Joomla.submitbutton('product.generate')">
								<?php echo Text::_('COM_GENESISUPDATER_GENERATE_FILES'); ?>
							</button>
						<?php endif; ?>
					</div>
				</div>
			<?php else : ?>
				<div class="alert alert-info">
					<?php echo Text::_('COM_GENESISUPDATER_SAVE_TO_GENERATE'); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<input type="hidden" name="task" value="">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
