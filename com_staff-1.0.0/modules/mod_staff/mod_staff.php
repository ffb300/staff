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

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Staff\Module\Staff\Site\Helper\StaffHelper;

$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wr = $wa->getRegistry();
$wr->addRegistryFile('media/mod_staff/joomla.asset.json');
$wa->useStyle('mod_staff.style')
    ->useScript('mod_staff.script');

require ModuleHelper::getLayoutPath('mod_staff', $params->get('content_type', 'blank'));
