<?php
/**
 * @version     CVS: 1.0.0
 * @package     com_staff
 * @subpackage  mod_staff
 * @author      Adi Badiozaman <adi@psp.edu.my>
 * @copyright   2023 PSP
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Staff\Module\Staff\Site\Helper\StaffHelper;

$element = StaffHelper::getItem($params);
?>

<?php if (!empty($element)) : ?>
	<div>
		<?php $fields = get_object_vars($element); ?>
		<?php foreach ($fields as $field_name => $field_value) : ?>
			<?php if (StaffHelper::shouldAppear($field_name)): ?>
				<div class="row">
					<div class="span4">
						<strong><?php echo StaffHelper::renderTranslatableHeader($params->get('item_table'), $field_name); ?></strong>
					</div>
					<div
						class="span8"><?php echo StaffHelper::renderElement($params->get('item_table'), $field_name, $field_value); ?></div>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
<?php endif;
