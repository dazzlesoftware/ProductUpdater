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
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var \Joomla\Component\Genesisupdater\Administrator\View\Products\HtmlView $this */

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('table.columns')->useScript('multiselect');

$user      = $this->getCurrentUser();
$userId    = $user->id;
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$canOrder  = $user->authorise('core.edit.state', 'com_genesisupdater');
$saveOrder = $listOrder === 'a.ordering';

if ($saveOrder && !empty($this->items)) {
    $saveOrderingUrl = 'index.php?option=com_genesisupdater&task=products.saveOrderAjax&tmpl=component';
    HTMLHelper::_('draggablelist.draggable');
}
?>
<form action="<?php echo Route::_('index.php?option=com_genesisupdater&view=products'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
				<?php if (empty($this->items)) : ?>
					<div class="alert alert-info">
						<span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
						<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
					</div>
				<?php else : ?>
					<table class="table" id="productList">
						<caption class="visually-hidden">
							<?php echo Text::_('COM_GENESISUPDATER_PRODUCTS_TABLE_CAPTION'); ?>
						</caption>
						<thead>
							<tr>
								<td style="width:1%" class="text-center">
									<?php echo HTMLHelper::_('grid.checkall'); ?>
								</td>
								<th scope="col" style="width:1%" class="text-center">
									<?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
								</th>
								<th scope="col" style="width:1%" class="text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
								</th>
								<th scope="col">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_GENESISUPDATER_FIELD_TITLE_LABEL', 'a.title', $listDirn, $listOrder); ?>
								</th>
								<th scope="col">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_GENESISUPDATER_FIELD_ELEMENT_LABEL', 'a.element', $listDirn, $listOrder); ?>
								</th>
								<th scope="col">
									<?php echo Text::_('COM_GENESISUPDATER_FIELD_TYPE_LABEL'); ?>
								</th>
								<th scope="col" style="width:6%" class="text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
								</th>
							</tr>
						</thead>
						<tbody <?php if ($saveOrder) : ?>class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="false"<?php endif; ?>>
						<?php foreach ($this->items as $i => $item) :
							$canEdit   = $user->authorise('core.edit', 'com_genesisupdater');
							$canChange = $user->authorise('core.edit.state', 'com_genesisupdater');
							?>
							<tr class="tr-<?php echo (int) $item->id; ?>" data-draggable-group="1">
								<td class="text-center">
									<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
								</td>
								<td class="text-center">
									<?php
									$iconClass = '';
									if (!$canChange) {
										$iconClass = ' inactive';
									} elseif (!$saveOrder) {
										$iconClass = ' inactive tip-top" title="' . HTMLHelper::_('tooltipText', 'JORDERINGDISABLED');
									}
									?>
									<span class="sortable-handle<?php echo $iconClass; ?>">
										<span class="icon-ellipsis-v" aria-hidden="true"></span>
									</span>
								</td>
								<td class="text-center">
									<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'products.', $canChange, 'cb'); ?>
								</td>
								<th scope="row">
									<?php if ($canEdit) : ?>
										<a href="<?php echo Route::_('index.php?option=com_genesisupdater&task=product.edit&id=' . (int) $item->id); ?>">
											<?php echo $this->escape($item->title); ?>
										</a>
									<?php else : ?>
										<?php echo $this->escape($item->title); ?>
									<?php endif; ?>
								</th>
								<td>
									<?php echo $this->escape($item->element); ?>
								</td>
								<td>
									<?php echo Text::_('COM_GENESISUPDATER_TYPE_' . strtoupper($item->type)); ?>
								</td>
								<td class="text-center">
									<?php echo (int) $item->id; ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
					<?php echo $this->pagination->getListFooter(); ?>
				<?php endif; ?>
				<input type="hidden" name="task" value="">
				<input type="hidden" name="boxchecked" value="0">
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
