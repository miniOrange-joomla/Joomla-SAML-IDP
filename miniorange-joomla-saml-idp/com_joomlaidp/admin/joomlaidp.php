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
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
require_once JPATH_COMPONENT . '/helpers/mo_saml_idp_customer_setup.php';
require_once JPATH_COMPONENT . '/helpers/mo_saml_idp_utility.php';

// Access check.
if (!Factory::getUser()->authorise('core.manage', 'com_joomlaidp'))
{
	throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'));
}

// Include dependancies
jimport('joomla.application.component.controller');

JLoader::registerPrefix('JoomlaIdp', JPATH_COMPONENT_ADMINISTRATOR);
 
// Get an instance of the controller prefixed by JoomlaIdp
$controller = BaseController::getInstance('JoomlaIdp');

// Perform the Request task
$app   = Factory::getApplication();
$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
$controller->execute((string) $input->get('task'));

// Redirect if set by the controller
$controller->redirect();