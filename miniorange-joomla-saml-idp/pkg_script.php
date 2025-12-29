<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Version;
/**
 * @author      miniOrange Security Software Pvt. Ltd.
 * @copyright   Copyright (C) 2015 miniOrange (https://www.miniorange.com)
 * @license     GNU General Public License version 3; see LICENSE.txt
 * @contact     info@xecurify.com
 */
    
class pkg_MiniorangeJoomlaSAMLIdpSSOInstallerScript
{
    /**
     * This method is called after a component is installed.
     *
     * @param  \stdClass $parent - Parent object calling this method.
     *
     * @return void
     */
    public function install($parent) 
    {
        jimport('miniorangejoomlaidpplugin.utility.IDP_Utilities');
        require_once JPATH_ADMINISTRATOR . '/components/com_joomlaidp/helpers/mo_saml_idp_customer_setup.php';
        $siteName = $_SERVER['SERVER_NAME'];
        $currentUser = Factory::getUser();
        $currentUserEmail = $currentUser->email;         
        $moPluginVersion = IDP_Utilities::GetPluginVersion();
        $jVersion   = new Version;
        $jCmsVersion = $jVersion->getShortVersion();
        $phpVersion = phpversion();
		$OS = IDP_Utilities:: _get_os_info();
        $serverSoftware = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'Unknown';
        $webServer = !empty($serverSoftware) ? trim(explode('/', $serverSoftware)[0]) : 'Unknown';
        $query1 = '[Plugin ' . $moPluginVersion . ' | PHP ' . $phpVersion .' | Joomla Version '. $jCmsVersion .' | OS : '.$OS.' | Web Server : '.$webServer.']';
        $content = '<div>
            Hello,<br><br>
            Plugin has been successfully installed on the following site.<br><br>
            <strong>Company:</strong> <a href="http://' . $siteName . '" target="_blank">' . $siteName . '</a><br>
            <strong>Admin Email:</strong> <a href="mailto:' . $currentUserEmail . '">' . $currentUserEmail . '</a><br>
            <strong>System Information:</strong> ' . $query1 . '<br><br>
        </div>';
        MoSamlIdpCustomer::send_idp_test_mail($currentUserEmail, $content);
            
    }

    /**
     * This method is called after a component is uninstalled.
     *
     * @param  \stdClass $parent - Parent object calling this method.
     *
     * @return void
     */
    public function uninstall($parent) 
    {
    }

    /**
     * This method is called after a component is updated.
     *
     * @param  \stdClass $parent - Parent object calling object.
     *
     * @return void
     */
    public function update($parent) 
    {
        // Update completed
    }

    /**
     * Runs just before any installation action is performed on the component.
     * Verifications and pre-requisites should run in this function.
     *
     * @param  string    $type   - Type of PreFlight action. Possible values are:
     *                           - * install
     *                           - * update
     *                           - * discover_install
     * @param  \stdClass $parent - Parent object calling object.
     *
     * @return void
     */
    public function preflight($type, $parent) 
    {
        // Preflight checks completed
    }

    /**
     * Runs right after any installation action is performed on the component.
     *
     * @param  string    $type   - Type of PostFlight action. Possible values are:
     *                           - * install
     *                           - * update
     *                           - * discover_install
     * @param  \stdClass $parent - Parent object calling object.
     *
     * @return void
     */
    function postflight($type, $parent) 
    {
       if ($type == 'uninstall') {
        return true;
        }
       $this->showInstallMessage('');
    }

    protected function showInstallMessage($messages=array()) {
        jimport('miniorangejoomlaidpplugin.utility.IDP_Utilities');
        ?>
        <style>
        
        .mo-row {
            width: 100%;
            display: block;
            margin-bottom: 2%;
        }
    
        .mo-row:after {
            clear: both;
            display: block;
            content: "";
        }
    
        .mo-column-2 {
            width: 19%;
            margin-right: 1%;
            float: left;
        }
    
        .mo-column-10 {
            width: 80%;
            float: left;
        }
        .mo_boot_btn {
        display: inline-block;
        font-weight: 400;
        text-align: center;
        vertical-align: middle;
        user-select: none;
        background-color: transparent;
        border: 1px solid transparent;
        padding: 4px 12px;
        font-size: 0.85rem;
        line-height: 1.5;
        border-radius: 0.25rem;
        transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
      
        .btn_cstm {
        background: linear-gradient(0deg,rgb(14 42 71) 0,rgb(26 69 138) 100%)!important;
        border: none;
        font-size: 1.1rem;
        padding: 0.3rem 1.5rem;
        color: #fff!important;
        cursor: pointer;
      }
    
    </style>
    <h3>miniOrange SAML IDP plugin</h3>
    <p>Our plugin is compatible with Joomla 3, 4, 5 and 6. Additionally, it integrates with all the SAML 2.0 compliant Service Providers.</p>
    <h4>Current Version: <?php echo IDP_Utilities::GetPluginVersion(); ?></h4>
    <h4>Steps to use the SAML IDP plugin.</h4>
    <ul>
        <li>Click on Components</li>
        <li>Click on miniOrange Joomla IDP and select Service Provider tab</li>
        <li>You can start configuring.</li>
    </ul>
    <div class="mo-row">
        <a class="mo_boot_btn btn_cstm" href="index.php?option=com_joomlaidp&view=accountsetup&tab-panel=overview">Get Started!</a>
        <a class="mo_boot_btn btn_cstm" href="https://plugins.miniorange.com/joomla-idp-saml-sso" target="_blank">Read the miniOrange documents</a>
        <a class="mo_boot_btn btn_cstm" href="https://plugins.miniorange.com/joomla-sso-ldap-mfa-solutions?section=saml-idp" target="_blank">Setup Guides</a>
        <a class="mo_boot_btn btn_cstm" href="https://www.miniorange.com/contact" target="_blank">Get Support!</a>
    </div>
        <?php
    }
  
}