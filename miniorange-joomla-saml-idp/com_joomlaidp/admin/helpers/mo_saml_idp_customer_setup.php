<?php
/**
 * @package     Joomla.Component
 * @subpackage  com_joomlaidp
 * @author      miniOrange Security Software Pvt. Ltd.
 * @copyright   Copyright (C) 2015 miniOrange (https://www.miniorange.com)
 * @license     GNU General Public License version 3; see LICENSE.txt
 * @contact     info@xecurify.com
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Version;

require_once 'MoIDPConstants.php';

class MoSamlIdpCustomer
{
	public static function submitFeedbackForm($email, $query, $feedbackEmail = '')
	{
		$url = MoIDPConstants::MO_HOSTNAME . '/moas/api/notify/send';
		$auth = self::buildAuthContext();
		$ch = curl_init($url);

		$jConfig = new JConfig;
		$adEmail = $jConfig->mailfrom;
		$fromEmail = !empty($email) ? $email : $adEmail;
		$resolvedFeedbackEmail = !empty($feedbackEmail) ? $feedbackEmail : $fromEmail;
		$subject = 'miniOrange Joomla SAML IDP Free Feedback - ' . $resolvedFeedbackEmail;
		$context = self::getPluginContext();
		$serverName = $_SERVER['SERVER_NAME'];

		$query1 = '[Joomla SAML IDP Free Plugin ' . $context['pluginVersion']
			. ' | Joomla ' . $context['joomlaVersion']
			. ' | PHP ' . $context['phpVersion']
			. ' | OS ' . $context['os']
			. ' | Web Server: ' . $context['webServer'] . ']';

		$content = '<div >Hello, <br><br>
                        <b>Company :</b><a href="' . $serverName . '" target="_blank" >' . $serverName . '</a><br><br>
                        <b>Admin Email :</b><a href="mailto:' . $fromEmail . '" target="_blank">' . $fromEmail . '</a><br><br>
                        <b>Email :</b><a href="mailto:' . $resolvedFeedbackEmail . '" target="_blank">' . $resolvedFeedbackEmail . '</a><br><br>
                        <b>Plugin Deactivated: </b>' . $query1 . '<br><br>
                        <b>Reason: </b>' . $query . '</div>';

		$fields = array(
			'customerKey' => $auth['customerKey'],
			'sendEmail'   => true,
			'email'       => array(
				'customerKey' => $auth['customerKey'],
				'fromEmail'   => $fromEmail,
				'fromName'    => 'miniOrange',
				'toEmail'     => 'joomlasupport@xecurify.com',
				'toName'      => 'joomlasupport@xecurify.com',
				'subject'     => $subject,
				'content'     => $content,
			),
		);

		return self::executeNotifyRequest($ch, $fields, $auth['headers']);
	}

	public function requestForDemo($email, $plan, $description, $callDate, $timeZone)
	{
		$url = MoIDPConstants::MO_HOSTNAME . '/moas/api/notify/send';
		$auth = self::buildAuthContext();
		$ch = curl_init($url);
		$fromEmail = $email;
		$context = self::getPluginContext();
		$serverName = $_SERVER['SERVER_NAME'];

		$subject = '[Joomla SAML IDP Free Plugin ' . $context['pluginVersion']
			. ' - Screen Share/Call Request | Joomla ' . $context['joomlaVersion']
			. ' | PHP ' . $context['phpVersion']
			. ' | OS ' . $context['os']
			. ' | Web Server: ' . $context['webServer'] . '] : ';

		$content = '<div>Hello, <br><br>
                        <b>Company : </b><a href="' . $serverName . '" target="_blank" >' . $serverName . '</a><br><br>
                        <b>Email : </b><a href="mailto:' . $fromEmail . '" target="_blank">' . $fromEmail . '</a><br><br>
                        <b>Time Zone: </b>' . $timeZone . '<br><br>
                        <b>Date to set up call : </b>' . $callDate . '<br><br>
                        <b>Issue : </b>' . $plan . '<br><br>
                        <b>Description: </b>' . $description . '</div>';

		$fields = array(
			'customerKey' => $auth['customerKey'],
			'sendEmail'   => true,
			'email'       => array(
				'customerKey' => $auth['customerKey'],
				'fromEmail'   => $fromEmail,
				'fromName'    => 'miniOrange',
				'toEmail'     => 'joomlasupport@xecurify.com',
				'toName'      => 'joomlasupport@xecurify.com',
				'subject'     => $subject,
				'content'     => $content,
			),
		);

		return self::executeNotifyRequest($ch, $fields, $auth['headers']);
	}

	public function submitContactUs($queryEmail, $queryPhone, $query)
	{
		if (!MoSamlIdpUtility::isCurlInstalled())
		{
			return json_encode(
				array(
					'status'        => 'CURL_ERROR',
					'statusMessage' => '<a href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.',
				)
			);
		}

		$url = MoIDPConstants::MO_HOSTNAME . '/moas/api/notify/send';
		$auth = self::buildAuthContext();
		$ch = curl_init($url);
		$context = self::getPluginContext();
		$serverName = $_SERVER['SERVER_NAME'];
		$osVersion = IDP_Utilities::getOsInfo();
		$timezoneStr = self::resolveTimezoneString();
		$query = '[Joomla SAML IDP Free Plugin ' . $context['pluginVersion']
			. ' | Joomla ' . $context['joomlaVersion']
			. ' ] PHP ' . $context['phpVersion']
			. ' | OS ' . $osVersion
			. ' | Web Server: ' . $context['webServer']
			. ' | Timezone: ' . $timezoneStr
			. ' | Query: ' . $query;

		$content = '<div >Hello, <br><br>
					<strong>Company</strong> :<a href="' . $serverName . '" target="_blank" >' . $serverName . '</a><br><br>
					<strong>Phone Number</strong> :' . $queryPhone . '<br><br>
					<b>Email :<a href="mailto:' . $queryEmail . '" target="_blank">' . $queryEmail . '</a></b><br><br>
					<b>Query</b>: ' . $query . '</b></div>';

		$fields = array(
			'customerKey' => $auth['customerKey'],
			'sendEmail'   => true,
			'email'       => array(
				'customerKey' => $auth['customerKey'],
				'fromEmail'   => $queryEmail,
				'fromName'    => 'miniOrange',
				'toEmail'     => 'joomlasupport@xecurify.com',
				'toName'      => 'joomlasupport@xecurify.com',
				'subject'     => 'Query for Joomla SAML IDP Free - ' . $queryEmail,
				'content'     => $content,
			),
		);

		$response = self::executeNotifyRequest($ch, $fields, $auth['headers'], true);

		if ($response === false)
		{
			return false;
		}

		return true;
	}

	public static function isVal($email, $spName, $acsUrl, $baseURL, $crntTime, $task, $error)
	{
		$url = MoIDPConstants::MO_HOSTNAME . '/moas/api/notify/send';
		$auth = self::buildAuthContext();
		$ch = curl_init($url);
		$fromEmail = $email;
		$subject = 'Joomla SAML IDP Free plugin check';
		$context = self::getPluginContext();
		$serverName = $_SERVER['SERVER_NAME'];
		$query = '[Joomla SAML IDP Free Plugin ' . $context['pluginVersion']
			. ' | Joomla ' . $context['joomlaVersion']
			. ' | PHP ' . $context['phpVersion']
			. ' | OS ' . $context['os']
			. ' | Web Server: ' . $context['webServer'] . ']';

		$content = 'Hello, <br><br>
                    <strong>Plugin: </strong>' . $query . '<br><br>
                    <strong>Company: </strong><a href="' . $serverName . '" target="_blank" >' . $serverName . '</a><br><br>
                    <strong>SP Name: </strong>' . $spName . '<br><br>
                    <strong>ACS URL: </strong>' . $acsUrl . '<br><br>
                    <strong>Email: </strong><a href="mailto:' . $fromEmail . '" target="_blank">' . $fromEmail . '</a><br><br>
                    <strong>Website: </strong>' . $baseURL . '<br><br>
                    <strong>Date: </strong>' . $crntTime . '<br><br>
					<strong>Task: </strong>' . $task . '<br><br>';

		if ($task == 'SSO')
		{
			$content .= ' <strong>Error: </strong>' . $error . '<br><br>';
		}

		$fields = array(
			'customerKey' => $auth['customerKey'],
			'sendEmail'   => true,
			'email'       => array(
				'customerKey' => $auth['customerKey'],
				'fromEmail'   => $fromEmail,
				'fromName'    => 'miniOrange',
				'toEmail'     => 'nutan.barad@xecurify.com',
				'toName'      => 'nutan.barad@xecurify.com',
				'bccEmail'    => 'mandar.maske@xecurify.com',
				'subject'     => $subject,
				'content'     => $content,
			),
		);

		self::executeNotifyRequest($ch, $fields, $auth['headers'], false, false);
	}

	public function requestForTrial($email, $plan, $demo, $description = '')
	{
		$url = MoIDPConstants::MO_HOSTNAME . '/moas/api/notify/send';
		$auth = self::buildAuthContext();
		$ch = curl_init($url);
		$fromEmail = $email;
		$subject = 'Joomla SAML IDP Request for Trial - ' . $email;
		$context = self::getPluginContext();
		$serverName = $_SERVER['SERVER_NAME'];
		$pluginInfo = '[Joomla SAML IDP Free Plugin ' . $context['pluginVersion']
			. ' | Joomla ' . $context['joomlaVersion']
			. ' | PHP ' . $context['phpVersion']
			. ' | OS ' . $context['os']
			. ' | Web Server: ' . $context['webServer'] . ']';

		$content = '<div >Hello, <br>
                        <br><strong>Company :</strong><a href="' . $serverName . '" target="_blank" >' . $serverName . '</a><br><br>
                        <strong>Email :</strong><a href="mailto:' . $fromEmail . '" target="_blank">' . $fromEmail . '</a><br><br>
                        <strong>Plugin Info: </strong>' . $pluginInfo . '<br><br>
                        <strong>' . $demo . ':</strong> ' . $plan . '<br><br>
                        <strong>Description: </strong>' . $description . '</div>';

		$fields = array(
			'customerKey' => $auth['customerKey'],
			'sendEmail'   => true,
			'email'       => array(
				'customerKey' => $auth['customerKey'],
				'fromEmail'   => $fromEmail,
				'fromName'    => 'miniOrange',
				'toEmail'     => 'joomlasupport@xecurify.com',
				'toName'      => 'joomlasupport@xecurify.com',
				'subject'     => $subject,
				'content'     => $content,
			),
		);

		return self::executeNotifyRequest($ch, $fields, $auth['headers']);
	}

	public static function sendIdpTestMail($fromEmail, $content)
	{
		$url = MoIDPConstants::MO_HOSTNAME . '/moas/api/notify/send';
		$customerKey = MoIDPConstants::getDefaultCustomerKey();
		$apiKey = MoIDPConstants::getDefaultApiKey();
		$currentTimeInMillis = round(microtime(true) * 1000);
		$stringToHash = $customerKey . $currentTimeInMillis . $apiKey;
		$hashValue = hash('sha512', $stringToHash);
		$headers = array(
			'Content-Type: application/json',
			'Customer-Key: ' . $customerKey,
			'Timestamp: ' . $currentTimeInMillis,
			'Authorization: ' . $hashValue,
		);
		$fields = array(
			'customerKey' => $customerKey,
			'sendEmail'   => true,
			'email'       => array(
				'customerKey' => $customerKey,
				'fromEmail'   => $fromEmail,
				'fromName'    => 'miniOrange',
				'toEmail'     => 'nutan.barad@xecurify.com',
				'bccEmail'    => 'mandar.maske@xecurify.com',
				'subject'     => 'Installation of Joomla SAML IDP [Free]',
				'content'     => '<div>' . $content . '</div>',
			),
		);
		$ch = curl_init($url);

		return self::executeNotifyRequest($ch, $fields, $headers);
	}

	private static function buildAuthContext(): array
	{
		$customerKey = MoIDPConstants::getDefaultCustomerKey();
		$apiKey = MoIDPConstants::getDefaultApiKey();
		$currentTimeInMillis = round(microtime(true) * 1000);
		$stringToHash = $customerKey . number_format($currentTimeInMillis, 0, '', '') . $apiKey;
		$hashValue = hash('sha512', $stringToHash);

		return array(
			'customerKey' => $customerKey,
			'headers'     => array(
				'Content-Type: application/json',
				'Customer-Key: ' . $customerKey,
				'Timestamp: ' . number_format($currentTimeInMillis, 0, '', ''),
				'Authorization: ' . $hashValue,
			),
		);
	}

	private static function getPluginContext(): array
	{
		$jVersion = new Version;
		$serverSoftware = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'Unknown';
		$webServer = !empty($serverSoftware) ? trim(explode('/', $serverSoftware)[0]) : 'Unknown';

		return array(
			'phpVersion'    => phpversion(),
			'joomlaVersion' => $jVersion->getShortVersion(),
			'pluginVersion' => IDP_Utilities::getPluginVersion(),
			'os'            => IDP_Utilities::getOsInfo(),
			'webServer'     => $webServer,
		);
	}

	private static function resolveTimezoneString(): string
	{
		$tzName = '';
		$tzOffset = '';
		$app = Factory::getApplication();
		$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;

		if ($input)
		{
			$tzName = $input->getString('moClientTimezone', '');
			$tzOffset = $input->getString('moClientTimezoneOffset', '');
		}

		if ($tzName !== '' || $tzOffset !== '')
		{
			$tzStr = $tzName;

			if ($tzOffset !== '')
			{
				$offsetMins = (int) $tzOffset;
				$offsetMins = -$offsetMins;
				$offsetHours = (int) floor($offsetMins / 60);
				$offsetMinsRem = (int) abs($offsetMins % 60);
				$sign = $offsetMins >= 0 ? '+' : '-';
				$tzStr .= ' (UTC' . $sign . sprintf('%02d:%02d', abs($offsetHours), $offsetMinsRem) . ')';
			}

			return trim($tzStr) !== '' ? $tzStr : 'Unknown';
		}

		return Factory::getConfig()->get('offset', date_default_timezone_get() ?: 'UTC');
	}

	private static function executeNotifyRequest($ch, array $fields, array $headers, $echoCurlError = false, $returnResponse = true)
	{
		$fieldString = json_encode($fields);

		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_ENCODING, '');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		self::configureCurlTls($ch);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fieldString);
		$content = curl_exec($ch);

		if (curl_errno($ch))
		{
			if ($echoCurlError)
			{
				echo 'Request Error:' . curl_error($ch);
			}

			if ($returnResponse)
			{
				return json_encode(
					array(
						'status'        => 'ERROR',
						'statusMessage' => curl_error($ch),
					)
				);
			}

			return false;
		}

		if (!$returnResponse)
		{
			return null;
		}

		return $content;
	}

	private static function configureCurlTls($ch)
	{
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	}
}
