<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Staff
 * @author     Adi Badiozaman <adi@psp.edu.my>
 * @copyright  2023 PSP
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Staff\Component\Staff\Api\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\ApiController;

/**
 * The Names controller
 *
 * @since  1.0.0
 */
class NamesController extends ApiController 
{
	/**
	 * The content type of the item.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $contentType = 'names';

	/**
	 * The default view for the display method.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $default_view = 'names';
}