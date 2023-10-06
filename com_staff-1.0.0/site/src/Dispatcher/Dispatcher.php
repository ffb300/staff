<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Staff
 * @author     Adi Badiozaman <adi@psp.edu.my>
 * @copyright  2023 PSP
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Staff\Component\Staff\Site\Dispatcher;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcher;
use Joomla\CMS\Language\Text;

/**
 * ComponentDispatcher class for Com_Staff
 *
 * @since  1.0.0
 */
class Dispatcher extends ComponentDispatcher
{
	/**
	 * Dispatch a controller task. Redirecting the user if appropriate.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function dispatch()
	{
		parent::dispatch();
	}
}
