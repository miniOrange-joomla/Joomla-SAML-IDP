<?php
/**
 * @package    miniOrange
 * @subpackage Plugins
 * @license    GNU/GPLv3
 * @copyright  Copyright 2015 miniOrange. All Rights Reserved.
 *
 *
 * This file is part of miniOrange Joomla SAML IDP plugin.
 *
 * miniOrange Joomla SAML IDP plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * miniOrange Joomla IDP plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with miniOrange SAML plugin.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

include_once JPATH_SITE . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_joomlaidp' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'DbHelper.php';
HTMLHelper::_('jquery.framework');

$document = Factory::getApplication()->getDocument();
$document->addScript(Uri::base() . 'components/com_joomlaidp/assets/js/bootstrap-select-min.js');
$document->addScript(Uri::base() . 'components/com_joomlaidp/assets/js/utilityjs.js');
$document->addStyleSheet(Uri::base() . 'components/com_joomlaidp/assets/css/miniorange_boot.css');
$document->addStyleSheet(Uri::base() . 'components/com_joomlaidp/assets/css/miniorange_idp.css');
$document->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');

class JoomlaIdpViewAccountSetup extends HtmlView
{
	public function display($tpl = null)
	{
		$this->lists = $this->get('List');

		if (count($errors = $this->get('Errors')))
		{
			Factory::getApplication()->enqueueMessage(implode('<br />', $errors), 'error');

			return false;
		}

		$this->setLayout('accountsetup');
		$this->addToolBar();
		parent::display($tpl);
	}

	protected function addToolBar()
	{
		ToolbarHelper::title(Text::_('COM_JOOMLAIDP_PLUGIN_TITLE'), 'mo_saml_logo mo_saml_icon');
	}

	public static function showRoleRelayRestriction()
	{
		$attribute = IDP_Utilities::fetchDatabaseValues('#__miniorangesamlidp', 'loadAssoc', '*');

		$licensingPageLink = Uri::base() . 'index.php?option=com_joomlaidp&view=accountsetup&tab-panel=license';
		$spEntityId = '';
		$spName = '';

		if (is_array($attribute))
		{
			$spEntityId = isset($attribute['sp_entityid']) ? $attribute['sp_entityid'] : '';
			$spName = isset($attribute['sp_name']) ? $attribute['sp_name'] : '';
		}

		$spRowHtml = '';

		if ($spName)
		{
			$spRowHtml = '<tr>
				<td class="mo_table_td_style">1</td>
				<td class="mo_table_td_style">' . $spName . '</td>
				<td class="mo_table_td_style">' . $spEntityId . '</td>
				<td class="mo_table_td_style">' . Text::_('COM_JOOMLAIDP_NOT_CONFIGURED') . '</td>
			</tr>';
		}
		?>

		<div class="mo_boot_col-sm-12 mo_boot_m-0 mo_boot_p-0 mo_idp_main_content">
			<div class="mo_boot_row mo_boot_p-2">
				<div class="mo_boot_col-sm-12 mo_boot_px-2">
					<h3>1.<?php echo Text::_('COM_JOOMLAIDP_ROLE_RESTRICTION'); ?>
						<sup>
							<div class="mo_tooltip">
								<img class="crown_img_small mo_idp_ml_px"
									src="<?php echo Uri::base(); ?>/components/com_joomlaidp/assets/images/crown.webp">
								<span class="mo_tooltiptext small mo_boot_btn-fetch">
									<?php echo Text::sprintf('COM_JOOMLAIDP_UPGRADE_NOTE', $licensingPageLink); ?>
								</span>
							</div>
						</sup>
					</h3>
				</div>

				<div class="mo_boot_col-sm-12 mo_boot_p-2 mo_boot_mt-4 mo_idp_mini_section">
					<div class="mo_boot_p-4">
						<div class="alert alert-info mo_boot_col-sm-12 mo_boot_mt-0">
							<span ms-1><?php echo Text::_('COM_JOOMLAIDP_ROLE_RESTRICTION_INFO'); ?>  </span>
						</div>
						<table class='customtemp'>
							<thead>
								<tr>
									<th class="mo_table_td_style" width="1%">Sr.No</th>
									<th class="mo_table_td_style" width="15%"><?php echo Text::_('COM_JOOMLAIDP_MULTISAMLIDPS_IDENTIFIER'); ?></th>
									<th class="mo_table_td_style" width="43%"><?php echo Text::_('COM_JOOMLAIDP_MULTISAMLIDPS_ISSUER') ?></th>
									<th class="mo_table_td_style" width="15%"><?php echo Text::_('COM_JOOMLAIDP_ROLE_RESTRICTION_STATUS') ?></th>
								</tr>
							</thead>
							<?php echo $spRowHtml; ?>
						</table>
					</div>
				</div>
			</div>
			<div class="mo_boot_row mo_boot_p-2">
				<div class="mo_boot_col-sm-12 mo_boot_px-2">
					<h3>2.<?php echo Text::_('COM_JOOMLAIDP_RELAY_RESTRICTION'); ?>
						<sup>
							<div class="mo_tooltip">
								<img class="crown_img_small mo_idp_ml_px"
									src="<?php echo Uri::base(); ?>/components/com_joomlaidp/assets/images/crown.webp">
								<span class="mo_tooltiptext small mo_boot_btn-fetch">
									<?php echo Text::sprintf('COM_JOOMLAIDP_UPGRADE_NOTE', $licensingPageLink); ?>
								</span>
							</div>
						</sup>
					</h3>
				</div>

				<div class="mo_boot_col-sm-12 mo_boot_p-2 mo_boot_mt-4 mo_idp_mini_section">
					<div class="mo_boot_p-4">
						<div class="alert alert-info mo_boot_col-sm-12 mo_boot_mt-0">
							<span ms-1><?php echo Text::_('COM_JOOMLAIDP_RELAY_RESTRICTION_INFO'); ?>  </span>
						</div>
						<table class='customtemp'>
							<thead>
								<tr>
									<th class="mo_table_td_style" width="1%">Sr.No</th>
									<th class="mo_table_td_style" width="15%"><?php echo Text::_('COM_JOOMLAIDP_MULTISAMLIDPS_IDENTIFIER'); ?></th>
									<th class="mo_table_td_style" width="43%"><?php echo Text::_('COM_JOOMLAIDP_MULTISAMLIDPS_ISSUER') ?></th>
									<th class="mo_table_td_style" width="15%"><?php echo Text::_('COM_JOOMLAIDP_RELAY_RESTRICTION_STATUS') ?></th>
								</tr>
							</thead>
							<?php echo $spRowHtml; ?>
						</table>
					</div>
				</div>
			</div>
			<div class="mo_boot_row mo_tab_border mo_boot_p-2 mo_boot_m-0">
				<div class="mo_boot_col-sm-12">
					<div class="mo_boot_row">
						<div class="mo_boot_col-sm-12 text-center">
							<input type="submit" class="btn btn_cstm mb-4 mo_idp_block_cursor" disabled value="<?php echo Text::_('COM_JOOMLAIDP_CLICK_TO_CONFIGURE'); ?>">
						</div>
					</div>
				</div>
			</div>
		</div>

		<?php
	}

	private static function moIdpOverviewIcon($name)
	{
		$icons = array(
			'any'   => '<svg viewBox="0 0 48 48" aria-hidden="true"><circle cx="14" cy="24" r="3.2" fill="#64748B"/><circle cx="24" cy="24" r="3.2" fill="#64748B"/><circle cx="34" cy="24" r="3.2" fill="#64748B"/></svg>',
			'trial' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="#3B82F6" stroke-width="1.7" stroke-linecap="round" d="M6.2 12.2V11a5.8 5.8 0 0 1 11.6 0v1.2"/><rect x="3.4" y="11.6" width="3.5" height="6.2" rx="1.5" fill="#3B82F6"/><rect x="17.1" y="11.6" width="3.5" height="6.2" rx="1.5" fill="#3B82F6"/><path fill="none" stroke="#3B82F6" stroke-width="1.7" stroke-linecap="round" d="M19 17.6v1.1a2.3 2.3 0 0 1-2.3 2.3H14.2"/></svg>',
			'more'  => '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="6" width="18" height="12.5" rx="2" fill="#F59E0B"/><path fill="none" stroke="#fff" stroke-width="1.6" stroke-linejoin="round" d="M4 8.2l8 5.4 8-5.4"/></svg>',
		);

		return isset($icons[$name]) ? $icons[$name] : '';
	}

	public static function showPluginOverview()
	{
		$version = '';
		$manifest = JPATH_COMPONENT_ADMINISTRATOR . '/joomlaidp.xml';

		if (is_readable($manifest))
		{
			$xml = simplexml_load_file($manifest);

			if ($xml && isset($xml->version))
			{
				$version = trim((string) $xml->version);
			}
		}

		$providers = array(
			array('image' => 'tableau.webp', 'label' => 'COM_JOOMLAIDP_OV_SP_TABLEAU', 'href' => 'https://plugins.miniorange.com/login-to-tableau-saml-single-sign-sso-using-joomla'),
			array('image' => 'easy-lms.webp', 'label' => 'COM_JOOMLAIDP_OV_SP_EASY_LMS', 'href' => 'https://plugins.miniorange.com/login-using-joomla-saml-single-sign-on-sso-into-easy-lms'),
			array('image' => 'zoom-uni-sso.webp', 'label' => 'COM_JOOMLAIDP_OV_SP_ZOOM', 'href' => 'https://plugins.miniorange.com/zoom-single-sign-on-sso-using-joomla'),
			array('image' => 'aws-cognito-Joomla-.webp', 'label' => 'COM_JOOMLAIDP_OV_SP_AWS', 'href' => 'https://plugins.miniorange.com/login-to-aws-cognito-saml-single-sign-sso-using-joomla'),
			array('image' => 'saml-sso-salesforce.webp', 'label' => 'COM_JOOMLAIDP_OV_SP_SALESFORCE', 'href' => 'https://plugins.miniorange.com/login-to-salesforce-saml-single-sign-sso-using-joomla'),
			array('image' => 'zoho.png', 'label' => 'COM_JOOMLAIDP_OV_SP_ZOHO', 'href' => 'https://plugins.miniorange.com/login-to-zoho-saml-single-sign-sso-using-joomla'),
			array('image' => 'moodle-new.png', 'label' => 'COM_JOOMLAIDP_OV_SP_MOODLE', 'href' => 'https://plugins.miniorange.com/moodle-saml-single-sign-on-sso-using-joomla-idp'),
			array('icon' => 'any', 'label' => 'COM_JOOMLAIDP_OV_SP_ANY', 'href' => 'https://plugins.miniorange.com/joomla-sso-ldap-mfa-solutions?section=saml-idp'),
		);

		$steps = array(
			array('title' => 'COM_JOOMLAIDP_OV_STEP_1_TITLE', 'text' => 'COM_JOOMLAIDP_OV_STEP_1_TEXT'),
			array('title' => 'COM_JOOMLAIDP_OV_STEP_2_TITLE', 'text' => 'COM_JOOMLAIDP_OV_STEP_2_TEXT'),
			array('title' => 'COM_JOOMLAIDP_OV_STEP_3_TITLE', 'text' => 'COM_JOOMLAIDP_OV_STEP_3_TEXT'),
		);

		$resources = array(
			array(
				'icon'  => 'trial',
				'tone'  => 'blue',
				'title' => 'COM_JOOMLAIDP_OV_TRIAL_TITLE',
				'text'  => 'COM_JOOMLAIDP_OV_TRIAL_TEXT',
				'btn'   => 'COM_JOOMLAIDP_OV_TRIAL_BTN',
				'href'  => Route::_('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=request_demo'),
				'blank' => false,
			),
			array(
				'icon'  => 'more',
				'tone'  => 'amber',
				'title' => 'COM_JOOMLAIDP_OV_MORE_TITLE',
				'text'  => 'COM_JOOMLAIDP_OV_MORE_TEXT',
				'btn'   => 'COM_JOOMLAIDP_OV_MORE_BTN',
				'href'  => 'https://plugins.miniorange.com/joomla',
				'blank' => true,
			),
		);

		$setupUrl = Route::_('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=sp');
		$plansUrl = Route::_('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=license');
		$imageBase = Uri::base() . 'components/com_joomlaidp/assets/images/';

		// phpcs:disable Joomla.ControlStructures.ControlStructuresBrackets
		?>
		<div class="mo_boot_col-sm-12">
			<div class="mo_ov mo_boot_mt-2">
				<div class="mo_ov_header">
					<div class="mo_ov_brand">
						<div class="mo_ov_logo" role="img" aria-label="miniOrange"></div>
						<div class="mo_ov_brand_copy">
							<h2><?php echo Text::_('COM_MINIORANGE_IDP_PLUGIN_TITLE'); ?></h2>
							<p class="mo_ov_lead"><?php echo Text::_('COM_JOOMLAIDP_OV_INTRO'); ?></p>
						</div>
					</div>
					<div class="mo_ov_status">
						<?php if ($version !== '') : ?>
							<span class="mo_ov_version">v<?php echo htmlspecialchars($version, ENT_QUOTES, 'UTF-8'); ?></span>
						<?php endif; ?>
					</div>
				</div>

				<div class="mo_ov_layout">
					<section class="mo_ov_panel">
						<h3 class="mo_ov_panel_title"><?php echo Text::_('COM_JOOMLAIDP_OV_STEPS'); ?></h3>
						<ol class="mo_ov_steps mo_boot_mt-4">
							<?php foreach ($steps as $index => $step) : ?>
								<li class="mo_ov_step">
									<span class="mo_ov_step_num"><?php echo (int) $index + 1; ?></span>
									<div class="mo_ov_step_card">
										<span class="mo_ov_step_title"><?php echo Text::_($step['title']); ?></span>
										<span class="mo_ov_step_text"><?php echo Text::_($step['text']); ?></span>
									</div>
								</li>
							<?php endforeach; ?>
						</ol>
						<a class="mo_ov_start mo_boot_mt-5" href="<?php echo $setupUrl; ?>" onclick="add_css_tab('#sptab'); return false;">
							<?php echo Text::_('COM_JOOMLAIDP_OV_TRY'); ?>
							<span aria-hidden="true">&rarr;</span>
						</a>
						<p class="mo_ov_plans"><?php echo Text::_('COM_JOOMLAIDP_OV_PLANS'); ?>
							<a class="mo_ov_plans_link" href="<?php echo $plansUrl; ?>" onclick="add_css_tab('#licensingtab'); return false;"><?php echo Text::_('COM_JOOMLAIDP_OV_PLANS_LINK'); ?></a>
						</p>
					</section>

					<div class="mo_ov_side">
						<section class="mo_ov_panel">
							<h3 class="mo_ov_panel_title"><?php echo Text::_('COM_JOOMLAIDP_OV_SPS_TITLE'); ?></h3>
							<div class="mo_ov_idps">
								<?php foreach ($providers as $provider) : ?>
									<?php
									$label = Text::_($provider['label']);

									if (!empty($provider['image']))
									{
										$icon = '<img src="' . htmlspecialchars($imageBase . $provider['image'], ENT_QUOTES, 'UTF-8') . '" alt="">';
									}
									else
									{
										$icon = self::moIdpOverviewIcon($provider['icon']);
									}
									?>
									<?php if ($provider['href'] !== '') : ?>
										<a class="mo_ov_idp" href="<?php echo htmlspecialchars($provider['href'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">
											<span class="mo_ov_idp_icon"><?php echo $icon; ?></span>
											<span class="mo_ov_idp_name"><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></span>
										</a>
									<?php else : ?>
										<div class="mo_ov_idp mo_ov_idp_static">
											<span class="mo_ov_idp_icon"><?php echo $icon; ?></span>
											<span class="mo_ov_idp_name"><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></span>
										</div>
									<?php endif; ?>
								<?php endforeach; ?>
							</div>
						</section>

						<section class="mo_ov_panel mo_ov_panel_grow">
							<h3 class="mo_ov_panel_title"><?php echo Text::_('COM_JOOMLAIDP_OV_RESOURCES'); ?></h3>
							<div class="mo_ov_resources">
								<?php foreach ($resources as $resource) : ?>
									<div class="mo_ov_res_row">
										<span class="mo_ov_res_icon mo_ov_res_icon_<?php echo htmlspecialchars($resource['tone'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo self::moIdpOverviewIcon($resource['icon']); ?></span>
										<div class="mo_ov_res_copy">
											<span class="mo_ov_res_title"><?php echo Text::_($resource['title']); ?></span>
											<span class="mo_ov_res_text"><?php echo Text::_($resource['text']); ?></span>
										</div>
										<?php if ($resource['href'] !== '') : ?>
											<a class="mo_ov_res_btn" href="<?php echo $resource['blank'] ? htmlspecialchars($resource['href'], ENT_QUOTES, 'UTF-8') : $resource['href']; ?>"<?php echo $resource['blank'] ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>><?php echo Text::_($resource['btn']); ?></a>
										<?php else : ?>
											<a class="mo_ov_res_btn" href="#" onclick="return false;"><?php echo Text::_($resource['btn']); ?></a>
										<?php endif; ?>
									</div>
								<?php endforeach; ?>
							</div>
						</section>
					</div>
				</div>
			</div>
		</div>
		<?php
		// phpcs:enable Joomla.ControlStructures.ControlStructuresBrackets
	}

	public static function showIDPInitiatedLoginDetails()
	{
		$siteUrl = Uri::root();
		$attribute = IDP_Utilities::fetchDatabaseValues('#__miniorangesamlidp', 'loadAssoc', '*');
		$spName = isset($attribute['sp_name']) ? $attribute['sp_name'] : '';
		$mainMenuLink = Uri::base() . 'index.php?option=com_menus&view=items&menutype=mainmenu';
		$baseUrl = Uri::root();
		$currentAdminLoginUrl = $baseUrl . 'administrator';
		$customAdminLoginUrl = $currentAdminLoginUrl . '/?your_key';
		$licensingPageLink = Uri::base() . 'index.php?option=com_joomlaidp&view=accountsetup&tab-panel=license';
		?>

		<div class="mo_boot_col-sm-12 mo_boot_m-0 mo_boot_p-0 mo_idp_main_content">
			<div class="mo_boot_row mo_boot_p-2">
				<div class="mo_boot_col-sm-12 mo_boot_px-2">
					<h3><?php echo Text::_('COM_JOOMLAIDP_CHECK_FEATTURES'); ?>
						<sup>
							<div class="mo_tooltip">
								<img class="crown_img_small mo_idp_ml_px"
									src="<?php echo Uri::base(); ?>/components/com_joomlaidp/assets/images/crown.webp">
								<span class="mo_tooltiptext small mo_boot_btn-fetch">
									<?php echo Text::sprintf('COM_JOOMLAIDP_UPGRADE_NOTE', $licensingPageLink); ?>
								</span>
							</div>
						</sup>
					</h3>
				</div>

				<div class="mo_boot_col-sm-12 mo_boot_mt-4">
					<div class="mo_boot_row mo_boot_mt-4">
						<div class="vtab mo_boot_col-sm-3">
							<button class="vtab_btn active" onclick="openTab(event, 'vaddon1')" id="defaultTab"><?php echo Text::_('COM_JOOMLAIDP_IDP_INITIATED'); ?></button>
							<button class="vtab_btn" onclick="openTab(event, 'vaddon2')"><?php echo Text::_('COM_JOOMLAIDP_CUSTOMIZED_URL'); ?></button>
							<button class="vtab_btn" onclick="openTab(event, 'vaddon3')"><?php echo Text::_('COM_JOOMLAIDP_GENERATE_CUSTOM_CERT'); ?></button>
						</div>

						<div class="vtab-box mo_boot_col-sm-9 mo_saml_dark_both pb-5">
							<div class="vtab_content mo_idp_disp" id="vaddon1">
								<h4 class="vheader"><?php echo Text::_('COM_JOOMLAIDP_ADD_LINK'); ?></h4>
								<div class="mo_boot_offset-1 mt-4"><?php echo Text::sprintf('COM_JOOMLAIDP_ACCOUNTSETUP_INSTRUCTIONS1', $mainMenuLink); ?></div>
								<div class="mo_boot_offset-1">
									<table class='customtemp'>
										<thead>
											<tr>
												<th class="mo_table_td_style"><?php echo Text::_('COM_JOOMLAIDP_NUM'); ?></th>
												<th class="mo_table_td_style"><?php echo  Text::_('COM_JOOMLAIDP_MULTISAMLIDPS_IDENTIFIER'); ?></th>
												<th class="mo_table_td_style"><?php echo Text::_('COM_JOOMLAIDP_ACCOUNTSETUP_IDPINITIATED_LOGIN_URL') ?></th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td class="mo_table_td_style">1</td>
												<td class="mo_table_td_style"><?php echo  $spName; ?></td>
												<td class="mo_table_td_style"><?php echo Text::_('COM_JOOMLAIDP_AVAILABLE') ?></td>
											</tr>
										</tbody>
									</table>
								</div>
								<div class="mo_boot_offset-1"><?php echo Text::_('COM_JOOMLAIDP_ACCOUNTSETUP_INSTRUCTIONS3'); ?></div>
							</div>
							<div class="vtab_content mo_idp_disp_no" id="vaddon2" >
								<h4 class="vheader"><?php echo Text::_('COM_JOOMLAIDP_CUSTOMIZED_URL'); ?></h4>
								<div class="alert alert-info">
									<?php echo Text::_('COM_JOOMLAIDP_ADMIN_LOGIN_INFO'); ?>
								</div>
								<div class="mo_boot_col-sm-12">
									<div class="mo_boot_row  mo_boot_mt-4">
										<div class="mo_boot_col-sm-4">
											<span class="saml_idp_label_css"><?php echo Text::_('COM_JOOMLAIDP_ADMIN_LOGIN_ENABLE'); ?></span>
										</div>
										<div class="mo_boot_col-sm-8">
											<label class="mo_saml_switch">
												<input type="checkbox" disabled>
												<span class="mo_saml_slider mo_idp_block_cursor"></span>
											</label>
											<span class="small"><strong><?php echo Text::_('COM_MINIORANGE_SAML_NOTE'); ?>: </strong><?php echo Text::_('COM_JOOMLAIDP_ADMIN_LOGIN_ENABLE_NOTE'); ?></span>
										</div>
									</div>
									<div class="mo_boot_row  mo_boot_mt-4">
										<div class="mo_boot_col-sm-4">
											<span class="saml_idp_label_css"><?php echo Text::_('COM_JOOMLAIDP_ADMIN_ACCESS'); ?></span>
										</div>
										<div class="mo_boot_col-sm-8">
											<input class="mo_boot_form-control mo_idp_block_cursor" type="text" placeholder="<?php echo Text::_('COM_JOOMLAIDP_ENTER_KEY'); ?>" disabled="disable"/>
										</div>
									</div>
									<div class="mo_boot_row  mo_boot_mt-4">
										<div class="mo_boot_col-sm-4">
											<span class="saml_idp_label_css"><?php echo Text::_('COM_JOOMLAIDP_CURRENT_ADMIN_URL'); ?></span>
										</div>
										<div class="mo_boot_col-sm-8 text-wrap">
											<div disabled="disable"><?php echo $currentAdminLoginUrl; ?></div>
										</div>
									</div>
									<div class="mo_boot_row  mo_boot_mt-4">
										<div class="mo_boot_col-sm-4">
											<span class="saml_idp_label_css"> <?php echo Text::_('COM_JOOMLAIDP_CUSTOM_ADMIN_URL'); ?></span>
										</div>
										<div class="mo_boot_col-sm-8 text-wrap">
											<div id="custom_admin_url" disabled="disable"><?php echo $customAdminLoginUrl ?></div>
										</div>
									</div>
									<div class="mo_boot_row  mo_boot_mt-4">
										<div class="mo_boot_col-sm-4">
											<span class="saml_idp_label_css"> <?php echo Text::_('COM_JOOMLAIDP_REDIRECT_AFTER_FAILURE'); ?></span>
										</div>
										<div class="mo_boot_col-sm-8">
											<select class="mo_boot_form-control" id="failure_response" readonly>
												<option> <?php echo Text::_('COM_JOOMLAIDP_HOMEPAGE'); ?></option>
												<option disabled> <?php echo Text::_('COM_JOOMLAIDP_CUSTOM_REDIRECT'); ?></option>
												<option disabled> <?php echo Text::_('COM_JOOMLAIDP_CUSTOM_REDIRECT_ONE'); ?></option>
											</select>
										</div>
									</div>
									<div class="mo_boot_row  mo_boot_mt-4">
										<div class="mo_boot_col-sm-4">
											<span class="saml_idp_label_css"><?php echo Text::_('COM_JOOMLAIDP_CUSTOM_REDIRECT_AFTER_FAILURE'); ?></span>
										</div>
										<div class="mo_boot_col-sm-8">
											<input class="mo_boot_form-control mo_idp_block_cursor" disabled type="text"/>
										</div>
									</div>
									<div class="mo_boot_row  mo_boot_mt-4" id="custom_message">
										<div class="mo_boot_col-sm-4">
											<span class="saml_idp_label_css"><?php echo Text::_('COM_JOOMLAIDP_CUSTOM_ERROR'); ?></span>
										</div>
										<div class="mo_boot_col-sm-8">
											<textarea  class="mo_boot_form-control mo_idp_block_cursor" disabled></textarea>
										</div>
									</div>
								</div>
								<div class="mo_boot_col-sm-12  mo_boot_mt-4  mo_boot_text-center">
									<input type="submit" class="btn btn_cstm mo_idp_block_cursor" value="<?php echo Text::_('COM_JOOMLAIDP_SAVE_BTN'); ?>" disabled/>
								</div>
							</div>

							<div class="vtab_content mo_idp_disp_no" id="vaddon3">
								<h4 class="vheader"><?php echo Text::_('COM_JOOMLAIDP_GENERATE_CUSTOM_CERT'); ?></h4>
								<div class="mo_boot_col-sm-12 mo_boot_mt-3 mo_idp_disp_no" id="generate_certificate_form">
									<div class="mo_boot_row mo_boot_mt-4">
										<div class="mo_boot_col-sm-3">
											<?php echo Text::_('COM_JOOMLAIDP_COUNTRY_CODE'); ?><span class="mo_saml_required">*</span> :
										</div>
										<div class="mo_boot_col-sm-8">
											<input class="mo_boot_form-control mo_idp_block_cursor" type="text"  placeholder=" <?php echo Text::_('COM_JOOMLAIDP_ENTER_CODE'); ?>" disabled>
										</div>
									</div>
									<div class="mo_boot_row mt-3">
										<div class="mo_boot_col-sm-3">
											<?php echo Text::_('COM_JOOMLAIDP_STATE'); ?><span class="mo_saml_required">*</span> :
										</div>
										<div class="mo_boot_col-sm-8">
											<input class=" mo_boot_form-control mo_idp_block_cursor" type="text"  placeholder=" <?php echo Text::_('COM_JOOMLAIDP_ENTER_STATE'); ?>" disabled />
										</div>
									</div>
									<div class="mo_boot_row mo_boot_mt-3">
										<div class="mo_boot_col-sm-3">
											<?php echo Text::_('COM_JOOMLAIDP_COMPANY'); ?><span class="mo_saml_required">*</span> :
										</div>
										<div class="mo_boot_col-sm-8">
											<input  class=" mo_boot_form-control mo_idp_block_cursor" type="text"  placeholder=" <?php echo Text::_('COM_JOOMLAIDP_ENTER_COMPANY'); ?>" disabled />
										</div>
									</div>
									<div class="mo_boot_row mo_boot_mt-3">
										<div class="mo_boot_col-sm-3">
											<?php echo Text::_('COM_JOOMLAIDP_UNIT'); ?><span class="mo_saml_required">*</span> :
										</div>
										<div class="mo_boot_col-sm-8">
											<input  class=" mo_boot_form-control mo_idp_block_cursor" type="text" placeholder=" <?php echo Text::_('COM_JOOMLAIDP_UNIT_INFO'); ?>" disabled />
										</div>
									</div>
									<div class="mo_boot_row mo_boot_mt-3">
										<div class="mo_boot_col-sm-3">
											<?php echo Text::_('COM_JOOMLAIDP_COMMON'); ?><span class="mo_saml_required">*</span> :
										</div>
										<div class="mo_boot_col-sm-8">
											<input  class="mo_boot_form-control mo_idp_block_cursor" type="text" placeholder=" <?php echo Text::_('COM_JOOMLAIDP_COMMON_NAME'); ?>" disabled />
										</div>
									</div>
									<div class="mo_boot_row mo_boot_mt-3">
										<div class="mo_boot_col-sm-3">
											<?php echo Text::_('COM_JOOMLAIDP_DIGEST'); ?><span class="mo_saml_required">*</span> :
										</div>
										<div class="mo_boot_col-sm-8">
											<select class="mo_boot_form-control" readonly>
												<option>SHA512</option>
												<option disabled>SHA384</option>
												<option disabled>SHA256</option>
												<option disabled>SHA1</option>
											</select>
										</div>
									</div>
									<div class="mo_boot_row mt-3">
										<div class="mo_boot_col-sm-3">
											<?php echo Text::_('COM_JOOMLAIDP_BITS'); ?><span class="mo_saml_required">*</span> :
										</div>
										<div class="mo_boot_col-sm-8">
											<select class=" mo_boot_form-control" readonly>  <?php echo Text::_('COM_MINIORANGE_SAML_VALID_DAYS'); ?>
												<option>2048 bits</option>
												<option disabled>1024 bits</option>
											</select>
										</div>
									</div>
									<div class="mo_boot_row mo_boot_mt-3">
										<div class="mo_boot_col-sm-3">
											<?php echo Text::_('COM_JOOMLAIDP_VALID_DATES'); ?><span class="mo_saml_required">*</span> :
										</div>
										<div class="mo_boot_col-sm-8">
											<select class=" mo_boot_form-control" readonly>
												<option>365 <?php echo Text::_('COM_JOOMLAIDP_DAYS'); ?></option>
												<option disabled>180 <?php echo Text::_('COM_JOOMLAIDP_DAYS'); ?></option>
												<option disabled>90 <?php echo Text::_('COM_JOOMLAIDP_DAYS'); ?></option>
												<option disabled>45 <?php echo Text::_('COM_JOOMLAIDP_DAYS'); ?></option>
												<option disabled>30 <?php echo Text::_('COM_JOOMLAIDP_DAYS'); ?></option>
												<option disabled>15 <?php echo Text::_('COM_JOOMLAIDP_DAYS'); ?></option>
												<option disabled>7 <?php echo Text::_('COM_JOOMLAIDP_DAYS'); ?></option>
											</select>
										</div>
									</div>
									<div class="mo_boot_row mo_boot_text-center mo_boot_mt-3">
										<div class="mo_boot_col-sm-12">
											<input type="submit" value=" <?php echo Text::_('COM_JOOMLAIDP_SAML_SELF_SIGNED'); ?>" disabled class="btn btn_cstm mo_idp_block_cursor"; />
											<input type="button" class="btn btn_cstm" value=" <?php echo Text::_('COM_JOOMLAIDP_BACK_BTN'); ?>" onclick = "hide_gen_cert_form()"/>
										</div>
									</div>
								</div>
								<div class="mo_boot_col-sm-12 mo_boot_mt-3" id="mo_gen_cert" >
									<div class="mo_boot_row">
										<div class="mo_boot_col-sm-12 alert alert-info" >
											<?php echo Text::_('COM_JOOMLAIDP_SAML_CUSTOM_CRT_NOTE'); ?>
										</div>
										<div class="mo_boot_col-sm-12 mo_boot_mt-3" id="customCertificateData"><br>
											<div class="mo_boot_row custom_certificate_table"  >
												<div class="mo_boot_col-sm-3">
														<?php echo Text::_('COM_JOOMLAIDP_SAML_PUBLIC_CRT'); ?>
														<span class="mo_saml_required">*</span>
												</div>
												<div class="mo_boot_col-sm-8">
													<textarea disabled="disabled" rows="5" cols="100" class="mo_saml_table_textbox w-100 mb-5 mo_idp_block_cursor"></textarea>
												</div>
											</div>
											<div class="mo_boot_row custom_certificate_table"  >
												<div class="mo_boot_col-sm-3">
														<?php echo Text::_('COM_JOOMLAIDP_SAML_PRIVATE_CRT'); ?>
														<span class="mo_saml_required">*</span>
												</div>
												<div class="mo_boot_col-sm-8">
													<textarea disabled="disabled" rows="5" cols="100" class="mo_saml_table_textbox w-100 mo_idp_block_cursor"></textarea>
												</div>
											</div>
											<div class="mo_boot_row mo_boot_mt-3 custom_certificate_table"  id="save_config_element">
												<div class="mo_boot_col-sm-12 mo_boot_text-center mo_boot_p-1">
													<input disabled="disabled" type="submit" name="submit" value=" <?php echo Text::_('COM_JOOMLAIDP_SAML_UPLOAD'); ?>" class="btn btn_cstm"/> &nbsp;&nbsp;
													<input type="button" name="submit" value=" <?php echo Text::_('COM_JOOMLAIDP_SAML_GENERATE'); ?>" class="btn btn_cstm" onclick="show_gen_cert_form()"/>&nbsp;&nbsp;
													<input disabled type="submit" name="submit" value=" <?php echo Text::_('COM_JOOMLAIDP_SAML_RM'); ?>" class="btn btn_cstm"/>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	public static function requestForDemo()
	{
		$currentUser = Factory::getUser();
		$customerResult = IDP_Utilities::fetchDatabaseValues('#__miniorange_saml_idp_customer', 'loadAssoc', array('*'));
		$adminEmail = isset($customerResult['email']) ? $customerResult['email'] : '';

		if ($adminEmail == '')
		{
			$adminEmail = $currentUser->email;
		}
		?>
			<div class="mo_boot_col-sm-12 mo_boot_m-0 mo_boot_p-0 mo_idp_main_content">
				<div class="mo_boot_row mo_boot_p-2">

							<div class="mo_boot_col-sm-12 mo_boot_px-2">
								<h3 class="mo_boot_offset-1"><?php echo Text::_('COM_JOOMLAIDP_DEMO_HEADER'); ?></h3>
							</div>
					<div class="mo_boot_col-sm-12">
						<div class=" mo_boot_offset-1">
							<div class="mo_boot_row">
								<div class="mo_boot_col-sm-11 alert alert-info">
									<span><?php echo Text::_('COM_JOOMLAIDP_DEMO_INFO'); ?> </span>
								</div>
							</div>
						</div>
					</div>
					<div class="mo_boot_mt-4 mo_boot_col-sm-12">
						<form  name="demo_request" method="post" action="<?php echo Route::_('index.php?option=com_joomlaidp&view=accountsetup&task=accountsetup.requestForDemoPlan');?>">
							<div>
								<div class="mo_boot_offset-1 mo_boot_mt-4 ">
									<div class="mo_boot_row">
										<div class="mo_boot_col-lg-3">
											<?php echo Text::_('COM_JOOMLAIDP_LOGIN_PAGE_EMAIL'); ?><span class="mo_saml_required">*</span>:
										</div>
										<div class="mo_boot_col-lg-8">
											<input type="email" class="mo_form-control mo_idp_border" name="email" value="<?php echo $adminEmail; ?>" placeholder="person@example.com" required />
										</div>
									</div>
								</div>
								<div class="mo_boot_offset-1 mt-4">
									<div class="mo_boot_row">
										<div class="mo_boot_col-lg-3">
											<?php echo Text::_('COM_JOOMLAIDP_REQUEST_FOR'); ?>:
										</div>
										<div class="mo_boot_col-lg-8 mo_boot_d-flex">
											<label class="mo_boot_mr-4"><input type="radio" name="demo"  value="7 days trial" CHECKED><?php echo Text::_('COM_JOOMLAIDP_TRIAL'); ?></label>
											<label><input type="radio" name="demo"  value="demo" ><?php echo Text::_('COM_JOOMLAIDP_DEMO'); ?></label>
										</div>
									</div>
								</div>
								<div class="mo_boot_offset-1 mo_boot_mt-4 ">
									<div class="mo_boot_row">
										<div class="mo_boot_col-lg-3">
											<?php echo Text::_('COM_JOOMLAIDP_REQUESTED_PLUGIN'); ?><span class="mo_saml_required">*</span>:
										</div>
										<div class="mo_boot_col-lg-8">
											<select required class="mo_form-control mo_idp_border" name="plan">
												<option disabled selected class="mo_idp_select_demo">----------------------- <?php echo Text::_('COM_JOOMLAIDP_SELECT'); ?> -----------------------</option>
												<option value="Joomla SAML IDP Premium Plugin">Joomla SAML IDP Premium Plugin</option>
												<option value="Not Sure"><?php echo Text::_('COM_JOOMLAIDP_NOT_SURE'); ?></option>
											</select>
										</div>
									</div>
								</div>
								<div class="mo_boot_offset-1 mo_boot_mt-4 ">
									<div class="mo_boot_row">
										<div class="mo_boot_col-lg-3">
											<?php echo Text::_('COM_JOOMLAIDP_DESCRIPTION'); ?><span class="mo_saml_required">*</span>:
										</div>
										<div class="mo_boot_col-lg-8">
											<textarea  name="description" class="mo_boot_form-text-control mo_idp_border mo_idp_valid_desc" cols="52" rows="7" onkeyup="mo_saml_valid(this)"
											onblur="mo_saml_valid(this)" onkeypress="mo_saml_valid(this)" required placeholder="<?php echo Text::_('COM_JOOMLAIDP_TRIAL_ASSISTANCE'); ?>"></textarea>
										</div>
									</div>
								</div>
							</div>
							<div class="mo_boot_row mo_boot_text-center">
								<div class="mo_boot_col-sm-12">
									<input type="hidden" name="option1" value="mo_saml_login_send_query"/><br>
									<input  type="submit" name="submit" value="<?php echo Text::_('COM_JOOMLAIDP_SUBMIT_BTN'); ?>" class="btn btn_cstm"/>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		<?php
	}

	public static function showAdvanceMapping()
	{
		$attribute = IDP_Utilities::fetchDatabaseValues('#__miniorangesamlidp', 'loadAssoc', '*');
		$licensingPageLink = Uri::base() . 'index.php?option=com_joomlaidp&view=accountsetup&tab-panel=license';

		if (isset($attribute['sp_entityid']) && !empty($attribute['sp_entityid']))
		{
			$nameIdAttribute = $attribute['nameid_attribute'];
			$disabled = ' ';
		}
		else
		{
			$nameIdAttribute = '';
			$disabled = 'disabled';
		}

		$attributeMappingHtml = '';

		for ($icnt = 1; $icnt <= 5; $icnt++)
		{
			$attributeMappingHtml .= '<div class="mo_boot_col-sm-6">
					<div class="mo_boot_row">
						<div class="mo_boot_col-sm-4">
							<b>' . Text::_('COM_JOOMLAIDP_ATTRIBUTE') . ' ' . $icnt . ' ' . Text::_('COM_JOOMLAIDP_NAME') . ':</b>
						</div>
						<div class="mo_boot_col-sm-8">
							<input type="text" class="mo_saml_idp_textfield mo_form-control mo_idp_block_cursor" disabled="disabled" placeholder="' . Text::_('COM_JOOMLAIDP_ATTRIBUTE_PLACEHOLDER') . '"/>
						</div>
					</div>
			</div>
			<div class="mo_boot_col-sm-6">
				<div class="mo_boot_row">
					<div class="mo_boot_col-sm-4">
						<b>' . Text::_('COM_JOOMLAIDP_ATTRIBUTE') . ' ' . $icnt . ' ' . Text::_('COM_JOOMLAIDP_VALUE') . ':</b>
					</div>
					<div class="mo_boot_col-sm-8">
						<select class="mo_saml_idp_textfield mo_form-control" readonly>
							<option value="">' . Text::_('COM_JOOMLAIDP_SELECT_ATTR_VAL') . '</option>
							<option value="emailAddress" disabled>' . Text::_('COM_JOOMLAIDP_EMAIL_ADDRESS') . '</option>
							<option value="username" disabled>' . Text::_('COM_JOOMLAIDP_USERNAME') . '</option>
							<option value="name" disabled>' . Text::_('COM_JOOMLAIDP_NAME') . '</option>
							<option value="firstname" disabled>' . Text::_('COM_JOOMLAIDP_FNAME') . '</option>
							<option value="lastname" disabled>' . Text::_('COM_JOOMLAIDP_LNAME') . '</option>
							<option value="groups" disabled>' . Text::_('COM_JOOMLAIDP_GROUPS') . '</option>
						</select>
					</div>
				</div><br>
			</div>';
		}
		?>
		<div class="mo_boot_col-sm-12 mo_boot_m-0 mo_boot_p-0 mo_idp_main_content">
			<div class="mo_boot_row mo_boot_p-2">
				<div class="mo_boot_col-sm-12 mo_boot_px-2">
					<h3>1.<?php echo Text::_('COM_JOOMLAIDP_ACCOUNTSETUP_CUSTOM_MAPPING'); ?></h3>
				</div>
				<div class="mo_boot_col-sm-12 mo_boot_p-2 mo_boot_mt-4 mo_idp_mini_section">
					<form action="<?php echo Route::_('index.php?option=com_joomlaidp&view=accountsetup&task=accountsetup.updateNameId'); ?>" name="updateNameId" method="post"enctype="multipart/form-data">
						<div class="mo_boot_col-sm-12 mo_boot_mt-4">
							 <div class="mo_boot_row mo_boot_mt-4">
								<div class="mo_boot_col-sm-2  mo_boot_ml-5">
									<span class="mo_boot_sm-4"><?php echo Text::_('COM_JOOMLAIDP_ATTRIBUTE_NAMEID'); ?> :</span>
								</div>
								<div class="mo_boot_col-sm-8">
									<select id="nameid_attribute" name="nameid_attribute" class="mo_form-control mo_idp_form_control">
										<option value="emailAddress" <?php echo $nameIdAttribute == 'emailAddress' ? 'selected = "selected"' : ''; ?>>emailAddress</option>
										<option value="username" <?php echo $nameIdAttribute == 'username' ? 'selected = "selected"' : ''; ?>>username</option>
									</select>
									<span class="small"><strong><?php echo Text::_('COM_MINIORANGE_SAML_NOTE'); ?>: </strong><?php echo Text::_('COM_JOOMLAIDP_ATTRIBUTE_MAPPING_INFO'); ?></span>
								</div>
							</div>
							<div class="mo_boot_row mo_boot_mt-4">
								<div class="mo_boot_col-sm-12 mo_boot_text-center">
									 <input type="submit" class="btn btn_cstm mb-4" value="<?php echo Text::_('COM_JOOMLAIDP_SAVE_BTN'); ?>" <?php echo $disabled ?>/>
								</div>
							</div>

						</div>
					</form>
				</div>
			</div>

			<div class="mo_boot_row mo_boot_p-2">
				<div class="mo_boot_col-sm-12 mo_boot_px-2">
					<div class="mo_boot_col-sm-12 mo_boot_px-2">
						<h3>2.<?php echo Text::_('COM_JOOMLAIDP_ACCOUNTSETUP_CUSTOM_MAPPING_2'); ?></h3>
					</div>


					<div class="mo_boot_col-sm-12 mo_boot_p-2 mo_boot_mt-4 mo_idp_mini_section">
						<div class="mo_boot_col-sm-12 mo_boot_m-3">
							<div class="mo_boot_d-flex mo_boot_align-items-center mo_boot_justify-content-between">
								<h3 class="mo_boot_mb-0 mo_boot_col-sm-7">a)
								<?php echo Text::_('COM_JOOMLAIDP_SAML_BASIC_ATTRIBUTE_MAPPING'); ?>
								<sup>
									<div class="mo_tooltip">
										<img class="crown_img_small mo_idp_ml_px"
											src="<?php echo Uri::base(); ?>/components/com_joomlaidp/assets/images/crown.webp">
										<span class="mo_tooltiptext small mo_boot_btn-fetch">
											<?php echo Text::sprintf('COM_JOOMLAIDP_UPGRADE_NOTE', $licensingPageLink); ?>
										</span>
									</div>
								</sup>
								</h3>
							</div>
							<div class="alert alert-info">
								<?php echo Text::_('COM_JOOMLAIDP_SAML_ATTRIBUTE_MAPPING_NOTE'); ?>
							</div>

							<div class="mo_boot_col-sm-12 mo_boot_mt-4">
								<div class="mo_boot_row">
									<?php echo $attributeMappingHtml; ?>
								</div>
							</div>
						</div>
					</div>

					<div class="mo_boot_col-sm-12 mo_boot_p-2 mo_boot_mt-4 mo_idp_mini_section">
						<div class="mo_boot_col-sm-12 mo_boot_m-3">
							<div class="mo_boot_d-flex mo_boot_align-items-center mo_boot_justify-content-between"
								onclick="toggleAdditionalAttributes()" style="cursor: pointer;">
								<h3 class="mo_boot_mb-0 mo_boot_col-sm-7">
									b) <?php echo Text::_('COM_JOOMLAIDP_MAP_ADDITIONAL_USER_ATTRIBUTES'); ?>
								</h3>
								<button class="mo_boot_col-sm-1 mo_boot_offset-sm-4 mo_idp_toggle_btn_black"
									id="additional-attributes-toggle">+</button>
							</div>

							<div id="additional-attributes-content" style="display: none;">
								<div class="mo_boot_row mo_boot_m-2 mo_idp_highlight_background_url_note mo_boot_mt-4">
									<div class="mo_boot_col-sm-12 mo_boot_m-4">
										<div class="mo_boot_d-flex mo_boot_align-items-center mo_boot_justify-content-between">
											<h4 class="mo_boot_mb-0 mo_boot_col-sm-6 ">
												<?php echo Text::_('COM_JOOMLAIDP_ADDITIONAL_USER_ATTRIBUTES'); ?>
												<sup>
												<div class="mo_tooltip">
													<img class="crown_img_small mo_idp_ml_px"
														src="<?php echo Uri::base(); ?>/components/com_joomlaidp/assets/images/crown.webp">
													<span class="mo_tooltiptext small mo_boot_btn-fetch">
														<?php echo Text::sprintf('COM_JOOMLAIDP_UPGRADE_NOTE', $licensingPageLink); ?>
													</span>
												</div>
											</sup>
											</h4>
											<div class="mo_boot_col-sm-2 mo_boot_offset-sm-4 mo_boot_p-0">
												<button class="mo_boot_btn btn_cstm mo_idp_block_cursor"
													disabled><?php echo Text::_('COM_JOOMLAIDP_ADD_BTN'); ?></button>
											</div>
										</div>
										<div class="alert alert-info mo_boot_col-sm-11">
											<?php echo Text::_('COM_JOOMLAIDP_SAML_ATTRIBUTE_PROFILE_MAPPING_NOTE'); ?>
										</div>

										<div class="mo_boot_col-sm-12 mo_boot_mt-3">
											<div class="mo_boot_row mo_boot_mt-2">
												<div class="mo_boot_col-sm-5">
													<input class="mo_saml_idp_textfield mo_form-control mo_idp_block_cursor"
														type="text"
														placeholder="<?php echo Text::_('COM_JOOMLAIDP_SAML_PROFILE_ATTRIBUTE_HEADER'); ?>"
														disabled />
												</div>
												<div class="mo_boot_col-sm-5 mo_boot_col-sm-5 mo_boot_offset-sm-1">
													<input class="mo_saml_idp_textfield mo_form-control mo_idp_block_cursor"
														type="text"
														placeholder="<?php echo Text::_('COM_JOOMLAIDP_SAML_IDP_PROFILE_ATTRIBUTE'); ?>"
														disabled />
												</div>
												<i class="fa fa-trash-o mo_boot_btn mo_boot_btn-sm mo_idp_block_cursor"
													style="color: #D90F0F; cursor: pointer; background: transparent; border: none; padding: 8px 12px; font-size: 20px;"></i>
											</div>

											<div class="mo_boot_row mo_boot_mt-2">
												<div class="mo_boot_col-sm-5">
													<input class="mo_saml_idp_textfield mo_form-control mo_idp_block_cursor"
														type="text"
														placeholder="<?php echo Text::_('COM_JOOMLAIDP_SAML_PROFILE_ATTRIBUTE_HEADER'); ?>"
														disabled />
												</div>
												<div class="mo_boot_col-sm-5 mo_boot_offset-sm-1">
													<input class="mo_saml_idp_textfield mo_form-control mo_idp_block_cursor"
														type="text"
														placeholder="<?php echo Text::_('COM_JOOMLAIDP_SAML_IDP_PROFILE_ATTRIBUTE'); ?>"
														disabled />
												</div>
												<i class="fa fa-trash-o mo_boot_btn mo_boot_btn-sm mo_idp_block_cursor"
													style="color: #D90F0F; cursor: pointer; background: transparent; border: none; padding: 8px 12px; font-size: 20px;"></i>
											</div>
										</div>
									</div>
								</div>
								<div class="mo_boot_row mo_boot_m-2 mo_idp_highlight_background_url_note mo_boot_mt-4">
									<div class="mo_boot_col-sm-12 mo_boot_m-4">
										<div class="mo_boot_d-flex mo_boot_align-items-center mo_boot_justify-content-between">
											<h4 class="mo_boot_mb-0 mo_boot_col-sm-6 ">
												<?php echo Text::_('COM_JOOMLAIDP_ADDITIONAL_USER_FIELD_ATTRIBUTES'); ?>
											<sup>
												<div class="mo_tooltip">
													<img class="crown_img_small mo_idp_ml_px"
														src="<?php echo Uri::base(); ?>/components/com_joomlaidp/assets/images/crown.webp">
													<span class="mo_tooltiptext small mo_boot_btn-fetch">
														<?php echo Text::sprintf('COM_JOOMLAIDP_UPGRADE_NOTE', $licensingPageLink); ?>
													</span>
												</div>
											</sup>
											</h4>

											<div class="mo_boot_col-sm-2 mo_boot_offset-sm-4 mo_boot_p-0">
												<button class="mo_boot_btn btn_cstm mo_idp_block_cursor"
													disabled><?php echo Text::_('COM_JOOMLAIDP_ADD_BTN'); ?></button>
											</div>
										</div>
										<div class="alert alert-info mo_boot_col-sm-11">
											<?php echo Text::_('COM_JOOMLAIDP_SAML_ATTRIBUTE_FILED_MAPPING_NOTE'); ?>
										</div>

										<div class="mo_boot_col-sm-12 mo_boot_mt-3">
											<div class="mo_boot_row mo_boot_mt-2">
												<div class="mo_boot_col-sm-5">
													<input class="mo_saml_idp_textfield mo_form-control mo_idp_block_cursor"
														type="text"
														placeholder="<?php echo Text::_('COM_JOOMLAIDP_SAML_FIELD_ATTRIBUTE_HEADER'); ?>"
														disabled />
												</div>
												<div class="mo_boot_col-sm-5 mo_boot_offset-sm-1">
													<input class="mo_saml_idp_textfield mo_form-control mo_idp_block_cursor"
														type="text"
														placeholder="<?php echo Text::_('COM_JOOMLAIDP_SAML_IDP_FIELD_ATTRIBUTE'); ?>"
														disabled />
												</div>
												<i class="fa fa-trash-o mo_boot_btn mo_boot_btn-sm mo_idp_block_cursor"
													style="color: #D90F0F; cursor: pointer; background: transparent; border: none; padding: 8px 12px; font-size: 20px;"></i>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="mo_boot_row m-0 p-0 mo_boot_mt-3">
				<div class="mo_boot_col-sm-12 m-0 p-0 mo_boot_text-center">
					<input type="submit" class="mo_boot_btn btn_cstm mo_idp_block_cursor"
						value="<?php echo Text::_('COM_JOOMLAIDP_SAVE_MAPPING'); ?>" disabled />
				</div>
			</div>

			<div class="mo_boot_row mo_boot_p-2 mo_boot_mt-4">
				<div class="mo_boot_d-flex mo_boot_align-items-center mo_boot_justify-content-between">
					<h3 class="mo_boot_mb-0 mo_boot_col-sm-12">
					<?php echo Text::_('COM_JOOMLAIDP_CONFIGURE_GROUP_MAPPING'); ?>
					<sup>
						<div class="mo_tooltip">
							<img class="crown_img_small mo_idp_ml_px"
								src="<?php echo Uri::base(); ?>/components/com_joomlaidp/assets/images/crown.webp">
							<span class="mo_tooltiptext small mo_boot_btn-fetch">
								<?php echo Text::sprintf('COM_JOOMLAIDP_UPGRADE_NOTE', $licensingPageLink); ?>
							</span>
						</div>
					</sup>
					</h3>
				</div>
				<div class="mo_boot_col-sm-12 mo_boot_p-2 mo_boot_mt-4 mo_idp_mini_section">
					<div class="mo_boot_col-sm-12 mo_boot_m-3">

						<div class="mo_boot_d-flex mo_boot_align-items-center mo_boot_justify-content-center">
							<h3 class="mo_boot_mb-0 mo_boot_col-sm-6">a)
								<?php echo Text::_('COM_JOOMLAIDP_COMMOA_SEPERATED'); ?>
							</h3>

							<label class="mo_saml_toggle-switch-rect mo_boot_ml-3">
								<input type="checkbox" id="enable_group_mapping" disabled>
								<span class="mo_saml_slider mo_idp_block_cursor"></span>
							</label>
						</div>
						<div class="mo_boot_col-sm-12 mo_boot_mt-3">
							<p class="mo_boot_mb-0"><strong><?php echo Text::_('COM_MINIORANGE_SAML_NOTE'); ?></strong>
								<?php echo Text::_('COM_JOOMLAIDP_SAML_GROUP_MAPPING_CHECKBOX_NOTE'); ?></p>
						</div>
					</div>
				</div>
			</div>
		</div>

		<?php
	}

	public static function showIdentityProviderConfigurations()
	{
		$siteUrl = Uri::root();
		$idpEntityId = $siteUrl . 'plugins/user/miniorangejoomlaidp/';

		$idpId = IDP_Utilities::fetchDatabaseValues('#__miniorange_saml_idp_customer', 'loadResult', 'idp_entity_id');

		if (!empty($idpId) && ($idpEntityId != $idpId))
		{
			$idpEntityId = $idpId;
		}
		?>

		<div class="mo_boot_col-sm-12 mo_boot_m-0 mo_boot_p-0 mo_idp_main_content">
			<div class="mo_boot_row mo_boot_p-2">
				<div class="mo_boot_col-sm-12 mo_boot_px-2">
				   <h3>1.<?php echo Text::_('COM_JOOMLAIDP_SAML_UPDATE_ENTITY'); ?></h3>

					<div class="mo_boot_col-sm-12 mo_boot_p-2 mo_boot_mt-4 mo_idp_mini_section">
						<form action="<?php echo Route::_('index.php?option=com_joomlaidp&view=accountsetup&task=accountsetup.updateIdpEntityId'); ?>" method="post" name="updateissueer" id="identity_provider_update_form">
							<div class="mo_boot_row mo_boot_mt-4">
								<div class="mo_boot_ml-5">
									<span class="mo_boot_col-sm-4"><?php echo Text::_('COM_JOOMLAIDP_ACCOUNTSETUP_ISSUER'); ?> :</span>
								</div>
								<div class="mo_boot_col-sm-8">
									<input class=" mo_form-control mo_saml_proxy_setup" type="text" name="mo_saml_idp_entity_id" value="<?php echo $idpEntityId; ?>" placeholder="<?php echo Text::_('COM_JOOMLAIDP_ISSUER_OF_IDP'); ?>" required />
									<span class="small"><strong><?php echo Text::_('COM_MINIORANGE_SAML_NOTE'); ?>: </strong><?php echo Text::_('COM_JOOMLAIDP_ISSUER_NOTE'); ?></span>
								</div>
							</div>
							<div class="mo_boot_row mo_boot_mt-4">
								<div class="mo_boot_col-sm-12 mo_boot_text-center">
									<input type="submit" class="btn btn_cstm mb-4" value="<?php echo Text::_('COM_JOOMLAIDP_UPDATE_BTN'); ?>"/>
								</div>
							</div>
						</form>
					</div>
				</div>
				<div class="mo_boot_col-sm-12 mo_boot_px-2 mo_boot_mt-4 ">
				<h3>2.<?php echo Text::_('COM_MINIORANGE_IDP_SHARE_METADATA'); ?></h3>
				<ul class="switch_tab_idp mo_boot_text-center mo_boot_p-0 mo_boot_mt-4 mo_boot_m-0">
					<li class="mo_idp_tab_current" id="metadata-url-tab-btn">
						<a href="#" class="mo_idp_bs_btn" onclick="showMetadataTab('metadata-url', event)">
							<i class="fa fa-link"></i>&nbsp;<?php echo Text::_('COM_JOOMLAIDP_METADATA_URL'); ?>

						</a>
					</li>
					<li class="" id="download-xml-tab-btn">
						<a href="#" class="mo_idp_bs_btn" onclick="showMetadataTab('download-xml', event)">
							<i class="fa fa-download"></i>&nbsp;<?php echo Text::_('COM_JOOMLAIDP_DOWNLOAD_METADATA'); ?>

						</a>
					</li>
					<li class="" id="manual-info-tab-btn">
						<a href="#" class="mo_idp_bs_btn" onclick="showMetadataTab('manual-info', event)">
							<i class="fa fa-hand-o-up"></i>&nbsp;<?php echo Text::_('COM_MINIORANGE_IDP_MANUAL_INFO'); ?>
						</a>
					</li>
				</ul>
				<div class="mo_boot_col-sm-12 mo_boot_p-2 mo_boot_mt-4 mo_idp_mini_section">
					<div id="metadata-url-tab" class="metadata-tab-content mo_boot_display_block">
						<div class="mo_boot_row">
							<div class="mo_boot_col-sm-12 mo_boot_text-center">
								<div class="mo_boot_row mo_boot_m-4">
									<p><?php echo Text::_('COM_MINIORANGE_IDP_SHARE_METADATA_TEXT'); ?></p>

								</div>
								<div class="mo_boot_row mo_boot_m-4 mo_idp_metadata_url_row">
									<div class="mo_boot_col-11">
										<span id="idp_metadata_url" class="mo_saml_highlight_background_url_note">
											<a class="mo_idp_metadata_link" href='<?php echo Uri::root() . 'plugins/system/joomlaidplogin/saml2idp/metadata/metadata.php'; ?>' id='metadata-linkss' target='_blank'><?php echo '<strong>' . Uri::root() . 'plugins/system/joomlaidplogin/saml2idp/metadata/metadata.php </strong>'; ?></a>
										</span>
									</div>
									<div class="mo_boot_col-1">
										<em class="fa fa-lg fa-copy mo_copy mo_copytooltip mo_boot_p-3"
											onclick="copyToClipboard('#idp_metadata_url');"></em>
									</div>
								</div>

							</div>
						</div>
					</div>


					<div id="download-xml-tab" class="metadata-tab-content mo_saml_display_none">
						<div class="mo_boot_row">
							<div class="mo_boot_col-sm-12 ">
								<div class="mo_boot_row mo_boot_m-4">
									<p><?php echo Text::_('COM_MINIORANGE_IDP_DOWNLOAD_METADATA_TEXT'); ?></p>
									<div class="mo_boot_col-sm-12 mo_boot_p-0">
									<a href="<?php echo  Uri::root() . 'plugins/system/joomlaidplogin/saml2idp/metadata/metadata.php?download=true'; ?>" class="btn btn_cstm anchor_tag">
										<?php echo Text::_('COM_JOOMLAIDP_DOWNLOAD_METADATA'); ?>
									</a>
									</div>
								</div>
							</div>
						</div>
					</div>


					<div id="manual-info-tab" class="metadata-tab-content mo_saml_display_none">
						<div class="mo_boot_row">
							<div class="mo_boot_col-sm-12 ">
								<div class="mo_boot_row mo_boot_m-4">
									<p><?php echo Text::_('COM_MINIORANGE_IDP_MANUAL_INFO_TITLE'); ?></p>
									<table class='customtemp mo_boot_col-sm-12'>
										<tr>
											<td class="mo_table_td_style mo_boot_p-3">
												<?php echo Text::_('COM_JOOMLAIDP_ACCOUNTSETUP_ISSUER'); ?>
											</td>
											<td><span id="issuer"><?php echo $idpEntityId; ?></span>
												<em class="fa fa-pull-right  fa-lg fa-copy mo_copy mo_copytooltip mo_boot_p-3"
													onclick="copyToClipboard('#issuer');"></em>
											</td>
										</tr>
										<tr>
											<td class="mo_table_td_style mo_boot_p-3">
												<?php echo Text::_('COM_JOOMLAIDP_ACCOUNTSETUP_SAML_LOGIN'); ?>
											</td>
											<td>
												<span id="login_url"><?php echo $siteUrl . 'index.php';  ?></span>
												<em class="fa fa-pull-right  fa-lg fa-copy mo_copy mo_copytooltip mo_boot_p-3" onclick="copyToClipboard('#login_url');"></em>
											</td>
										</tr>
										<tr>
											<td class="mo_table_td_style mo_boot_p-3">
												<?php echo Text::_('COM_JOOMLAIDP_ACCOUNTSETUP_CERTIFICATE'); ?>
											</td>
											<td>
												<?php echo Text::_('COM_JOOMLAIDP_DOWNLOAD_CRT'); ?>
												<a class="metadata_btn_cstm btn btn_cstm" href="<?php echo Uri::root() . 'plugins/system/joomlaidplogin/saml2idp/cert/idp-signing.crt'; ?>" download="idp-signing.crt"><i class="fa fa-download" aria-hidden="true"></i></a>
											</td>
										</tr>
										<tr>
											<td class="mo_table_td_style mo_boot_p-3">
												<?php echo Text::_('COM_JOOMLAIDP_ACCOUNTSETUP_SAML_LOGOUT'); ?>
											</td>
											<td>
												<a href="<?php echo Route::_('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=license'); ?>"><b><?php echo Text::_('COM_JOOMLAIDP_PREMIUM_FEATURE'); ?></b></a>
												<a href="<?php echo Route::_('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=license'); ?>">
													<img class="crown_img_small mo_idp_crown_pos" src="<?php echo Uri::base();?>/components/com_joomlaidp/assets/images/crown.webp" alt="">
												</a>
											</td>
										</tr>
										<tr>
											<td class="mo_table_td_style mo_boot_p-3">
												<?php echo Text::_('COM_JOOMLAIDP_ACCOUNTSETUP_ASSERTION_SIGNED'); ?>
											</td>
											<td>
												<a href="<?php echo Route::_('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=license'); ?>"><b><?php echo Text::_('COM_JOOMLAIDP_PREMIUM_FEATURE'); ?></b></a>
												<a href="<?php echo Route::_('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=license'); ?>">
													<img class="crown_img_small mo_idp_crown_pos" src="<?php echo Uri::base();?>/components/com_joomlaidp/assets/images/crown.webp" alt="">
												</a>
												</td>
										</tr>

									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			</div>
		</div>

		<?php
	}


	public function showLicensingPlanDetails()
	{
		$isRegistered = MoSamlIdpUtility::isCustomerRegistered();
		$result = IDP_Utilities::fetchDatabaseValues('#__miniorange_saml_idp_customer', 'loadAssoc', '*');
		$userEmail  = isset($result['email']) ? $result['email'] : '';
		$upgradeURL = " https://portal.miniorange.com/initializePayment?requestOrigin=joomla_saml_idp_premium_plan";
		$newTab = '_blank';
		$circleIcon = '
        <svg class="min-w-[8px] min-h-[8px]" width="8" height="8" viewBox="0 0 18 18" fill="none">
            <circle id="a89fc99c6ce659f06983e2283c1865f1" cx="9" cy="9" r="7" stroke="rgb(99 102 241)" stroke-width="4"></circle>
        </svg>
         ';

		?>

		<div class="mo_boot_col-sm-12 mo_boot_m-0 mo_boot_p-0 mo_idp_main_content">
		<div class="mo_boot_row mo_boot_p-2">
		<div id="mo_saml_pricing_page" class="mo_idp_pricing_page mo_boot_col-sm-12 my-2">
			<div class="mo_boot_row mo_idp_pricing_snippet_grid justify-content-center">
				<div class="mo_idp_pricing_card">
					<div class="mo_idp_plan_head">
						<h5 class="mo_idp_free_plan"><?php echo Text::_('COM_JOOMLAIDP_FREE_PLAN'); ?></h5>
						<div class="mo_idp_price_box">
							<span class="mo_idp_price_main">$0<span class="mo_idp_price_period">/year</span></span>
							<span class="mo_idp_price_meta"><?php echo Text::_('COM_JOOMLAIDP_FREE_FOREVER'); ?></span>
						</div>
						<p class="mo_idp_plan_note"><?php echo Text::_('COM_JOOMLAIDP_FREE_PLAN_NOTE'); ?></p>
						<div class="mo_idp_txt_center">
							<a href="#" class="upgrade_button mo_idp_license_btns mo_idp_active_plan"><?php echo Text::_('COM_JOOMLAIDP_ACTIVE_PLAN'); ?></a>
						</div>
					</div>
					<div class="mo_idp_plan_features">
				   <div class="mo_boot_mb-0 mo_boot_text-center mo_idp_feature_header">
					   <a href="#" onclick="toggleIncludedFeatures('free_plan_features'); return false;">
						   <i class="fa fa-check-circle"></i> <strong><?php echo Text::_('COM_JOOMLAIDP_INCLUDED_FEATURES'); ?></strong>
					   </a>
					   <ul id="free_plan_features" class="mt-mo-4 grow mo_idp_license_point mo_idp_first_Plan mo_boot_mt-3 mo_idp_features_list" style="display: none;">
					   <li class="mo_saml_feature_snippet"><span>•</span><span class="mo_idp_upgrade_feature"><?php echo Text::_('COM_MINIORANGE_IDP_FREE_NOW_DESC_A'); ?></span></li>
					   <li class="mo_saml_feature_snippet"><span>•</span><span class="mo_idp_upgrade_feature"><?php echo Text::_('COM_MINIORANGE_IDP_FREE_NOW_DESC_B'); ?></span></li>
					   <li class="mo_saml_feature_snippet"><span>•</span><span class="mo_idp_upgrade_feature"><?php echo Text::_('COM_MINIORANGE_IDP_FREE_NOW_DESC_C'); ?></span></li>
					   <li class="mo_saml_feature_snippet"><span>•</span><span class="mo_idp_upgrade_feature"><?php echo Text::_('COM_MINIORANGE_IDP_FREE_NOW_DESC_D'); ?></span></li>
					   <li class="mo_saml_feature_snippet"><span>•</span><span class="mo_idp_upgrade_feature"><?php echo Text::_('COM_MINIORANGE_IDP_FREE_NOW_DESC_E'); ?></span></li>
					   <li class="mo_saml_feature_snippet"><span>•</span><span class="mo_idp_upgrade_feature"><?php echo Text::_('COM_MINIORANGE_IDP_FREE_NOW_DESC_F'); ?></span></li>
					   <li class="mo_saml_feature_snippet"><span>•</span><span class="mo_idp_upgrade_feature"><?php echo Text::_('COM_MINIORANGE_IDP_FREE_NOW_DESC_G'); ?></span></li>
					   <li class="mo_saml_feature_snippet"><span>•</span><span class="mo_idp_upgrade_feature"><?php echo Text::_('COM_MINIORANGE_IDP_FREE_NOW_DESC_H'); ?></span></li>
					   </ul>
				   </div>
				   <div class="mo_boot_mb-0 mo_boot_text-center mo_idp_feature_header mo_boot_mt-3">
					   <a href="#" onclick="toggleIncludedFeatures('free_plan_not_included_features'); return false;">
						   <i class="fa fa-times-circle" style="color: #cc0000;"></i> <strong><?php echo Text::_('COM_JOOMLAIDP_NOT_INCLUDED_FEATURES'); ?></strong>
					   </a>
					   <ul id="free_plan_not_included_features" class="mt-mo-4 grow mo_idp_license_point mo_idp_first_Plan mo_boot_mt-3 mo_idp_features_list" style="display: none;">
					   <li class="mo_saml_feature_snippet"><span>•</span><span class="mo_idp_upgrade_feature"><?php echo Text::_('COM_MINIORANGE_IDP_UPGRADE_NOW_DESC_B'); ?></span></li>
					   <li class="mo_saml_feature_snippet"><span>•</span><span class="mo_idp_upgrade_feature"><?php echo Text::_('COM_MINIORANGE_IDP_UPGRADE_NOW_DESC_C'); ?></span></li>
					   <li class="mo_saml_feature_snippet"><span>•</span><span class="mo_idp_upgrade_feature"><?php echo Text::_('COM_MINIORANGE_IDP_UPGRADE_NOW_DESC_D'); ?></span></li>
					   <li class="mo_saml_feature_snippet"><span>•</span><span class="mo_idp_upgrade_feature"><?php echo Text::_('COM_MINIORANGE_IDP_UPGRADE_NOW_DESC_E'); ?></span></li>
					   <li class="mo_saml_feature_snippet"><span>•</span><span class="mo_idp_upgrade_feature"><?php echo Text::_('COM_MINIORANGE_IDP_UPGRADE_NOW_DESC_F'); ?></span></li>
					   <li class="mo_saml_feature_snippet"><span>•</span><span class="mo_idp_upgrade_feature"><?php echo Text::_('COM_MINIORANGE_IDP_UPGRADE_NOW_DESC_G'); ?></span></li>
					   <li class="mo_saml_feature_snippet"><span>•</span><span class="mo_idp_upgrade_feature"><?php echo Text::_('COM_MINIORANGE_IDP_UPGRADE_NOW_DESC_H'); ?></span></li>
					   <li class="mo_saml_feature_snippet"><span>•</span><span class="mo_idp_upgrade_feature"><?php echo Text::_('COM_MINIORANGE_IDP_UPGRADE_NOW_DESC_I'); ?></span></li>
					   <li class="mo_saml_feature_snippet"><span>•</span><span class="mo_idp_upgrade_feature"><?php echo Text::_('COM_MINIORANGE_IDP_UPGRADE_NOW_DESC_J'); ?></span></li>
					   </ul>
				   </div>
					</div>
						</div>
						<div class="mo_idp_pricing_card premium">
						<div class="mo_idp_plan_head">
						<span class="mo_idp_recommended"><?php echo Text::_('COM_JOOMLAIDP_RECOMMENDED'); ?></span>
						<h5><?php echo Text::_('COM_JOOMLAIDP_PREMIUM_PLAN'); ?></h5>
						<div class="mo_idp_price_box" id="mo_idp_price_total">
							<span class="mo_idp_price_main">$<span id="mo_idp_price_amount">249</span><span class="mo_idp_price_period">/year<sup>*</sup></span></span>
							<span class="mo_idp_price_meta"><?php echo Text::_('COM_JOOMLAIDP_PER_INSTANCE_PRICING'); ?></span>
						</div>
						<div class="mo_idp_price_box mo_idp_disp_no" id="mo_idp_price_contact">
							<a target="_blank" class="mo_idp_price_contact_link"
								href="https://www.miniorange.com/contact"><?php echo Text::_('COM_JOOMLAIDP_CONTACT_US'); ?></a>
						</div>
						<select name="user-slab" id="mo_idp_user_slab" class="mo_boot_col-12 mo_idp_users_details slab_dropdown mo_boot_mt-4 mo_boot_mb-2 mo_boot_p-1 mo_form-control mo_idp_form_control">
							<?php
							// phpcs:disable Joomla.ControlStructures.ControlStructuresBrackets
							$userPrices = array(
								'100' => 199, '200' => 299, '300' => 399, '400' => 449, '500' => 499,
								'750' => 749, '1000' => 949, '2000' => 1549, '3000' => 2149,
								'4000' => 2599, '5000' => 2999,
							);

							foreach ($userPrices as $users => $price)
							{
								echo '<option value="' . $users . '" data-price="' . (int) $price . '"' . ((int) $users === 100 ? ' selected' : '') . '>'
									. Text::_('COM_JOOMLAIDP_NO_USERS') . ': ' . $users . ' ($' . (int) $price . ')</option>';
							}
							?>
							<option value="5000p"><?php echo Text::_('COM_JOOMLAIDP_NO_USERS'); ?>: 5000+</option>
						</select>
						<select name="sp-slab" id="mo_idp_sp_slab" class="mo_boot_col-12 mo_idp_sp_details slab_dropdown mo_boot_mb-4 mo_boot_p-1 mo_form-control mo_idp_form_control">
							<?php
							for ($sps = 1; $sps <= 5; $sps++)
							{
								$spPrice = $sps * 50;
								echo '<option value="' . $sps . '" data-price="' . $spPrice . '"' . ($sps === 1 ? ' selected' : '') . '>'
									. Text::_('COM_JOOMLAIDP_NO_SPS') . ': ' . $sps . ' ($' . $spPrice . ')</option>';
							}

							// phpcs:enable Joomla.ControlStructures.ControlStructuresBrackets
							?>
						</select>
							<div class="mo_idp_txt_center">
								<a class="upgrade_button mo_idp_license_btns" target="<?php echo $newTab;?>"
									href="<?php echo $upgradeURL;?>">
									<?php echo Text::_('COM_JOOMLAIDP_UPGRADE_NOW'); ?>
								</a>
							</div>
						</div>

							<div class="pricing-content mo_idp_plan_features">
								<div class="mo_boot_mb-0 mo_boot_text-center mo_idp_feature_header">
									<a href="#" onclick="toggleIncludedFeatures('premium_plan_features', event); return false;">
										<i class="fa fa-check-circle"></i> <strong><?php echo Text::_('COM_JOOMLAIDP_INCLUDED_FEATURES'); ?></strong>
									</a>
									<ul id="premium_plan_features" class="mt-mo-4 grow mo_idp_license_point mo_boot_mt-3 mo_idp_features_list" style="display: none;">
										<li class="mo_saml_feature_snippet"><span>•</span><span class="mo_idp_upgrade_feature"><?php echo Text::_('COM_MINIORANGE_IDP_UPGRADE_NOW_DESC_A'); ?></span></li>
										<li class="mo_saml_feature_snippet"><span>•</span><span class="mo_idp_upgrade_feature"><?php echo Text::_('COM_MINIORANGE_IDP_UPGRADE_NOW_DESC_B'); ?></span></li>
										<li class="mo_saml_feature_snippet"><span>•</span><span class="mo_idp_upgrade_feature"><?php echo Text::_('COM_MINIORANGE_IDP_UPGRADE_NOW_DESC_C'); ?></span></li>
										<li class="mo_saml_feature_snippet"><span>•</span><span class="mo_idp_upgrade_feature"><?php echo Text::_('COM_MINIORANGE_IDP_UPGRADE_NOW_DESC_D'); ?></span></li>
										<li class="mo_saml_feature_snippet"><span>•</span><span class="mo_idp_upgrade_feature"><?php echo Text::_('COM_MINIORANGE_IDP_UPGRADE_NOW_DESC_E'); ?></span></li>
										<li class="mo_saml_feature_snippet"><span>•</span><span class="mo_idp_upgrade_feature"><?php echo Text::_('COM_MINIORANGE_IDP_UPGRADE_NOW_DESC_F'); ?></span></li>
										<li class="mo_saml_feature_snippet"><span>•</span><span class="mo_idp_upgrade_feature"><?php echo Text::_('COM_MINIORANGE_IDP_UPGRADE_NOW_DESC_G'); ?></span></li>
										<li class="mo_saml_feature_snippet"><span>•</span><span class="mo_idp_upgrade_feature"><?php echo Text::_('COM_MINIORANGE_IDP_UPGRADE_NOW_DESC_H'); ?></span></li>
										<li class="mo_saml_feature_snippet"><span>•</span><span class="mo_idp_upgrade_feature"><?php echo Text::_('COM_MINIORANGE_IDP_UPGRADE_NOW_DESC_I'); ?></span></li>
										<li class="mo_saml_feature_snippet"><span>•</span><span class="mo_idp_upgrade_feature"><?php echo Text::_('COM_MINIORANGE_IDP_UPGRADE_NOW_DESC_J'); ?></span></li>
									</ul>
								</div>
								<!-- Hidden "Not-Included Features" section to match free card height -->
								<div class="mo_boot_mb-0 mo_boot_text-center mo_idp_feature_header mo_boot_mt-3 mo_idp_hidden_not_included">
									<a href="#" onclick="toggleIncludedFeatures('premium_plan_not_included_features', event); return false;">
									 <strong><?php echo Text::_('COM_JOOMLAIDP_NOT_INCLUDED_FEATURES'); ?></strong>
									</a>

								</div>
							</div>
						</div>
					</div>
				</div>
			<div class="mo_boot_col-sm-12 mo_boot_my-2">
				<div class=" mo_boot_offset-1">
					<div class="mo_boot_row">
						<div class="mo_boot_col-sm-11 alert alert-info">
							<span class="icon-info-circle" aria-hidden="true"></span><span
								class="visually-hidden">Info</span><span
								class="mo_idp_ml_px"><?php echo Text::_('COM_JOOMLAIDP_MULTIPLE_IDP');?> </span>
						</div>
					</div>
				</div>
			</div>

		<div class="mo_boot_col-sm-12">
			<div class="mo_boot_col-sm-12 mo_boot_p-4 mo_boot_mt-4 mo_idp_mini_section">
				<div class="mo_boot_d-flex mo_boot_justify-content-between mo_boot_align-items-center"
					onclick="toggleUpgradeSection()" style="cursor: pointer;">
					<h3 class="mo_boot_mb-0 mo_boot_col-sm-7"><?php echo Text::_('COM_JOOMLAIDP_SAML_UPGRADE_HEADER'); ?>
					</h3>
					<button class="mo_boot_col-sm-1 mo_boot_offset-sm-4 mo_idp_toggle_btn_black"
						id="upgrade-toggle">+</button>
				</div>
				<div id="upgrade-content" style="display: none;">
					<div class="mo_boot_row mo_boot_mt-3 mo_boot_col-sm-12">
						<div class="mo_boot_col-sm-12 mo_boot_row">
							<div class="mo_boot_col-sm-6 mo_works-step mo_boot_d-flex">
								<div class="mo_saml_step_number">1</div>
								<p class="mo_boot_mb-0"><?php echo Text::_('COM_JOOMLAIDP_SAML_UPGRADE_ONE'); ?></p>
							</div>

							<div class="mo_boot_col-sm-6 mo_works-step mo_boot_d-flex">
								<div class="mo_saml_step_number">4</div>
								<p class="mo_boot_mb-0"><?php echo Text::_('COM_JOOMLAIDP_SAML_UPGRADE_FOUR'); ?></p>
							</div>
						</div>

						<div class="mo_boot_col-sm-12 mo_boot_row">
							<div class="mo_boot_col-sm-6 mo_works-step mo_boot_d-flex">
								<div class="mo_saml_step_number">2</div>
								<p class="mo_boot_mb-0"><?php echo Text::_('COM_JOOMLAIDP_SAML_UPGRADE_TWo'); ?></p>
							</div>

							<div class="mo_boot_col-sm-6 mo_works-step mo_boot_d-flex">
								<div class="mo_saml_step_number">5</div>
								<p class="mo_boot_mb-0"><?php echo Text::_('COM_JOOMLAIDP_SAML_UPGRADE_FIVE'); ?></p>
							</div>
						</div>

						<div class="mo_boot_col-sm-12 mo_boot_row">
							<div class="mo_boot_col-sm-6 mo_works-step mo_boot_d-flex">
								<div class="mo_saml_step_number">3</div>
								<p class="mo_boot_mb-0"><?php echo Text::_('COM_JOOMLAIDP_SAML_UPGRADE_THREE'); ?></p>
							</div>

							<div class="mo_boot_col-sm-6 mo_works-step mo_boot_d-flex">
								<div class="mo_saml_step_number">6</div>
								<p class="mo_boot_mb-0"><?php echo Text::_('COM_JOOMLAIDP_SAML_UPGRADE_SIX'); ?></p>
							</div>
						</div>

					</div>
				</div>
			</div>
		</div>

		<div class="mo_boot_col-sm-12 mo_boot_mt-4">
			<div class="mo_boot_col-sm-12 mo_boot_p-4 mo_boot_mt-4 mo_idp_mini_section">
				<div class="mo_boot_d-flex mo_boot_justify-content-between mo_boot_align-items-center"
					onclick="toggleLicensingSection()" style="cursor: pointer;">
					<h3 class="mo_boot_mb-0 mo_boot_col-sm-7">
						<?php echo Text::_('COM_JOOMLAIDP_SAML_UPGRADE_HEADER_DETAILS_RETURN_POLICY'); ?>
					</h3>
					<button class="mo_boot_col-sm-1 mo_boot_offset-sm-4 mo_idp_toggle_btn_black"
						id="licensing-toggle">+</button>
				</div>
				<div id="licensing-content" style="display: none;">
					<div class="mo_boot_mt-3 mo_boot_col-sm-12">

						<div>
							<p class="mo_boot_mb-2"><?php echo Text::_('COM_JOOMLAIDP_RETURN_POLICY_DESC'); ?></p>
							<h4 class="mo_boot_mb-3"><?php echo Text::_('COM_JOOMLAIDP_SAML_UPGRADE_HEADER_DETAILS_B'); ?></h4>
							<p>1. <?php echo Text::_('COM_JOOMLAIDP_SAML_UPGRADE_HEADER_DETAILS_C'); ?> <a
									href="mailto:joomlasupport@xecurify.com">joomlasupport@xecurify.com</a></p>
							<p>2. <?php echo Text::_('COM_JOOMLAIDP_SAML_UPGRADE_HEADER_DETAILS_D'); ?></p>
							<p>3. <?php echo Text::_('COM_JOOMLAIDP_SAML_UPGRADE_HEADER_DETAILS_E'); ?></p>

							<h4 class="mo_boot_mb-3"><?php echo Text::_('COM_JOOMLAIDP_SAML_UPGRADE_HEADER_DETAILS_F'); ?></h4>
							<ol class="mo_boot_mb-0" style="padding-left: 20px;">
								<p><?php echo Text::_('COM_JOOMLAIDP_SAML_UPGRADE_HEADER_DETAILS_G'); ?></p>
								<p><?php echo Text::_('COM_JOOMLAIDP_SAML_UPGRADE_HEADER_DETAILS_H'); ?></p>
								<p><?php echo Text::_('COM_JOOMLAIDP_SAML_UPGRADE_HEADER_DETAILS_I'); ?></p>
							</ol>
						</div>
					</div>
				</div>
			</div>
		</div>

		</div>
	</div>
		<?php
	}


	public static function showServiceProviderList()
	{
		$app = Factory::getApplication();
		$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
		$spId = (int) $input->get('sp_id', 0);

		if ($spId <= 0)
		{
			$db = MoSamlIdpDb::getDb();
			$query = $db->getQuery(true);
			$query->select($db->quoteName('id'))
				->from($db->quoteName('#__miniorangesamlidp'))
				->order($db->quoteName('id') . ' ASC');
			$db->setQuery($query, 0, 1);
			$spId = (int) $db->loadResult();
		}

		$attribute = $spId > 0
			? IDP_Utilities::fetchDatabaseValues('#__miniorangesamlidp', 'loadAssoc', '*', 'id', $spId)
			: array();

		self::showServiceProviderConfigurations($attribute, $spId);
		self::importExportConfiguration($attribute);
	}


	public static function importExportConfiguration($attribute = array())
	{
		$spEntityId = isset($attribute['sp_entityid']) ? $attribute['sp_entityid'] : '';
		$exportState = $spEntityId ? 'enabled' : 'disabled';
		$licensingPageLink = Uri::base() . 'index.php?option=com_joomlaidp&view=accountsetup&tab-panel=license';
		?>
	<div id="mo_idp_import_export_id" class="mo_boot_col-sm-12 mo_boot_m-0 mo_boot_p-0 mo_idp_main_content mo_saml_display_none">
		<div class="mo_boot_row mo_boot_p-2">
			<div class="mo_boot_col-sm-12 mo_boot_px-2">
				<div class="mo_boot_row mo_boot_mb-4">
					<div class="mo_boot_col-sm-10">
						<h3 class="mo_saml_form_heading"><?php echo Text::_('COM_JOOMLAIDP_IMPORT_EXPORT_CONFIG'); ?>
						</h3>
					</div>
					<div class="mo_boot_col-sm-2 text-right">
						<button class="mo_boot_btn btn_cstm" onclick="backToIdpList()" id="back_to_idp_list">
							<?php echo Text::_('COM_JOOMLAIDP_BACK_BTN'); ?>
						</button>
					</div>
				</div>

				<div class="mo_boot_row mo_boot_mb-4">
					<div class="mo_boot_col-sm-12">
						<div class="mo_boot_p-4 mo_idp_mini_section">
							<div class="mo_boot_row">
								<div class="mo_boot_col-sm-8">
									<h3 class="mo_saml_form_heading">
										<?php echo Text::_('COM_JOOMLAIDP_EXPORT_CONFIG'); ?>
									</h3>
									<p class="mo_saml_config_text">
										<?php echo Text::_('COM_JOOMLAIDP_DOWNLOAD_CURRENT_PLUGIN_SETTINGS'); ?>
									</p>
								</div>
								<div class="mo_boot_col-sm-4 text-right">
								<input type="button" class="btn btn_cstm mo_idp_crown_pos mo_idp_export_config" <?php echo $exportState; ?> onclick="jQuery('#mo_idp_exportconfig').submit();" value="<?php echo Text::_('COM_JOOMLAIDP_EXPORT_CONFIG'); ?>">

								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="mo_boot_row mo_boot_mb-4">
					<div class="mo_boot_col-sm-12">
						<div class="mo_boot_p-4 mo_idp_mini_section">
							<div class="mo_boot_row">
								<div class="mo_boot_col-sm-8">
									<h3 class="mo_saml_form_heading">
										<?php echo Text::_('COM_JOOMLAIDP_IMPORT_CONFIG'); ?>
										<sup>
											<div class="mo_tooltip">
												<img class="crown_img_small mo_idp_ml_px"
													src="<?php echo Uri::base(); ?>/components/com_joomlaidp/assets/images/crown.webp">
												<span class="mo_tooltiptext small mo_boot_btn-fetch">
													<?php echo Text::sprintf('COM_JOOMLAIDP_UPGRADE_NOTE', $licensingPageLink); ?>
												</span>
											</div>
										</sup>
									</h3>
									<p class="mo_saml_config_text">
										<?php echo Text::_('COM_JOOMLAIDP_IMPORT_CONFIGURATION_FILE'); ?>
									</p>
								</div>
								<div class="mo_boot_col-sm-4 text-right">
								<input type="button" class="btn btn_cstm mo_idp_crown_pos mo_idp_export_config" disabled="disabled" value="<?php echo Text::_('COM_JOOMLAIDP_IMPORT_CONFIG'); ?>">

								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="mo_boot_row">
					<div class="mo_boot_col-sm-12">
						<p> <strong><?php echo Text::_('COM_MINIORANGE_SAML_NOTE'); ?>:</strong> <?php echo Text::_('COM_JOOMLAIDP_IMPORT_EXPORT_CONFIG_HELP'); ?></p>
					</div>
				</div>

				<form name="f" id="mo_idp_exportconfig" method="post" action="<?php echo Route::_('index.php?option=com_joomlaidp&view=accountsetup&task=accountsetup.importExportConfiguration'); ?>">
				</form>
			</div>
		</div>
	</div>
		<?php
	}


	public static function showServiceProviderConfigurations($attribute = null, $spId = 0)
	{
		if ($attribute === null)
		{
			$attribute = IDP_Utilities::fetchDatabaseValues('#__miniorangesamlidp', 'loadAssoc', '*');
		}

		$spName = '';
		$spEntityId = '';
		$acsUrl = '';
		$nameIdFormat = '';
		$nameIdAttribute = '';
		$assertionSigned = '';
		$relayState = '';

		if (isset($attribute['sp_entityid']))
		{
			$spEntityId = $attribute['sp_entityid'];
			$acsUrl = $attribute['acs_url'];
			$nameIdFormat = $attribute['nameid_format'];
			$spName = $attribute['sp_name'];
			$nameIdAttribute = $attribute['nameid_attribute'];
			$assertionSigned = $attribute['assertion_signed'];
			$relayState = $attribute['default_relay_state'];
		}

		$setupGuides = json_decode(IDP_Utilities::setupGuides(), true);
		$guideCount = count($setupGuides);
		$showManualTab = $spId > 0;
		$testConfigState = $spEntityId ? 'enabled' : 'disabled';
		$deleteState = $spEntityId ? 'enabled' : 'disabled';
		$exportState = $spEntityId ? 'enabled' : 'disabled';
		$guideOptionsHtml = '';

		if (!empty($setupGuides) && is_array($setupGuides))
		{
			foreach ($setupGuides as $guide)
			{
				if (isset($guide['name']) && isset($guide['link']))
				{
					$guideOptionsHtml .= '<option value="' . htmlspecialchars($guide['link']) . '">'
						. htmlspecialchars($guide['name']) . '</option>';
				}
			}
		}
		?>
		<div id="sp_list_table" class="mo_boot_col-sm-12 mo_boot_m-0 mo_boot_p-0 mo_idp_main_content">
			<div class="mo_boot_row mo_boot_m-0 mo_boot_p-0">
				<div class="mo_boot_col-sm-12 mo_tab_border">
					<div class="mo_boot_col-sm-12 mo_boot_p-2">
						<form action="<?php echo Route::_('index.php?option=com_joomlaidp&view=accountsetup&task=accountsetup.saveServiceProvider'); ?>" method="post" name="adminForm" id="identity_provider_settings_form" enctype="multipart/form-data">
							<?php echo HTMLHelper::_('form.token'); ?>
							<input id="mo_saml_local_configuration_form_action" type="hidden" name="option1" value="mo_saml_save_config"/>
							<input type="hidden" name="sp_id" value="<?php echo $spId; ?>" />
							<div class="mo_boot_row mo_boot_mt-3" >
								<div class="mo_boot_col-lg-9 mo_boot_col-sm-6">
									<h3 class="mo_idp_sp_head"><?php echo Text::_('COM_JOOMLAIDP_CHOOSE_METHOD_TO_SETUP_IDP_CONFIG'); ?></h3>
								</div>
								<div class="mo_boot_col-sm-12 mo_boot_col-lg-3 mo_boot_text-center" >
									<button type="button" class="mo_boot_btn btn_cstm" onclick="showImportExportConfig()">
										<?php echo Text::_('COM_JOOMLAIDP_IMPORT_EXPORT'); ?>
									</button>
								</div>
								<div class="mo_boot_col-sm-12 mo_boot_mt-3 mo_saml_dark_bg">
									<ul class="switch_tab_sp mo_boot_text-center mo_boot_p-0 mo_boot_mt-4">
									<li class="<?php echo $showManualTab ? '' : 'mo_saml_current_tab'; ?>" id="auto_configuration" onclick="show_metadata_form()" style="cursor: pointer;"><a href="#" id="mo_saml_upload_idp_tab" class="mo_saml_bs_btn" onclick="return false;"><?php echo Text::_('COM_JOOMLAIDP_SP_METADATA_BTN'); ?></a></li>

										<li class="<?php echo $showManualTab ? 'mo_saml_current_tab' : ''; ?>" id="manual_configuration" onclick="hide_metadata_form()" style="cursor: pointer;"><a href="#" id="mo_saml_idp_manual_tab" class="mo_saml_bs_btn" onclick="return false;"><?php echo Text::_('COM_MINIORANGE_SAML_MANUAL_CONFIG'); ?></a></li>
									</ul>
								</div>
							</div>
							<div id="idpdata" class="mt-4 mo_boot_p-4 mo_idp_mini_section<?php echo $showManualTab ? '' : ' mo_idp_disp_no'; ?>">
								<div class="mo_boot_row mo_boot_mt-3" id="name">
									<div class="mo_boot_col-sm-4">
										<span class="saml_idp_label_css"><?php echo Text::_('COM_JOOMLAIDP_SP_NAME'); ?><span class="mo_saml_required">*</span></span>
									</div>
									<div class="mo_boot_col-sm-8">
										<input type="text" class="mo_form-control was-validated mo_saml_proxy_setup" name="sp_name" placeholder="<?php echo Text::_('COM_JOOMLAIDP_SP_NAME_PLACEHOLDER'); ?>" value="<?php echo $spName; ?>" required />
										<span class="small"><strong><?php echo Text::_('COM_MINIORANGE_SAML_NOTE'); ?>: </strong><?php echo Text::_('COM_JOOMLAIDP_ENTER_SP_NAME'); ?></span>
									</div>
								</div>

								<div class="mo_boot_row mo_boot_mt-3">
									<div class="mo_boot_col-sm-4">
										<span class="saml_idp_label_css"><?php echo Text::_('COM_MINIORANGE_IDP_SP_GUIDES'); ?></span>
									</div>
									<div class="mo_boot_col-sm-8">
										<select id="idp_setup_guide_dropdown" class="mo_form-control mo_saml_proxy_setup" onchange="if(this.value) window.open(this.value, '_blank'); this.value='';">
											<option value=""><?php echo Text::_('COM_JOOMLAIDP_SELECT_IDP_GUIDE'); ?></option>
											<?php echo $guideOptionsHtml; ?>
										</select>
									</div>
								</div>

								<div class="mo_boot_row mo_boot_mt-3" id="sp_entity">
									<div class="mo_boot_col-sm-4">
										<span class="saml_idp_label_css"><?php echo Text::_('COM_JOOMLAIDP_SP_ISSUER'); ?><span class="mo_saml_required">*</span></span>
									</div>
									<div class="mo_boot_col-sm-8">
										<input type="url" id="sp_entityid" class="mo_form-control was-validated mo_saml_proxy_setup" name="sp_entityid" placeholder="<?php echo Text::_('COM_JOOMLAIDP_ENTER_ISSUER'); ?>" value="<?php echo $spEntityId; ?>" required />
										<span class="small"><strong><?php echo Text::_('COM_MINIORANGE_SAML_NOTE'); ?>: </strong><?php echo Text::_('COM_JOOMLAIDP_ISSUER_INFO'); ?></span>
									</div>
								</div>
								<div class="mo_boot_row mo_boot_mt-3" id="sp_sso_url">
									<div class="mo_boot_col-sm-4">
										<span class="saml_idp_label_css"><?php echo Text::_('COM_MINIORANGE_IDP_ACS_URL'); ?><span class="mo_saml_required">*</span></span>
									</div>
									<div class="mo_boot_col-sm-8">
										<input type="url" id="acs_url" class="mo_form-control was-validated mo_saml_proxy_setup" name="acs_url" placeholder="<?php echo Text::_('COM_JOOMLAIDP_ENTER_ASC'); ?>" value="<?php echo $acsUrl; ?>" required />
										<span class="small"><strong><?php echo Text::_('COM_MINIORANGE_SAML_NOTE'); ?>: </strong><?php echo Text::_('COM_JOOMLAIDP_ASC_INFO'); ?></span>
									</div>
								</div>
								<div class="mo_boot_row mo_boot_mt-3" id="sp_nameid_format">
									<div class="mo_boot_col-sm-4"><?php echo Text::_('COM_MINIORANGE_IDP_NAMEID_FORMAT'); ?></div>
									<div class="mo_boot_col-sm-8">
										<select class="mo_form-control mo_saml_proxy_setup mo_saml_dark_bg" id="nameid_format" name="nameid_format">
											<option value="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified" <?php echo $nameIdFormat == 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified' ? 'selected = "selected"' : ''; ?>>
												urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified
											</option>
											<option value="urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress" <?php echo $nameIdFormat == 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress' ? 'selected = "selected"' : ''; ?>>
												urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress
											</option>
											<option value="urn:oasis:names:tc:SAML:1.1:nameid-format:transient" <?php echo $nameIdFormat == 'urn:oasis:names:tc:SAML:1.1:nameid-format:transient' ? 'selected = "selected"' : ''; ?>>
												urn:oasis:names:tc:SAML:1.1:nameid-format:transient
											</option>
										</select>
										<span class="small"><strong><?php echo Text::_('COM_MINIORANGE_SAML_NOTE'); ?>: </strong><?php echo Text::_('COM_JOOMLAIDP_NAMEID_INFO'); ?></span>
									</div>
								</div>
								<div class="mo_boot_row mo_boot_mt-3" id="sp_sso_url">
									<div class="mo_boot_col-sm-4">
										<span class="saml_idp_label_css"><?php echo Text::_('COM_MINIORANGE_IDP_RELAY_STATE'); ?></span>
									</div>
									<div class="mo_boot_col-sm-8">
										<input type="url" class="mo_form-control was-validated mo_saml_proxy_setup" name="default_relay_state" placeholder="<?php echo Text::_('COM_JOOMLAIDP_ENTER_RELAY'); ?>" value="<?php echo $relayState; ?>"  />
										<span class="small"><strong><?php echo Text::_('COM_MINIORANGE_SAML_NOTE'); ?>: </strong><?php echo Text::_('COM_JOOMLAIDP_RELAY_INFO'); ?></span>
									</div>
								</div>
								<div class="mo_boot_row mo_boot_mt-4" id="saml_login">
									<div class="mo_boot_col-sm-4"><?php echo Text::_('COM_MINIORANGE_IDP_SIGNED_ASSERTION'); ?>
									</div>
									<div class="mo_boot_col-sm-8">
										<label class="mo_saml_switch">
											<input type="checkbox" id ="login_link_check" name="assertion_signed" value="1"
											<?php echo ($assertionSigned == 1 ? 'checked' : ''); ?>
											>
											<span class="mo_saml_slider"></span>
										</label>
										<br><span class="small"><strong><?php echo Text::_('COM_MINIORANGE_SAML_NOTE'); ?>: </strong><?php echo Text::_('COM_JOOMLAIDP_CHECK_SIGN'); ?></span>
									</div>
								</div><br>
								<div class="mo_boot_col-sm-12 mo_boot_p-4 mo_boot_mt-4 mo_adv_feat">
									<div class="mo_boot_d-flex mo_boot_justify-content-between mo_boot_align-items-center"
										onclick="toggleAdvancedFeatures()" style="cursor: pointer;">
										<h3 class="mo_boot_mb-0 mo_boot_col-sm-7"><?php echo Text::_('COM_JOOMLAIDP_ADVACE_FEATURES'); ?> <sup><a href="index.php?option=com_joomlaidp&view=accountsetup&tab-panel=license">
											<img class="crown_img_small mo_idp_ml_px" src="<?php echo Uri::base();?>/components/com_joomlaidp/assets/images/crown.webp" alt="Premium">
											   </a></sup></h3>
										<button class="mo_boot_col-sm-1 mo_boot_offset-sm-4 mo_idp_toggle_btn_black"
											id="advanced-features-toggle">+</button>
									</div>
									<div id="advanced-features-content" class="mo_boot_p-3" style="display: none;">
										<div class="mo_tooltip">
											<div class="mo_boot_row mo_boot_mt-3" id="sp_slo">
												<div class="mo_boot_col-sm-4"><?php echo Text::_('COM_MINIORANGE_IDP_LOGOUT_URL'); ?>
												</div>
												<div class="mo_boot_col-sm-8">
													<input class=" mo_form-control mo_idp_block_cursor" type="text" name="single_logout_url" placeholder="Enter the SLO URL" disabled>
												</div>
											</div>
											<div class="mo_boot_row mo_boot_mt-3" id="sp_binding_type">
												<div class="mo_boot_col-sm-4">
													<?php echo Text::_('COM_JOOMLAIDP_BINDING'); ?>
												</div>
												<div class="mo_boot_col-sm-8">
													<input type="radio" class="mo_idp_block_cursor" name="miniorange_saml_sp_sso_binding" value="HttpRedirect" checked=1 aria-invalid="false" disabled><span class="ml-1"><?php echo Text::_('COM_MINIORANGE_IDP_SP_REDIRECT'); ?></span><br />
													<input type="radio" class="mo_idp_block_cursor" name="miniorange_saml_idp_sso_binding" value="HttpPost" aria-invalid="false" disabled><span class="ml-1"><?php echo Text::_('COM_MINIORANGE_IDP_SP_POST'); ?></span>
												</div>
											</div>
											<div class="mo_boot_row mo_boot_mt-3" id="sp_certificate_signed">
												<div class="mo_boot_col-sm-4"><?php echo Text::_('COM_MINIORANGE_IDP_SP_CERT_A'); ?>
												</div>
												<div class="mo_boot_col-sm-8">
													<textarea rows="3" cols="80" name="certificate" class="mo_idp_certificate mo_idp_block_cursor" disabled></textarea>
												</div>
											</div>
											<div class="mo_boot_row mo_boot_mt-3" id="sp_certificate_assertion">
												<div class="mo_boot_col-sm-4"><?php echo Text::_('COM_MINIORANGE_IDP_SP_CERT_B'); ?>
												</div>
												<div class="mo_boot_col-sm-8">
													<textarea rows="3" cols="80" name="certificate" class="mo_idp_certificate mo_idp_block_cursor"  disabled></textarea>
												</div>
											</div>
											<div class="mo_boot_row mo_boot_mt-3" id="sp_slo">
												<div class="mo_boot_col-sm-4">
													<?php echo Text::_('COM_JOOMLAIDP_SIGNED'); ?>
												</div>
												<div class="mo_boot_col-sm-8">
													<label class="mo_saml_switch">
														<input type="checkbox" disabled>
														<span class="mo_saml_slider mo_idp_block_cursor"></span>
													</label>
												</div>
											</div>
											<div class="mo_boot_row mo_boot_mt-3" id="sp_slo">
												<div class="mo_boot_col-sm-4">
													<?php echo Text::_('COM_JOOMLAIDP_ENCRYPT'); ?>
												</div>
												<div class="mo_boot_col-sm-8">
													<label class="mo_saml_switch">
														<input type="checkbox" disabled>
														<span class="mo_saml_slider mo_idp_block_cursor"></span>
													</label>
												</div>
											</div>
											<div class="mo_boot_row mo_boot_mt-3" id="sp_slo">
												<div class="mo_boot_col-sm-4">
													<?php echo Text::_('COM_JOOMLAIDP_VALIDATE_TIME'); ?>
												</div>
												<div class="mo_boot_col-sm-8">
													<input class=" mo_form-control mo_idp_block_cursor" type="text"  placeholder="<?php echo Text::_('COM_JOOMLAIDP_ENTER_TIME'); ?>" name="saml_response_validation_time" disabled>
												</div>
											</div>
										</div>
									</div>
								</div>

								<div class="mo_boot_row mo_boot_mt-5">
									<div class="mo_boot_col-sm-12 mo_boot_text-center">
										<input type="submit" class="btn btn_cstm" value="<?php echo Text::_('COM_JOOMLAIDP_SAVE_BTN'); ?>"/>
										<input  type="button" id='test-config' <?php echo $testConfigState; ?> title='<?php echo Text::_('COM_JOOMLAIDP_TEST_TITLE'); ?>' class="btn btn_cstm mo_idp_test_cinfig" onclick='showTestWindow()' value="<?php echo Text::_('COM_JOOMLAIDP_TEST_CONFIG'); ?>">

										<input type="submit" class="btn btn_cstm_red " <?php echo $deleteState; ?> value="<?php echo Text::_('COM_JOOMLAIDP_DELETE_SP'); ?>" name="mo_saml_delete" />
									</div>
								</div>

							</div>
							<input type="hidden" id="idp-initiated-url" value="<?php echo Route::_('index.php?option=com_idpinitiatedlogin'); ?>"/>
						</form>
						<form name="f" id="mo_idp_exportconfig"  method="post" action="<?php echo Route::_('index.php?option=com_joomlaidp&view=accountsetup&task=accountsetup.importExportConfiguration'); ?>" >
						</form>
						<div class="mt-4 mo_boot_p-4 mo_idp_mini_section<?php echo $showManualTab ? ' mo_idp_disp_no' : ''; ?>" id="upload_metadata_form">
								<form action="<?php echo Route::_('index.php?option=com_joomlaidp&view=accountsetup&task=accountsetup.handleUploadMetadata'); ?>" name="metadataForm" method="post" id="IDP_metadata_form" enctype="multipart/form-data">
									<div class="mo_boot_row">
										<div class="mo_boot_col-sm-3">
											<span class="saml_idp_label_css"><?php echo Text::_('COM_JOOMLAIDP_SP_NAME'); ?><span class="mo_saml_required">*</span> :</span>
										</div>
										<div class="mo_boot_col-sm-7 mo_boot_col-lg-6">
											<input type="text" class="mo_boot_form-control mo_boot_form-text-control" id="sp_upload_name" name="sp_upload_name" placeholder="<?php echo Text::_('COM_JOOMLAIDP_SP_NAME_PLACEHOLDER'); ?>" required>
										</div>
									</div>
									<div class="mo_boot_row mo_boot_mt-5">
										<div class="mo_boot_col-sm-5 mo_boot_col-lg-3">
											<input id="mo_saml_upload_metadata_form_action" type="hidden" name="option1" value="uploadMetadata"/>
											<?php echo Text::_('COM_JOOMLAIDP_SP_METADATA_UPLOAD'); ?>:
										</div>
										<div class="mo_boot_col-sm-6">
											<input type="hidden" name="action"  value="upload_metadata" />
											<input type="file"  id="metadata_uploaded_file" class="form-control-file"  name="metadata_file" />
										</div>
										<div class="mo_boot_col-sm-12 mo_boot_col-lg-3 mo_boot_text-center">
											<button type="button" class="float-lg-right btn btn_cstm mo_idp_adv_feat_btns" id="upload_metadata_file" name="option1" method="post">
												<span class="icon-upload mo_boot_btn-fetch" aria-hidden="true"></span>&nbsp;&nbsp;<?php echo Text::_('COM_JOOMLAIDP_CRT_UPLOAD'); ?>
											</button>
										</div>
									</div>
									<div class="mo_boot_mt-5 ">
										<div class="mo_boot_text-center metadata_or  " >
											<div class="mo_idp_ip_or">
												<span class="btn  mo_saml_rounded_circle mo_boot_p-2"><?php echo Text::_('COM_JOOMLAIDP_OR'); ?></span>
											</div>
										</div>
									</div>
									<div class="mo_boot_row mo_boot_mt-5">
										<div class="mo_boot_col-sm-5 mo_boot_col-lg-3">
											<input type="hidden" name="action" value="uploadMetadata"/>
											<?php echo Text::_('COM_JOOMLAIDP_SP_METADATA_URL'); ?>:
										</div>
										<div class="mo_boot_col-sm-7 mo_boot_col-lg-6">
											<input type="url" id="metadata_url" name="metadata_url" placeholder=" <?php echo Text::_('COM_JOOMLAIDP_ENTER_METADATA_URL'); ?>" class="mo_boot_form-control mo_boot_form-text-control"/>
										</div>
										<div class=" mo_boot_col-sm-12 mo_boot_col-lg-3 mo_boot_text-center ">
											<button type="button" class=" float-lg-right btn btn_cstm mo_idp_adv_feat_btns" name="option1" method="post" id="fetch_metadata">
												<span class="icon-download mo_boot_btn-fetch" aria-hidden="true"></span> <?php echo Text::_('COM_JOOMLAIDP_FETCH_METADATA'); ?>
											</button>
										</div>
									</div>
								</form>
						</div>
					</div>
				</div>

			</div>
		</div>
		<?php
	}
}
