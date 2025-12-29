<?php

/**
 * @package     Joomla.Component
 * @subpackage  com_joomlaidp
 * @author      miniOrange Security Software Pvt. Ltd.
 * @copyright   Copyright (C) 2015 miniOrange (https://www.miniorange.com)
 * @license     GNU General Public License version 3; see LICENSE.txt
 * @contact     info@xecurify.com
 */
 
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;


/**
 * AccountSetup Model
 *
 * @since  0.0.1
 */
class JoomlaidpModelAccountSetup extends AdminModel
{
	
	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed    A JForm object on success, false on failure
	 *
	 * @since   1.6
	 */
	
	
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm(
			'com_joomlaidp.accountsetup',
			'accountsetup',
			array(
				'control' => 'jform',
				'load_data' => $loadData
			)
		);
 
		if (empty($form))
		{
			return false;
		}
 
		return $form;
	}
	
	public function getList()
	{
		// Initialize variables.
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
 
		// Create the base select statement.
		$query->select('sp_name')
                ->from($db->quoteName('#__miniorangesamlidp'));
		$result = array();
		$result = BaseDatabaseModel::_getList($query);
		return $result;
	}
	
}