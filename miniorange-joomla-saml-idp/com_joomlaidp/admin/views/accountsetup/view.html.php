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


// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;
Use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\View\HtmlView;
HTMLHelper::_('jquery.framework');

$document = Factory::getApplication()->getDocument();
$document->addScript(Uri::base() . 'components/com_joomlaidp/assets/js/bootstrap-select-min.js');
$document->addScript(Uri::base() . 'components/com_joomlaidp/assets/js/utilityjs.js');

$document->addStyleSheet(Uri::base() . 'components/com_joomlaidp/assets/css/miniorange_boot.css');
$document->addStyleSheet(Uri::base() . 'components/com_joomlaidp/assets/css/miniorange_idp.css');
$document->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');

/**
 * Register/Login View
 *
 * @since  0.0.1
 */
class JoomlaIdpViewAccountSetup extends HtmlView
{
    function display($tpl = null)
    {
        // Get data from the model
        $this->lists = $this->get('List');
        if (count($errors = $this->get('Errors'))) {
            Factory::getApplication()->enqueueMessage(implode('<br />', $errors), 'error');
            return false;
        }
        $this->setLayout('accountsetup');
        // Set the toolbar
        $this->addToolBar();
        // Display the template
        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function addToolBar()
    {
        ToolbarHelper::title(Text::_('COM_JOOMLAIDP_PLUGIN_TITLE'), 'mo_saml_logo mo_saml_icon');
    }

    public static function showRoleRelayRestriction()
    {
        $attribute = IDP_Utilities::fetchDatabaseValues('#__miniorangesamlidp', 'loadAssoc', '*');

        $licensing_page_link=Uri::base().'index.php?option=com_joomlaidp&view=accountsetup&tab-panel=license';
        $sp_entityid = '';
        $sp_name = '';
        if (is_array($attribute)) {
            $sp_entityid = isset($attribute['sp_entityid']) ? $attribute['sp_entityid'] : '';
            $sp_name = isset($attribute['sp_name']) ? $attribute['sp_name'] : '';
        }
        ?>

        <div class="mo_boot_col-sm-12 mo_boot_m-0 mo_boot_p-0">
            <div class="mo_boot_row mo_tab_border mo_boot_p-2 mo_boot_m-0">
                <div class="mo_boot_col-sm-12 mo_boot_mt-3">
                    <div class="mo_boot_row">
                        <div class="mo_boot_col-lg-5">
                            <h3 class="mo_saml_form_head"><?php echo Text::_('COM_JOOMLAIDP_RESTRICTIONS'); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="mo_boot_col-sm-12 mt-5">
                    <h4 class="form-head form-head-bar"><?php echo Text::_('COM_JOOMLAIDP_ROLE_RESTRICTION'); ?><div class="mo_tooltip"><img class="crown_img_small mo_idp_ml_px" src="<?php echo Uri::base();?>/components/com_joomlaidp/assets/images/crown.webp"><span class="mo_tooltiptext small"><?php echo Text::sprintf('COM_JOOMLAIDP_AVAILABLE',$licensing_page_link); ?></span></div></h4>
                    <div class="alert alert-info">
                        <span ms-1><?php echo Text::_('COM_JOOMLAIDP_ROLE_RESTRICTION_INFO'); ?>  </span>           
                    </div>

                    <div class="mo_boot_p-4">
                        <table class='customtemp'>
                            <thead>
                                <tr>
                                    <th class="mo_table_td_style" width="1%">Sr.No</th>
                                    <th class="mo_table_td_style" width="15%"><?php echo Text::_('COM_JOOMLAIDP_MULTISAMLIDPS_IDENTIFIER'); ?></th>
                                    <th class="mo_table_td_style" width="43%"><?php echo Text::_('COM_JOOMLAIDP_MULTISAMLIDPS_ISSUER') ?></th>
                                    <th class="mo_table_td_style" width="15%"><?php echo Text::_('COM_JOOMLAIDP_ROLE_RESTRICTION_STATUS') ?></th>
                                </tr>
                            </thead>
                            <?php 
                             if ($sp_name){
                                ?>
                                    <tr>
                                        <td class="mo_table_td_style">1</td>
                                        <td class="mo_table_td_style"><?php echo  $sp_name; ?></td>
                                        <td class="mo_table_td_style"><?php echo  $sp_entityid; ?></td>
                                        <td class="mo_table_td_style"><?php echo Text::_('COM_JOOMLAIDP_NOT_CONFIGURED') ?></td>
                                    </tr>
                                <?php
                            }
                            ?> 
                        </table>
                    </div>
                </div>
                <div class="mo_boot_col-sm-12 mo_boot_mt-5">
                    <h4 class="form-head form-head-bar"><?php echo Text::_('COM_JOOMLAIDP_RELAY_RESTRICTION'); ?><div class="mo_tooltip"><img class="crown_img_small mo_idp_ml_px" src="<?php echo Uri::base();?>/components/com_joomlaidp/assets/images/crown.webp"><span class="mo_tooltiptext small"><?php echo Text::sprintf('COM_JOOMLAIDP_AVAILABLE',$licensing_page_link); ?></span></div></h4>
                    <div class="alert alert-info">
                        <span ms-1><?php echo Text::_('COM_JOOMLAIDP_RELAY_RESTRICTION_INFO'); ?>  </span>           
                    </div>
                    <div class="mo_boot_p-4">
                        <table class='customtemp'>
                            <thead>
                                <tr>
                                    <th class="mo_table_td_style" width="1%">Sr.No</th>
                                    <th class="mo_table_td_style" width="15%"><?php echo Text::_('COM_JOOMLAIDP_MULTISAMLIDPS_IDENTIFIER'); ?></th>
                                    <th class="mo_table_td_style" width="43%"><?php echo Text::_('COM_JOOMLAIDP_MULTISAMLIDPS_ISSUER') ?></th>
                                    <th class="mo_table_td_style" width="15%"><?php echo Text::_('COM_JOOMLAIDP_RELAY_RESTRICTION_STATUS') ?></th>
                                </tr>
                            </thead>
                            <?php 
                             if ($sp_name){
                                ?>
                                    <tr>
                                        <td class="mo_table_td_style">1</td>
                                        <td class="mo_table_td_style"><?php echo  $sp_name; ?></td>
                                        <td class="mo_table_td_style"><?php echo  $sp_entityid; ?></td>
                                        <td class="mo_table_td_style"><?php echo Text::_('COM_JOOMLAIDP_NOT_CONFIGURED') ?></td>
                                    </tr>
                                <?php
                            }
                            ?> 
                        </table>
                    </div>
                    <div class="mo_boot_row mo_boot_mt-5">
                        <div class="mo_boot_col-sm-12 text-center">
                            <input type="submit" class="btn btn_cstm mb-4" disabled value="<?php echo Text::_('COM_JOOMLAIDP_CLICK_TO_CONFIGURE'); ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php
    }

    public static function showPluginOverview()
    {
        ?>
            <div class="mo_boot_col-sm-12 mo_boot_m-0 mo_boot_p-2 mo_tab_border" >
                <section class="mo_saml_section mo_saml_dark_bg">
                    <div class="mo_saml_circle"></div>
                    <div class="mo_saml_content mo_boot_m-0 mo_boot_col-sm-7">
                        <div class="mo_boot_text_box mo_idp_heading_plugin">
                        <h2><?php echo Text::_('COM_MINIORANGE_IDP_PLUGIN_TITLE'); ?></h2>
                        <p class="mo_idp_heading_desc">
                                <?php 
                                    echo Text::_('COM_JOOMLAIDP_OVERVIEW_DESCRIPTION');
                                ?>
                            </p>
                            <div class=" mo_idp_overview_tab">
                            <input type="button" class="btn btn_cstm " target="_blank" onclick="window.open('https://plugins.miniorange.com/joomla-idp-saml-sso')" value="<?php echo Text::_('COM_MINIORANGE_VISIT_SITE'); ?>" />
                            <input type="button" class="btn btn_cstm " target="_blank" onclick="window.open('https://plugins.miniorange.com/joomla-sso-ldap-mfa-solutions?section=saml-idp')" value="<?php echo Text::_('COM_MINIORANGE_SAML_IDP_GUIDES'); ?>" />
                            <a class="btn btn_cstm " href="<?php echo Route::_('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=sp')?>"><?php echo Text::_('COM_MINIORANGE_SAML_IDP_CONFIG'); ?></a>
                            <a class="btn btn_cstm " href="<?php echo Route::_('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=license')?>"><?php echo Text::_('COM_MINIORANGE_LICENSE_PLANS'); ?></a>
                             </div>                        
                        </div>
                    </div>
                    <div class="mo_saml_imgBox">
                        <img class="mo_idp_img_overview mo_boot_w-100 mo_boot_h-auto" style="max-width: 500px; height: auto;" src="<?php echo Uri::base();?>/components/com_joomlaidp/assets/images/overview_tab.png">
                    </div>
                </section>
            </div>	
        <?php
    }

    public static function showIDPInitiatedLoginDetails()
    {
        $site_url = Uri::root();
        $attribute = IDP_Utilities::fetchDatabaseValues('#__miniorangesamlidp', 'loadAssoc','*');
        $sp_name = isset($attribute['sp_name']) ? $attribute['sp_name'] : '';
        $main_menu_link=Uri::base().'index.php?option=com_menus&view=items&menutype=mainmenu';
        $base_url = Uri::root();
        $current_admin_login_url = $base_url . 'administrator';
        $custom_admin_loign_url = $current_admin_login_url . '/?your_key';
        $licensing_page_link=Uri::base().'index.php?option=com_joomlaidp&view=accountsetup&tab-panel=license';
        ?>

        <div class="mo_boot_col-sm-12 mo_boot_m-0 mo_boot_p-0">
            <div class="mo_boot_row mo_tab_border mo_boot_p-2 mo_boot_m-0">

                <div class="mo_boot_col-sm-12 mo_boot_mt-3">
                    <div class="mo_boot_row">
                        <div class="mo_boot_col-lg-5">
                            <h3 class="mo_saml_form_head"><?php echo Text::_('COM_JOOMLAIDP_CHECK_FEATTURES'); ?><div class="mo_tooltip"><img class="crown_img_small mo_idp_ml_px"src="<?php echo Uri::base();?>/components/com_joomlaidp/assets/images/crown.webp"><span class="mo_tooltiptext small"><?php echo Text::sprintf('COM_JOOMLAIDP_UPGRADE_NOTE',$licensing_page_link); ?></span></div></h3>
                        </div>
                    </div>
                </div>
                <div class="mo_boot_col-sm-12">
                    <div class="mo_boot_row mo_boot_mt-4">
                        <div class="vtab mo_boot_col-sm-3">
                            <button class="vtab_btn active" onclick="openTab(event, 'vaddon1')" id="defaultTab"><?php echo Text::_('COM_JOOMLAIDP_IDP_INITIATED'); ?></button>
                            <button class="vtab_btn" onclick="openTab(event, 'vaddon2')"><?php echo Text::_('COM_JOOMLAIDP_CUSTOMIZED_URL'); ?></button>
                            <button class="vtab_btn" onclick="openTab(event, 'vaddon3')"><?php echo Text::_('COM_JOOMLAIDP_GENERATE_CUSTOM_CERT'); ?></button>
                        </div>
                        
                        <div class="vtab-box mo_boot_col-sm-9 mo_saml_dark_both pb-5">
                            <div class="vtab_content mo_idp_disp" id="vaddon1">
                                <h4 class="vheader"><?php echo Text::_('COM_JOOMLAIDP_ADD_LINK'); ?></h4>
                                <div class="mo_boot_offset-1 mt-4"><?php echo Text::sprintf('COM_JOOMLAIDP_ACCOUNTSETUP_INSTRUCTIONS1',$main_menu_link); ?></div>
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
                                                <td class="mo_table_td_style"><?php echo  $sp_name; ?></td>
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
                                                <span class="mo_saml_slider"></span>
                                            </label>
                                            <span class="small"><strong><?php echo Text::_('COM_MINIORANGE_SAML_NOTE'); ?>: </strong><?php echo Text::_('COM_JOOMLAIDP_ADMIN_LOGIN_ENABLE_NOTE'); ?></span>
                                        </div>
                                    </div>
                                    <div class="mo_boot_row  mo_boot_mt-4">
                                        <div class="mo_boot_col-sm-4">
                                            <span class="saml_idp_label_css"><?php echo Text::_('COM_JOOMLAIDP_ADMIN_ACCESS'); ?></span>
                                        </div>
                                        <div class="mo_boot_col-sm-8">
                                            <input class="form-control" type="text" placeholder="<?php echo Text::_('COM_JOOMLAIDP_ENTER_KEY'); ?>" disabled="disable"/>
                                        </div>
                                    </div>
                                    <div class="mo_boot_row  mo_boot_mt-4">
                                        <div class="mo_boot_col-sm-4">
                                            <span class="saml_idp_label_css"><?php echo Text::_('COM_JOOMLAIDP_CURRENT_ADMIN_URL'); ?></span>
                                        </div>
                                        <div class="mo_boot_col-sm-8 text-wrap">
                                            <div disabled="disable"><?php echo $current_admin_login_url; ?></div>
                                        </div>
                                    </div>
                                    <div class="mo_boot_row  mo_boot_mt-4">
                                        <div class="mo_boot_col-sm-4">
                                            <span class="saml_idp_label_css"> <?php echo Text::_('COM_JOOMLAIDP_CUSTOM_ADMIN_URL'); ?></span>
                                        </div>
                                        <div class="mo_boot_col-sm-8 text-wrap">
                                            <div id="custom_admin_url" disabled="disable"><?php echo $custom_admin_loign_url ?></div>
                                        </div>
                                    </div>
                                    <div class="mo_boot_row  mo_boot_mt-4">
                                        <div class="mo_boot_col-sm-4">
                                            <span class="saml_idp_label_css"> <?php echo Text::_('COM_JOOMLAIDP_REDIRECT_AFTER_FAILURE'); ?></span>
                                        </div>
                                        <div class="mo_boot_col-sm-8">
                                            <select class="mo_boot_form-control" id="failure_response" readonly>
                                                <option> <?php echo Text::_('COM_JOOMLAIDP_HOMEPAGE'); ?></option>
                                                <option> <?php echo Text::_('COM_JOOMLAIDP_CUSTOM_REDIRECT'); ?></option>
                                                <option> <?php echo Text::_('COM_JOOMLAIDP_CUSTOM_REDIRECT_ONE'); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mo_boot_row  mo_boot_mt-4">
                                        <div class="mo_boot_col-sm-4">
                                            <span class="saml_idp_label_css"><?php echo Text::_('COM_JOOMLAIDP_CUSTOM_REDIRECT_AFTER_FAILURE'); ?></span>
                                        </div>
                                        <div class="mo_boot_col-sm-8">
                                            <input class="mo_boot_form-control" disabled type="text"/>
                                        </div>
                                    </div>
                                    <div class="mo_boot_row  mo_boot_mt-4" id="custom_message">
                                        <div class="mo_boot_col-sm-4">
                                            <span class="saml_idp_label_css"><?php echo Text::_('COM_JOOMLAIDP_CUSTOM_ERROR'); ?></span>
                                        </div>
                                        <div class="mo_boot_col-sm-8">
                                            <textarea  class="mo_boot_form-control" disabled></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="mo_boot_col-sm-12  mo_boot_mt-4  mo_boot_text-center">
                                    <input type="submit" class="btn btn_cstm" value="<?php echo Text::_('COM_JOOMLAIDP_SAVE_BTN'); ?>" disabled/>
                                </div>
                            </div>

                            <div class="vtab_content mo_idp_disp_no" id="vaddon3">
                                <h4 class="vheader"><?php echo Text::_('COM_JOOMLAIDP_GENERATE_CUSTOM_CERT'); ?></h4>
                                <div class="mo_boot_col-sm-12 mo_boot_mt-3 mo_boot_mo_idp_disp_no" id="generate_certificate_form">
                                    <div class="mo_boot_row mo_boot_mt-4">
                                        <div class="mo_boot_col-sm-3">
                                            <?php echo Text::_('COM_JOOMLAIDP_COUNTRY_CODE'); ?><span class="mo_saml_required">*</span> :
                                        </div>
                                        <div class="mo_boot_col-sm-8">
                                            <input class="mo_boot_form-control" type="text"  placeholder=" <?php echo Text::_('COM_JOOMLAIDP_ENTER_CODE'); ?>" disabled>
                                        </div>
                                    </div>
                                    <div class="mo_boot_row mt-3">
                                        <div class="mo_boot_col-sm-3">
                                            <?php echo Text::_('COM_JOOMLAIDP_STATE'); ?><span class="mo_saml_required">*</span> :
                                        </div>
                                        <div class="mo_boot_col-sm-8">
                                            <input class=" mo_boot_form-control" type="text"  placeholder=" <?php echo Text::_('COM_JOOMLAIDP_ENTER_STATE'); ?>" disabled />
                                        </div>
                                    </div>
                                    <div class="mo_boot_row mo_boot_mt-3">
                                        <div class="mo_boot_col-sm-3">
                                            <?php echo Text::_('COM_JOOMLAIDP_COMPANY'); ?><span class="mo_saml_required">*</span> :
                                        </div>
                                        <div class="mo_boot_col-sm-8">
                                            <input  class=" mo_boot_form-control" type="text"  placeholder=" <?php echo Text::_('COM_JOOMLAIDP_ENTER_COMPANY'); ?>" disabled />
                                        </div>
                                    </div>
                                    <div class="mo_boot_row mo_boot_mt-3">
                                        <div class="mo_boot_col-sm-3">
                                            <?php echo Text::_('COM_JOOMLAIDP_UNIT'); ?><span class="mo_saml_required">*</span> :
                                        </div>
                                        <div class="mo_boot_col-sm-8">
                                            <input  class=" mo_boot_form-control" type="text" placeholder=" <?php echo Text::_('COM_JOOMLAIDP_UNIT_INFO'); ?>" disabled />
                                        </div>
                                    </div>
                                    <div class="mo_boot_row mo_boot_mt-3">
                                        <div class="mo_boot_col-sm-3">
                                            <?php echo Text::_('COM_JOOMLAIDP_COMMON'); ?><span class="mo_saml_required">*</span> :
                                        </div>
                                        <div class="mo_boot_col-sm-8">
                                            <input  class="mo_boot_form-control" type="text" placeholder=" <?php echo Text::_('COM_JOOMLAIDP_COMMON_NAME'); ?>" disabled />
                                        </div>
                                    </div>
                                    <div class="mo_boot_row mo_boot_mt-3">
                                        <div class="mo_boot_col-sm-3">
                                            <?php echo Text::_('COM_JOOMLAIDP_DIGEST'); ?><span class="mo_saml_required">*</span> :
                                        </div>
                                        <div class="mo_boot_col-sm-8">
                                            <select class="mo_boot_form-control" readonly>                            
                                                <option>SHA512</option>
                                                <option>SHA384</option>
                                                <option>SHA256</option>
                                                <option>SHA1</option>                            
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
                                                <option>1024 bits</option>                                                               
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
                                                <option>180 <?php echo Text::_('COM_JOOMLAIDP_DAYS'); ?></option>                                                                                               
                                                <option>90 <?php echo Text::_('COM_JOOMLAIDP_DAYS'); ?></option>                                                                                               
                                                <option>45 <?php echo Text::_('COM_JOOMLAIDP_DAYS'); ?></option>                                                                                               
                                                <option>30 <?php echo Text::_('COM_JOOMLAIDP_DAYS'); ?></option>                                                                                               
                                                <option>15 <?php echo Text::_('COM_JOOMLAIDP_DAYS'); ?></option>                                                                                               
                                                <option>7 <?php echo Text::_('COM_JOOMLAIDP_DAYS'); ?></option>                                                                                               
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mo_boot_row mo_boot_text-center mo_boot_mt-3">
                                        <div class="mo_boot_col-sm-12">
                                            <input type="submit" value=" <?php echo Text::_('COM_JOOMLAIDP_SAML_SELF_SIGNED'); ?>" disabled class="btn btn_cstm"; />
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
                                                    <textarea disabled="disabled" rows="5" cols="100" class="mo_saml_table_textbox w-100 mb-5"></textarea>
                                                </div>
                                            </div>
                                            <div class="mo_boot_row custom_certificate_table"  >
                                                <div class="mo_boot_col-sm-3">
                                                        <?php echo Text::_('COM_JOOMLAIDP_SAML_PRIVATE_CRT'); ?>
                                                        <span class="mo_saml_required">*</span>
                                                </div>
                                                <div class="mo_boot_col-sm-8">
                                                    <textarea disabled="disabled" rows="5" cols="100" class="mo_saml_table_textbox w-100"></textarea>
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

    public static function requestfordemo()
    {
        $current_user = Factory::getUser();
        $customerResult = IDP_Utilities::fetchDatabaseValues('#__miniorange_saml_idp_customer', 'loadAssoc', array('*'));
        $admin_email = isset($customerResult['email']) ? $customerResult['email'] : '';
        if ($admin_email == '') $admin_email = $current_user->email;
        
        ?>
            <div class="mo_boot_col-sm-12 mo_boot_m-0 mo_boot_p-0">
                <div class="mo_boot_row mo_tab_border mo_boot_p-2 mo_boot_m-0">
                    <div class="mo_boot_col-sm-12 mo_boot_mt-3">
                        <div class="mo_boot_row">
                            <div class="mo_boot_col-lg-5">
                                <h3 class="mo_saml_form_head"><?php echo Text::_('COM_JOOMLAIDP_DEMO_HEADER'); ?></h3>
                            </div>
                        </div>
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
                                            <?php echo Text::_('COM_JOOMLAIDP_LOGIN_PAGE_EMAIL'); ?>:
                                        </div>
                                        <div class="mo_boot_col-lg-8">
                                            <input type="email" class="mo_form-control mo_idp_border" name="email" value="<?php echo $admin_email; ?>" placeholder="person@example.com" required />
                                        </div>
                                    </div>
                                </div>
                                <div class="mo_boot_offset-1 mt-4">
                                    <div class="mo_boot_row">
                                        <div class="mo_boot_col-lg-3">
                                            <?php echo Text::_('COM_JOOMLAIDP_REQUEST_FOR'); ?>:
                                        </div>
                                        <div class="mo_boot_col-lg-4">
                                            <label><input type="radio" name="demo"  value="7 days trial" CHECKED><?php echo Text::_('COM_JOOMLAIDP_TRIAL'); ?></label>
                                        </div>
                                        <div class="mo_boot_col-lg-4">
                                            <label><input type="radio" name="demo"  value="demo" ><?php echo Text::_('COM_JOOMLAIDP_DEMO'); ?></label>
                                        </div>
                                    </div>
                                </div>
                                <div class="mo_boot_offset-1 mo_boot_mt-4 ">
                                    <div class="mo_boot_row">
                                        <div class="mo_boot_col-lg-3">
                                            <?php echo Text::_('COM_JOOMLAIDP_REQUESTED_PLUGIN'); ?>:
                                        </div>
                                        <div class="mo_boot_col-lg-8">
                                            <select required class="mo_form-control mo_idp_border" name="plan">
                                                <option disabled selected  class="mo_idp_select_demo">----------------------- <?php echo Text::_('COM_JOOMLAIDP_SELECT'); ?> -----------------------</option>
                                                <option value="Joomla SAML IDP Premium Plugin">Joomla SAML IDP Premium Plugin</option>
                                                <option value="Not Sure"><?php echo Text::_('COM_JOOMLAIDP_NOT_SURE'); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="mo_boot_offset-1 mo_boot_mt-4 ">
                                    <div class="mo_boot_row">
                                        <div class="mo_boot_col-lg-3">
                                            <?php echo Text::_('COM_JOOMLAIDP_DESCRIPTION'); ?>:
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
        $licensing_page_link=Uri::base().'index.php?option=com_joomlaidp&view=accountsetup&tab-panel=license';
        if (isset($attribute['sp_entityid']) && !empty($attribute['sp_entityid'])) {
            $nameid_attribute = $attribute['nameid_attribute'];
            $disabled=" ";
        }else 
        {
            $nameid_attribute ='';
            $disabled="disabled";
        }
        ?>
        <div class="mo_boot_col-sm-12 mo_boot_m-0 mo_boot_p-0">
            <div class="mo_boot_row mo_tab_border mo_boot_p-2 mo_boot_m-0">
                <div class="mo_boot_col-sm-12 mo_boot_mt-3">
                    <div class="mo_boot_row">
                        <div class="mo_boot_col-lg-5">
                            <h3 class="mo_saml_form_head"><?php echo Text::_('COM_JOOMLAIDP_ATTRIBUTE_MAPPING'); ?></h3>
                        </div>
                    </div>
                </div>
                <form action="<?php echo Route::_('index.php?option=com_joomlaidp&view=accountsetup&task=accountsetup.updateNameId'); ?>" name="updateNameId" method="post"enctype="multipart/form-data">
                    <div class="mo_boot_col-sm-12 mo_boot_mt-5">
                        <div class="mo_boot_row">
                            <div class="mo_boot_col-sm-3">
                                <span class="mo_boot_offset-lg-3"><?php echo  Text::_('COM_JOOMLAIDP_ATTRIBUTE_NAMEID'); ?></span>
                            </div>
                            <div class="mo_boot_col-sm-6">
                                <select id="nameid_attribute" name="nameid_attribute" class="mo_form-control mo_idp_form_control">
                                    <option value="emailAddress" <?php if ($nameid_attribute == 'emailAddress') echo 'selected = "selected"'; ?>>emailAddress</option>
                                    <option value="username" <?php if ($nameid_attribute == 'username') echo 'selected = "selected"'; ?>>username</option>
                                </select>
                                <span class="small"><strong><?php echo Text::_('COM_MINIORANGE_SAML_NOTE'); ?>: </strong><?php echo Text::_('COM_JOOMLAIDP_ATTRIBUTE_MAPPING_INFO'); ?></span>
                            </div>
                            <div class="mo_boot_col-sm-2">
                                <input type="submit" class="btn btn_cstm" value="<?php echo Text::_('COM_JOOMLAIDP_SAVE_BTN'); ?>" <?php echo $disabled ?>/>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="mo_boot_col-sm-12 mo_boot_mt-5">
                    <h4 class="form-head form-head-bar"><?php echo Text::_('COM_JOOMLAIDP_SAML_BASIC_ATTRIBUTE_MAPPING'); ?><div class="mo_tooltip"><img class="crown_img_small mo_idp_ml_px" src="<?php echo Uri::base();?>/components/com_joomlaidp/assets/images/crown.webp"><span class="mo_tooltiptext small"><?php echo Text::sprintf('COM_JOOMLAIDP_UPGRADE_NOTE',$licensing_page_link); ?></span></div></h4>
                    <div class="alert alert-info">
                        <?php echo Text::_('COM_JOOMLAIDP_SAML_ATTRIBUTE_MAPPING_NOTE'); ?>         
                    </div>
                    <div class="mo_boot_col-sm-12 mo_boot_mt-5">
                        <div class="mo_boot_row">
                            <?php
                                for($icnt = 1; $icnt <= 5; $icnt++)
                                {
                                    ?>
                                    <div class="mo_boot_col-sm-6">
                                            <div class="mo_boot_row">
                                                <div class="mo_boot_col-sm-4">
                                                    <b><?php echo Text::_('COM_JOOMLAIDP_ATTRIBUTE'); ?> <?php echo $icnt ?> <?php echo Text::_('COM_JOOMLAIDP_NAME'); ?>:</b>
                                                </div>
                                                <div class="mo_boot_col-sm-8">
                                                    <input type="text" class="mo_saml_idp_textfield mo_form-control" disabled="disabled" placeholder="<?php echo Text::_('COM_JOOMLAIDP_ATTRIBUTE_PLACEHOLDER'); ?>"/>
                                                </div>
                                            </div>
                                    </div>
                                    <div class="mo_boot_col-sm-6">
                                        <div class="mo_boot_row">
                                            <div class="mo_boot_col-sm-4">
                                                <b><?php echo Text::_('COM_JOOMLAIDP_ATTRIBUTE'); ?> <?php echo $icnt;?> <?php echo Text::_('COM_JOOMLAIDP_VALUE'); ?>:</b>
                                            </div>
                                            <div class="mo_boot_col-sm-8">
                                                <select class="mo_saml_idp_textfield mo_form-control" readonly>
                                                    <option value=""><?php echo Text::_('COM_JOOMLAIDP_SELECT_ATTR_VAL'); ?></option>
                                                    <option value="emailAddress"><?php echo Text::_('COM_JOOMLAIDP_EMAIL_ADDRESS'); ?></option>
                                                    <option value="username"><?php echo Text::_('COM_JOOMLAIDP_USERNAME'); ?></option>
                                                    <option value="name"><?php echo Text::_('COM_JOOMLAIDP_NAME'); ?></option>
                                                    <option value="firstname"><?php echo Text::_('COM_JOOMLAIDP_FNAME'); ?></option>
                                                    <option value="lastname"><?php echo Text::_('COM_JOOMLAIDP_LNAME'); ?></option>
                                                    <option value="groups"><?php echo Text::_('COM_JOOMLAIDP_GROUPS'); ?></option>
                                                </select>
                                            </div>
                                        </div><br>
                                    </div>
                                    <?php
                                }
                            ?>
                        </div>
                    </div>
                </div>
                <div class="mo_boot_col-sm-12 mo_boot_mt-5">
                    <div class="mo_boot_row ml-1">
                        <div class="col-sm-2 col-lg-1">
                            <label class="mo_saml_switch">
                                <input type="checkbox" disabled>
                                <span class="mo_saml_slider"></span>
                            </label>
                        </div>
                        <div class="mo_boot_col-sm-9" class="mo_idp_adv_note">
                            <?php echo Text::_('COM_JOOMLAIDP_COMMOA_SEPERATED'); ?><div class="mo_tooltip"><img class="crown_img_small mo_idp_ml_px" src="<?php echo Uri::base();?>/components/com_joomlaidp/assets/images/crown.webp"><span class="mo_tooltiptext small"><?php echo Text::sprintf('COM_JOOMLAIDP_UPGRADE_NOTE',$licensing_page_link); ?></span></div><br>
                            <span class="small"><strong><?php echo Text::_('COM_MINIORANGE_SAML_NOTE'); ?>: </strong><?php echo Text::_('COM_JOOMLAIDP_SAML_GROUP_MAPPING_CHECKBOX_NOTE'); ?></span>
                        </div>
                    </div>
                </div>
                <div class="mo_boot_col-sm-12 mo_boot_mt-5">
                    <h4 class="form-head form-head-bar"><?php echo Text::_('COM_JOOMLAIDP_ADDITIONAL_USER_ATTRIBUTES'); ?><div class="mo_tooltip"><img class="crown_img_small mo_idp_ml_px" src="<?php echo Uri::base();?>/components/com_joomlaidp/assets/images/crown.webp"><span class="mo_tooltiptext small"><?php echo Text::sprintf('COM_JOOMLAIDP_UPGRADE_NOTE',$licensing_page_link); ?></span></div></h4>
                    <div class="alert alert-info">
                        <?php echo Text::_('COM_JOOMLAIDP_SAML_ATTRIBUTE_PROFILE_MAPPING_NOTE'); ?>         
                    </div>
                    <div class="mo_boot_p-4">
                        <div class="mo_boot_row mo_boot_mt-2">
                            <div class="mo_boot_col-sm-5 mo_boot_text-center">
                                <?php echo Text::_('COM_JOOMLAIDP_SAML_PROFILE_ATTRIBUTE_HEADER'); ?>
                            </div>
                            <div class="mo_boot_col-sm-5 mo_boot_text-center">
                                <?php echo Text::_('COM_JOOMLAIDP_SAML_IDP_PROFILE_ATTRIBUTE'); ?>
                            </div>
                            <div class="mo_boot_col-sm-2 mo_boot_text-center">
                                <input type="button" class="btn btn_cstm mo_group_mapping_btn" disabled value="+" />
                            </div>
                        </div>
                        <div class="mo_boot_row mo_boot_mt-4">
                            <div class="mo_boot_col-sm-5">
                                <input disabled type="text" class="mo_form-control " />
                            </div>
                            <div class="mo_boot_col-sm-5">
                                <input disabled type="text" class="mo_form-control " />
                            </div>
                            <div class="mo_boot_col-sm-2 mo_boot_text-center">
                                <input type="button" class="btn btn_cstm_red mo_group_mapping_btn" disabled value="-" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mo_boot_col-sm-12 mo_boot_mt-5">
                    <h4 class="form-head form-head-bar"><?php echo Text::_('COM_JOOMLAIDP_ADDITIONAL_USER_FIELD_ATTRIBUTES'); ?><div class="mo_tooltip"><img class="crown_img_small mo_idp_ml_px" src="<?php echo Uri::base();?>/components/com_joomlaidp/assets/images/crown.webp"><span class="mo_tooltiptext small"><?php echo Text::sprintf('COM_JOOMLAIDP_UPGRADE_NOTE',$licensing_page_link); ?></span></div></h4>
                    <div class="alert alert-info">
                        <?php echo Text::_('COM_JOOMLAIDP_SAML_ATTRIBUTE_FILED_MAPPING_NOTE'); ?>         
                    </div>
                    <div class="mo_boot_p-4">
                        <div class="mo_boot_row mo_boot_mt-2">
                            <div class="mo_boot_col-sm-5 mo_boot_text-center">
                                <?php echo Text::_('COM_JOOMLAIDP_SAML_FIELD_ATTRIBUTE_HEADER'); ?>
                            </div>
                            <div class="mo_boot_col-sm-5 mo_boot_text-center">
                                <?php echo Text::_('COM_JOOMLAIDP_SAML_IDP_FIELD_ATTRIBUTE'); ?>
                            </div>
                            <div class="mo_boot_col-sm-2 mo_boot_text-center">
                                <input type="button" class="btn btn_cstm mo_group_mapping_btn" disabled value="+" />
                            </div>
                        </div>
                        <div class="mo_boot_row mo_boot_mt-4">
                            <div class="mo_boot_col-sm-5">
                                <input disabled type="text" class=" mo_form-control " />
                            </div>
                            <div class="mo_boot_col-sm-5">
                                <input disabled type="text" class=" mo_form-control " />
                            </div>
                            <div class="mo_boot_col-sm-2 mo_boot_text-center">
                                <input type="button" class="btn btn_cstm_red mo_group_mapping_btn" disabled value="-" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mo_boot_col-sm-10 mo_boot_mt-5">
                    <div class="mo_boot_text-center">
                        <input type="submit" class="btn btn_cstm mb-4" disabled value="<?php echo Text::_('COM_JOOMLAIDP_SAVE_MAPPING'); ?>">
                    </div>
                </div>
            </div>
        </div>

        <?php
    }

    public static function showIdentityProviderConfigurations()
    {
        $site_url = Uri::root();
        $idp_entity_id = $site_url . 'plugins/user/miniorangejoomlaidp/';

        $idpid = IDP_Utilities::fetchDatabaseValues('#__miniorange_saml_idp_customer', 'loadResult','idp_entity_id');

        if (!empty($idpid) && ($idp_entity_id != $idpid))
            $idp_entity_id = $idpid;
        
        ?>
        
        <div class="mo_boot_col-sm-12 mo_boot_m-0 mo_boot_p-0">
            <div class="mo_boot_row mo_tab_border mo_boot_p-0 mo_boot_m-0">
                <div class="mo_boot_col-sm-12 mo_boot_mt-3">
                    <div class="mo_boot_row">
                        <div class="mo_boot_col-lg-5">
                            <h3 class="mo_saml_form_head"><?php echo Text::_('COM_JOOMLAIDP_IDP_METADATA'); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="mo_boot_col-sm-12 mo_boot_mt-5">
                    <h4 class="form-head form-head-bar"><?php echo Text::_('COM_JOOMLAIDP_PROVIDE_METADATA'); ?></h4>
                    <div class="mo_boot_row mo_boot_mt-4">
                        <div class="mo_boot_col-sm-3 mo_boot_col-lg-2">
                            <span ><?php echo Text::_('COM_JOOMLAIDP_METADATA_URL'); ?> :</span>
                        </div>
                        <div class="mo_boot_col-sm-9 text-wrap">
                             <span id="idp_metadata_url" class=" mo_saml_highlight_background_url_note mo_saml_dark_bg " >
                                 <em class="fa fa-lg fa-copy mo_idp_copy_btns mo_boot_p-3" onclick="copyToClipboard('#idp_metadata_url');" title="Copy to clipboard"></em>
                                 <a class="mo_idp_metadata_link" href='<?php echo Uri::root() . 'plugins/system/joomlaidplogin/saml2idp/metadata/metadata.php' ; ?>' id='metadata-linkss' target='_blank'><?php echo '<strong>' . Uri::root() . 'plugins/system/joomlaidplogin/saml2idp/metadata/metadata.php </strong>'; ?></a>
                             </span> 
                        </div> 
                    </div>
                  
                    <div class="mo_boot_row mo_boot_mt-4">
                        <div class="mo_boot_col-sm-3 mo_boot_col-lg-2">
                            <span class="mo_boot_mo_boot-ml-5"><?php echo Text::_('COM_JOOMLAIDP_METADATA_FILE'); ?> :</span>
                        </div>
                        <div class="mo_boot_col-sm-7 ">
                            <a href="<?php echo  Uri::root() . 'plugins/system/joomlaidplogin/saml2idp/metadata/metadata.php?download=true'; ?>" class="btn btn_cstm anchor_tag">
                                <?php echo Text::_('COM_JOOMLAIDP_DOWNLOAD_METADATA'); ?>   
                            </a>
                        </div>
                    </div>
                    <div class="mo_boot_mt-5 ">
                        <div class="mo_boot_text-center metadata_or" >
                            <div class="mo_idp_ip_or">
                                <span class="mo_saml_rounded_circle mo_boot_p-2" ><?php echo Text::_('COM_JOOMLAIDP_OR'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mo_boot_col-sm-12 mo_boot_mt-5">
                    <h4 class="form-head form-head-bar"><?php echo Text::_('COM_JOOMLAIDP_METADATA_OPTIONS'); ?></h4>
                    <div id="mo_other_idp" class="p-4">
                        <table class='customtemp'>
                            <tr>
                                <td class="mo_table_td_style"><?php echo Text::_('COM_JOOMLAIDP_ACCOUNTSETUP_ISSUER'); ?></td>
                                <td><span id="issuer"><?php echo $idp_entity_id; ?></span>
                                    <em class="fa fa-pull-right  fa-lg fa-copy mo_copy mo_copytooltip mo_boot_p-3" 
                                        onclick="copyToClipboard('#issuer');"><span class="mo_copytooltiptext copied_text mo_boot_p-2"><?php echo Text::_('COM_JOOMLAIDP_COPY_BTN'); ?></span></em>
                                </td>
                            </tr>
                            <tr>
                                <td class="mo_table_td_style"><?php echo Text::_('COM_JOOMLAIDP_ACCOUNTSETUP_SAML_LOGIN'); ?></td>
                                <td>
                                    <span id="login_url"><?php echo $site_url . 'index.php';  ?></span>
                                    <em class="fa fa-pull-right  fa-lg fa-copy mo_copy mo_copytooltip mo_boot_p-3" onclick="copyToClipboard('#login_url');"><span class="mo_copytooltiptext copied_text mo_boot_p-2"><?php echo Text::_('COM_JOOMLAIDP_COPY_BTN'); ?></span> </em>
                                </td>
                            </tr>
                            <tr>
                                <td class="mo_table_td_style"><?php echo Text::_('COM_JOOMLAIDP_ACCOUNTSETUP_CERTIFICATE'); ?></td>
                                <td>
                                    <?php echo Text::_('COM_JOOMLAIDP_DOWNLOAD_CRT'); ?>
                                    <a class="btn metadata_btn_cstm anchor_tag "  href="<?php echo Uri::root() . 'plugins/system/joomlaidplogin/saml2idp/cert/idp-signing.crt'; ?>"><i class="fa fa-download" aria-hidden="true"></i></a>
                                </td>
                            </tr>
                            <tr>
                                <td class="mo_table_td_style">
                                    <?php echo Text::_('COM_JOOMLAIDP_ACCOUNTSETUP_SAML_LOGOUT'); ?>
                                </td>
                                <td>
                                    <a href="index.php?option=com_joomlaidp&amp;view=accountsetup&amp;tab-panel=license"><b><?php echo Text::_('COM_JOOMLAIDP_PREMIUM_FEATURE'); ?></b></a>
                                    <img class="crown_img_small mo_idp_crown_pos" src="<?php echo Uri::base();?>/components/com_joomlaidp/assets/images/crown.webp">
                                </td>
                            </tr>
                            <tr>
                                <td class="mo_table_td_style">
                                    <?php echo Text::_('COM_JOOMLAIDP_ACCOUNTSETUP_ASSERTION_SIGNED'); ?>
                                </td>
                                <td>
                                <a href="index.php?option=com_joomlaidp&amp;view=accountsetup&amp;tab-panel=license"><b><?php echo Text::_('COM_JOOMLAIDP_PREMIUM_FEATURE'); ?></b></a>
                                    <img class="crown_img_small mo_idp_crown_pos" src="<?php echo Uri::base();?>/components/com_joomlaidp/assets/images/crown.webp">
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="mo_boot_col-sm-12">
                    <div class="metadata_or mo_idp_ip_or" ></div>
                </div>
                <div class="mo_boot_col-sm-12 mt-5">
                    <h4 class="form-head form-head-bar "><?php echo Text::_('COM_JOOMLAIDP_SAML_UPDATE_ENTITY'); ?></h4>
                    <form action="<?php echo Route::_('index.php?option=com_joomlaidp&view=accountsetup&task=accountsetup.updateIdpEntityId'); ?>" method="post" name="updateissueer" id="identity_provider_update_form">
                        <div class="mo_boot_row mo_boot_mt-4">
                            <div class="mo_boot_col-sm-2  mo_boot_ml-5">
                                <span class="mo_boot_ml-5"><?php echo Text::_('COM_JOOMLAIDP_ACCOUNTSETUP_ISSUER'); ?> :</span>
                            </div>
                            <div class="mo_boot_col-sm-8">
                                <input class=" mo_form-control mo_saml_proxy_setup" type="text" name="mo_saml_idp_entity_id" value="<?php echo $idp_entity_id; ?>" placeholder="<?php echo Text::_('COM_JOOMLAIDP_ISSUER_OF_IDP'); ?>" required />
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
        </div>
     
        <?php
    }


    public function showLicensingPlanDetails()
    {
        $isRegistered = MoSamlIdpUtility::is_customer_registered();
        $result       = IDP_Utilities::fetchDatabaseValues('#__miniorange_saml_idp_customer', 'loadAssoc','*');
        $userEmail  = isset($result['email']) ? $result['email'] : '';
        $upgradeURL = " https://portal.miniorange.com/initializePayment?requestOrigin=joomla_saml_idp_premium_plan";
        $newTab = '_blank';
        $circle_icon = '
        <svg class="min-w-[8px] min-h-[8px]" width="8" height="8" viewBox="0 0 18 18" fill="none">
            <circle id="a89fc99c6ce659f06983e2283c1865f1" cx="9" cy="9" r="7" stroke="rgb(99 102 241)" stroke-width="4"></circle>
        </svg>
         ';
         
        ?> 
        
        <div class="mo_boot_col-sm-12 mo_boot_m-0 mo_boot_p-0">
        <div class="mo_boot_row mo_tab_border mo_boot_p-2 mo_boot_m-0">
            <div class="mo_boot_col-sm-12 mo_boot_mt-3">
                <div class="mo_boot_row">
                    <div class="mo_boot_col-lg-5">
                        <h3 class="mo_saml_form_head"><?php echo Text::_('COM_JOOMLAIDP_LICENSING_HEADER'); ?></h3>
                    </div>
                </div>
            </div>
    
        <div id="mo_saml_pricing_page" class="mo_idp_pricing_page mo_boot_col-sm-12 my-2">
            <div class="mo_boot_row mo_idp_pricing_snippet_grid justify-content-center">
                <div class="mo_idp_pricing_card">

                        <h5 class="mo_idp_free_plan"><?php echo Text::_('COM_JOOMLAIDP_FREE_PLAN'); ?></h5>
                               
                               <h1 class="mo_boot_p-0 mo_boot_m-1">$0<span class="corner-star">*</span></h1>
                
                   <div class="mo_idp_txt_center mo_boot_mt-4">
                       <a href="#"
                           class="upgrade_button mo_idp_license_btns"><?php echo Text::_('COM_JOOMLAIDP_ACTIVE_PLAN'); ?></a>
                   </div>
                   <ul class="mt-mo-4 grow mo_idp_license_point mo_idp_first_Plan mo_boot_mt-5">
                   <li class="mo_saml_feature_snippet"><span><?php echo $circle_icon; ?></span><span class="mo_idp_upgrade_feature"><?php echo Text::_('COM_MINIORANGE_IDP_FREE_NOW_DESC_A'); ?></span></li>
                   <li class="mo_saml_feature_snippet"><span><?php echo $circle_icon; ?></span><span class="mo_idp_upgrade_feature"><?php echo Text::_('COM_MINIORANGE_IDP_FREE_NOW_DESC_B'); ?></span></li>
                   <li class="mo_saml_feature_snippet"><span><?php echo $circle_icon; ?></span><span class="mo_idp_upgrade_feature"><?php echo Text::_('COM_MINIORANGE_IDP_FREE_NOW_DESC_C'); ?></span></li>
                   <li class="mo_saml_feature_snippet"><span><?php echo $circle_icon; ?></span><span class="mo_idp_upgrade_feature"><?php echo Text::_('COM_MINIORANGE_IDP_FREE_NOW_DESC_D'); ?></span></li>
                   <li class="mo_saml_feature_snippet"><span><?php echo $circle_icon; ?></span><span class="mo_idp_upgrade_feature"><?php echo Text::_('COM_MINIORANGE_IDP_FREE_NOW_DESC_E'); ?></span></li>
                   <li class="mo_saml_feature_snippet"><span><?php echo $circle_icon; ?></span><span class="mo_idp_upgrade_feature"><?php echo Text::_('COM_MINIORANGE_IDP_FREE_NOW_DESC_F'); ?></span></li>
                   <li class="mo_saml_feature_snippet"><span><?php echo $circle_icon; ?></span><span class="mo_idp_upgrade_feature"><?php echo Text::_('COM_MINIORANGE_IDP_FREE_NOW_DESC_G'); ?></span></li>
                   <li class="mo_saml_feature_snippet"><span><?php echo $circle_icon; ?></span><span class="mo_idp_upgrade_feature"><?php echo Text::_('COM_MINIORANGE_IDP_FREE_NOW_DESC_H'); ?></span></li>                       
                   </ul>
                        </div>
                        <div class=" mo_idp_pricing_card">

                        <h5><?php echo Text::_('COM_JOOMLAIDP_PREMIUM_PLAN'); ?></h5>
                        <div>
                        <select name="user-slab" class="mo_boot_col-12 mo_idp_users_details slab_dropdown mo_boot_mt-4 mo_boot_mb-4 mo_boot_p-1">
                                <option value="100" selected><?php echo Text::_('COM_JOOMLAIDP_NO_USERS'); ?>: 100</option>
                                <option value="200"><?php echo Text::_('COM_JOOMLAIDP_NO_USERS'); ?>: 200</option>
                                <option value="300"><?php echo Text::_('COM_JOOMLAIDP_NO_USERS'); ?>: 300</option>
                                <option value="400"><?php echo Text::_('COM_JOOMLAIDP_NO_USERS'); ?>: 400</option>
                                <option value="500"><?php echo Text::_('COM_JOOMLAIDP_NO_USERS'); ?>: 500</option>
                                <option value="750"><?php echo Text::_('COM_JOOMLAIDP_NO_USERS'); ?>: 750</option>
                                <option value="1000"><?php echo Text::_('COM_JOOMLAIDP_NO_USERS'); ?>: 1000</option>
                                <option value="2000"><?php echo Text::_('COM_JOOMLAIDP_NO_USERS'); ?>: 2000</option>
                                <option value="3000"><?php echo Text::_('COM_JOOMLAIDP_NO_USERS'); ?>: 3000</option>
                                <option value="4000"><?php echo Text::_('COM_JOOMLAIDP_NO_USERS'); ?>: 4000</option>
                                <option value="5000"><?php echo Text::_('COM_JOOMLAIDP_NO_USERS'); ?>: 5000</option>
                                <option value="5000p"><?php echo Text::_('COM_JOOMLAIDP_NO_USERS'); ?>: 5000+</option>
                            </select>
                        </div>
    
                            <div class=" mo_boot_row col-12 mo_boot_mt-0">
                                <div class="col-6">
                                <div class="mo_idp_price_slab_100 text-center" id="mo_idp_price_slab1_100">
                                    <span class="price-value mo_idp_premium_value">
                                        <h1 class="p-0 m-1">$199 /year<span class="corner-star">*</span></h1>
                                    </span>
                                </div>
    
                                <div class="mo_idp_price_slab_200 text-center mo_idp_disp_no m-1"
                                    id="mo_idp_price_slab1_200">
                                    <span class="price-value mo_idp_plan_value">
                                        <span class="mo_idp_upfrade_font">$</span>299 /year *
                                    </span>
                                </div>
    
                                <div class="mo_idp_price_slab_300 text-center mo_idp_disp_no m-1"
                                    id="mo_idp_price_slab1_300">
                                    <span class="price-value mo_idp_plan_value">
                                        <span class="mo_idp_upfrade_font">$</span>399 /year *
                                    </span>
                                </div>
    
                                <div class="mo_idp_price_slab_400 text-center mo_idp_disp_no m-1"
                                    id="mo_idp_price_slab1_400">
                                    <span class="price-value mo_idp_plan_value">
                                        <span class="mo_idp_upfrade_font">$</span>499 /year *
                                    </span>
                                </div>
    
                                <div class="mo_idp_price_slab_500 text-center mo_idp_disp_no m-1"
                                    id="mo_idp_price_slab1_500">
                                    <span class="price-value mo_idp_plan_value">
                                        <span class="mo_idp_upfrade_font">$</span>599 /year *
                                    </span>
                                </div>
    
                                <div class="mo_idp_price_slab_750 text-center mo_idp_disp_no m-1"
                                    id="mo_idp_price_slab1_750">
                                    <span class="price-value mo_idp_plan_value">
                                        <span class="mo_idp_upfrade_font">$</span>749 /year *
                                    </span>
                                </div>
    
                                <div class="mo_idp_price_slab_1000 text-center mo_idp_disp_no m-1"
                                    id="mo_idp_price_slab1_1000">
                                    <span class="price-value mo_idp_plan_value">
                                        <span class="mo_idp_upfrade_font">$</span>949 /year *
                                    </span>
                                </div>
    
                                <div class="mo_idp_price_slab_2000 text-center mo_idp_disp_no m-1"
                                    id="mo_idp_price_slab1_2000">
                                    <span class="price-value mo_idp_plan_value">
                                        <span class="mo_idp_upfrade_font">$</span>1549 /year *
                                    </span>
                                </div>
    
                                <div class="mo_idp_price_slab_3000 text-center mo_idp_disp_no m-1"
                                    id="mo_idp_price_slab1_3000">
                                    <span class="price-value mo_idp_plan_value">
                                        <span class="mo_idp_upfrade_font">$</span>2149 /year *
                                    </span>
                                </div>
    
                                <div class="mo_idp_price_slab_4000 text-center mo_idp_disp_no m-1"
                                    id="mo_idp_price_slab1_4000">
                                    <span class="price-value mo_idp_plan_value">
                                        <span class="mo_idp_upfrade_font">$</span>2599 /year *
                                    </span>
                                </div>
    
                                <div class="mo_idp_price_slab_5000 text-center mo_idp_disp_no m-1"
                                    id="mo_idp_price_slab1_5000">
                                    <span class="price-value mo_idp_plan_value">
                                        <span class="mo_idp_upfrade_font">$</span>2999 /year *
                                    </span>
                                </div>
                                </div>
                                <div class="col-6">
                                <div class="text-center mo_boot_mt-3">
                                    <small class="mo_idp_upgrade_feature">Per instance pricing</small>
                                </div>
                                </div>
                            </div>
                                <div class="mo_idp_price_slab_5000p text-center mo_idp_disp_no m-1"
                                    id="mo_idp_price_slab1_5000p">
                                    <a target="_blank" class="upgrade_button mo_idp_plan_value"
                                        href="https://www.miniorange.com/contact"><?php echo Text::_('COM_JOOMLAIDP_CONTACT_US'); ?></a>
    
                                </div>
    
                            
                            <div class="mo_idp_txt_center mo_boot_mt-4">
                                <a class="upgrade_button mo_idp_license_btns" target="<?php echo $newTab;?>"
                                    href="<?php echo $upgradeURL;?>">
                                    <?php echo Text::_('COM_JOOMLAIDP_UPGRADE_NOW'); ?>
                                </a>
                            </div>
    
                            <div class="pricing-content">
                                <ul class=" grow mo_idp_license_point mo_boot_mt-5">
                                    <li class="mo_saml_feature_snippet"><span><?php echo $circle_icon; ?></span><span class="mo_idp_upgrade_feature"><?php echo Text::_('COM_MINIORANGE_IDP_UPGRADE_NOW_DESC_A'); ?></span></li>
                                    <li class="mo_saml_feature_snippet"><span><?php echo $circle_icon; ?></span><span class="mo_idp_upgrade_feature"><?php echo Text::_('COM_MINIORANGE_IDP_UPGRADE_NOW_DESC_B'); ?></span></li>
                                    <li class="mo_saml_feature_snippet"><span><?php echo $circle_icon; ?></span><span class="mo_idp_upgrade_feature"><?php echo Text::_('COM_MINIORANGE_IDP_UPGRADE_NOW_DESC_C'); ?></span></li>
                                    <li class="mo_saml_feature_snippet"><span><?php echo $circle_icon; ?></span><span class="mo_idp_upgrade_feature"><?php echo Text::_('COM_MINIORANGE_IDP_UPGRADE_NOW_DESC_D'); ?></span></li>
                                    <li class="mo_saml_feature_snippet"><span><?php echo $circle_icon; ?></span><span class="mo_idp_upgrade_feature"><?php echo Text::_('COM_MINIORANGE_IDP_UPGRADE_NOW_DESC_E'); ?></span></li>
                                    <li class="mo_saml_feature_snippet"><span><?php echo $circle_icon; ?></span><span class="mo_idp_upgrade_feature"><?php echo Text::_('COM_MINIORANGE_IDP_UPGRADE_NOW_DESC_F'); ?></span></li>
                                    <li class="mo_saml_feature_snippet"><span><?php echo $circle_icon; ?></span><span class="mo_idp_upgrade_feature"><?php echo Text::_('COM_MINIORANGE_IDP_UPGRADE_NOW_DESC_G'); ?></span></li>
                                    <li class="mo_saml_feature_snippet"><span><?php echo $circle_icon; ?></span><span class="mo_idp_upgrade_feature"><?php echo Text::_('COM_MINIORANGE_IDP_UPGRADE_NOW_DESC_H'); ?></span></li>
                                    <li class="mo_saml_feature_snippet"><span><?php echo $circle_icon; ?></span><span class="mo_idp_upgrade_feature"><?php echo Text::_('COM_MINIORANGE_IDP_UPGRADE_NOW_DESC_I'); ?></span></li>
                                    <li class="mo_saml_feature_snippet"><span><?php echo $circle_icon; ?></span><span class="mo_idp_upgrade_feature"><?php echo Text::_('COM_MINIORANGE_IDP_UPGRADE_NOW_DESC_J'); ?></span></li>
                                </ul>

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
            <div class=" mo_boot_col-sm-12 mo_boot_my-4">
                <h4 class="form-head form-head-bar"><?php echo Text::_('COM_JOOMLAIDP_SAML_UPGRADE_HEADER');?></h4>
                <section id="mo_saml_section-steps" class="mo_boot_mt-4">
                    <div class="mo_boot_col-sm-12 mo_boot_row ">
                        <div class=" mo_boot_col-sm-6 mo_works-step mo_idp_faq_page">
                            <div class="mo_boot_pt-1"><strong>1</strong></div>
                            <p><?php echo Text::_('COM_JOOMLAIDP_SAML_UPGRADE_ONE');?></p>
                        </div>
                        <div class="mo_boot_col-sm-6 mo_works-step">
                            <div class="mo_boot_pt-1"><strong>4</strong></div>
                            <p><?php echo Text::_('COM_JOOMLAIDP_SAML_UPGRADE_FOUR');?></p>
                        </div>
                    </div>
    
                    <div class="mo_boot_col-sm-12 mo_boot_row">
                        <div class=" mo_boot_col-sm-6 mo_works-step mo_idp_faq_page">
                            <div class="mo_boot_pt-1"><strong>2</strong></div>
                            <p> <?php echo Text::_('COM_JOOMLAIDP_SAML_UPGRADE_TWo');?> </p>
                        </div>
                        <div class="mo_boot_col-sm-6 mo_works-step">
                            <div class="mo_boot_pt-1"><strong>5</strong></div>
                            <p><?php echo Text::_('COM_JOOMLAIDP_SAML_UPGRADE_FIVE');?> </p>
                        </div>
                    </div>
    
                    <div class="mo_boot_col-sm-12 mo_boot_row ">
                        <div class="mo_boot_col-sm-6 mo_works-step mo_idp_faq_page">
                            <div class="mo_boot_pt-1"><strong>3</strong></div>
                            <p><?php echo Text::_('COM_JOOMLAIDP_SAML_UPGRADE_THREE');?></p>
                        </div>
                        <div class=" mo_boot_col-sm-6 mo_works-step">
                            <div class="mo_boot_pt-1"><strong>6</strong></div>
                            <p><?php echo Text::_('COM_JOOMLAIDP_SAML_UPGRADE_SIX');?></p>
                        </div>
                    </div>
                </section>
            </div>
            <div class=" mo_boot_col-sm-12 mo_boot_my-4">
                <h4 class="form-head form-head-bar"><?php echo Text::_('COM_JOOMLAIDP_SAML_UPGRADE_HEADER_DETAILS_RETURN_POLICY');?></h4>
                <section id="mo_saml_section-steps" class="mo_boot_mt-4">
                    <p><?php echo Text::_('COM_JOOMLAIDP_SAML_UPGRADE_HEADER_DETAILS'); ?></p>
                    <strong class="mo_boot_mt-2"><?php echo Text::_('COM_JOOMLAIDP_SAML_UPGRADE_HEADER_DETAILS_B'); ?></strong>
                        <ol class="mo_boot_mt-1">1.<?php echo Text::_('COM_JOOMLAIDP_SAML_UPGRADE_HEADER_DETAILS_C'); ?> <a href="mailto:joomlasupport@xecurify.com">joomlasupport@xecurify.com</a></ol>
                        <ol>2.<?php echo Text::_('COM_JOOMLAIDP_SAML_UPGRADE_HEADER_DETAILS_D'); ?></ol>
                        <ol class="mo_boot_mb-1">3.<?php echo Text::_('COM_JOOMLAIDP_SAML_UPGRADE_HEADER_DETAILS_E'); ?></ol>
                    <strong class="mo_boot_mt-3"><?php echo Text::_('COM_JOOMLAIDP_SAML_UPGRADE_HEADER_DETAILS_F'); ?></strong>
                        <ol class="mo_boot_mt-1"><?php echo Text::_('COM_JOOMLAIDP_SAML_UPGRADE_HEADER_DETAILS_G'); ?></ol>
                        <ol><?php echo Text::_('COM_JOOMLAIDP_SAML_UPGRADE_HEADER_DETAILS_H'); ?></ol>
                        <ol class="mo_boot_mb-1"><?php echo Text::_('COM_JOOMLAIDP_SAML_UPGRADE_HEADER_DETAILS_I'); ?></ol>
                </section>
            </div>
            <div class=" mo_boot_col-sm-12 mo_boot_my-4">
                <h4 class="form-head form-head-bar"><?php echo Text::_('COM_JOOMLAIDP_FRQUENTLY_ASKED');?></h4>
                <div class="mo_boot_mx-4">
                    <div class="mo_boot_row">
                        <div class="mo_boot_col-sm-6">
                            <h3 class="mo_saml_faq_page"><?php echo Text::_('COM_JOOMLAIDP_FAQ1');?></h3>
                            <div class="mo_saml_faq_body">
                                <p><?php echo Text::_('COM_JOOMLAIDP_FAQ1_DETAILS');?></p>
                            </div>
                            <hr class="mo_saml_hr_line">
                        </div>
    
                        <div class="mo_boot_col-sm-6">
                            <h3 class="mo_saml_faq_page"><?php echo Text::_('COM_JOOMLAIDP_FAQ2');?></h3>
                            <div class="mo_saml_faq_body">
                                <p><?php echo Text::_('COM_JOOMLAIDP_FAQ2_DETAILS');?></p>
                            </div>
                            <hr class="mo_saml_hr_line">
                        </div>
                    </div>
                    <div class="mo_boot_row">
                        <div class="mo_boot_col-sm-6">
                            <h3 class="mo_saml_faq_page"><?php echo Text::_('COM_JOOMLAIDP_FAQ3');?></h3>
                            <div class="mo_saml_faq_body">
                                <p><?php echo Text::_('COM_JOOMLAIDP_FAQ3_DETAILS');?></p>
                            </div>
                            <hr class="mo_saml_hr_line">
                        </div>
    
                        <div class="mo_boot_col-sm-6">
                            <h3 class="mo_saml_faq_page"><?php echo Text::_('COM_JOOMLAIDP_FAQ4');?></h3>
                            <div class="mo_saml_faq_body">
                                <p><?php echo Text::_('COM_JOOMLAIDP_FAQ4_DETAILS');?></p>
                            </div>
                            <hr class="mo_saml_hr_line">
                        </div>
                    </div>
                    <div class="mo_boot_row">
                        <div class="mo_boot_col-sm-6">
                            <h3 class="mo_saml_faq_page"><?php echo Text::_('COM_JOOMLAIDP_FAQ5');?></h3>
                            <div class="mo_saml_faq_body">
                                <p><?php echo Text::_('COM_JOOMLAIDP_FAQ5_DETAILS');?></p>
                            </div>
                            <hr class="mo_saml_hr_line">
                        </div>
                        <div class="mo_boot_col-sm-6">
                            <h3 class="mo_saml_faq_page"><?php echo Text::_('COM_JOOMLAIDP_FAQ6');?></h3>
                            <div class="mo_saml_faq_body">
                                <?php echo Text::_('COM_JOOMLAIDP_FAQ6_DETAILS');?>
                            </div>
                            <hr class="mo_saml_hr_line">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
        <?php
    }


    public static function showServiceProviderConfigurations()
    {
        $attribute = IDP_Utilities::fetchDatabaseValues('#__miniorangesamlidp', 'loadAssoc', '*'); 
        $sp_name = "";
        $sp_entityid = "";
        $acs_url = "";
        $nameid_format = "";
        $nameid_attribute = "";
        $assertion_signed = "";
        $relay_state = "";

        if (isset($attribute['sp_entityid'])) {
            $sp_entityid = $attribute['sp_entityid'];
            $acs_url = $attribute['acs_url'];
            $nameid_format = $attribute['nameid_format'];
            $sp_name = $attribute['sp_name'];
            $nameid_attribute = $attribute['nameid_attribute'];
            $assertion_signed = $attribute['assertion_signed'];
            $relay_state = $attribute['default_relay_state'];
        }

        $setup_guides=json_decode(IDP_Utilities::setupGuides(),true);
        $guide_count = count($setup_guides);
        $isSystemEnabled = PluginHelper::isEnabled('system', 'joomlaidplogin');
        $isUserEnabled = PluginHelper::isEnabled('user', 'miniorangejoomlaidp');
        if (!$isSystemEnabled || !$isUserEnabled) {
            ?>
            <div id="system-message-container">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <div class="alert alert-error">
                    <h4 class="alert-heading"><?php echo Text::_('COM_JOOMLAIDP_MULTISAMLIDPS_WARNING'); ?></h4>
                    <div class="alert-message">
                        <h4><?php echo Text::_('COM_JOOMLAIDP_MULTISAMLIDPS_WARNING_HEADER'); ?></h4>
                        <ul>
                            <li><?php echo Text::_('COM_JOOMLAIDP_MULTISAMLIDPS_WARNING_SYSTEM'); ?></li>
                            <li><?php echo Text::_('COM_JOOMLAIDP_MULTISAMLIDPS_WARNING_USER'); ?></li>
                        </ul>
                        <h4><?php echo Text::_('COM_JOOMLAIDP_MULTISAMLIDPS_WARNING_STEPS'); ?></h4>
                        <ul>
                            <li><?php echo Text::_('COM_JOOMLAIDP_MULTISAMLIDPS_WARNING_STEP1'); ?></li>
                            <li><?php echo Text::_('COM_JOOMLAIDP_MULTISAMLIDPS_WARNING_STEP2'); ?></li>
                            <li><?php echo Text::_('COM_JOOMLAIDP_MULTISAMLIDPS_WARNING_STEP3'); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
            <?php
        }
        ?>
        <div class="mo_boot_col-sm-12 mo_boot_m-0 mo_boot_p-0">
            <div class="mo_boot_row mo_boot_m-0 mo_boot_p-0">
                <div class="mo_boot_col-sm-8 mo_tab_border mo_idp_section">
                    <div class="mo_boot_col-sm-12 mo_boot_p-2">
                        <form action="<?php echo Route::_('index.php?option=com_joomlaidp&view=accountsetup&task=accountsetup.saveServiceProvider'); ?>" method="post" name="adminForm" id="identity_provider_settings_form" enctype="multipart/form-data">
                            <?php echo HTMLHelper::_('form.token'); ?>
                            <input id="mo_saml_local_configuration_form_action" type="hidden" name="option1" value="mo_saml_save_config"/>
                            <div class="mo_boot_row mo_boot_mt-3" >
                                <div class="mo_boot_col-lg-5 mo_boot_col-sm-6">
                                    <h3 class="mo_saml_form_head mo_idp_sp_head"><?php echo Text::_('COM_JOOMLAIDP_SP'); ?></h3>
                                </div>
                                <div class="mo_boot_col-lg-7 mo_boot_col-sm-6" >
                                    <input type="button" class="btn btn_cstm mo_idp_crown_pos mo_idp_export_config"<?php if ($sp_entityid) echo "enabled";else echo "disabled"; ?>  onclick="jQuery('#mo_idp_exportconfig').submit();" value="<?php echo Text::_('COM_JOOMLAIDP_EXPORT_CONFIG'); ?>"> 
                                </div>
                                <div class="mo_boot_col-sm-12 mo_boot_mt-3 mo_saml_dark_bg">
                                    <ul class="switch_tab_sp text-center mo_boot_p-2 mo_saml_dark_bg">
                                        <li class="mo_saml_current_tab" id="manual_configuration"><a href="#" id="mo_saml_idp_manual_tab" class="mo_saml_bs_btn" onclick="hide_metadata_form()"><?php echo Text::_('COM_MINIORANGE_SAML_MANUAL_CONFIG'); ?></a></li>
                                        <li class="mo_boot_col-sm-12 mo_boot_col-lg-2"><?php echo Text::_('COM_MINIORANGE_SAML_OR'); ?></li>
                                        <li class="" id="auto_configuration"><a href="#" id="mo_saml_upload_idp_tab" class="mo_saml_bs_btn" onclick="show_metadata_form()"><?php echo Text::_('COM_JOOMLAIDP_SP_METADATA_BTN'); ?></a></li>
                                        
                                    </ul>
                                </div>
                            </div>
                            <div id="idpdata" class="mt-4">
                                <div class="mo_boot_row mo_boot_mt-3" id="name">
                                    <div class="mo_boot_col-sm-4">
                                        <span class="saml_idp_label_css"><?php echo Text::_('COM_JOOMLAIDP_SP_NAME'); ?><span class="mo_saml_required">*</span></span>   
                                    </div>
                                    <div class="mo_boot_col-sm-8">
                                        <input type="text" class="mo_form-control was-validated mo_saml_proxy_setup" name="sp_name" placeholder="<?php echo Text::_('COM_JOOMLAIDP_SP_NAME_PLACEHOLDER'); ?>" value="<?php echo $sp_name; ?>" required />
                                        <span class="small"><strong><?php echo Text::_('COM_MINIORANGE_SAML_NOTE'); ?>: </strong><?php echo Text::_('COM_JOOMLAIDP_ENTER_SP_NAME'); ?></span>
                                    </div>
                                </div>
                                <div class="mo_boot_row mo_boot_mt-3" id="sp_entity">
                                    <div class="mo_boot_col-sm-4">
                                        <span class="saml_idp_label_css"><?php echo Text::_('COM_JOOMLAIDP_SP_ISSUER'); ?><span class="mo_saml_required">*</span></span>   
                                    </div>
                                    <div class="mo_boot_col-sm-8">
                                        <input type="url" id="sp_entityid" class="mo_form-control was-validated mo_saml_proxy_setup" name="sp_entityid" placeholder="<?php echo Text::_('COM_JOOMLAIDP_ENTER_ISSUER'); ?>" value="<?php echo $sp_entityid; ?>" required />
                                        <span class="small"><strong><?php echo Text::_('COM_MINIORANGE_SAML_NOTE'); ?>: </strong><?php echo Text::_('COM_JOOMLAIDP_ISSUER_INFO'); ?></span>
                                    </div>
                                </div>
                                <div class="mo_boot_row mo_boot_mt-3" id="sp_sso_url">
                                    <div class="mo_boot_col-sm-4">
                                        <span class="saml_idp_label_css"><?php echo Text::_('COM_MINIORANGE_IDP_ACS_URL'); ?><span class="mo_saml_required">*</span></span>   
                                    </div>
                                    <div class="mo_boot_col-sm-8">
                                        <input type="url" id="acs_url" class="mo_form-control was-validated mo_saml_proxy_setup" name="acs_url" placeholder="<?php echo Text::_('COM_JOOMLAIDP_ENTER_ASC'); ?>" value="<?php echo $acs_url; ?>" required />
                                        <span class="small"><strong><?php echo Text::_('COM_MINIORANGE_SAML_NOTE'); ?>: </strong><?php echo Text::_('COM_JOOMLAIDP_ASC_INFO'); ?></span>
                                    </div>
                                </div>
                                <div class="mo_boot_row mo_boot_mt-3" id="sp_nameid_format">
                                    <div class="mo_boot_col-sm-4"><?php echo Text::_('COM_MINIORANGE_IDP_NAMEID_FORMAT'); ?></div>
                                    <div class="mo_boot_col-sm-8">
                                        <select class="mo_form-control mo_saml_proxy_setup mo_saml_dark_bg" id="nameid_format" name="nameid_format">
                                            <option value="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified" <?php if ($nameid_format == 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified') echo 'selected = "selected"'; ?>>
                                                urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified
                                            </option>
                                            <option value="urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress" <?php if ($nameid_format == 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress') echo 'selected = "selected"'; ?>>
                                                urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress
                                            </option>
                                            <option value="urn:oasis:names:tc:SAML:1.1:nameid-format:transient" <?php if ($nameid_format == 'urn:oasis:names:tc:SAML:1.1:nameid-format:transient') echo 'selected = "selected"'; ?>>
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
                                        <input type="url" class="mo_form-control was-validated mo_saml_proxy_setup" name="default_relay_state" placeholder="<?php echo Text::_('COM_JOOMLAIDP_ENTER_RELAY'); ?>" value="<?php echo $relay_state; ?>"  />
                                        <span class="small"><strong><?php echo Text::_('COM_MINIORANGE_SAML_NOTE'); ?>: </strong><?php echo Text::_('COM_JOOMLAIDP_RELAY_INFO'); ?></span>
                                    </div>
                                </div>
                                <div class="mo_boot_row mo_boot_mt-4" id="saml_login">
                                    <div class="mo_boot_col-sm-4"><?php echo Text::_('COM_MINIORANGE_IDP_SIGNED_ASSERTION'); ?>
                                    </div>
                                    <div class="mo_boot_col-sm-8">
                                        <label class="mo_saml_switch">
                                            <input type="checkbox" id ="login_link_check" name="assertion_signed" value="1"
                                            <?php echo($assertion_signed == 1 ? 'checked' : ''); ?>
                                            >
                                            <span class="mo_saml_slider"></span>
                                        </label>
                                        <br><span class="small"><strong><?php echo Text::_('COM_MINIORANGE_SAML_NOTE'); ?>: </strong><?php echo Text::_('COM_JOOMLAIDP_CHECK_SIGN'); ?></span>
                                    </div>
                                </div><br>
                                <details !important class="mo_saml_dark_bg">
                                    <summary class="mo_saml_main_summary mo_saml_dark_text" ><?php echo Text::_('COM_JOOMLAIDP_ADVACE_FEATURES'); ?> <sup><a href="index.php?option=com_joomlaidp&view=accountsetup&tab-panel=license">
                                        <img class="crown_img_small mo_idp_ml_px" src="<?php echo Uri::base();?>/components/com_joomlaidp/assets/images/crown.webp" alt="Premium">
                                           </a></strong></sup></summary><hr><div class="mo_tooltip">
                                    <div class="mo_boot_row mo_boot_mt-3" id="sp_slo">
                                        <div class="mo_boot_col-sm-4"><?php echo Text::_('COM_MINIORANGE_IDP_LOGOUT_URL'); ?>
                                        </div>
                                        <div class="mo_boot_col-sm-8">
                                            <input class=" mo_form-control" type="text" name="single_logout_url" placeholder="Enter the SLO URL" disabled>
                                        </div>
                                    </div>
                                    <div class="mo_boot_row mo_boot_mt-3" id="sp_binding_type">
                                        <div class="mo_boot_col-sm-4">
                                            <?php echo Text::_('COM_JOOMLAIDP_BINDING'); ?>
                                        </div>
                                        <div class="mo_boot_col-sm-8">
                                            <input type="radio" name="miniorange_saml_sp_sso_binding" value="HttpRedirect" checked=1 aria-invalid="false" disabled><span class="ml-1"><?php echo Text::_('COM_MINIORANGE_IDP_SP_REDIRECT'); ?></span><br />
                                            <input type="radio" name="miniorange_saml_idp_sso_binding" value="HttpPost" aria-invalid="false" disabled><span class="ml-1"><?php echo Text::_('COM_MINIORANGE_IDP_SP_POST'); ?></span>
                                        </div>
                                    </div>
                                    <div class="mo_boot_row mo_boot_mt-3" id="sp_certificate_signed">
                                        <div class="mo_boot_col-sm-4"><?php echo Text::_('COM_MINIORANGE_IDP_SP_CERT_A'); ?>
                                        </div>
                                        <div class="mo_boot_col-sm-8">
                                            <textarea rows="3" cols="80" name="certificate" class="mo_idp_certificate" disabled></textarea>
                                        </div>
                                    </div>
                                    <div class="mo_boot_row mo_boot_mt-3" id="sp_certificate_assertion">
                                        <div class="mo_boot_col-sm-4"><?php echo Text::_('COM_MINIORANGE_IDP_SP_CERT_B'); ?>
                                        </div>
                                        <div class="mo_boot_col-sm-8">
                                            <textarea rows="3" cols="80" name="certificate" class="mo_idp_certificate"  disabled></textarea>
                                        </div>
                                    </div>
                                    <div class="mo_boot_row mo_boot_mt-3" id="sp_slo">
                                        <div class="mo_boot_col-sm-4">
                                            <?php echo Text::_('COM_JOOMLAIDP_SIGNED'); ?>
                                        </div>
                                        <div class="mo_boot_col-sm-8">
                                            <label class="mo_saml_switch">
                                                <input type="checkbox" disabled>
                                                <span class="mo_saml_slider"></span>
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
                                                <span class="mo_saml_slider"></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mo_boot_row mo_boot_mt-3" id="sp_slo">
                                        <div class="mo_boot_col-sm-4">
                                            <?php echo Text::_('COM_JOOMLAIDP_VALIDATE_TIME'); ?>
                                        </div>
                                        <div class="mo_boot_col-sm-8">
                                            <input class=" mo_form-control" type="text"  placeholder="<?php echo Text::_('COM_JOOMLAIDP_ENTER_TIME'); ?>" name="saml_response_validation_time" disabled>
                                        </div>
                                    </div>
                                    
                                </details>
                                
                                <div class="mo_boot_row mo_boot_mt-5">
                                    <div class="mo_boot_col-sm-12 mo_boot_text-center">
                                        <input type="submit" class="btn btn_cstm" value="<?php echo Text::_('COM_JOOMLAIDP_SAVE_BTN'); ?>"/>
                                        <input  type="button" id='test-config' <?php if ($sp_entityid) echo "enabled";else echo "disabled"; ?> title='<?php echo Text::_('COM_JOOMLAIDP_TEST_TITLE'); ?>' class="btn btn_cstm mo_idp_test_cinfig" onclick='showTestWindow()' value="<?php echo Text::_('COM_JOOMLAIDP_TEST_CONFIG'); ?>">

                                        <input type="submit" class="btn btn_cstm_red " <?php if ($sp_entityid) echo "enabled"; else echo "disabled"; ?> value="<?php echo Text::_('COM_JOOMLAIDP_DELETE_SP'); ?>" name="mo_saml_delete" />
                                    </div>
                                </div>
                                      
                            </div>
                            <input type="hidden" id="idp-initiated-url" value="<?php echo Route::_('index.php?option=com_idpinitiatedlogin'); ?>"/>
                        </form>
                        <form name="f" id="mo_idp_exportconfig"  method="post" action="<?php echo Route::_('index.php?option=com_joomlaidp&view=accountsetup&task=accountsetup.importExportConfiguration'); ?>" >
                        </form>
                        <div class="mo_boot_row mo_boot_mt-5 mo_boot_mt-3 mo_boot_py-3 mo_boot_px-2 mo_idp_disp_no" id="upload_metadata_form">
                            <div class="mo_boot_col-sm-12 mo_boot_mt-1">
                                <form action="<?php echo Route::_('index.php?option=com_joomlaidp&view=accountsetup&task=accountsetup.handleUploadMetadata'); ?>" name="metadataForm" method="post" id="IDP_metadata_form" enctype="multipart/form-data">
                                    <div class="mo_boot_row">
                                        <div class="mo_boot_col-sm-3">
                                            <span class="saml_idp_label_css"><?php echo Text::_('COM_JOOMLAIDP_SP_NAME'); ?><span class="mo_saml_required">*</span> :</span>   
                                        </div>
                                        <div class="mo_boot_col-sm-9">
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
                                        <div class="mo_boot_col-sm-9 mo_boot_col-lg-3 ">
                                            <button type="button" class="btn btn_cstm mo_idp_crown_pos mo_idp_upl_metadata mo_idp_adv_feat_btns" id="upload_metadata_file"  name="option1" method="post"><svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z" />
                                            <path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708l3-3z" />
                                            </svg>&nbsp;&nbsp;<?php echo Text::_('COM_JOOMLAIDP_CRT_UPLOAD'); ?></button>
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
                                            <input type="url" id="metadata_url" name="metadata_url" placeholder=" <?php echo Text::_('COM_JOOMLAIDP_ENTER_METADATA_URL'); ?>" class="form-control"/>
                                        </div>
                                        <div class=" mo_boot_col-sm-12 mo_boot_col-lg-3 mo_boot_text-center ">
                                            <button type="button" class=" float-lg-right btn btn_cstm mo_idp_adv_feat_btns" name="option1" method="post" id="fetch_metadata">
                                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                <path fill-rule="evenodd" d="M3.5 6a.5.5 0 0 0-.5.5v8a.5.5 0 0 0 .5.5h9a.5.5 0 0 0 .5-.5v-8a.5.5 0 0 0-.5-.5h-2a.5.5 0 0 1 0-1h2A1.5 1.5 0 0 1 14 6.5v8a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 14.5v-8A1.5 1.5 0 0 1 3.5 5h2a.5.5 0 0 1 0 1h-2z"></path>
                                                <path fill-rule="evenodd" d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"></path>
                                                </svg> <?php echo Text::_('COM_JOOMLAIDP_FETCH_METADATA'); ?>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mo_boot_col-sm-4 mo_tab_border">
                    <div class=" mo_boot_m-0 mo_boot_p-0">
                        <div class="mo_boot_col-sm-12 mo_boot_p-2">
                            <div class="mo_setup_guide_title text-center">
                                <strong><?php echo Text::_('COM_MINIORANGE_IDP_SP_GUIDES'); ?></strong>
                            </div>
                        </div>
                        <div class="mo_boot_col-sm-12  mo_boot_m-0 mo_boot_px-4 mo_boot_py-4 mo_boot_mo_idp_setup" >
                            <?php 
                            for($i=1;$i<$guide_count;$i+=2)
                            {
                                if(isset($setup_guides[$i]) && isset($setup_guides[$i+1]))
                                {
                            ?>
                             <div class="mo_boot_row mo_boot_m-0 mo_boot_p-2" >
                            <div class="mo_boot_col-sm-6 mo_boot_m-0 mo_boot_p-0 mo_boot_text-center">
                                <strong><a class="mo_idp_guide_color" href="<?php  echo $setup_guides[$i]['link']; ?>" target="_blank" ><?php  echo $setup_guides[$i]['name']; ?></a></strong>
                            </div>
                            <div class="mo_boot_col-sm-6 mo_boot_m-0 mo_boot_p-0 mo_boot_text-center">
                                <strong><a class="mo_idp_guide_color" href="<?php  echo $setup_guides[$i+1]['link']; ?>" target="_blank" ><?php  echo $setup_guides[$i+1]['name']; ?></a></strong>
                            </div>
                        </div>
                        <hr>
                        <?php
                            }
                            else if(isset($setup_guides[$i]))
                            {
                            ?>
                            <div class="mo_boot_row mo_boot_m-0 mo_boot_p-2" >
                            <div class="mo_boot_col-sm-12 mo_boot_m-0 mo_boot_p-0 mo_boot_text-center">
                                <strong><a class="mo_idp_guide_color" href="<?php  echo $setup_guides[$i]['link']; ?>" target="_blank" ><?php  echo $setup_guides[$i]['name']; ?></a></strong>
                            </div>
                        </div>
                        <hr>
                    <?php
                            }
                        }
                        ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
