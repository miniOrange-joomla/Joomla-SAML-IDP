<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  plg_user_miniorangejoomlaidp
 * @author      miniOrange Security Software Pvt. Ltd.
 * @copyright   Copyright (C) 2015 miniOrange (https://www.miniorange.com)
 * @license     GNU General Public License version 3; see LICENSE.txt
 * @contact     info@xecurify.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
jimport('joomla.plugin.plugin');

if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

class plgUserMiniorangejoomlaidp extends CMSPlugin
{
    /**
     * This method should handle any authentication and report back to the subject
     *
     *
     * @access    public
     * @param array $credentials Array holding the user credentials ('username' and 'password')
     * @param array $options Array of extra options
     * @param object $response Authentication response object
     * @return    boolean
     */
    public function onUserAfterLogin($options)
    {
        $app = Factory::getApplication();
        $input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
        $cookie = ($input && $input->cookie) ? $input->cookie->getArray() : [];
        if (isset($cookie['response_params'])) {
            $response_params = json_decode(stripslashes($cookie['response_params']), true);
            if (strcmp($response_params['moIdpsendResponse'], 'true') == 0) {
                $current_user = Factory::getUser();
                if (in_array(8, $current_user->groups) || in_array(7, $current_user->groups)) {
                    $this->mo_idp_send_reponse($response_params['acs_url'], $response_params['audience'], $response_params['relayState'], $response_params['inResponseTo']);
                } else {
                    IDP_Utilities::dispatchMessage();
                }
            }
        }
    }

    private function mo_idp_send_reponse($acs_url, $audience, $relayState, $inResponseTo)
    {
        $current_user = Factory::getUser();
        $row = IDP_Utilities::fetchDatabaseValues('#__miniorangesamlidp', 'loadAssoc','*');

        $email = $current_user->email;
        $username = $current_user->username;

        $issuer = Uri::root() . 'plugins/user/miniorangejoomlaidp/';

        $idpid = IDP_Utilities::fetchDatabaseValues('#__miniorange_saml_idp_customer', 'loadResult', 'idp_entity_id');

        if (!empty($idpid) && ($issuer != $idpid))
            $issuer = $idpid;

        $nameid_attribute = $row['nameid_attribute'];
        $nameid_format = $row['nameid_format'];
        $assertion_signed = $row['assertion_signed'];
        $saml_response_obj = new GenerateResponse($email, $username, $acs_url, $issuer, $audience, $nameid_attribute, $nameid_format, $assertion_signed, $inResponseTo);

        $saml_response = $saml_response_obj->createSamlResponse();

        ob_clean();
        IDP_Utilities::unsetCookieVariables(array('response_params', 'acs_url', 'audience', 'relayState', 'inResponseTo'));

        setcookie('response_params', '', time() - 86400, '/');

        $this->_send_response($saml_response, $relayState, $acs_url);
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