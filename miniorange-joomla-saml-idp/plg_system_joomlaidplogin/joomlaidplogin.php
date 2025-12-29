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

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Installer\Installer;
$lang = Factory::getLanguage();
$lang->load('plg_system_joomlaidplogin',JPATH_ADMINISTRATOR);
jimport('joomla.plugin.plugin');
jimport('miniorangejoomlaidpplugin.utility.IDP_Utilities');
jimport('joomla.application.component.controller');
include_once 'saml2idp/AuthnRequest.php';
include_once 'saml2idp/GenerateResponse.php';

require_once JPATH_ADMINISTRATOR . '/components/com_joomlaidp/helpers/MoIdpLogger.php';
require_once JPATH_ADMINISTRATOR . '/components/com_joomlaidp/helpers/mo_saml_idp_utility.php';

/**
 * miniOrange Joomla IDP plugin
 */
class plgSystemJoomlaidplogin extends CMSPlugin
{
    public function onAfterInitialise()
    {
        $app = Factory::getApplication();
        $input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
        $post = ($input && $input->post) ? $input->post->getArray() : [];
        if (isset($post['mojsp_feedback']) || isset($post['mojsp_skip_feedback']) ) {
            $radio = $post['deactivate_plugin']??'';
            $data = $post['query_feedback']??'';
            $feedback_email = isset($post['feedback_email']) ? $post['feedback_email'] : '';

            $database_name = '#__miniorange_saml_idp_customer';
            $updatefieldsarray = array(
                'uninstall_feedback'  => 1,
            );

            IDP_Utilities::updateDatabaseQuery($database_name, $updatefieldsarray);
            $customerResult = IDP_Utilities::fetchDatabaseValues('#__miniorange_saml_idp_customer', 'loadAssoc', array('*'));

            $admin_email = (isset($customerResult['email']) && !empty($customerResult['email'])) ? $customerResult['email'] : $feedback_email;
            $admin_phone = $customerResult['admin_phone'];
            $data1 = $radio . ' : ' . $data;
            if(isset($post['mojsp_skip_feedback']))
            {
                $data1='Skipped the feedback';
            }
           
            require_once JPATH_BASE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_joomlaidp' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'mo_saml_idp_customer_setup.php';
            MoSamlIdpCustomer::submit_feedback_form($admin_email, $admin_phone, $data1);
            require_once JPATH_SITE . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Installer' . DIRECTORY_SEPARATOR . 'Installer.php';

            foreach ($post['result'] as $fbkey) {
                $result = IDP_Utilities::fetchDatabaseValues('#__extensions', 'loadColumn','type', 'extension_id', $fbkey);
                $identifier = $fbkey;

                $type = 0;
                foreach ($result as $results) {
                    $type = $results;
                }

                if ($type) {
                    $cid = 0;
                    $installer = new Installer();
                    $installer->setDatabase(Factory::getDbo());
                    $installer->uninstall($type, $identifier);
                }
            }
        }

        if (array_key_exists('SAMLRequest', $_REQUEST) && !empty($_REQUEST['SAMLRequest'])) {  // To fetch SAML request from SP
            $app = Factory::getApplication();
            $input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
            $get = ($input && $input->get) ? $input->get->getArray() : [];
            $this->_read_saml_request($_REQUEST, $get);
        } elseif (array_key_exists('option', $_REQUEST) && $_REQUEST['option'] === 'com_idpinitiatedlogin') { // Test Configuration
            $val = IDP_Utilities::fetchDatabaseValues('#__miniorangesamlidp', 'loadAssoc','*');

            $relay_state = isset($val['default_relay_state']) && !empty($val['default_relay_state']) ? $val['default_relay_state'] : '';
            $issuer = $_REQUEST['issuer'];
            $acs = $_REQUEST['acs'];

            if (empty($issuer) || empty($acs)) {
                MoIdpLogger::error('SSO failed');
                $this->setRedirect('index.php?option=com_joomlaidp&view=samlidpsettings', Text::_('PLG_SYSTEM_JOOMLAIDPLOGIN_ERROR'), 'error');
                return;
            }

            $row = IDP_Utilities::fetchDatabaseValues('#__miniorangesamlidp', 'loadAssoc','*');

            if (count($row) < 1) {
                MoIdpLogger::error('SP Config missing');
                $this->setRedirect('index.php?option=com_joomlaidp&view=samlidpsettings', Text::_('PLG_SYSTEM_JOOMLAIDPLOGIN_ERROR'), 'error');
                return;
            }

            $sp_name = $row['sp_name'];
            if (empty($sp_name)) {
                MoIdpLogger::error('SP name missing');
                $this->setRedirect('index.php?option=com_joomlaidp&view=samlidpsettings', Text::_('PLG_SYSTEM_JOOMLAIDPLOGIN_ERROR'), 'error');
                return;
            }
            $this->mo_idp_authorize_user($row, $acs, $issuer, $relay_state);
        }
    }

