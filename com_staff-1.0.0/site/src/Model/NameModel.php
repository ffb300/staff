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
use \Joomla\Utilities\ArrayHelper;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Table\Table;
use \Joomla\CMS\MVC\Model\ItemModel;
use \Joomla\CMS\Helper\TagsHelper;
use \Joomla\CMS\Object\CMSObject;
use \Joomla\CMS\User\UserFactoryInterface;
use \Staff\Component\Staff\Site\Helper\StaffHelper;

/**
 * Staff model.
 *
 * @since  1.0.0
 */
class NameModel extends ItemModel
{
	public $_item;

	
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
         * This method revises if the $id of the item belongs to the current user
         * @param   integer     $id     The id of the item
         * @return  boolean             true if the user is the owner of the row, false if not.
         *
         */
        public function userIDItem($id){
            try{
                $user = Factory::getApplication()->getIdentity();
                $db   = $this->getDbo();
                
                $query = $db->getQuery(true);
                $query->select("id")
                      ->from($db->quoteName('#__staff_department'))
                      ->where("id = " . $db->escape($id))
                      ->where("created_by = " . $user->id);

                $db->setQuery($query);

                $results = $db->loadObject();
                if ($results){
                    return true;
                }else{
                    return false;
                }
            }catch(\Exception $exc){
                return false;
            }
        }

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 *
	 * @throws Exception
	 */
	protected function populateState()
	{
		$app  = Factory::getApplication('com_staff');
		$user = $app->getIdentity();

		// Check published state
		if ((!$user->authorise('core.edit.state', 'com_staff')) && (!$user->authorise('core.edit', 'com_staff')))
		{
			$this->setState('filter.published', 1);
			$this->setState('filter.archived', 2);
		}

		// Load state from the request userState on edit or from the passed variable on default
		if (Factory::getApplication()->input->get('layout') == 'edit')
		{
			$id = Factory::getApplication()->getUserState('com_staff.edit.name.id');
		}
		else
		{
			$id = Factory::getApplication()->input->get('id');
			Factory::getApplication()->setUserState('com_staff.edit.name.id', $id);
		}

		$this->setState('name.id', $id);

		// Load the parameters.
		$params       = $app->getParams();
		$params_array = $params->toArray();

		if (isset($params_array['item_id']))
		{
			$this->setState('name.id', $params_array['item_id']);
		}

		$this->setState('params', $params);
	}

	/**
	 * Method to get an object.
	 *
	 * @param   integer $id The id of the object to get.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @throws Exception
	 */
	public function getItem($id = null)
	{
		if ($this->_item === null)
		{
			$this->_item = false;

			if (empty($id))
			{
				$id = $this->getState('name.id');
			}

			// Get a level row instance.
			$table = $this->getTable();

			// Attempt to load the row.
			if ($table && $table->load($id))
			{
				if(empty($id) || $this->isAdminOrSuperUser() || $table->created_by == Factory::getApplication()->getIdentity()->id){

				// Check published state.
				if ($published = $this->getState('filter.published'))
				{
					if (isset($table->state) && $table->state != $published)
					{
						throw new \Exception(Text::_('COM_STAFF_ITEM_NOT_LOADED'), 403);
					}
				}

				// Convert the Table to a clean CMSObject.
				$properties  = $table->getProperties(1);
				$this->_item = ArrayHelper::toObject($properties, CMSObject::class);

				} else {
                                                throw new \Exception(Text::_("JERROR_ALERTNOAUTHOR"), 401);
                                          }
			}

			if (empty($this->_item))
			{
				throw new \Exception(Text::_('COM_STAFF_ITEM_NOT_LOADED'), 404);
			}
		}

		

		 $container = \Joomla\CMS\Factory::getContainer();

		 $userFactory = $container->get(UserFactoryInterface::class);

		if (isset($this->_item->created_by))
		{
			$user = $userFactory->loadUserById($this->_item->created_by);
			$this->_item->created_by_name = $user->name;
		}

		 $container = \Joomla\CMS\Factory::getContainer();

		 $userFactory = $container->get(UserFactoryInterface::class);

		if (isset($this->_item->modified_by))
		{
			$user = $userFactory->loadUserById($this->_item->modified_by);
			$this->_item->modified_by_name = $user->name;
		}

		if (isset($this->_item->position) && $this->_item->position != '')
		{
			if (is_object($this->_item->position))
			{
				$this->_item->position = ArrayHelper::fromObject($this->_item->position);
			}

			$values = (is_array($this->_item->position)) ? $this->_item->position : explode(',',$this->_item->position);

			$textValue = array();

			foreach ($values as $value)
			{
				$db    = $this->getDbo();
				$query = $db->getQuery(true);

				$query
					->select('`#__staff_position_3944836`.`name`')
					->from($db->quoteName('#__staff_position', '#__staff_position_3944836'))
					->where($db->quoteName('name') . ' = ' . $db->quote($value));

				$db->setQuery($query);
				$results = $db->loadObject();

				if ($results)
				{
					$textValue[] = $results->name;
				}
			}

			$this->_item->position = !empty($textValue) ? implode(', ', $textValue) : $this->_item->position;

		}

		if (isset($this->_item->department) && $this->_item->department != '')
		{
			if (is_object($this->_item->department))
			{
				$this->_item->department = ArrayHelper::fromObject($this->_item->department);
			}

			$values = (is_array($this->_item->department)) ? $this->_item->department : explode(',',$this->_item->department);

			$textValue = array();

			foreach ($values as $value)
			{
				$db    = $this->getDbo();
				$query = $db->getQuery(true);

				$query
					->select('`#__staff_department_3944837`.`name`')
					->from($db->quoteName('#__staff_department', '#__staff_department_3944837'))
					->where($db->quoteName('name') . ' = ' . $db->quote($value));

				$db->setQuery($query);
				$results = $db->loadObject();

				if ($results)
				{
					$textValue[] = $results->name;
				}
			}

			$this->_item->department = !empty($textValue) ? implode(', ', $textValue) : $this->_item->department;

		}

		return $this->_item;
	}
	


