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
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
 
/**
 * General Controller of JoomlaIDP component
 *
 * @package     Joomla.Component
 * @subpackage  com_joomlaidp
 * @since       0.0.7
 */
class JoomlaIdpController extends BaseController
{
	/**
	 * The default view for the display method.
	 *
	 * @var string
	 * @since 12.2
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$app   = Factory::getApplication();
		$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
		$view = $input->getCmd('view', 'accountsetup');
		$input->set('view', $view);

		parent::display($cachable, $urlparams);

		return $this;
	}
}