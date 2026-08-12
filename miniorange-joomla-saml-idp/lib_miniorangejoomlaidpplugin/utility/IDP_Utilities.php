<?php
/**
 * @package     Joomla.Library
 * @subpackage  lib_miniorangejoomlaidpplugin
 * @author      miniOrange Security Software Pvt. Ltd.
 * @copyright   Copyright (C) 2015 miniOrange (https://www.miniorange.com)
 * @license     GNU General Public License version 3; see LICENSE.txt
 * @contact     info@xecurify.com
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Version;

$lang = Factory::getLanguage();
$lang->load('lib_miniorangejoomlaidpplugin', JPATH_SITE) || $lang->load('lib_miniorangejoomlaidpplugin', JPATH_ADMINISTRATOR);
include 'xmlseclibs.php';
include_once JPATH_SITE . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_joomlaidp' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'DbHelper.php';
include_once JPATH_SITE . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_joomlaidp' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'mo_saml_idp_customer_setup.php';

class IDP_Utilities
{
	public static function getPluginVersion()
	{
		$db = MoSamlIdpDb::getDb();
		$dbQuery = $db->getQuery(true)
			->select('manifest_cache')
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('element') . ' = ' . $db->quote('com_joomlaidp'));
		$db->setQuery($dbQuery);
		$manifest = json_decode($db->loadResult());

		return $manifest->version;
	}

	public static function updateUser($email)
	{
		$userIn = 1;
		$db = MoSamlIdpDb::getDb();
		$query = $db->getQuery(true);
		$query->update($db->quoteName('#__users'))
			->set($db->quoteName('userIn') . ' = ' . $db->quote($userIn))
			->where($db->quoteName('email') . ' = ' . $db->quote($email));
		$db->setQuery($query);
		$db->execute();
	}

	public static function dispatchMessage()
	{
		$lang = Factory::getLanguage();
		$lang->load('lib_miniorangejoomlaidpplugin', JPATH_SITE) || $lang->load('lib_miniorangejoomlaidpplugin', JPATH_ADMINISTRATOR);

		echo '<div style="font-family:Calibri,sans-serif;padding:0 3%;">';
		echo '<div style="color: #a94442;background-color: #f2dede;padding: 15px;margin-bottom: 20px;text-align:center;border:1px solid #E6B3B2;font-size:18pt;">' . Text::_('LIB_MINIORANGEJOOMLAIDPPLUGIN_ERROR_HEADER') . ' </div>
			<div style="color: #a94442;font-size:14pt; margin-bottom:20px;"><p><strong>' . Text::_('LIB_MINIORANGEJOOMLAIDPPLUGIN_ERROR') . ' : </strong>' . Text::_('LIB_MINIORANGEJOOMLAIDPPLUGIN_ERROR_INFO') . ' </p>
			<p><strong>' . Text::_('LIB_MINIORANGEJOOMLAIDPPLUGIN_CAUSE') . '</strong>: ' . Text::_('LIB_MINIORANGEJOOMLAIDPPLUGIN_ERROR1') . '</p>
			</div>
			<div style="margin:3%;display:block;text-align:center;">
			<a style="padding:1%;width:100px;background: #0091CD none repeat scroll 0 0;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #0073AA;box-shadow: 0 1px 0 rgba(120, 200, 230, 0.6) inset;color: #FFF;" href="https://plugins.miniorange.com/joomla-idp-saml-sso#pricing">' . Text::_('LIB_MINIORANGEJOOMLAIDPPLUGIN_UPGRADE_BTN') . '</a>';
		exit;
	}

	public static function isValid($email)
	{
		$db = MoSamlIdpDb::getDb();
		$query = $db->getQuery(true);
		$query->select('userIn')->from('#__users')->where($db->quoteName('email') . ' = ' . $db->quote($email));
		$db->setQuery($query);

		return $db->loadResult();
	}

	public static function getUCnt()
	{
		$db = MoSamlIdpDb::getDb();
		$query = $db->getQuery(true);
		$query->select('COUNT(*)');
		$query->from($db->quoteName('#__users'));
		$query->where($db->quoteName('userIn') . ' = 1');
		$db->setQuery($query);

		return $db->loadResult();
	}

	public static function generateID()
	{
		return '_' . self::stringToHex(self::generateRandomBytes(21));
	}

	public static function stringToHex($bytes)
	{
		$ret = '';

		for ($i = 0; $i < strlen($bytes); $i++)
		{
			$ret .= sprintf('%02x', ord($bytes[$i]));
		}

		return $ret;
	}

	public static function generateRandomBytes($length, $fallback = true)
	{
		return openssl_random_pseudo_bytes($length);
	}

	public static function xpQuery(DOMNode $node, $query)
	{
		static $xpCache = null;

		if ($node instanceof DOMDocument)
		{
			$doc = $node;
		}
		else
		{
			$doc = $node->ownerDocument;
		}

		if ($xpCache === null || !$xpCache->document->isSameNode($doc))
		{
			$xpCache = new DOMXPath($doc);
			$xpCache->registerNamespace('soap-env', 'http://schemas.xmlsoap.org/soap/envelope/');
			$xpCache->registerNamespace('saml_protocol', 'urn:oasis:names:tc:SAML:2.0:protocol');
			$xpCache->registerNamespace('saml_assertion', 'urn:oasis:names:tc:SAML:2.0:assertion');
			$xpCache->registerNamespace('saml_metadata', 'urn:oasis:names:tc:SAML:2.0:metadata');
			$xpCache->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');
			$xpCache->registerNamespace('xenc', 'http://www.w3.org/2001/04/xmlenc#');
		}

		$results = $xpCache->query($query, $node);
		$ret = array();

		for ($i = 0; $i < $results->length; $i++)
		{
			$ret[$i] = $results->item($i);
		}

		return $ret;
	}

	public static function parseBoolean(DOMElement $node, $attributeName, $default = null)
	{
		if (!$node->hasAttribute($attributeName))
		{
			return $default;
		}

		$value = $node->getAttribute($attributeName);

		switch (strtolower($value))
		{
			case '0':
			case 'false':
				return false;
			case '1':
			case 'true':
				return true;
			default:
				throw new Exception('Invalid value of boolean attribute ' . var_export($attributeName, true) . ': ' . var_export($value, true));
		}
	}

	public static function desanitizeCertificate($certificate)
	{
		$certificate = preg_replace("/[\r\n]+/", '', $certificate);
		$certificate = str_replace('-----BEGIN CERTIFICATE-----', '', $certificate);
		$certificate = str_replace('-----END CERTIFICATE-----', '', $certificate);
		$certificate = str_replace(' ', '', $certificate);

		return $certificate;
	}

	public static function unsetCookieVariables($vars)
	{
		$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
			|| (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);

		foreach ($vars as $var)
		{
			$cookie = Factory::getApplication()->input->cookie->get($var);
			unset($cookie);

			if (PHP_VERSION_ID >= 70300)
			{
				setcookie(
					$var,
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
				setcookie($var, '', time() - 86400, '/', '', $isHttps, true);
			}
		}
	}

	public static function isValidCheck($spName, $acsUrl, $task, $error)
	{
		$jConfig = new JConfig;
		$email = $jConfig->mailfrom;
		$baseURL = Uri::root();
		$crntTime = date('m/d/Y H:i:s', time());
		$customer = new MoSamlIdpCustomer;
		$customer->isVal($email, $spName, $acsUrl, $baseURL, $crntTime, $task, $error);
	}

	public static function getJoomlaCmsVersion()
	{
		$jVersion = new Version;

		return $jVersion->getShortVersion();
	}

	public static function showErrorMessage($errors, $cause)
	{
		$lang = Factory::getLanguage();
		$lang->load('lib_miniorangejoomlaidpplugin', JPATH_SITE);
		?>
		<div style="font-family:Calibri;padding:0 3%;">
			<div style="color: #a94442;background-color: #f2dede;padding: 15px;margin-bottom: 20px;text-align:center;border:1px solid #E6B3B2;font-size:18pt;"> <?php echo Text::_('LIB_MINIORANGEJOOMLAIDPPLUGIN_ERROR_HEADER'); ?></div>
			<div style="color: #a94442;font-size:14pt; margin-bottom:20px;">
				<p><strong><?php echo Text::_('LIB_MINIORANGEJOOMLAIDPPLUGIN_ERROR'); ?>: </strong><?php echo $errors; ?></p>
				<p><strong><?php echo Text::_('LIB_MINIORANGEJOOMLAIDPPLUGIN_CAUSE'); ?>: </strong><?php echo $cause; ?></p>
			</div>
			<form action="<?php echo Uri::root(); ?>">
				<div style="margin:3%;display:block;text-align:center;">
					<input style="padding:1%;width:100px;background: #0091CD none repeat scroll 0% 0%;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #0073AA;box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;color: #FFF;" type="submit" value="<?php echo Text::_('LIB_MINIORANGEJOOMLAIDPPLUGIN_DONE_BTN'); ?>" onClick="self.close();">
				</div>
			</form>
		</div>
		<?php
		exit;
	}

	public static function fetchDatabaseValues($table, $loadBy, $colName = '*', $idName = 'id', $idValue = 1)
	{
		$db = MoSamlIdpDb::getDb();
		$query = $db->getQuery(true);

		$query->select($colName);
		$query->from($db->quoteName($table));

		if (is_numeric($idValue))
		{
			$query->where($db->quoteName($idName) . " = $idValue");
		}
		else
		{
			$query->where($db->quoteName($idName) . ' = ' . $db->quote($idValue));
		}

		$db->setQuery($query);
		$defaultConfig = null;

		if ($loadBy == 'loadAssoc')
		{
			$defaultConfig = $db->loadAssoc();
		}
		elseif ($loadBy == 'loadResult')
		{
			$defaultConfig = $db->loadResult();
		}
		elseif ($loadBy == 'loadColumn')
		{
			$defaultConfig = $db->loadColumn();
		}

		return $defaultConfig;
	}

	public static function updateDatabaseQuery($databaseName, $updatefieldsarray)
	{
		$db = MoSamlIdpDb::getDb();
		$query = $db->getQuery(true);
		$databaseFields = array();

		foreach ($updatefieldsarray as $key => $value)
		{
			$databaseFields[] = $db->quoteName($key) . ' = ' . $db->quote($value);
		}

		$query->update($db->quoteName($databaseName))->set($databaseFields)->where($db->quoteName('id') . ' = 1');
		$db->setQuery($query);
		$db->execute();
	}

	public static function invokeFeedbackForm($post, $id)
	{
		$lang = Factory::getLanguage();
		$lang->load('lib_miniorangejoomlaidpplugin', JPATH_SITE) || $lang->load('lib_miniorangejoomlaidpplugin', JPATH_ADMINISTRATOR);

		$result = self::fetchDatabaseValues('#__extensions', 'loadColumn', 'extension_id', 'name', 'COM_JOOMLAIDP');
		$tables = MoSamlIdpDb::getDb()->getTableList();
		$tab = 0;

		foreach ($tables as $table)
		{
			if (strpos($table, 'miniorange_saml_idp_customer'))
			{
				$tab = $table;
			}
		}

		if (!$tab)
		{
			return;
		}

		$customerResult = self::fetchDatabaseValues('#__miniorange_saml_idp_customer', 'loadAssoc', array('*'));
		$adminEmail = $customerResult['email'];
		$jConfig = new JConfig;
		$checkEmail = $jConfig->mailfrom;
		$feedbackEmail = !empty($adminEmail) ? $adminEmail : $checkEmail;
		$fid = $customerResult['uninstall_feedback'];
		$tpostData = $post;

		if ($fid != 0)
		{
			return;
		}

		$matched = false;

		foreach ($result as $results)
		{
			if ($results == $id)
			{
				$matched = true;
				break;
			}
		}

		if (!$matched)
		{
			return;
		}

		$deactivateReasons = array(
			Text::_('LIB_MINIORANGEJOOMLAIDPPLUGIN_FEEDBACK_FORM_REASON2'),
			Text::_('LIB_MINIORANGEJOOMLAIDPPLUGIN_FEEDBACK_FORM_REASON3'),
			Text::_('LIB_MINIORANGEJOOMLAIDPPLUGIN_FEEDBACK_FORM_REASON4'),
			Text::_('LIB_MINIORANGEJOOMLAIDPPLUGIN_FEEDBACK_FORM_REASON5'),
			Text::_('LIB_MINIORANGEJOOMLAIDPPLUGIN_FEEDBACK_FORM_REASON6'),
			Text::_('LIB_MINIORANGEJOOMLAIDPPLUGIN_FEEDBACK_FORM_REASON7'),
			Text::_('LIB_MINIORANGEJOOMLAIDPPLUGIN_FEEDBACK_FORM_REASON8'),
		);

		$reasonsHtml = '';

		foreach ($deactivateReasons as $reason)
		{
			$reasonsHtml .= '<div class=" radio " style="padding:1px;margin-left:2%">'
				. '<label style="font-weight:normal;font-size:14.6px" for="' . $reason . '">'
				. '<input type="radio" name="deactivate_plugin" value="' . $reason . '" required> '
				. $reason . '</label></div>';
		}

		$cidHtml = '';

		foreach ($tpostData['cid'] as $key)
		{
			$cidHtml .= '<input type="hidden" name="result[]" value=' . $key . '>';
		}

		echo '<link rel="stylesheet" type="text/css" href="' . Uri::base() . '/components/com_joomlaidp/assets/css/miniorange_idp.css" />';
		echo '<div class="form-style-6">';
		echo '<h1>' . Text::_('LIB_MINIORANGEJOOMLAIDPPLUGIN_FEEDBACK_FORM') . '</h1>';
		echo '<h3>' . Text::_('LIB_MINIORANGEJOOMLAIDPPLUGIN_FEEDBACK_FORM_REASON1') . ' </h3>';
		echo '<form name="f" method="post" action="" id="mojsp_feedback">';
		echo '<input type="hidden" name="mojsp_feedback" value="mojsp_feedback"/>';
		echo '<div><p style="margin-left:2%">' . $reasonsHtml . '</p><br>';
		echo '<textarea id="query_feedback" name="query_feedback" rows="4" style="margin-left:2%" cols="50" placeholder="' . Text::_('LIB_MINIORANGEJOOMLAIDPPLUGIN_FEEDBACK_QUERY') . '"></textarea><br><br><br>';
		echo '<tr><td width="20%"><b>' . Text::_('LIB_MINIORANGEJOOMLAIDPPLUGIN_FEEDBACK_EMAIL') . '<span style="color: #ff0000;">*</span>:</b></td>';
		echo '<td><input type="email" name="feedback_email" required value="' . $feedbackEmail . '" placeholder="' . Text::_('LIB_MINIORANGEJOOMLAIDPPLUGIN_FEEDBACK_EMAIL_PLACEHOLDER') . '" style="width:55%"/></td></tr>';
		echo $cidHtml;
		echo '<br><br><div class="mojsp_modal-footer">';
		echo '<input type="submit" name="miniorange_feedback_submit" style="cursor: pointer;" class="button button-primary button-large" value="' . Text::_('LIB_MINIORANGEJOOMLAIDPPLUGIN_SUBMIT_BTN') . '"/>';
		echo '</div></div></form>';
		echo '<form name="f" method="post" action="" id="mojsp_feedback_form_close">';
		echo '<input type="hidden" name="mojsp_skip_feedback" value="mojsp_skip_feedback"/>';
		echo $cidHtml;
		echo '<div style="text-align:center"><button type="submit" style="background:none;border:none;padding:0;color:#2a69b8;text-decoration:underline;cursor:pointer;font-size:inherit;">' . Text::_('LIB_MINIORANGEJOOMLAIDPPLUGIN_SKIP_FEEDBACK_BTN') . '</button></div>';
		echo '</form></div>';

		$reason2 = Text::_('LIB_MINIORANGEJOOMLAIDPPLUGIN_FEEDBACK_FORM_REASON2');
		$reason3 = Text::_('LIB_MINIORANGEJOOMLAIDPPLUGIN_FEEDBACK_FORM_REASON3');
		$reason4 = Text::_('LIB_MINIORANGEJOOMLAIDPPLUGIN_FEEDBACK_FORM_REASON4');
		$reason5 = Text::_('LIB_MINIORANGEJOOMLAIDPPLUGIN_FEEDBACK_FORM_REASON5');
		$reason6 = Text::_('LIB_MINIORANGEJOOMLAIDPPLUGIN_FEEDBACK_FORM_REASON6');
		$reason8 = Text::_('LIB_MINIORANGEJOOMLAIDPPLUGIN_FEEDBACK_FORM_REASON8');
		$placeholder1 = Text::_('LIB_MINIORANGEJOOMLAIDPPLUGIN_FEEDBACK_PLACEHOLDER1');
		$placeholder2 = Text::_('LIB_MINIORANGEJOOMLAIDPPLUGIN_FEEDBACK_PLACEHOLDER2');
		$placeholder3 = Text::_('LIB_MINIORANGEJOOMLAIDPPLUGIN_FEEDBACK_PLACEHOLDER3');
		$placeholder4 = Text::_('LIB_MINIORANGEJOOMLAIDPPLUGIN_FEEDBACK_PLACEHOLDER4');
		$placeholder5 = Text::_('LIB_MINIORANGEJOOMLAIDPPLUGIN_FEEDBACK_PLACEHOLDER5');
		$placeholder6 = Text::_('LIB_MINIORANGEJOOMLAIDPPLUGIN_FEEDBACK_PLACEHOLDER6');

		echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>';
		echo "<script>
			jQuery('input:radio[name=\"deactivate_plugin\"]').click(function () {
				var reason = jQuery(this).val();
				jQuery('#query_feedback').removeAttr('required');
				if (reason === '$reason2') {
					jQuery('#query_feedback').attr('placeholder', '$placeholder6');
				} else if (reason === '$reason3') {
					jQuery('#query_feedback').attr('placeholder', '$placeholder1');
				} else if (reason === '$reason5') {
					jQuery('#query_feedback').attr('placeholder', '$placeholder2');
				} else if (reason === '$reason2') {
					jQuery('#query_feedback').attr('placeholder', '$placeholder3');
				} else if (reason === '$reason8' || reason === '$reason6') {
					jQuery('#query_feedback').attr('placeholder', '$placeholder4');
					jQuery('#query_feedback').prop('required', true);
				} else if (reason === '$reason4') {
					jQuery('#query_feedback').attr('placeholder', '$placeholder5');
				}
			});
		</script>";
		exit;
	}

	public static function getOsInfo()
	{
		$userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

		$osArray = [
			'windows nt 10' => 'Windows 10',
			'windows nt 6.3' => 'Windows 8.1',
			'windows nt 6.2' => 'Windows 8',
			'windows nt 6.1|windows nt 7.0' => 'Windows 7',
			'windows nt 6.0' => 'Windows Vista',
			'windows nt 5.2' => 'Windows Server 2003/XP x64',
			'windows nt 5.1' => 'Windows XP',
			'windows xp' => 'Windows XP',
			'windows nt 5.0|windows nt5.1|windows 2000' => 'Windows 2000',
			'windows me' => 'Windows ME',
			'windows nt 4.0|winnt4.0' => 'Windows NT',
			'windows ce' => 'Windows CE',
			'windows 98|win98' => 'Windows 98',
			'windows 95|win95' => 'Windows 95',
			'win16' => 'Windows 3.11',
			'mac os x 10.1[^0-9]' => 'Mac OS X Puma',
			'macintosh|mac os x' => 'Mac OS X',
			'mac_powerpc' => 'Mac OS 9',
			'linux' => 'Linux',
			'ubuntu' => 'Linux - Ubuntu',
			'iphone' => 'iPhone',
			'ipod' => 'iPod',
			'ipad' => 'iPad',
			'android' => 'Android',
			'blackberry' => 'BlackBerry',
			'webos' => 'Mobile',

			'(media center pc).([0-9]{1,2}\.[0-9]{1,2})' => 'Windows Media Center',
			'(win)([0-9]{1,2}\.[0-9x]{1,2})' => 'Windows',
			'(win)([0-9]{2})' => 'Windows',
			'(windows)([0-9x]{2})' => 'Windows',

			'Win 9x 4.90' => 'Windows ME',
			'(windows)([0-9]{1,2}\.[0-9]{1,2})' => 'Windows',
			'win32' => 'Windows',
			'(java)([0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,2})' => 'Java',
			'(Solaris)([0-9]{1,2}\.[0-9x]{1,2}){0,1}' => 'Solaris',
			'dos x86' => 'DOS',
			'Mac OS X' => 'Mac OS X',
			'Mac_PowerPC' => 'Macintosh PowerPC',
			'(mac|Macintosh)' => 'Mac OS',
			'(sunos)([0-9]{1,2}\.[0-9]{1,2}){0,1}' => 'SunOS',
			'(beos)([0-9]{1,2}\.[0-9]{1,2}){0,1}' => 'BeOS',
			'(risc os)([0-9]{1,2}\.[0-9]{1,2})' => 'RISC OS',
			'unix' => 'Unix',
			'os/2' => 'OS/2',
			'freebsd' => 'FreeBSD',
			'openbsd' => 'OpenBSD',
			'netbsd' => 'NetBSD',
			'irix' => 'IRIX',
			'plan9' => 'Plan9',
			'osf' => 'OSF',
			'aix' => 'AIX',
			'GNU Hurd' => 'GNU Hurd',
			'(fedora)' => 'Linux - Fedora',
			'(kubuntu)' => 'Linux - Kubuntu',
			'(ubuntu)' => 'Linux - Ubuntu',
			'(debian)' => 'Linux - Debian',
			'(CentOS)' => 'Linux - CentOS',
			'(Mandriva).([0-9]{1,3}(\.[0-9]{1,3})?(\.[0-9]{1,3})?)' => 'Linux - Mandriva',
			'(SUSE).([0-9]{1,3}(\.[0-9]{1,3})?(\.[0-9]{1,3})?)' => 'Linux - SUSE',
			'(Dropline)' => 'Linux - Slackware (Dropline GNOME)',
			'(ASPLinux)' => 'Linux - ASPLinux',
			'(Red Hat)' => 'Linux - Red Hat',
			'(linux)' => 'Linux',
			'(amigaos)([0-9]{1,2}\.[0-9]{1,2})' => 'AmigaOS',
			'amiga-aweb' => 'AmigaOS',
			'amiga' => 'Amiga',
			'AvantGo' => 'PalmOS',
			'[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,3})' => 'Linux',
			'(webtv)/([0-9]{1,2}\.[0-9]{1,2})' => 'WebTV',
			'Dreamcast' => 'Dreamcast OS',
			'GetRight' => 'Windows',
			'go!zilla' => 'Windows',
			'gozilla' => 'Windows',
			'gulliver' => 'Windows',
			'ia archiver' => 'Windows',
			'NetPositive' => 'Windows',
			'mass downloader' => 'Windows',
			'microsoft' => 'Windows',
			'offline explorer' => 'Windows',
			'teleport' => 'Windows',
			'web downloader' => 'Windows',
			'webcapture' => 'Windows',
			'webcollage' => 'Windows',
			'webcopier' => 'Windows',
			'webstripper' => 'Windows',
			'webzip' => 'Windows',
			'wget' => 'Windows',
			'Java' => 'Unknown',
			'flashget' => 'Windows',
			'MS FrontPage' => 'Windows',
			'(msproxy)/([0-9]{1,2}.[0-9]{1,2})' => 'Windows',
			'(msie)([0-9]{1,2}.[0-9]{1,2})' => 'Windows',
			'libwww-perl' => 'Unix',
			'UP.Browser' => 'Windows CE',
			'NetAnts' => 'Windows',
		];

		$archRegex = '/\b(x86_64|x86-64|Win64|WOW64|x64|ia64|amd64|ppc64|sparc64|IRIX64)\b/ix';
		$arch = preg_match($archRegex, $userAgent) ? '64' : '32';

		foreach ($osArray as $regex => $value)
		{
			if (preg_match('{\b(' . $regex . ')\b}i', $userAgent))
			{
				return $value . ' x' . $arch;
			}
		}

		return 'Unknown';
	}

	public static function setupGuides()
	{
		return '
        {
                "1": {
                  "name": "Moodle",
                  "link": "https://plugins.miniorange.com/moodle-saml-single-sign-on-sso-using-joomla-idp"
                },
                "2": {
                  "name": "Tableau",
                  "link": "https://plugins.miniorange.com/login-to-tableau-saml-single-sign-sso-using-joomla"
                },
                "3": {
                  "name": "ZOHO",
                  "link": "https://plugins.miniorange.com/login-to-zoho-saml-single-sign-sso-using-joomla"
                },
                "4": {
                  "name": "Nextcloud",
                  "link": "https://plugins.miniorange.com/nextcloud-saml-single-sign-on-sso-for-joomla-idp"
                },
                "5": {
                  "name": "AWS Congito",
                  "link": "https://plugins.miniorange.com/login-to-aws-cognito-saml-single-sign-sso-using-joomla"
                },
                "6": {
                  "name": "Salesforce",
                  "link": "https://plugins.miniorange.com/login-to-salesforce-saml-single-sign-sso-using-joomla"
                },

                "7": {
                  "name": "Zoom",
                  "link": "https://plugins.miniorange.com/zoom-single-sign-on-sso-using-joomla"
                },
                "8": {
                  "name": "Zendesk",
                  "link": "https://plugins.miniorange.com/zendesk-single-sign-on-sso-for-joomla-idp"
                },
                "9": {
                  "name": "Easy LMS",
                  "link": "https://plugins.miniorange.com/login-using-joomla-saml-single-sign-on-sso-into-easy-lms"
                },
                "10": {
                  "name": "Linkedin",
                  "link": "https://plugins.miniorange.com/login-using-joomla-saml-single-sign-on-sso-into-linkedin"
                },
                "11": {
                  "name": "Slack",
                  "link": "https://plugins.miniorange.com/login-using-joomla-saml-single-sign-on-sso-into-slack"
                },
                "12": {
                  "name": "Workplace",
                  "link": "https://plugins.miniorange.com/single-sign-workplace-facebook-sp-joomla-idp"
                },
                "13": {
                  "name": "Owncloud",
                  "link": "https://plugins.miniorange.com/login-to-owncloud-saml-single-sign-sso-using-joomla"
                },
                "14": {
                  "name": "AppStream2",
                  "link": "https://plugins.miniorange.com/login-to-appstream-2-0-saml-single-sign-sso-using-joomla"
                },
                "15": {
                  "name": "Panopto",
                  "link": "https://plugins.miniorange.com/login-to-panopto-saml-single-sign-sso-using-joomla"
                },
                "16": {
                  "name": "Drupal",
                  "link": "https://plugins.miniorange.com/drupal-saml-single-sign-on-sso-with-joomla"
                },
                "17": {
                  "name": "Klipfolio",
                  "link": "https://plugins.miniorange.com/login-to-klipfolio-saml-single-sign-sso-using-joomla"
                },
                "18": {
                    "name": "Rokcetchat",
                    "link": "https://plugins.miniorange.com/login-to-rocketchat-saml-single-sign-sso-using-joomla"
                },
                "19": {
                  "name": "Deskpro",
                  "link": "https://plugins.miniorange.com/login-to-deskpro-saml-single-sign-sso-using-joomla"
                },
                "20": {
                  "name": "FreshDesk",
                  "link": "https://plugins.miniorange.com/freshdesk-single-sign-on-sso-with-joomla"
                },
                "21": {
                  "name": "Box",
                  "link": "https://plugins.miniorange.com/login-using-joomla-saml-single-sign-on-sso-into-box"
                },
                "22": {
                  "name": "Monday.com",
                  "link": "https://plugins.miniorange.com/login-using-joomla-saml-single-sign-on-sso-into-monday-dot-com"
                },
                "23": {
                    "name": "Trello",
                    "link": "https://plugins.miniorange.com/login-using-joomla-saml-single-sign-on-sso-into-trello"
                  },
                "24": {
                    "name": "Other SPs",
                    "link": "https://plugins.miniorange.com/joomla-sso-ldap-mfa-solutions?section=saml-idp"
                }


        }';
	}
}
