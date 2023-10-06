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
use \Joomla\CMS\Session\Session;
use Joomla\Utilities\ArrayHelper;

$canEdit = Factory::getApplication()->getIdentity()->authorise('core.edit', 'com_staff');

if (!$canEdit && Factory::getApplication()->getIdentity()->authorise('core.edit.own', 'com_staff'))
{
	$canEdit = Factory::getApplication()->getIdentity()->id == $this->item->created_by;
}
?>

<div class="item_fields">

	<table class="table">
		

		<tr>
			<th><?php echo Text::_('COM_STAFF_FORM_LBL_NAME_NAME'); ?></th>
			<td><?php echo $this->item->name; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_STAFF_FORM_LBL_NAME_EMAIL'); ?></th>
			<td><?php echo $this->item->email; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_STAFF_FORM_LBL_NAME_TELEPHONE'); ?></th>
			<td><?php echo $this->item->telephone; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_STAFF_FORM_LBL_NAME_POSITION'); ?></th>
			<td><?php echo $this->item->position; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_STAFF_FORM_LBL_NAME_DEPARTMENT'); ?></th>
			<td><?php echo $this->item->department; ?></td>
		</tr>

	</table>

</div>

<?php $canCheckin = Factory::getApplication()->getIdentity()->authorise('core.manage', 'com_staff.' . $this->item->id) || $this->item->checked_out == Factory::getApplication()->getIdentity()->id; ?>
	<?php if($canEdit && $this->item->checked_out == 0): ?>

	<a class="btn btn-outline-primary" href="<?php echo Route::_('index.php?option=com_staff&task=name.edit&id='.$this->item->id); ?>"><?php echo Text::_("COM_STAFF_EDIT_ITEM"); ?></a>
	<?php elseif($canCheckin && $this->item->checked_out > 0) : ?>
	<a class="btn btn-outline-primary" href="<?php echo Route::_('index.php?option=com_staff&task=name.checkin&id=' . $this->item->id .'&'. Session::getFormToken() .'=1'); ?>"><?php echo Text::_("JLIB_HTML_CHECKIN"); ?></a>

<?php endif; ?>

<?php if (Factory::getApplication()->getIdentity()->authorise('core.delete','com_staff.name.'.$this->item->id)) : ?>

	<a class="btn btn-danger" rel="noopener noreferrer" href="#deleteModal" role="button" data-bs-toggle="modal">
		<?php echo Text::_("COM_STAFF_DELETE_ITEM"); ?>
	</a>

	<?php echo HTMLHelper::_(
                                    'bootstrap.renderModal',
                                    'deleteModal',
                                    array(
                                        'title'  => Text::_('COM_STAFF_DELETE_ITEM'),
                                        'height' => '50%',
                                        'width'  => '20%',
                                        
                                        'modalWidth'  => '50',
                                        'bodyHeight'  => '100',
                                        'footer' => '<button class="btn btn-outline-primary" data-bs-dismiss="modal">Close</button><a href="' . Route::_('index.php?option=com_staff&task=name.remove&id=' . $this->item->id, false, 2) .'" class="btn btn-danger">' . Text::_('COM_STAFF_DELETE_ITEM') .'</a>'
                                    ),
                                    Text::sprintf('COM_STAFF_DELETE_CONFIRM', $this->item->id)
                                ); ?>

<?php endif; ?>