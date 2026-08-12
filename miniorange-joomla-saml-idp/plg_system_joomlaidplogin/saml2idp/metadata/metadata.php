<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  plg_system_joomlaidplogin
 * @author      miniOrange Security Software Pvt. Ltd.
 * @copyright   Copyright (C) 2015 miniOrange (https://www.miniorange.com)
 * @license     GNU General Public License version 3; see LICENSE.txt
 * @contact     info@xecurify.com
 */

defined('_JEXEC');

if (!defined('_JEXEC'))
{
	define('_JEXEC', 1);
}

defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;

if (!defined('_JDEFINES'))
{
	define('JPATH_BASE', dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))));
	require_once JPATH_BASE . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'defines.php';
}

require_once JPATH_BASE . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'framework.php';
jimport('miniorangejoomlaidpplugin.utility.IDP_Utilities');
include_once JPATH_SITE . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_joomlaidp' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'DbHelper.php';
$header = isset($_REQUEST['download']) && boolval($_REQUEST['download']) ? 'Content-Disposition: attachment; filename="Metadata.xml"' : 'Content-Type: text/xml';

$siteUrl = Uri::root();
$siteUrl = substr($siteUrl, 0, strpos($siteUrl, 'plugins'));
$entityId = $siteUrl . 'plugins/user/miniorangejoomlaidp/';
$db = MoSamlIdpDb::getDb();
$query = $db->getQuery(true);
$query->select(array($db->quoteName('idp_entity_id')));
$query->from($db->quoteName('#__miniorange_saml_idp_customer'));
$query->where($db->quoteName('id') . '=1');
$db->setQuery($query);
$idpid = $db->loadResult();

if (!empty($idpid) && ($entityId != $idpid))
{
	$entityId = $idpid;
}

$loginUrl = $siteUrl . 'index.php';
$logoutUrl = $siteUrl . 'index.php/log-out';
$certificate = file_get_contents(JPATH_BASE . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR . 'joomlaidplogin' . DIRECTORY_SEPARATOR . 'saml2idp' . DIRECTORY_SEPARATOR . 'cert' . DIRECTORY_SEPARATOR . 'idp-signing.crt');
$certificate = IDP_Utilities::desanitizeCertificate($certificate);

header($header);
echo '<?xml version="1.0" encoding="UTF-8"?>
        <md:EntityDescriptor xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata" entityID="' . $entityId . '">
            <md:IDPSSODescriptor WantAuthnRequestsSigned="false" protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol">
                <md:KeyDescriptor use="signing">
			        <ds:KeyInfo xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
				        <ds:X509Data>
        					<ds:X509Certificate>' . $certificate . '</ds:X509Certificate>
		        		</ds:X509Data>
			        </ds:KeyInfo>
		        </md:KeyDescriptor>
		    <md:NameIDFormat>urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress</md:NameIDFormat>
		    <md:NameIDFormat>urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified</md:NameIDFormat>
		    <md:SingleSignOnService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST" Location="' . $loginUrl . '"/>
		    <md:SingleSignOnService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect" Location="' . $loginUrl . '"/>
	    </md:IDPSSODescriptor>
    </md:EntityDescriptor>';
