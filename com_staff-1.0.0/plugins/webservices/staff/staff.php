<?php
/**
 * @package    Com_Staff
 * @author     Adi Badiozaman <adi@psp.edu.my>
 * @copyright  2023 PSP
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\ApiRouter;

/**
 * Web Services adapter for staff.
 *
 * @since  1.0.0
 */
class PlgWebservicesStaff extends CMSPlugin
{
	public function onBeforeApiRoute(&$router)
	{
		
		$router->createCRUDRoutes('v1/staff/names', 'names', ['component' => 'com_staff']);
	}
}
