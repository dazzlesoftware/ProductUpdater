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

/** @var \Joomla\Component\Genesisupdater\Administrator\View\Changelog\HtmlView $this */

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')->useScript('form.validate');

$input = $this->item->id ? 'index.php?option=com_genesisupdater&task=changelog.edit&id=' . (int) $this->item->id : 'index.php?option=com_genesisupdater&task=changelog.add';
?>
<form action="<?php echo Route::_($input); ?>" method="post" name="adminForm" id="changelog-form" aria-label="<?php echo Text::_('COM_GENESISUPDATER_CHANGELOG_FORM_ARIA_LABEL', true); ?>" class="form-validate">
	<div class="row">
		<div class="col-lg-9">
			<div class="card">
				<div class="card-body">
					<?php echo $this->form->renderFieldset('details'); ?>
					<hr>
					<?php echo $this->form->renderFieldset('entries'); ?>
				</div>
			</div>
		</div>
		<div class="col-lg-3">
			<?php if (!empty($this->item->id)) : ?>
				<div class="card">
					<div class="card-body">
						<button type="button" class="btn btn-primary btn-sm" onclick="Joomla.submitbutton('changelog.generate')">
							<?php echo Text::_('COM_GENESISUPDATER_GENERATE_FILES'); ?>
						</button>
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
