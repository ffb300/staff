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

$elements = StaffHelper::getList($params);

$tableField = explode(':', $params->get('field'));
$table_name = !empty($tableField[0]) ? $tableField[0] : '';
$field_name = !empty($tableField[1]) ? $tableField[1] : '';
?>

<?php if (!empty($elements)) : ?>
	<table class="jcc-table">
		<?php foreach ($elements as $element) : ?>
			<tr>
				<th><?php echo StaffHelper::renderTranslatableHeader($table_name, $field_name); ?></th>
				<td><?php echo StaffHelper::renderElement(
						$table_name, $params->get('field'), $element->{$field_name}
					); ?></td>
			</tr>
		<?php endforeach; ?>
	</table>
<?php endif;
