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
use Joomla\CMS\Plugin\PluginHelper;
HTMLHelper::_('jquery.framework');

$document = Factory::getApplication()->getDocument();
$document->addScript(Uri::base() . 'components/com_joomlaidp/assets/js/bootstrap-select-min.js');
$document->addScript(Uri::base() . 'components/com_joomlaidp/assets/js/utilityjs.js');
$document->addScript(Uri::base() . 'components/com_joomlaidp/assets/js/country.js');
$document->addStyleSheet(Uri::base() . 'components/com_joomlaidp/assets/css/miniorange_boot.css');
$document->addStyleSheet(Uri::base() . 'components/com_joomlaidp/assets/css/miniorange_idp.css');
$document->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css');

require_once JPATH_ADMINISTRATOR . '/components/com_joomlaidp/helpers/MoIdpLogger.php';

$cmsVersion = IDP_Utilities::getJoomlaCmsVersion();

if ($cmsVersion >= 4.0)
{
	HTMLHelper::_('script', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js');
}

$app = Factory::getApplication();
$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
$tab = ($input && $input->get) ? $input->get->getArray() : [];
$idpActiveTab = isset($tab['tab-panel']) ? $tab['tab-panel'] : 'overview';

if ($idpActiveTab === 'logs')
{
	$idpActiveTab = 'errorlog';
}

$testConfig = isset($tab['test-config']) ? true : false;

if (MoSamlIdpUtility::isCurlInstalled() == 0)
{
	?>
	<div id="curl-message-container" class="mb-4">
		<div class="alert alert-danger">
			<div class="d-flex align-items-center mb-2">
				<span class="fs-4 me-2">⚠️</span>
				<h4 class="alert-heading mb-0">
					<?php echo Text::_('COM_JOOMLAIDP_MULTISAMLIDPS_WARNING'); ?>
				</h4>
			</div>
			<p class="mb-0">
				<?php echo Text::sprintf(
					'COM_JOOMLAIDP_CURL_WARNING_MESSAGE',
					Text::_('COM_JOOMLAIDP_CURL_EXTENSION_LINK'),
					Text::_('COM_JOOMLAIDP_CURL_EXTENSION_LABEL')
				); ?>
			</p>
		</div>
	</div>
	<?php
}

if (isset($tab['tab-panel']) && !empty($tab['tab-panel']))
{
	if ($cmsVersion >= 4.0)
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

$legacyNavHtml = '';

if ($cmsVersion <= 4.0)
{
	$legacyNavHtml = '<a id="request_demo" class="mo_boot_p-3 mo_nav-tab mo_nav_tab_' . ($tab == 'request_demo' ? 'active' : '') . '" href="#request-demo" data-toggle="tab" onclick="add_css_tab(\'#request_demo\');" data-toggle="tab">'
		. '<span><i class="fa fa-solid fa-bars"> </i></span> Free Trial'
		. '</a>'
		. '<a id="support_tab" class="mo_boot_p-3 mo_nav-tab mo_nav_tab_' . ($tab == 'support_tab' ? 'active' : '') . '" href="#support-tab" data-toggle="tab" onclick="add_css_tab(\'#support_tab\');" data-toggle="tab">'
		. '<span><i class="fa fa-solid fa-headset"> </i></span> ' . Text::_('COM_MINIORANGE_SUPPORT_BUTTON')
		. '</a>';
}

$isSystemEnabled = PluginHelper::isEnabled('system', 'joomlaidplogin');
$isUserEnabled = PluginHelper::isEnabled('user', 'miniorangejoomlaidp');

if (!$isSystemEnabled || !$isUserEnabled)
{
	?>
	<div id="system-message-container" class="mb-4">
		<div id="help_plugin_warning" class="alert alert-danger">

			<div class="d-flex align-items-center mb-2">
				<span class="fs-4 me-2">⚠️</span>
				<h4 class="alert-heading mb-0">
					<?php echo Text::_('COM_JOOMLAIDP_MULTISAMLIDPS_WARNING'); ?>
				</h4>
			</div>

			<p class="mb-3">
				<?php echo Text::_('COM_JOOMLAIDP_MULTISAMLIDPS_WARNING_HEADER'); ?>
			</p>

			<div class="row g-3">

				<!-- Plugins column -->
				<div class="col-md-6">
					<div class="card border-0 bg-white bg-opacity-50 h-100">
						<div class="card-body mo_tab_border p-3">
							<p class="fw-bold mb-2 text-uppercase small text-muted">
								<?php echo Text::_('COM_JOOMLAIDP_MULTISAMLIDPS_REQUIRED_PLUGINS'); ?>
							</p>
							<ul class="list-unstyled mb-0">
								<li class="d-flex justify-content-between align-items-center mb-2">
									<span><?php echo Text::_('COM_JOOMLAIDP_MULTISAMLIDPS_WARNING_SYSTEM'); ?></span>
									<span class="badge <?php echo !$isSystemEnabled ? 'bg-danger' : 'bg-success'; ?>"><?php echo !$isSystemEnabled ? Text::_('COM_JOOMLAIDP_MULTISAMLIDPS_PLUGIN_DISABLED') : Text::_('COM_JOOMLAIDP_MULTISAMLIDPS_PLUGIN_ENABLED'); ?></span>
								</li>
								<li class="d-flex justify-content-between align-items-center">
									<span><?php echo Text::_('COM_JOOMLAIDP_MULTISAMLIDPS_WARNING_USER'); ?></span>
									<span class="badge <?php echo !$isUserEnabled ? 'bg-danger' : 'bg-success'; ?>"><?php echo !$isUserEnabled ? Text::_('COM_JOOMLAIDP_MULTISAMLIDPS_PLUGIN_DISABLED') : Text::_('COM_JOOMLAIDP_MULTISAMLIDPS_PLUGIN_ENABLED'); ?></span>
								</li>
							</ul>
						</div>
					</div>
				</div>

				<!-- Steps column -->
				<div class="col-md-6">
					<div class="card border-0 bg-white bg-opacity-50 h-100">
						<div class="card-body mo_tab_border p-3">
							<p class="fw-bold mb-2 text-uppercase small text-muted">
								<?php echo Text::_('COM_JOOMLAIDP_MULTISAMLIDPS_WARNING_STEPS'); ?>
							</p>
							<ol class="ps-3 mb-0">
								<li class="mb-1"><?php echo Text::_('COM_JOOMLAIDP_MULTISAMLIDPS_WARNING_STEP1'); ?></li>
								<li class="mb-1"><?php echo Text::_('COM_JOOMLAIDP_MULTISAMLIDPS_WARNING_STEP2'); ?></li>
								<li><?php echo Text::_('COM_JOOMLAIDP_MULTISAMLIDPS_WARNING_STEP3'); ?></li>
							</ol>
						</div>
					</div>
				</div>

			</div>
		</div>
	</div>
	<?php
}
?>
	<div class="mo_boot_container-fluid mo_boot_p-0 mo_boot_m-0">
		<div class="mo_boot_row mo_boot_p-0 mo_boot_m-0">
			<div id="mo_saml_nav_parent" class="mo_boot_col-lg-12 mo_boot_p-0 mo_boot_m-0">
		  
				<a id="overviewtab" class=" mo_boot_px-0 mo_boot_py-5 mo_boot_m-0 mo_nav-tab mo_nav_tab_<?php echo $idpActiveTab == 'overview' ? 'active' : ''; ?>" href="#overview_plugin" onclick="add_css_tab('#overviewtab');"data-toggle="tab">
					<i class=" fa fa-rectangle-list"> </i>
					<span class="mo_idp_tab "> <?php echo Text::_('COM_JOOMLAIDP_TAB1_OVERVIEW'); ?></span>
				</a>

				<a id="sptab" class="mo_boot_p-3  mo_nav-tab mo_nav_tab_<?php echo $idpActiveTab == 'sp' ? 'active' : ''; ?>" href="#service-provider" onclick="add_css_tab('#sptab');" data-toggle="tab">
					<span><i class="fa fa-server"> </i>
					<span class="mo_idp_tab "> <?php echo Text::_('COM_JOOMLAIDP_TAB3_SERVICE_PROVIDER'); ?></span>

				</a>

				<a id="idptab" class="mo_boot_p-3  mo_nav-tab mo_nav_tab_<?php echo $idpActiveTab == 'idp' ? 'active' : ''; ?>" href="#identity-provider" onclick="add_css_tab('#idptab');" data-toggle="tab">
					<span><i class="fa fa-address-card"> </i>
					<span class="mo_idp_tab "> <?php echo Text::_('COM_JOOMLAIDP_TAB4_IDENTITY_PROVIDER'); ?></span>

				</a>

				<a id="advance_mapping_tab" class="mo_boot_p-3  mo_nav-tab mo_nav_tab_<?php echo $idpActiveTab == 'advance_mapping' ? 'active' : ''; ?>" href="#iadvance_mapping" onclick="add_css_tab('#advance_mapping_tab');" data-toggle="tab">
					<span><i class="fa fa-solid fa-map"> </i>
					<span class="mo_idp_tab "> <?php echo Text::_('COM_JOOMLAIDP_MAPPING'); ?></span>

				</a>

				<a id="rolerelay_restiction" class="mo_boot_p-3  mo_nav-tab mo_nav_tab_<?php echo $idpActiveTab == 'role_relay_restriciton' ? 'active' : ''; ?>" href="#role_relay_restriciton_id" onclick="add_css_tab('#rolerelay_restiction');" data-toggle="tab">
					<span><i class="fa fa-solid fa-triangle-exclamation"> </i>
					<span class="mo_idp_tab "> <?php echo Text::_('COM_JOOMLAIDP_RELAY_RESTRICTION_TAB_NAME'); ?></span>

				</a>

				<a id="signin_settings_tab" class="mo_boot_p-3  mo_nav-tab mo_nav_tab_<?php echo $idpActiveTab == 'signin_settings' ? 'active' : ''; ?>" href="#signin_settings_id" onclick="add_css_tab('#signin_settings_tab');" data-toggle="tab">
					<span><i class="fa fa-solid fa-user"> </i>
					<span class="mo_idp_tab "> <?php echo Text::_('COM_JOOMLAIDP_SIGNIN_SETTINGS'); ?></span>

				</a>

				<a id="errorlogtab" class="mo_boot_p-3  mo_nav-tab mo_nav_tab_<?php echo $idpActiveTab == 'errorlog' ? 'active' : ''; ?>" href="#error-logs" onclick="add_css_tab('#errorlogtab');" data-toggle="tab">
					<span><i class="fas fa-shield-alt"></i>
					<span class="mo_idp_tab "> <?php echo Text::_('COM_MINIORANGE_IDP_LOG_TAB'); ?></span>

				</a>
				
				<a id="licensingtab" class="mo_boot_p-3  mo_nav-tab mo_nav_tab_<?php echo $idpActiveTab == 'license' ? 'active' : ''; ?>" href="#licensing-plans" onclick="add_css_tab('#licensingtab');" data-toggle="tab">
					<span><i class="fa-solid fa-circle-up"></i>
					<span class="mo_idp_tab "> <?php echo Text::_('COM_JOOMLAIDP_TAB6_LICENSING_PLANS'); ?></span>

				</a>


			 
				<?php echo $legacyNavHtml; ?>
			</div>
		</div>
	</div>

	
	<div class="mo_container tab-content mo_idp_tab_content" id="myTabContent">

		<div id="overview_plugin" class="tab-pane mo_boot_mt-3 <?php echo $idpActiveTab == 'overview' ? 'active' : '';?>"> 
			<?php
				$className = "JoomlaIdpViewAccountSetup";
				$funcName = "showPluginOverview";
				call_user_func(array($className, $funcName));
			?>
		</div>

		<div id="service-provider" class="tab-pane mo_boot_mt-3 <?php echo $idpActiveTab == 'sp' ? 'active' : '';?>"> 
			<?php
				$className = "JoomlaIdpViewAccountSetup";
				$funcName = "showServiceProviderList";
				call_user_func(array($className, $funcName));
			?>
		</div>

		<div id="identity-provider" class="tab-pane mo_boot_mt-3 <?php echo $idpActiveTab == 'idp' ? 'active' : ''; ?>"> 
			<?php
				$className = "JoomlaIdpViewAccountSetup";
				$funcName = "showIdentityProviderConfigurations";
				call_user_func(array($className, $funcName));
			?>
		</div>

		<div id="iadvance_mapping" class="tab-pane mo_boot_mt-3 <?php echo $idpActiveTab == 'advance_mapping' ? 'active' : ''; ?>"> 
			<?php
				$className = "JoomlaIdpViewAccountSetup";
				$funcName = "showAdvanceMapping";
				call_user_func(array($className, $funcName));
			?>
		</div>

		<div id="role_relay_restriciton_id" class="tab-pane mo_boot_mt-3 <?php echo $idpActiveTab == 'role_relay_restriciton' ? 'active' : ''; ?>"> 
			<?php
				$className = "JoomlaIdpViewAccountSetup";
				$funcName = "showRoleRelayRestriction";
				call_user_func(array($className, $funcName));
			?>
		</div>

		<div id="signin_settings_id" class="tab-pane mo_boot_mt-3 <?php echo $idpActiveTab == 'signin_settings' ? 'active' : ''; ?>"> 
			<?php
				$className = "JoomlaIdpViewAccountSetup";
				$funcName = "showIDPInitiatedLoginDetails";
				call_user_func(array($className, $funcName));
			?>
		</div>

		<div id="licensing-plans" class="tab-pane mo_boot_mt-3 <?php echo $idpActiveTab == 'license' ? 'active' : ''; ?>">
		   
						<?php
							$result      = IDP_Utilities::fetchDatabaseValues('#__miniorange_saml_idp_customer', 'loadAssoc', '*');
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

		<div id="error-logs" class="tab-pane mo_boot_mt-3 <?php echo $idpActiveTab == 'errorlog' ? 'active' : ''; ?>">
			<?php moLoggers(); ?>
		</div>

		<div id="request-demo" class="tab-pane mo_boot_mt-3 <?php echo $idpActiveTab == 'request_demo' ? 'active' : ''; ?>">
		<?php
			$className = "JoomlaIdpViewAccountSetup";
			$funcName = "requestForDemo";
			call_user_func(array($className, $funcName));
		?>
		</div>
		<div id="support-tab" class="tab-pane mo_boot_mt-3 <?php echo $idpActiveTab == 'support_tab' ? 'active' : ''; ?>">
				<?php moSamlIdpSupport(); ?>
		</div>
	</div>
</div>
<?php

function moLoggers(): void
{
	$list = MoIdpLogger::getAllLogs();
	$logRowsHtml = '';

	if (empty($list))
	{
		$logRowsHtml = '<tr><td colspan="4" class="mo_boot_text-center text-muted">'
			. '<i class="fas fa-exclamation-circle"></i> ' . Text::_('COM_MINIORANGE_LOGGER_NO_LOGS')
			. '</td></tr>';
	}
	else
	{
		foreach ($list as $log)
		{
			$logData = json_decode($log->message, true);
			$issue = $logData['issue'] ?? '-';
			$logCode = $logData['code'] ?? '-';
			$logLevelProperty = 'log_level';
			$entryLevel = $log->$logLevelProperty;
			$logLevel = strtolower(htmlspecialchars($entryLevel));
			$icon = '<i class="fas fa-info-circle"></i>';
			$badgeClass = 'badge bg-secondary';

			switch ($logLevel)
			{
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
					$badgeClass = 'badge bg-danger mo_boot_btn-fetch';
					break;
			}

			$logRowsHtml .= '<tr>'
				. '<td class="text-center"><i class="far fa-clock text-secondary"></i> ' . date('j F Y h:ia', strtotime($log->timestamp)) . '</td>'
				. '<td class="text-center"><span class="text-center ' . $badgeClass . '">' . $icon . ' ' . htmlspecialchars($entryLevel) . '</span></td>'
				. '<td class="text-center fw-bold">' . htmlspecialchars($logCode) . '</td>'
				. '<td class="text-break"><span class="' . $badgeClass . '"><i class="fas fa-info-circle"></i></span> '
				. nl2br(htmlspecialchars($issue)) . '</td>'
				. '</tr>';
		}
	}

	?>
	<div class="mo_boot_col-sm-12 mo_boot_m-0 mo_boot_p-0 mo_idp_main_content">
		<div class="mo_boot_row mo_boot_p-2">
			<div class="mo_boot_col-sm-12 mo_boot_px-2">
				<div class="mo_idp_log_header">
					<h3 class="mo_saml_form_heading mo_boot_mb-0">
						<i class="fas fa-clipboard-list"></i> <?php echo Text::_('COM_MINIORANGE_LOGGER_TITLE'); ?>
					</h3>
					<div class="mo_idp_log_actions">
						<form method="post" action="<?php echo Route::_('index.php?option=com_joomlaidp&view=accountsetup&task=accountsetup.resetLogs'); ?>">
							<?php echo HTMLHelper::_('form.token'); ?>
							<button type="submit" name="reset_logs" class="mo_idp_log_action_btn mo_idp_log_action_btn_danger" title="<?php echo Text::_('COM_MINIORANGE_LOGGER_RESET_BUTTON'); ?>">
								<i class="fas fa-trash-alt"></i>
							</button>
						</form>
						<a href="<?php echo Route::_('index.php?option=com_joomlaidp&view=accountsetup&task=accountsetup.refreshLogs'); ?>"
							class="mo_idp_log_action_btn mo_idp_log_action_btn_primary" title="<?php echo Text::_('COM_MINIORANGE_LOGGER_REFRESH_BUTTON'); ?>">
							<i class="fas fa-sync-alt"></i>
						</a>
						<a href="<?php echo Route::_('index.php?option=com_joomlaidp&view=accountsetup&task=accountsetup.downloadLogs'); ?>"
							class="mo_idp_log_action_btn mo_idp_log_action_btn_success" title="<?php echo Text::_('COM_MINIORANGE_LOGGER_DOWNLOAD_BUTTON'); ?>">
							<i class="fas fa-download"></i>
						</a>
					</div>
				</div>
				<div class="mo_idp_mini_section mo_boot_p-4">
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
				<?php echo $logRowsHtml; ?>
				</tbody>
			</table>
				</div>
			</div>
		</div>
	</div>
	<?php
}


function moSamlIdpSupport()
{
	$currentUser = Factory::getUser();
	$result       = IDP_Utilities::fetchDatabaseValues('#__miniorange_saml_idp_customer', 'loadAssoc', '*');
	$adminEmail  = isset($result['email']) ? $result['email'] : '';
	$adminPhone  = isset($result['admin_phone']) ? $result['admin_phone'] : '';

	if ($adminEmail == '' || empty($adminEmail))
	{
		$adminEmail = $currentUser->email;
	}
	?>
			<div class="mo_boot_col-sm-12 mo_boot_m-0 mo_boot_p-0 mo_idp_main_content">
				<div class="mo_boot_row mo_boot_p-2">
							<div class="mo_boot_col-sm-12 mo_boot_px-2">
								<h3 class="mo_boot_offset-1"><?php echo Text::_('COM_JOOMLAIDP_SUPPORT_HEADER'); ?></h3>
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
							<input type="hidden" name="moClientTimezone" id="moClientTimezone" value="" />
							<input type="hidden" name="moClientTimezoneOffset" id="moClientTimezoneOffset" value="" />
							<div class="mo_boot_offset-1 mo_boot_mt-2 ">
								<div class="mo_boot_row">
									<div class="mo_boot_col-sm-3">
										<?php echo Text::_('COM_JOOMLAIDP_LOGIN_PAGE_EMAIL'); ?><span class="mo_saml_required">*</span> :
									</div>
									<div class="mo_boot_col-sm-8">
										<input type="email" class=" mo_boot_form-control mo_boot_form-text-control mo_saml_proxy_setup" name="mo_saml_query_email" value="<?php echo $adminEmail; ?>" placeholder="<?php echo Text::_('COM_JOOMLAIDP_EMAIL_TITLE'); ?>" required />
									</div>
								</div>  
							</div>
							<div class="mo_boot_offset-1 mo_boot_mt-2 ">
								<div class="mo_boot_row">
									<div class="mo_boot_col-sm-3">
										<?php echo Text::_('COM_JOOMLAIDP_SAML_SUPPORT_NUMBER'); ?> :
									</div>
									<div class="mo_boot_col-sm-8">
										<div class="mo_boot_row mo_boot_gutter-0">
											<div class="mo_boot_col-sm-4">
												<select id="mo_saml_country_code" name="mo_saml_country_code" class="mo_boot_form-control mo_boot_form-text-control mo_saml_proxy_setup" title="Country code">
													<option value="">--</option>
												</select>
											</div>
											<div class="mo_boot_col-sm-8">
												<input type="text" class="mo_boot_form-control mo_boot_form-text-control mo_saml_proxy_setup" name="mo_saml_query_phone" id="mo_saml_query_phone" value="<?php echo htmlspecialchars($adminPhone ?? ''); ?>" placeholder="<?php echo Text::_('COM_JOOMLAIDP_SAML_PHONE_PLACEHOLDER'); ?>"/>
											</div>
										</div>
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
