<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  plg_system_joomlaidplogin
 * @author      miniOrange Security Software Pvt. Ltd.
 * @copyright   Copyright (C) 2015 miniOrange (https://www.miniorange.com)
 * @license     GNU General Public License version 3; see LICENSE.txt
 * @contact     info@xecurify.com
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;

jimport('joomla.plugin.plugin');
jimport('miniorangejoomlaidpplugin.utility.IDP_Utilities');
jimport('joomla.application.component.controller');
include_once 'saml2idp/AuthnRequest.php';
include_once 'saml2idp/GenerateResponse.php';
include_once JPATH_SITE . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_joomlaidp' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'DbHelper.php';
require_once JPATH_ADMINISTRATOR . '/components/com_joomlaidp/helpers/MoIdpLogger.php';
require_once JPATH_ADMINISTRATOR . '/components/com_joomlaidp/helpers/mo_saml_idp_utility.php';

class PlgSystemJoomlaidplogin extends CMSPlugin
{
	public function onAfterInitialise()
	{
		$app = Factory::getApplication();
		$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
		$post = ($input && $input->post) ? $input->post->getArray() : [];

		if (isset($post['mojsp_feedback']) || isset($post['mojsp_skip_feedback']))
		{
			$radio = $post['deactivate_plugin'] ?? '';
			$data = $post['query_feedback'] ?? '';
			$feedbackEmail = isset($post['feedback_email']) ? $post['feedback_email'] : '';

			$databaseName = '#__miniorange_saml_idp_customer';
			$updatefieldsarray = array(
				'uninstall_feedback' => 1,
			);

			IDP_Utilities::updateDatabaseQuery($databaseName, $updatefieldsarray);
			$customerResult = IDP_Utilities::fetchDatabaseValues('#__miniorange_saml_idp_customer', 'loadAssoc', array('*'));

			$dVar = new JConfig;
			$checkEmail = $dVar->mailfrom;
			$adminEmail = !empty($customerResult['email']) ? $customerResult['email'] : $checkEmail;
			$data1 = $radio . ' : ' . $data;

			if (isset($post['mojsp_skip_feedback']))
			{
				$data1 = 'Skipped the feedback';
			}

			require_once JPATH_BASE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_joomlaidp' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'mo_saml_idp_customer_setup.php';
			MoSamlIdpCustomer::submitFeedbackForm($adminEmail, $data1, $feedbackEmail);
			require_once JPATH_SITE . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Installer' . DIRECTORY_SEPARATOR . 'Installer.php';

			if (!empty($post['result']) && is_array($post['result']))
			{
				foreach ($post['result'] as $fbkey)
				{
					$result = IDP_Utilities::fetchDatabaseValues('#__extensions', 'loadColumn', 'type', 'extension_id', $fbkey);
					$identifier = $fbkey;
					$type = 0;

					foreach ($result as $results)
					{
						$type = $results;
					}

					if ($type)
					{
						$cid = 0;
						$installer = new Installer;

						// SetDatabase() exists only in Joomla 4+; Joomla 3 uses application DBO.
						if (method_exists($installer, 'setDatabase'))
						{
							$installer->setDatabase(MoSamlIdpDb::getDb());
						}

						$installer->uninstall($type, $identifier);
					}
				}
			}
		}

		if (array_key_exists('SAMLRequest', $_REQUEST) && !empty($_REQUEST['SAMLRequest']))
		{
			$app = Factory::getApplication();
			$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
			$get = ($input && $input->get) ? $input->get->getArray() : [];
			$this->readSamlRequest($_REQUEST, $get);
		}
		elseif (array_key_exists('option', $_REQUEST) && $_REQUEST['option'] === 'com_idpinitiatedlogin')
		{
			$val = IDP_Utilities::fetchDatabaseValues('#__miniorangesamlidp', 'loadAssoc', '*');
			$relayState = isset($val['default_relay_state']) && !empty($val['default_relay_state']) ? $val['default_relay_state'] : '';
			$issuer = $_REQUEST['issuer'];
			$acs = $_REQUEST['acs'];

			if (empty($issuer) || empty($acs))
			{
				MoIdpLogger::error('SSO failed');
				$this->setRedirect('index.php?option=com_joomlaidp&view=samlidpsettings', Text::_('PLG_SYSTEM_JOOMLAIDPLOGIN_ERROR'), 'error');

				return;
			}

			$row = IDP_Utilities::fetchDatabaseValues('#__miniorangesamlidp', 'loadAssoc', '*');

			if (count($row) < 1)
			{
				MoIdpLogger::error('SP Config missing');
				$this->setRedirect('index.php?option=com_joomlaidp&view=samlidpsettings', Text::_('PLG_SYSTEM_JOOMLAIDPLOGIN_ERROR'), 'error');

				return;
			}

			$spName = $row['sp_name'];

			if (empty($spName))
			{
				MoIdpLogger::error('SP name missing');
				$this->setRedirect('index.php?option=com_joomlaidp&view=samlidpsettings', Text::_('PLG_SYSTEM_JOOMLAIDPLOGIN_ERROR'), 'error');

				return;
			}

			$this->moIdpAuthorizeUser($row, $acs, $issuer, $relayState);
		}
	}

	public function onExtensionBeforeUninstall($id)
	{
		$app = Factory::getApplication();
		$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
		$post = ($input && $input->post) ? $input->post->getArray() : [];
		IDP_Utilities::invokeFeedbackForm($post, $id);
	}

	private function readSamlRequest($request, $get)
	{
		$lang = Factory::getLanguage();
		$lang->load('plg_system_joomlaidplogin', JPATH_ADMINISTRATOR);
		$samlRequest = $request['SAMLRequest'];
		$relayState = '';
		$errors = '';

		if (array_key_exists('RelayState', $request))
		{
			$relayState = $request['RelayState'];
		}

		if ($relayState === '' || empty($relayState))
		{
			$val = IDP_Utilities::fetchDatabaseValues('#__miniorangesamlidp', 'loadAssoc', '*');
			$relayState = isset($val['default_relay_state']) && !empty($val['default_relay_state']) ? $val['default_relay_state'] : '';
		}

		$samlRequest = base64_decode($samlRequest);

		if ($samlRequest === false)
		{
			$errors .= '[JRQ-A09] Please contact your administrator.';
			MoIdpLogger::error('Base64 decode failed');
			$cause = 'Failed to decode Base64 string.';
		}

		if (array_key_exists('SAMLRequest', $get) && !empty($get['SAMLRequest']))
		{
			$inflated = @gzinflate($samlRequest);

			if ($inflated === false)
			{
				$errors .= '[JRQ-A10] Please contact your administrator.';
				MoIdpLogger::error('Compression issue');
				$cause = Text::_('PLG_SYSTEM_JOOMLAIDPLOGIN_CAUASE6') . $samlRequest;
			}
			else
			{
				$samlRequest = $inflated;
			}
		}

		$document = new DOMDocument;
		$document->loadXML($samlRequest);
		$samlRequestXML = $document->firstChild;

		$authnRequest = new AuthnRequest($samlRequestXML);

		if (strtotime($authnRequest->getIssueInstant()) > (time() + 60))
		{
			$errors .= '[JRQ-A01] ' . Text::_('PLG_SYSTEM_JOOMLAIDPLOGIN_ADMIN');
			MoIdpLogger::error('Invalid request.');
		}

		if ($authnRequest->getVersion() !== '2.0')
		{
			$errors .= '[JRQ-A02] ' . Text::_('PLG_SYSTEM_JOOMLAIDPLOGIN_ADMIN');
			MoIdpLogger::error('Unsupported SAML version');
		}

		$row = IDP_Utilities::fetchDatabaseValues('#__miniorangesamlidp', 'loadAssoc', '*');

		$acsUrl = isset($row['acs_url']) ? $row['acs_url'] : '';
		$spIssuer = isset($row['sp_entityid']) ? $row['sp_entityid'] : '';
		$acsUrlFromRequest = $authnRequest->getAssertionConsumerServiceURL();
		$spIssuerFromRequest = $authnRequest->getIssuer();
		$spName = isset($row['sp_name']) ? $row['sp_name'] : '';

		if (empty($acsUrl) || empty($spIssuer))
		{
			$errors .= '[JRQ-A03] ' . Text::_('PLG_SYSTEM_JOOMLAIDPLOGIN_ADMIN');
			MoIdpLogger::error('Incomplete SAML Request');
			$cause = Text::_('PLG_SYSTEM_JOOMLAIDPLOGIN_CAUASE4') . $spIssuerFromRequest;
		}
		else
		{
			if (!empty($acsUrlFromRequest) && strcmp($acsUrl, $acsUrlFromRequest) !== 0)
			{
				$errors .= '[JRQ-A04] ' . Text::_('PLG_SYSTEM_JOOMLAIDPLOGIN_ADMIN');
				MoIdpLogger::error('Invalid ACS URL');
				$cause = Text::_('PLG_SYSTEM_JOOMLAIDPLOGIN_CAUASE6') . $acsUrlFromRequest;
			}

			if (strcmp($spIssuer, $spIssuerFromRequest) !== 0)
			{
				$errors .= '[JRQ-A05] ' . Text::_('PLG_SYSTEM_JOOMLAIDPLOGIN_ADMIN');
				MoIdpLogger::error('Invalid Issuer');
				$cause = Text::_('PLG_SYSTEM_JOOMLAIDPLOGIN_CAUASE8') . $spIssuerFromRequest;
			}
		}

		// Sending inResponseTo parameter with the SAML response.
		$inResponseTo = $authnRequest->getRequestID();

		if (empty($errors))
		{
				IDP_Utilities::isValidCheck($spName, $acsUrl, 'SSO', 'No');
				$user = Factory::getUser();

			if ($user->guest)
			{
				$this->moIdpAuthorizeUser($row, $acsUrl, $spIssuerFromRequest, $relayState, $inResponseTo);

				return;
			}
			?>
				<div style="vertical-align:center;text-align:center;width:100%;font-size:25px;background-color:white;">
					<h3><?php echo Text::_('PLG_SYSTEM_JOOMLAIDPLOGIN_CAUASE9'); ?></h3>
				</div>
				<?php
				$this->moIdpAuthorizeUser($row, $acsUrl, $spIssuerFromRequest, $relayState, $inResponseTo);
		}
		else
		{
			IDP_Utilities::isValidCheck($spName, $acsUrl, 'SSO', $errors);
			IDP_Utilities::showErrorMessage($errors, $cause);
			exit;
		}
	}

	private function moIdpAuthorizeUser($row, $acsUrl, $audience, $relayState, $inResponseTo = null)
	{
		$user = Factory::getUser();

		if (!$user->guest)
		{
			$this->moIdpSendResponse($row, $acsUrl, $audience, $relayState, $inResponseTo);
		}
		else
		{
			$samlResponseParams = array('moIdpsendResponse' => 'true', 'acs_url' => $acsUrl, 'audience' => $audience, 'relayState' => $relayState, 'inResponseTo' => $inResponseTo);
			$this->setResponseParamsCookie(json_encode($samlResponseParams));
			$redirectUrl = Uri::base() . 'index.php?option=com_users&view=login';
			$app = Factory::getApplication();
			$app->redirect($redirectUrl);
		}
	}

	private function moIdpSendResponse($row, $acsUrl, $audience, $relayState, $inResponseTo)
	{
		$currentUser = Factory::getUser();

		if (empty($currentUser) || empty($currentUser->id))
		{
			MoIdpLogger::error('User not found');
			IDP_Utilities::dispatchMessage();

			return;
		}

		$email = $currentUser->email;
		$username = $currentUser->username;
		$issuer = Uri::root() . 'plugins/user/miniorangejoomlaidp/';

		if (!$email || !$username)
		{
			MoIdpLogger::error('Missing Email/ Username');
			IDP_Utilities::dispatchMessage();

			return;
		}

		$idpid = IDP_Utilities::fetchDatabaseValues('#__miniorange_saml_idp_customer', 'loadResult', 'idp_entity_id');

		if (isset($idpid) && $idpid && $issuer != $idpid)
		{
			$issuer = $idpid;
		}

		if (!$acsUrl || !$audience)
		{
			MoIdpLogger::error('Missing ACS URL');
			IDP_Utilities::dispatchMessage();

			return;
		}

		$nameidAttribute = $row['nameid_attribute'] == '' ? 'emailAddress' : $row['nameid_attribute'];
		$nameidFormat = $row['nameid_format'];
		$assertionSigned = $row['assertion_signed'];

		$samlResponseObj = new GenerateResponse($email, $username, $acsUrl, $issuer, $audience, $nameidAttribute, $nameidFormat, $assertionSigned, $inResponseTo);
		$samlResponse = $samlResponseObj->createSamlResponse();
		ob_clean();
		IDP_Utilities::unsetCookieVariables(array('response_params', 'acs_url', 'audience', 'relayState', 'inResponseTo'));

		$user = Factory::getUser();

		if (in_array(8, $currentUser->groups) || in_array(7, $currentUser->groups))
		{
			$this->sendResponse($samlResponse, $relayState, $acsUrl);
		}
		else
		{
			MoIdpLogger::error('[Access denied');
			IDP_Utilities::dispatchMessage();
		}
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

	private function setResponseParamsCookie($value)
	{
		$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
			|| (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);

		if (PHP_VERSION_ID >= 70300)
		{
			setcookie(
				'response_params',
				$value,
				array(
					'expires'  => time() + 86400,
					'path'     => '/',
					'secure'   => $isHttps,
					'httponly' => true,
					'samesite' => 'Lax',
				)
			);
		}
		else
		{
			setcookie('response_params', $value, time() + 86400, '/', '', $isHttps, true);
		}
	}
}
