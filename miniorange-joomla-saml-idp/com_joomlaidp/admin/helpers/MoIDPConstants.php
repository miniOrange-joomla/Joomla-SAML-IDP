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
defined('_JEXEC') or die;

class MoIDPConstants
{
	const MO_HOSTNAME = 'https://login.xecurify.com';

	private const DEFAULT_CUSTOMER_KEY_B64 = 'MTY1NTU=';

	private const DEFAULT_API_KEY_B64 = 'ZkZkMlhjdlRHRGVtWnZidzFiY1Vlc05KV0VxS2JiVXE=';

	public static function getDefaultCustomerKey()
	{
		$envKey = getenv('MO_SAML_IDP_DEFAULT_CUSTOMER_KEY');

		if ($envKey !== false && $envKey !== '')
		{
			return $envKey;
		}

		return base64_decode(self::DEFAULT_CUSTOMER_KEY_B64);
	}

	public static function getDefaultApiKey()
	{
		$envKey = getenv('MO_SAML_IDP_DEFAULT_API_KEY');

		if ($envKey !== false && $envKey !== '')
		{
			return $envKey;
		}

		return base64_decode(self::DEFAULT_API_KEY_B64);
	}
}