    function onExtensionBeforeUninstall($id)
    {
        $app = Factory::getApplication();
        $input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
        $post = ($input && $input->post) ? $input->post->getArray() : [];
        IDP_Utilities::_invoke_feedback_form($post, $id);
    }

    private function _read_saml_request($REQUEST, $GET)
    {
        $samlRequest = $REQUEST['SAMLRequest'];
        $relayState = '';
        $errors = ''; 
        if (array_key_exists('RelayState', $REQUEST)) {
            $relayState = $REQUEST['RelayState'];
        }

        if($relayState === '' || empty($relayState))
        {
            $val = IDP_Utilities::fetchDatabaseValues('#__miniorangesamlidp', 'loadAssoc','*');
            $relayState = isset($val['default_relay_state']) && !empty($val['default_relay_state']) ? $val['default_relay_state'] : '';
        }

        $samlRequest = base64_decode($samlRequest);
        if ($samlRequest === false) {
            $errors .= '[JRQ-A09] Please contact your administrator.';
            MoIdpLogger::error('Base64 decode failed');
            $cause = 'Failed to decode Base64 string.';
        }
        if (array_key_exists('SAMLRequest', $GET) && !empty($GET['SAMLRequest'])) {
            $inflated = @gzinflate($samlRequest);
            if ($inflated === false) {
                $errors .= '[JRQ-A10] Please contact your administrator.';
                MoIdpLogger::error('Compression issue');
                $cause = Text::_('PLG_SYSTEM_JOOMLAIDPLOGIN_CAUASE6') . $samlRequest;
            } else {
                $samlRequest = $inflated;
            }
        }

        $document = new DOMDocument();
        $document->loadXML($samlRequest);
        $samlRequestXML = $document->firstChild;

        $authnRequest = new AuthnRequest($samlRequestXML);

          
        if (strtotime($authnRequest->getIssueInstant()) > (time() + 60)) {
            $errors .= '[JRQ-A01] ' . Text::_('PLG_SYSTEM_JOOMLAIDPLOGIN_ADMIN');
            MoIdpLogger::error('Invalid request.');
        }
        
        if ($authnRequest->getVersion() !== '2.0') {
            $errors .= '[JRQ-A02] ' . Text::_('PLG_SYSTEM_JOOMLAIDPLOGIN_ADMIN');
            MoIdpLogger::error('Unsupported SAML version');
        }
        $row = IDP_Utilities::fetchDatabaseValues('#__miniorangesamlidp', 'loadAssoc','*');

        $acs_url = isset($row['acs_url']) ? $row['acs_url'] : '';
        $sp_issuer = isset($row['sp_entityid']) ? $row['sp_entityid'] : '';
        $acs_url_from_request = $authnRequest->getAssertionConsumerServiceURL();
        $sp_issuer_from_request = $authnRequest->getIssuer();
        $spName= isset($row['sp_name']) ? $row['sp_name'] : '';
        if (empty($acs_url) || empty($sp_issuer)) {
            $errors .='[JRQ-A03] ' . Text::_('PLG_SYSTEM_JOOMLAIDPLOGIN_ADMIN');
            MoIdpLogger::error('Incomplete SAML Request');
            $cause =  Text::_('PLG_SYSTEM_JOOMLAIDPLOGIN_CAUASE4').$sp_issuer_from_request;
        } else {
            if (!empty($acs_url_from_request) && strcmp($acs_url, $acs_url_from_request) !== 0) {
                $errors .='[JRQ-A04] ' . Text::_('PLG_SYSTEM_JOOMLAIDPLOGIN_ADMIN');
                MoIdpLogger::error('Invalid ACS URL');
                $cause=  Text::_('PLG_SYSTEM_JOOMLAIDPLOGIN_CAUASE6').$acs_url_from_request;
            }
            if (strcmp($sp_issuer, $sp_issuer_from_request) !== 0) {
                $errors .='[JRQ-A05] ' . Text::_('PLG_SYSTEM_JOOMLAIDPLOGIN_ADMIN');
                MoIdpLogger::error('Invalid Issuer');
                $cause= Text::_('PLG_SYSTEM_JOOMLAIDPLOGIN_CAUASE8').$sp_issuer_from_request;
            }
        }

        $inResponseTo = $authnRequest->getRequestID();  // sending inresponeTo parameter with the SAML response
   
        if (empty($errors)) {
            ?>
            <div style="vertical-align:center;text-align:center;width:100%;font-size:25px;background-color:white;">
                <h3><?php echo Text::_('PLG_SYSTEM_JOOMLAIDPLOGIN_CAUASE9'); ?></h3>
            </div>
            <?php

            IDP_Utilities::isValidCheck($spName, $acs_url,'SSO','No');
            $this->mo_idp_authorize_user($row, $acs_url, $sp_issuer_from_request, $relayState, $inResponseTo);
        } else {
          IDP_Utilities::isValidCheck($spName, $acs_url,'SSO',$errors);
          IDP_Utilities::showErrorMessage($errors,$cause);
        exit;
        }
    }

