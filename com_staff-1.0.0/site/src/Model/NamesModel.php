<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Staff
 * @author     Adi Badiozaman <adi@psp.edu.my>
 * @copyright  2023 PSP
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Staff\Component\Staff\Site\Model;
// No direct access.
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\MVC\Model\ListModel;
use \Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use \Joomla\CMS\Helper\TagsHelper;
use \Joomla\CMS\Layout\FileLayout;
use \Joomla\Database\ParameterType;
use \Joomla\Utilities\ArrayHelper;
use \Staff\Component\Staff\Site\Helper\StaffHelper;


/**
 * Methods supporting a list of Staff records.
 *
 * @since  1.0.0
 */
class NamesModel extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see    JController
	 * @since  1.0.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'state', 'a.state',
				'ordering', 'a.ordering',
				'created_by', 'a.created_by',
				'modified_by', 'a.modified_by',
				'name', 'a.name',
				'email', 'a.email',
				'telephone', 'a.telephone',
				'position', 'a.position',
				'department', 'a.department',
			);
		}

		parent::__construct($config);
	}

	
       /**
        * Checks whether or not a user is manager or super user
        *
        * @return bool
        */
        public function isAdminOrSuperUser()
        {
            try{
                $user = Factory::getApplication()->getIdentity();
                return in_array("8", $user->groups) || in_array("7", $user->groups);
            }catch(\Exception $exc){
                return false;
            }
        }

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   Elements order
	 * @param   string  $direction  Order direction
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// List state information.
		parent::populateState('a.position', 'ASC');

		$app = Factory::getApplication();
		$list = $app->getUserState($this->context . '.list');

		$value = $app->getUserState($this->context . '.list.limit', $app->get('list_limit', 25));
		$list['limit'] = $value;
		
		$this->setState('list.limit', $value);

		$value = $app->input->get('limitstart', 0, 'uint');
		$this->setState('list.start', $value);

		$ordering  = $this->getUserStateFromRequest($this->context .'.filter_order', 'filter_order', 'a.position');
		$direction = strtoupper($this->getUserStateFromRequest($this->context .'.filter_order_Dir', 'filter_order_Dir', 'ASC'));
		
		if(!empty($ordering) || !empty($direction))
		{
			$list['fullordering'] = $ordering . ' ' . $direction;
		}

		$app->setUserState($this->context . '.list', $list);

		

		$context = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $context);

		// Split context into component and optional section
		if (!empty($context))
		{
			$parts = FieldsHelper::extract($context);

			if ($parts)
			{
				$this->setState('filter.component', $parts[0]);
				$this->setState('filter.section', $parts[1]);
			}
		}
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  DatabaseQuery
	 *
	 * @since   1.0.0
	 */
	protected function getListQuery()
	{
			// Create a new query object.
			$db    = $this->getDbo();
			$query = $db->getQuery(true);

			// Select the required fields from the table.
			$query->select(
						$this->getState(
								'list.select', 'DISTINCT a.*'
						)
				);

			$query->from('`#__staff_name` AS a');
			
		// Join over the users for the checked out user.
		$query->select('uc.name AS uEditor');
		$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Join over the created by field 'created_by'
		$query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');

		// Join over the created by field 'modified_by'
		$query->join('LEFT', '#__users AS modified_by ON modified_by.id = a.modified_by');
		// Join over the foreign key 'position'
		$query->select('`#__staff_position_3944836`.`name` AS positions_fk_value_3944836');
		$query->join('LEFT', '#__staff_position AS #__staff_position_3944836 ON #__staff_position_3944836.`name` = a.`position`');
		// Join over the foreign key 'department'
		$query->select('`#__staff_department_3944837`.`name` AS departments_fk_value_3944837');
		$query->join('LEFT', '#__staff_department AS #__staff_department_3944837 ON #__staff_department_3944837.`name` = a.`department`');
		if(!$this->isAdminOrSuperUser()){
			$query->where("a.created_by = " . Factory::getApplication()->getIdentity()->get("id"));
		}
			
		if (!Factory::getApplication()->getIdentity()->authorise('core.edit', 'com_staff'))
		{
			$query->where('a.state = 1');
		}
		else
		{
			$query->where('(a.state IN (0, 1))');
		}

			// Filter by search in title
			$search = $this->getState('filter.search');

			if (!empty($search))
			{
				if (stripos($search, 'id:') === 0)
				{
					$query->where('a.id = ' . (int) substr($search, 3));
				}
				else
				{
					$search = $db->Quote('%' . $db->escape($search, true) . '%');
					$query->where('( a.name LIKE ' . $search . ' )');
				}
			}
			

		// Filtering position
		$filter_position = $this->state->get("filter.position");

		if ($filter_position)
		{
			$query->where("a.`position` = '".$db->escape($filter_position)."'");
		}

		// Filtering department
		$filter_department = $this->state->get("filter.department");

		if ($filter_department)
		{
			$query->where("a.`department` = '".$db->escape($filter_department)."'");
		}

			
			
			// Add the list ordering clause.
			$orderCol  = $this->state->get('list.ordering', 'a.position');
			$orderDirn = $this->state->get('list.direction', 'ASC');

			if ($orderCol && $orderDirn)
			{
				$query->order($db->escape($orderCol . ' ' . $orderDirn));
			}

			return $query;
	}

	/**
	 * Method to get an array of data items
	 *
	 * @return  mixed An array of data on success, false on failure.
	 */
	public function getItems()
	{
		$items = parent::getItems();
		
		foreach ($items as $item)
		{

			if (isset($item->position))
			{

				$values    = explode(',', $item->position);
				$textValue = array();

				foreach ($values as $value)
				{
					$db    = $this->getDbo();
					$query = $db->getQuery(true);
					$query
						->select('`#__staff_position_3944836`.`name`')
						->from($db->quoteName('#__staff_position', '#__staff_position_3944836'))
						->where($db->quoteName('#__staff_position_3944836.name') . ' = '. $db->quote($db->escape($value)));

					$db->setQuery($query);
					$results = $db->loadObject();

					if ($results)
					{
						$textValue[] = $results->name;
					}
				}

				$item->position = !empty($textValue) ? implode(', ', $textValue) : $item->position;
			}


			if (isset($item->department))
			{

				$values    = explode(',', $item->department);
				$textValue = array();

				foreach ($values as $value)
				{
					$db    = $this->getDbo();
					$query = $db->getQuery(true);
					$query
						->select('`#__staff_department_3944837`.`name`')
						->from($db->quoteName('#__staff_department', '#__staff_department_3944837'))
						->where($db->quoteName('#__staff_department_3944837.name') . ' = '. $db->quote($db->escape($value)));

					$db->setQuery($query);
					$results = $db->loadObject();

					if ($results)
					{
						$textValue[] = $results->name;
					}
				}

				$item->department = !empty($textValue) ? implode(', ', $textValue) : $item->department;
			}

		}

		return $items;
	}

	/**
	 * Overrides the default function to check Date fields format, identified by
	 * "_dateformat" suffix, and erases the field if it's not correct.
	 *
	 * @return void
	 */
	protected function loadFormData()
	{
		$app              = Factory::getApplication();
		$filters          = $app->getUserState($this->context . '.filter', array());
		$error_dateformat = false;

		foreach ($filters as $key => $value)
		{
			if (strpos($key, '_dateformat') && !empty($value) && $this->isValidDate($value) == null)
			{
				$filters[$key]    = '';
				$error_dateformat = true;
			}
		}

		if ($error_dateformat)
		{
			$app->enqueueMessage(Text::_("COM_STAFF_SEARCH_FILTER_DATE_FORMAT"), "warning");
			$app->setUserState($this->context . '.filter', $filters);
		}

		return parent::loadFormData();
	}

	/**
	 * Checks if a given date is valid and in a specified format (YYYY-MM-DD)
	 *
	 * @param   string  $date  Date to be checked
	 *
	 * @return bool
	 */
	private function isValidDate($date)
	{
		$date = str_replace('/', '-', $date);
		return (date_create($date)) ? Factory::getDate($date)->format("Y-m-d") : null;
	}
}
