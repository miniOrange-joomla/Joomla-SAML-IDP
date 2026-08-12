<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  plg_user_miniorangejoomlaidp
 * @author      miniOrange Security Software Pvt. Ltd.
 * @copyright   Copyright (C) 2015 miniOrange (https://www.miniorange.com)
 * @license     GNU General Public License version 3; see LICENSE.txt
 * @contact     info@xecurify.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;

jimport('joomla.plugin.plugin');

if (!defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

class PlgUserMiniorangejoomlaidp extends CMSPlugin
{
	/**
	 * This method should handle any authentication and report back to the subject
	 *
	 * @param   array   $options  Login options
	 *
	 * @return  void
	 */
	public function onUserAfterLogin($options)
	{
		$app = Factory::getApplication();
		$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
		$cookie = ($input && $input->cookie) ? $input->cookie->getArray() : [];

		if (isset($cookie['response_params']))
		{
			$responseParams = json_decode(stripslashes($cookie['response_params']), true);

			if (strcmp($responseParams['moIdpsendResponse'], 'true') == 0)
			{
				$currentUser = Factory::getUser();

				if (in_array(8, $currentUser->groups) || in_array(7, $currentUser->groups))
				{
					$this->moIdpSendResponse($responseParams['acs_url'], $responseParams['audience'], $responseParams['relayState'], $responseParams['inResponseTo']);
				}
				else
				{
					IDP_Utilities::dispatchMessage();
				}
			}
		}
	}

	private function moIdpSendResponse($acsUrl, $audience, $relayState, $inResponseTo)
	{
		$currentUser = Factory::getUser();
		$row = IDP_Utilities::fetchDatabaseValues('#__miniorangesamlidp', 'loadAssoc', '*');

		$email = $currentUser->email;
		$username = $currentUser->username;

		$issuer = Uri::root() . 'plugins/user/miniorangejoomlaidp/';

		$idpid = IDP_Utilities::fetchDatabaseValues('#__miniorange_saml_idp_customer', 'loadResult', 'idp_entity_id');

		if (!empty($idpid) && ($issuer != $idpid))
		{
			$issuer = $idpid;
		}

		$nameidAttribute = $row['nameid_attribute'];
		$nameidFormat = $row['nameid_format'];
		$assertionSigned = $row['assertion_signed'];
		$samlResponseObj = new GenerateResponse($email, $username, $acsUrl, $issuer, $audience, $nameidAttribute, $nameidFormat, $assertionSigned, $inResponseTo);

		$samlResponse = $samlResponseObj->createSamlResponse();

		ob_clean();
		IDP_Utilities::unsetCookieVariables(array('response_params', 'acs_url', 'audience', 'relayState', 'inResponseTo'));

		$this->clearResponseParamsCookie();

		$this->sendResponse($samlResponse, $relayState, $acsUrl);
	}

	private function sendResponse($samlResponse, $ssoUrl, $acsUrl)
	{
		$samlResponse = base64_encode($samlResponse);
		?>
		<form id="responseform" action="<?php echo $acsUrl; ?>" method="post">
			<input type="hidden" name="SAMLResponse" value="<?php echo htmlspecialchars($samlResponse); ?>"/>
			<input type="hidden" name="RelayState" value="<?php echo $ssoUrl; ?>"/>
		</form>
		<script>
			setTimeout(function () {
				document.getElementById('responseform').submit();
			}, 100);
		</script>
		<?php
		exit;
	}

	private function clearResponseParamsCookie()
	{
		$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
			|| (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);

		if (PHP_VERSION_ID >= 70300)
		{
			setcookie(
				'response_params',
				'',
				array(
					'expires'  => time() - 86400,
					'path'     => '/',
					'secure'   => $isHttps,
					'httponly' => true,
					'samesite' => 'Lax',
				)
			);
		}
		else
		{
			setcookie('response_params', '', time() - 86400, '/', '', $isHttps, true);
		}
	}
}