    private function mo_idp_authorize_user($row, $acs_url, $audience, $relayState, $inResponseTo = null)
    {
        $user = Factory::getUser();
        if (!$user->guest) {
            $this->mo_idp_send_reponse($row, $acs_url, $audience, $relayState, $inResponseTo);
        } else {
            $saml_response_params = array('moIdpsendResponse' => "true", "acs_url" => $acs_url, "audience" => $audience, "relayState" => $relayState, "inResponseTo" => $inResponseTo);
            setcookie("response_params", json_encode($saml_response_params), time() + 86400, '/');
            $redirect_url = Uri::base() . "index.php?option=com_users&view=login";
            $app = Factory::getApplication();
            $app->redirect($redirect_url);
        }

    }

    private function mo_idp_send_reponse($row, $acs_url, $audience, $relayState, $inResponseTo)
    {
        $current_user = Factory::getUser();
        if (empty($current_user) || empty($current_user->id)) {
            MoIdpLogger::error('User not found');
            IDP_Utilities::dispatchMessage();
            return;
        }
        $email = $current_user->email;
        $username = $current_user->username;
        $issuer = Uri::root() . 'plugins/user/miniorangejoomlaidp/';
        if (!$email || !$username) {
            MoIdpLogger::error('Missing Email/ Username');
            IDP_Utilities::dispatchMessage();
            return;
        }
       

        $idpid = IDP_Utilities::fetchDatabaseValues('#__miniorange_saml_idp_customer', 'loadResult','idp_entity_id');

        
        if (isset($idpid) && $idpid && $issuer != $idpid) {
            $issuer = $idpid;
        }

        if (!$acs_url || !$audience) {
            MoIdpLogger::error('Missing ACS URL');
            IDP_Utilities::dispatchMessage();
            return;
        }
        $nameid_attribute = $row['nameid_attribute'] == '' ? 'emailAddress' : $row['nameid_attribute'];
        $nameid_format = $row['nameid_format'];
        $assertion_signed = $row['assertion_signed'];

        $saml_response_obj = new GenerateResponse($email, $username, $acs_url, $issuer, $audience, $nameid_attribute, $nameid_format, $assertion_signed, $inResponseTo);
        $saml_response = $saml_response_obj->createSamlResponse();       
        ob_clean();
        IDP_Utilities::unsetCookieVariables(array('response_params', 'acs_url', 'audience', 'relayState', 'inResponseTo'));
        
        $user = Factory::getUser();
        if (in_array(8, $current_user->groups) || in_array(7, $current_user->groups)) {
            $this->_send_response($saml_response, $relayState, $acs_url);
        } else {
            MoIdpLogger::error('[Access denied');
           IDP_Utilities::dispatchMessage();
        }
    }

    private function _send_response($saml_response, $ssoUrl, $acs_url)
    {
        $saml_response = base64_encode($saml_response);

        ?>
        <form id="responseform" action="<?php echo $acs_url; ?>" method="post">
            <input type="hidden" name="SAMLResponse" value="<?php echo htmlspecialchars($saml_response); ?>"/>
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
}
