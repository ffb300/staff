<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Staff
 * @author     Adi Badiozaman <adi@psp.edu.my>
 * @copyright  2023 PSP
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Layout\LayoutHelper;
use \Joomla\CMS\Session\Session;
use \Joomla\CMS\User\UserFactoryInterface;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', 'select');

$user       = Factory::getApplication()->getIdentity();
$userId     = $user->get('id');
$listOrder  = $this->state->get('list.ordering');
$listDirn   = $this->state->get('list.direction');
$canCreate  = $user->authorise('core.create', 'com_staff') && file_exists(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'nameform.xml');
$canEdit    = $user->authorise('core.edit', 'com_staff') && file_exists(JPATH_COMPONENT .  DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'nameform.xml');
$canCheckin = $user->authorise('core.manage', 'com_staff');
$canChange  = $user->authorise('core.edit.state', 'com_staff');
$canDelete  = $user->authorise('core.delete', 'com_staff');

// Import CSS
$wa = $this->document->getWebAssetManager();
$wa->useStyle('com_staff.list');
?>

<form action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>" method="post"
	  name="adminForm" id="adminForm">
	<?php if(!empty($this->filterForm)) { echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); } ?>
	<div class="table-responsive">
		<table class="table table-striped" id="nameList">
			<thead>
			<tr>
				
					<th class=''>
						<?php echo HTMLHelper::_('grid.sort',  'COM_STAFF_NAMES_NAME', 'a.name', $listDirn, $listOrder); ?>
					</th>

					<th class=''>
						<?php echo HTMLHelper::_('grid.sort',  'COM_STAFF_NAMES_EMAIL', 'a.email', $listDirn, $listOrder); ?>
					</th>

					<th class=''>
						<?php echo HTMLHelper::_('grid.sort',  'COM_STAFF_NAMES_TELEPHONE', 'a.telephone', $listDirn, $listOrder); ?>
					</th>

					<th class=''>
						<?php echo HTMLHelper::_('grid.sort',  'COM_STAFF_NAMES_POSITION', 'a.position', $listDirn, $listOrder); ?>
					</th>

					<th class=''>
						<?php echo HTMLHelper::_('grid.sort',  'COM_STAFF_NAMES_DEPARTMENT', 'a.department', $listDirn, $listOrder); ?>
					</th>

						<?php if ($canEdit || $canDelete): ?>
					<th class="center">
						<?php echo Text::_('COM_STAFF_NAMES_ACTIONS'); ?>
					</th>
					<?php endif; ?>

			</tr>
			</thead>
			<tfoot>
			<tr>
				<td colspan="<?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?>">
					<div class="pagination">
						<?php echo $this->pagination->getPagesLinks(); ?>
					</div>
				</td>
			</tr>
			</tfoot>
			<tbody>
			<?php foreach ($this->items as $i => $item) : ?>
				<?php $canEdit = $user->authorise('core.edit', 'com_staff'); ?>
				<?php if (!$canEdit && $user->authorise('core.edit.own', 'com_staff')): ?>
				<?php $canEdit = Factory::getApplication()->getIdentity()->id == $item->created_by; ?>
				<?php endif; ?>

				<tr class="row<?php echo $i % 2; ?>">
					
					<td>
						<?php $canCheckin = Factory::getApplication()->getIdentity()->authorise('core.manage', 'com_staff.' . $item->id) || $item->checked_out == Factory::getApplication()->getIdentity()->id; ?>
						<?php if($canCheckin && $item->checked_out > 0) : ?>
							<a href="<?php echo Route::_('index.php?option=com_staff&task=name.checkin&id=' . $item->id .'&'. Session::getFormToken() .'=1'); ?>">
							<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'name.', false); ?></a>
						<?php endif; ?>
						<a href="<?php echo Route::_('index.php?option=com_staff&view=name&id='.(int) $item->id); ?>">
							<?php echo $this->escape($item->name); ?></a>
					</td>
					<td>
						<?php echo $item->email; ?>
					</td>
					<td>
						<?php echo $item->telephone; ?>
					</td>
					<td>
						<?php echo $item->position; ?>
					</td>
					<td>
						<?php echo $item->department; ?>
					</td>
					<?php if ($canEdit || $canDelete): ?>
						<td class="center">
							<?php $canCheckin = Factory::getApplication()->getIdentity()->authorise('core.manage', 'com_staff.' . $item->id) || $item->checked_out == Factory::getApplication()->getIdentity()->id; ?>

							<?php if($canEdit && $item->checked_out == 0): ?>
								<a href="<?php echo Route::_('index.php?option=com_staff&task=name.edit&id=' . $item->id, false, 2); ?>" class="btn btn-mini" type="button"><i class="icon-edit" ></i></a>
							<?php endif; ?>
							<?php if ($canDelete): ?>
								<a href="<?php echo Route::_('index.php?option=com_staff&task=nameform.remove&id=' . $item->id, false, 2); ?>" class="btn btn-mini delete-button" type="button"><i class="icon-trash" ></i></a>
							<?php endif; ?>
						</td>
					<?php endif; ?>

				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php if ($canCreate) : ?>
		<a href="<?php echo Route::_('index.php?option=com_staff&task=nameform.edit&id=0', false, 0); ?>"
		   class="btn btn-success btn-small"><i
				class="icon-plus"></i>
			<?php echo Text::_('COM_STAFF_ADD_ITEM'); ?></a>
	<?php endif; ?>

	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value=""/>
	<input type="hidden" name="filter_order_Dir" value=""/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>

<?php
	if($canDelete) {
		$wa->addInlineScript("
			jQuery(document).ready(function () {
				jQuery('.delete-button').click(deleteItem);
			});

			function deleteItem() {

				if (!confirm(\"" . Text::_('COM_STAFF_DELETE_MESSAGE') . "\")) {
					return false;
				}
			}
		", [], [], ["jquery"]);
	}
?>