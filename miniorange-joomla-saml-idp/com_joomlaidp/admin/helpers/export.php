<?php
defined('_JEXEC') or die;
/**
 * @package     Joomla.Component
 * @subpackage  com_joomlaidp
 * @author      miniOrange Security Software Pvt. Ltd.
 * @copyright   Copyright (C) 2015 miniOrange (https://www.miniorange.com)
 * @license     GNU General Public License version 3; see LICENSE.txt
 * @contact     info@xecurify.com
 */

include 'BasicIDPEnum.php';

class MoSpInfo extends BasicIDPEnum
{
	const SP_NAME = 'sp_name';

	const SP_ENTITYID = 'sp_entityid';

	const ACS_URL = 'acs_url';

	const DEFAULT_RELAY_STATE = 'default_relay_state';

	const NAMEID_ATTRIBUTE = 'nameid_attribute';

	const NAMEID_FORMAT = 'nameid_format';

	const ENABLED = 'enabled';

	const ASSERTION_SIGNED = 'assertion_signed';
}
