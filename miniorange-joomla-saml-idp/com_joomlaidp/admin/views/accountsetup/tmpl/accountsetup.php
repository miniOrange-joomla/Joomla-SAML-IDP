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
defined('_JEXEC') or die('Restricted Access');
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
HTMLHelper::_('jquery.framework');

$document = Factory::getApplication()->getDocument();
$document->addScript(Uri::base() . 'components/com_joomlaidp/assets/js/bootstrap-select-min.js');
$document->addScript(Uri::base() . 'components/com_joomlaidp/assets/js/utilityjs.js');
$document->addStyleSheet(Uri::base() . 'components/com_joomlaidp/assets/css/miniorange_boot.css');
$document->addStyleSheet(Uri::base() . 'components/com_joomlaidp/assets/css/miniorange_idp.css');
$document->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css');

require_once JPATH_ADMINISTRATOR . '/components/com_joomlaidp/helpers/MoIdpLogger.php';

$cms_version = IDP_Utilities::getJoomlaCmsVersion();
if($cms_version >= 4.0)
{
    HTMLHelper::_('script', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js');
}
$app = Factory::getApplication();
$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
$tab = ($input && $input->get) ? $input->get->getArray() : [];
$idp_active_tab = isset($tab['tab-panel']) ? $tab['tab-panel'] : 'overview';
$test_config = isset($tab['test-config']) ? true : false;

if (MoSamlIdpUtility::is_curl_installed() == 0) { ?>
    <p class="mo_idp_warning">
        (Warning:
            <a href="http://php.net/manual/en/curl.installation.php" target="_blank">PHP CURL extension</a>
        is not installed or disabled) Please go to Troubleshooting for steps to enable curl.
    </p>
    <?php
} 

if (isset($tab['tab-panel']) && !empty($tab['tab-panel']))
{
    if($cms_version >= 4.0)
    {
    ?>
    <script>
        jQuery(document).ready(function () {
            jQuery('#subhead-container').css('min-height', '55px');
            var subheadDiv = document.getElementById('subhead-container');
            var supportButton = '<div class="mo_boot_d-inline-block mo_boot_mr-2 mo_idp_free_btn"><a class="mo_boot_btn btn_cstm mo_idp_free_btn" href="<?php echo Route::_('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=support_tab')?>"><?php echo Text::_('COM_MINIORANGE_SUPPORT_BUTTON'); ?></a></div>';
            var trialButton = '<div class="mo_boot_d-inline-block mo_boot_float-right mo_boot_mr-3 "><a class="mo_boot_btn btn_cstm mo_idp_free_btn" href="<?php echo Route::_('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=request_demo')?>"><?php echo Text::_('COM_MINIORANGE_IDP_FREE_TRIAL'); ?></a></div>';
            var buttonsContainer = '<div class="">' + supportButton + trialButton + '</div>';
            subheadDiv.innerHTML = buttonsContainer;
        });
    </script>
    <?php
    }
}
?>
    <div class="mo_boot_container-fluid mo_boot_p-0 mo_boot_m-0">
        <div class="mo_boot_row mo_boot_p-0 mo_boot_m-0">
            <div id="mo_saml_nav_parent" class="mo_boot_col-lg-12 mo_boot_p-0 mo_boot_m-0">
          
                <a id="overviewtab" class=" mo_boot_px-0 mo_boot_py-5 mo_boot_m-0 mo_nav-tab mo_nav_tab_<?php echo $idp_active_tab == 'overview' ? 'active' : ''; ?>" href="#overview_plugin" onclick="add_css_tab('#overviewtab');"data-toggle="tab">
                    <i class=" fa fa-rectangle-list"> </i>
                    <span class="mo_idp_tab "> <?php echo Text::_('COM_JOOMLAIDP_TAB1_OVERVIEW'); ?></span>
                </a>

                <a id="sptab" class="mo_boot_p-3  mo_nav-tab mo_nav_tab_<?php echo $idp_active_tab == 'sp' ? 'active' : ''; ?>" href="#service-provider" onclick="add_css_tab('#sptab');" data-toggle="tab">
                    <span><i class="fa fa-server"> </i>
                    <span class="mo_idp_tab "> <?php echo Text::_('COM_JOOMLAIDP_TAB3_SERVICE_PROVIDER'); ?></span>

                </a>

                <a id="idptab" class="mo_boot_p-3  mo_nav-tab mo_nav_tab_<?php echo $idp_active_tab == 'idp' ? 'active' : ''; ?>" href="#identity-provider" onclick="add_css_tab('#idptab');" data-toggle="tab">
                    <span><i class="fa fa-address-card"> </i>
                    <span class="mo_idp_tab "> <?php echo Text::_('COM_JOOMLAIDP_TAB4_IDENTITY_PROVIDER'); ?></span>

                </a>

                <a id="advance_mapping_tab" class="mo_boot_p-3  mo_nav-tab mo_nav_tab_<?php echo $idp_active_tab == 'advance_mapping' ? 'active' : ''; ?>" href="#iadvance_mapping" onclick="add_css_tab('#advance_mapping_tab');" data-toggle="tab">
                    <span><i class="fa fa-solid fa-map"> </i>
                    <span class="mo_idp_tab "> <?php echo Text::_('COM_JOOMLAIDP_MAPPING'); ?></span>

                </a>

                <a id="rolerelay_restiction" class="mo_boot_p-3  mo_nav-tab mo_nav_tab_<?php echo $idp_active_tab == 'role_relay_restriciton' ? 'active' : ''; ?>" href="#role_relay_restriciton_id" onclick="add_css_tab('#rolerelay_restiction');" data-toggle="tab">
                    <span><i class="fa fa-solid fa-triangle-exclamation"> </i>
                    <span class="mo_idp_tab "> <?php echo Text::_('COM_JOOMLAIDP_RELAY_RESTRICTION_TAB_NAME'); ?></span>

                </a>

                <a id="signin_settings_tab" class="mo_boot_p-3  mo_nav-tab mo_nav_tab_<?php echo $idp_active_tab == 'signin_settings' ? 'active' : ''; ?>" href="#signin_settings_id" onclick="add_css_tab('#signin_settings_tab');" data-toggle="tab">
                    <span><i class="fa fa-solid fa-user"> </i>
                    <span class="mo_idp_tab "> <?php echo Text::_('COM_JOOMLAIDP_SIGNIN_SETTINGS'); ?></span>

                </a>

                <a id="licensingtab" class="mo_boot_p-3  mo_nav-tab mo_nav_tab_<?php echo $idp_active_tab == 'license' ? 'active' : ''; ?>" href="#licensing-plans" onclick="add_css_tab('#licensingtab');" data-toggle="tab">
                    <span><i class="fa-solid fa-circle-up"></i>
                    <span class="mo_idp_tab "> <?php echo Text::_('COM_JOOMLAIDP_TAB6_LICENSING_PLANS'); ?></span>

                </a>

                <a id="errorlogtab" class="mo_boot_p-3  mo_nav-tab mo_nav_tab_<?php echo $idp_active_tab == 'errorlog' ? 'active' : ''; ?>" href="#error-logs" onclick="add_css_tab('#errorlogtab');" data-toggle="tab">
                    <span><i class="fas fa-shield-alt"></i>
                    <span class="mo_idp_tab "> <?php echo Text::_('COM_MINIORANGE_IDP_LOG_TAB'); ?></span>

                </a>

             
                <?php
              if($cms_version <= 4.0)
                {
                ?>
                    <a id="request_demo" class="mo_boot_p-3 mo_nav-tab mo_nav_tab_<?php echo $tab == 'request_demo' ? 'active' : ''; ?>" href="#request-demo" data-toggle="tab" onclick="add_css_tab('#request_demo');" data-toggle="tab">
                        <span><i class="fa fa-solid fa-bars"> </i></span> Free Trial
                    </a>
                    <a id="support_tab" class="mo_boot_p-3 mo_nav-tab mo_nav_tab_<?php echo $tab == 'support_tab' ? 'active' : ''; ?>" href="#support-tab" data-toggle="tab" onclick="add_css_tab('#support_tab');" data-toggle="tab">
                        <span><i class="fa fa-solid fa-headset"> </i></span> <?php echo Text::_('COM_MINIORANGE_SUPPORT_BUTTON'); ?>
                    </a>
                <?php
                }
                ?>  
            </div>
        </div>
    </div>

    
    <div class="mo_container tab-content" id="myTabContent">

        <div id="overview_plugin" class="tab-pane <?php echo $idp_active_tab == 'overview' ? 'active' : '';?>"> 
            <?php
                $class_name = "JoomlaIdpViewAccountSetup";
                $func_name = "showPluginOverview";
                call_user_func(array($class_name, $func_name));
            ?>
        </div>

        <div id="service-provider" class="tab-pane <?php echo $idp_active_tab == 'sp' ? 'active' : '';?>"> 
            <?php
                $class_name = "JoomlaIdpViewAccountSetup";
                $func_name = "showServiceProviderConfigurations";
                call_user_func(array($class_name, $func_name));
            ?>
        </div>

        <div id="identity-provider" class="tab-pane <?php echo $idp_active_tab == 'idp' ? 'active' : ''; ?>"> 
            <?php
                $class_name = "JoomlaIdpViewAccountSetup";
                $func_name = "showIdentityProviderConfigurations";
                call_user_func(array($class_name, $func_name));
            ?>
        </div>

        <div id="iadvance_mapping" class="tab-pane <?php echo $idp_active_tab == 'advance_mapping' ? 'active' : ''; ?>"> 
            <?php
                $class_name = "JoomlaIdpViewAccountSetup";
                $func_name = "showAdvanceMapping";
                call_user_func(array($class_name, $func_name));
            ?>
        </div>

        <div id="role_relay_restriciton_id" class="tab-pane <?php echo $idp_active_tab == 'role_relay_restriciton' ? 'active' : ''; ?>"> 
            <?php
                $class_name = "JoomlaIdpViewAccountSetup";
                $func_name = "showRoleRelayRestriction";
                call_user_func(array($class_name, $func_name));
            ?>
        </div>

        <div id="signin_settings_id" class="tab-pane <?php echo $idp_active_tab == 'signin_settings' ? 'active' : ''; ?>"> 
            <?php
                $class_name = "JoomlaIdpViewAccountSetup";
                $func_name = "showIDPInitiatedLoginDetails";
                call_user_func(array($class_name, $func_name));
            ?>
        </div>

        <div id="licensing-plans" class="tab-pane <?php echo $idp_active_tab == 'license' ? 'active' : ''; ?>">
           
                        <?php
                            $result      = IDP_Utilities::fetchDatabaseValues('#__miniorange_saml_idp_customer', 'loadAssoc','*');
                            $email       = isset($result['email']) ? $result['email'] : '';
                            $hostName    = MoSamlIdpUtility::getHostName();
                            $loginUrl    = $hostName . '/moas/login';
                            $redirectUrl = $hostName . '/moas/initializepayment';
                            echo $this->showLicensingPlanDetails();
                        ?>
              
            <form id="idp_default_form" method="post"
                action="<?php echo Route::_('index.php?option=com_joomlaidp&view=samlidpsettings'); ?>">
            </form>
            <form class="mo_idp_disp_no" id="moidp_loginform" action="<?php echo $loginUrl; ?>" target="_blank"
                method="post">
                <input name="username" value="<?php echo $email; ?>" type="email" class="mo_idp_disp_no">
                <input name="redirectUrl" value="<?php echo $redirectUrl; ?>" type="hidden">
                <input name="requestOrigin" id="requestOrigin" type="hidden">
            </form>
        </div>

        <div id="error-logs" class="tab-pane <?php echo $idp_active_tab == 'errorlog' ? 'active' : ''; ?>">
           
        <div class="mo_boot_col-sm-12 mo_tab_border mo_boot_p-2 mo_boot_m-0">
                <div class="col-sm-12" >
                    <?php moLoggers();?>
                </div>
            </div>
        </div>

        <div id="request-demo" class="tab-pane <?php if ($idp_active_tab == 'request_demo') echo 'active'; ?>">
        <?php
            $class_name = "JoomlaIdpViewAccountSetup";
            $func_name = "requestfordemo";
            call_user_func(array($class_name, $func_name));
        ?>
        </div>
        <div id="support-tab" class="tab-pane <?php if ($idp_active_tab == 'support_tab') echo 'active'; ?>" >
            <div class="mo_boot_row">
                <?php mo_saml_idp_support(); ?>
            </div>
        </div>
    </div>
</div>
<?php

function moLoggers(): void
{
    $list = MoIdpLogger::getAllLogs();

    ?>
    <div class="card card-outline-primary">
    <div class="mo_boot_d-flex  mo_saml_justify-content-end mo_boot_mt-3 mo_saml_gap pr-4">
        <form method="post" action="index.php?option=com_joomlaidp&view=accountsetup&task=accountsetup.resetLogs">
    
        <button type="submit" name="reset_logs" class="mo_boot_btn mo_boot_px-4 mo_boot_py-2 shadow-lg mo_boot_btn-danger mo_saml_height">
            <i class="fas fa-trash-alt me-2"></i>
        </button>

        <?php echo HTMLHelper::_('form.token'); ?>
        </form>
        <a href="index.php?option=com_joomlaidp&view=accountsetup&tab-panel=errorlog"
        class="mo_boot_btn mo_boot_px-4 mo_boot_py-2 shadow-lg mo_boot_btn-primary mo_saml_height btn_cstm">
            <i class="fas fa-sync-alt me-2"></i>
        </a>

        <a href="index.php?option=com_joomlaidp&view=accountsetup&task=accountsetup.downloadLogs"
        class="mo_boot_btn mo_boot_px-4 mo_boot_py-2 shadow-lg mo_boot_btn-success mo_saml_height btn_cstm">
            <i class="fas fa-download me-2"></i>
        </a>

    </div>
        <div class="card-header text-white text-center mo_boot_d-flex mo_saml_justify-content-center ">
            <h2 class="fw-bold mo_boot_m-0">
                <i class="fas fa-clipboard-list me-2"></i> <?php echo Text::_('COM_MINIORANGE_LOGGER_TITLE'); ?>
            </h2>
        </div>
        <div class="card-body mo_boot_p-4 mo_boot_mb-4">
            <table class="table table-striped table-bordered table-hover mo_log_table">
                <thead class="table-primary">
                <tr class="text-center">
                    <th><?php echo Text::_('COM_MINIORANGE_LOGGER_DATE'); ?></th>
                    <th><?php echo Text::_('COM_MINIORANGE_LOGGER_LEVEL'); ?></th>
                    <th><?php echo Text::_('COM_MINIORANGE_LOGGER_CODE'); ?></th>
                    <th><?php echo Text::_('COM_MINIORANGE_LOGGER_MESSAGE'); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($list)): ?>
                    <tr>
                        <td colspan="4" class="mo_boot_text-center text-muted">
                            <i class="fas fa-exclamation-circle"></i> <?php echo Text::_('COM_MINIORANGE_LOGGER_NO_LOGS'); ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($list as $log): ?>
                        <?php
                        $logData = json_decode($log->message, true);
                        $issue = $logData['issue'] ?? '-';
                        $logCode = $logData['code'] ?? '-';
                        $logLevel = strtolower(htmlspecialchars($log->log_level));
                        switch ($logLevel) {
                            case 'info':
                                $icon = '<i class="fas fa-check-circle text-success"></i>';
                                $badgeClass = 'badge bg-success';
                                break;
                            case 'warn':
                                $icon = '<i class="fas fa-exclamation-triangle text-warning"></i>';
                                $badgeClass = 'badge bg-warning text-dark';
                                break;
                            case 'err':
                            case 'error':
                                $icon = '<i class="fas fa-times-circle text-white"></i>';
                                $badgeClass = 'badge bg-danger';
                                break;
                            default:
                                $icon = '<i class="fas fa-info-circle"></i>';
                                $badgeClass = 'badge bg-secondary';
                        }
                        ?>
                        <tr>
                            <td class="text-center">
                                <i class="far fa-clock text-secondary"></i>
                                <?php echo date('j F Y h:ia', strtotime($log->timestamp)); ?>
                            </td>
                            <td class="text-center">
                                <span class="text-center <?php echo $badgeClass; ?>">
                                    <?php echo $icon . ' ' . htmlspecialchars($log->log_level); ?>
                                </span>
                            </td>
                            <td class="text-center fw-bold">
                                <?php echo htmlspecialchars($logCode); ?>
                            </td>
                            <td class="text-break">
                                <span class="<?php echo $badgeClass; ?>">
                                    <i class="fas fa-info-circle"></i>
                                </span>
                                <?php echo nl2br(htmlspecialchars($issue)); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}


    function mo_saml_idp_support()
    {
        $current_user = Factory::getUser();
        $result       = IDP_Utilities::fetchDatabaseValues('#__miniorange_saml_idp_customer', 'loadAssoc', '*');
        $admin_email  = isset($result['email']) ? $result['email'] : '';
        $admin_phone  = isset($result['admin_phone']) ? $result['admin_phone'] : '';

        if ($admin_email == '' || empty($admin_email))
            $admin_email = $current_user->email;
        ?>
            <div class="mo_boot_col-sm-12 mo_boot_m-0">
                <div class="mo_boot_row mo_tab_border mo_boot_p-2 mo_boot_m-0">
                    <div class="mo_boot_col-sm-12 mo_boot_mt-3">
                        <div class="mo_boot_row">
                            <div class="mo_boot_col-lg-5">
                                <h3 class="mo_saml_form_head"><?php echo Text::_('COM_JOOMLAIDP_SUPPORT_HEADER'); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="mo_boot_col-sm-12">
                        <div class=" mo_boot_offset-1">
                            <div class="mo_boot_row">
                                <div class="mo_boot_col-sm-11 alert alert-info">
                                    <span><?php echo Text::_('COM_JOOMLAIDP_SUPPORT_DESCRIPTION'); ?> </span>
                                </div>    
                            </div> 
                        </div>
                    </div>
                    <div class="mo_boot_col-sm-12 mo_boot_p-2">
                        <form  name="f" method="post" action="<?php echo Route::_('index.php?option=com_joomlaidp&view=accountsetup&task=accountsetup.contactUs');?>">
                            <input type="hidden" name="option1" value="mo_saml_login_send_query"/>
                            <div class="mo_boot_offset-1 mo_boot_mt-2 ">
                                <div class="mo_boot_row">
                                    <div class="mo_boot_col-sm-3">
                                        <?php echo Text::_('COM_JOOMLAIDP_LOGIN_PAGE_EMAIL'); ?><span class="mo_saml_required">*</span> :
                                    </div>
                                    <div class="mo_boot_col-sm-8">
                                        <input type="email" class=" mo_boot_form-control mo_boot_form-text-control mo_saml_proxy_setup" name="mo_saml_query_email" value="<?php echo $admin_email; ?>" placeholder="<?php echo Text::_('COM_JOOMLAIDP_EMAIL_TITLE'); ?>" required />
                                    </div>
                                </div>  
                            </div>
                            <div class="mo_boot_offset-1 mo_boot_mt-2 ">
                                <div class="mo_boot_row">
                                    <div class="mo_boot_col-sm-3">
                                        <?php echo Text::_('COM_JOOMLAIDP_SAML_SUPPORT_NUMBER'); ?> :
                                    </div>
                                    <div class="mo_boot_col-sm-8">
                                        <input type="text" class=" mo_boot_form-control mo_boot_form-text-control mo_saml_proxy_setup" name="mo_saml_query_phone" pattern="[\+]\d{11,14}|[\+]\d{1,4}([\s]{0,1})(\d{0}|\d{9,10})" value="<?php echo $admin_phone; ?>" placeholder="<?php echo Text::_('COM_JOOMLAIDP_SAML_PHONE_PLACEHOLDER'); ?>"/>
                                    </div>
                                </div>  
                            </div>
                            <div class="mo_boot_offset-1 mo_boot_mt-2 ">
                                <div class="mo_boot_row">
                                    <div class="mo_boot_col-sm-3">
                                        <?php echo Text::_('COM_JOOMLAIDP_SAML_SUPPORT_QUERY'); ?><span class="mo_saml_required">*</span> :
                                    </div>
                                    <div class="mo_boot_col-sm-8">
                                        <textarea  name="mo_saml_query" class="mo_boot_form-text-control mo_idp_border mo_idp_valid_desc" cols="52" rows="7" required placeholder="<?php echo Text::_('COM_JOOMLAIDP_SAML_WRITE_QUERY'); ?>"></textarea>
                                    </div>
                                </div>  
                            </div>
                            <div class="mo_boot_row mo_boot_text-center mo_boot_mt-3">
                                <div class="mo_boot_col-sm-12">
                                    <input type="submit" name="send_query" value="<?php echo Text::_('COM_JOOMLAIDP_SAML_SUBMIT_QUERY'); ?>" class="btn btn_cstm" />
                                </div>
                            </div>
                        </form>
                    </div> 
                </div>
            </div>
            
        <?php
    }               
?>