	/**
	 * Get an instance of Table class
	 *
	 * @param   string $type   Name of the Table class to get an instance of.
	 * @param   string $prefix Prefix for the table class name. Optional.
	 * @param   array  $config Array of configuration values for the Table object. Optional.
	 *
	 * @return  Table|bool Table if success, false on failure.
	 */
	public function getTable($type = 'Name', $prefix = 'Administrator', $config = array())
	{
		return parent::getTable($type, $prefix, $config);
	}

	/**
	 * Get the id of an item by alias
	 * @param   string $alias Item alias
	 *
	 * @return  mixed
	 * 
	 * @deprecated  No replacement
	 */
	public function getItemIdByAlias($alias)
	{
		$table      = $this->getTable();
		$properties = $table->getProperties();
		$result     = null;
		$aliasKey   = null;
		if (method_exists($this, 'getAliasFieldNameByView'))
		{
			$aliasKey   = $this->getAliasFieldNameByView('name');
		}
		

		if (key_exists('alias', $properties))
		{
			$table->load(array('alias' => $alias));
			$result = $table->id;
		}
		elseif (isset($aliasKey) && key_exists($aliasKey, $properties))
		{
			$table->load(array($aliasKey => $alias));
			$result = $table->id;
		}
		if(empty($result) || $this->isAdminOrSuperUser() || $table->created_by == Factory::getApplication()->getIdentity()->id){
			return $result;
		} else {
                                                throw new \Exception(Text::_("JERROR_ALERTNOAUTHOR"), 401);
                                          }
	}

	/**
	 * Method to check in an item.
	 *
	 * @param   integer $id The id of the row to check out.
	 *
	 * @return  boolean True on success, false on failure.
	 *
	 * @since   1.0.0
	 */
	public function checkin($id = null)
	{
		// Get the id.
		$id = (!empty($id)) ? $id : (int) $this->getState('name.id');
				if($id || $this->userIDItem($id) || $this->isAdminOrSuperUser()){
		if ($id)
		{
			// Initialise the table
			$table = $this->getTable();

			// Attempt to check the row in.
			if (method_exists($table, 'checkin'))
			{
				if (!$table->checkin($id))
				{
					return false;
				}
			}
		}

		return true;
		}else{
                               throw new \Exception(Text::_("JERROR_ALERTNOAUTHOR"), 401);
                           }
	}

	/**
	 * Method to check out an item for editing.
	 *
	 * @param   integer $id The id of the row to check out.
	 *
	 * @return  boolean True on success, false on failure.
	 *
	 * @since   1.0.0
	 */
	public function checkout($id = null)
	{
		// Get the user id.
		$id = (!empty($id)) ? $id : (int) $this->getState('name.id');

				if($id || $this->userIDItem($id) || $this->isAdminOrSuperUser()){
		if ($id)
		{
			// Initialise the table
			$table = $this->getTable();

			// Get the current user object.
			$user = Factory::getApplication()->getIdentity();

			// Attempt to check the row out.
			if (method_exists($table, 'checkout'))
			{
				if (!$table->checkout($user->get('id'), $id))
				{
					return false;
				}
			}
		}

		return true;
				}else{
                               throw new \Exception(Text::_("JERROR_ALERTNOAUTHOR"), 401);
                           }
	}

	/**
	 * Publish the element
	 *
	 * @param   int $id    Item id
	 * @param   int $state Publish state
	 *
	 * @return  boolean
	 */
	public function publish($id, $state)
	{
		$table = $this->getTable();
				if($id || $this->userIDItem($id) || $this->isAdminOrSuperUser()){
		$table->load($id);
		$table->state = $state;

		return $table->store();
				}else{
                               throw new \Exception(Text::_("JERROR_ALERTNOAUTHOR"), 401);
                           }
	}

	/**
	 * Method to delete an item
	 *
	 * @param   int $id Element id
	 *
	 * @return  bool
	 */
	public function delete($id)
	{
		$table = $this->getTable();

		if(empty($id) || $this->isAdminOrSuperUser() || $table->created_by == Factory::getApplication()->getIdentity()->id){
			return $table->delete($id);
		} else {
                                                throw new \Exception(Text::_("JERROR_ALERTNOAUTHOR"), 401);
                                          }
	}

	
}
