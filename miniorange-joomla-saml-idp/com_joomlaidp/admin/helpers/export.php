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

include "BasicIDPEnum.php";

class mo_sp_info extends BasicIDPEnum{
	
    const sp_name = "sp_name";
	const sp_entityid="sp_entityid";
	const acs_url ="acs_url";
	const default_relay_state = "default_relay_state";
    const nameid_attribute = 'nameid_attribute';
    const nameid_format = 'nameid_format';
    const enabled = 'enabled';
    const assertion_signed = 'assertion_signed';

}

 
