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
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
/**
 * AccountSetup Controller
 *
 * @package     Joomla.Component
 * @subpackage  com_joomlaidp
 * @since       0.0.9
 */
class JoomlaIdpControllerAccountSetup extends FormController
{
	function __construct()
	{
		$this->view_list = 'accountsetup';
		parent::__construct();
	}


	function saveServiceProvider(){

		$app = Factory::getApplication();
        $input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
        $post = ($input && $input->post) ? $input->post->getArray() : [];
        if(!isset($post['sp_name']) && !isset($post['sp_entityid']) && !isset($post['acs_url'])){
            $this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=sp');
            return;
        }
		$isDelete = isset($post['mo_saml_delete']) ? $post['mo_saml_delete'] : '';

		if ($isDelete == "Delete SP Configuration")
        {
            $data = new stdClass();
            $data->id = 1;
            $data->sp_name = '';
            $data->sp_entityid = '';
            $data->acs_url = '';
            $data->nameid_format = '';
            $data->nameid_attribute = '';
            $data->default_relay_state = '';
            $data->assertion_signed = 0;
            $data->enabled = 0;

            $db = Factory::getDBO();
            $this->updateOrInsertRecord($db, '#__miniorangesamlidp', $data);

            $message = Text::_('COM_JOOMLAIDP_MSG_5');
            $this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=sp', $message );
        }
		else{
            $spName = isset($post['sp_name']) ? $post['sp_name'] : '';
            $issuer = isset($post['sp_entityid']) ? $post['sp_entityid'] : '';
            $acsUrl = isset($post['acs_url']) ? $post['acs_url'] : '';
            $nameIdFormat = isset($post['nameid_format']) ? $post['nameid_format'] : '';
            $defaultRelayState = isset($post['default_relay_state']) ? $post['default_relay_state'] : '';
            $assertionSigned = isset($post['assertion_signed']) ? isset($post['assertion_signed']) : 0;
            if(empty($spName) || empty($issuer) || empty($acsUrl) || empty($nameIdFormat)){
                $message = Text::_('COM_JOOMLAIDP_MSG_6');
                $this->setRedirect('index.php?option=com_joomlaidp&view=samlidpsettings',  $message,'error');
                return FALSE;
            }

            $spName = strtolower(trim($spName));
            $issuer = trim($issuer);
            $acsUrl = trim($acsUrl);

            $data = new stdClass();
            $data->id = 1;
            $data->sp_name = $spName;
            $data->sp_entityid = $issuer;
            $data->acs_url = $acsUrl;
            $data->nameid_format = $nameIdFormat;
            $data->default_relay_state = $defaultRelayState;
            $data->assertion_signed = $assertionSigned;
            $data->enabled = TRUE;

            $db = Factory::getDBO();
            $this->updateOrInsertRecord($db, '#__miniorangesamlidp', $data);

            IDP_Utilities::isValidCheck($spName, $acsUrl,'Save Details','');
            $message = Text::_('COM_JOOMLAIDP_MSG_7') .' (' . $spName . ') '.Text::_('COM_JOOMLAIDP_MSG_8');
            $this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=sp', $message );
        }
	}

	/**
	 * Helper function to check if record exists and update or insert accordingly
	 */
	function updateOrInsertRecord($db, $table, $data) {
		// Check if record with id=1 exists
		$query = $db->getQuery(true);
		$query->select('id')
			  ->from($db->quoteName($table))
			  ->where($db->quoteName('id') . ' = 1');
		$db->setQuery($query);
		$result = $db->loadResult();
		
		if ($result) {
			// Record exists, update it
			$db->updateObject($table, $data, 'id', true);
		} else {
			// Record doesn't exist, ensure all required fields have default values
			if ($table === '#__miniorangesamlidp') {
				// Set default values for required fields if not already set
				if (!isset($data->sp_name) || empty($data->sp_name)) {
					$data->sp_name = '';
				}
				if (!isset($data->sp_entityid) || empty($data->sp_entityid)) {
					$data->sp_entityid = '';
				}
				if (!isset($data->acs_url) || empty($data->acs_url)) {
					$data->acs_url = '';
				}
				if (!isset($data->default_relay_state) || empty($data->default_relay_state)) {
					$data->default_relay_state = '';
				}
				if (!isset($data->nameid_format) || empty($data->nameid_format)) {
					$data->nameid_format = '';
				}
				if (!isset($data->nameid_attribute) || empty($data->nameid_attribute)) {
					$data->nameid_attribute = '';
				}
				if (!isset($data->enabled)) {
					$data->enabled = 0;
				}
				if (!isset($data->assertion_signed)) {
					$data->assertion_signed = 0;
				}
			}
			// Insert new record
			$db->insertObject($table, $data, 'id');
		}
	}

	
    function updateNameId()
    {
        $app = Factory::getApplication();
        $input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
        $post = ($input && $input->post) ? $input->post->getArray() : [];
        if(!isset($post['nameid_attribute'])){
            $this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=advance_mapping');
            return;
        }

        $data = new stdClass();
        $data->id = 1;
        $data->nameid_attribute = empty($post['nameid_attribute']) ? 'emailAddress' : $post['nameid_attribute'];
        $db = Factory::getDBO();
        $this->updateOrInsertRecord($db, '#__miniorangesamlidp', $data);
        $message=Text::_('COM_JOOMLAIDP_MSG_W');
        $this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=advance_mapping', $message );

    }
	function handleUploadMetadata(){

		require_once JPATH_COMPONENT.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'MetadataReader.php';
		$app = Factory::getApplication();
        $input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
        $post = ($input && $input->post) ? $input->post->getArray() : [];
    
        if(count($post) == 0){
            $this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=sp');
            return;
        }
     
        $file  = $input->files->getArray();
        
        if ( !isset($post['sp_upload_name']) || empty($post['sp_upload_name'])) {
        	$this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=sp', Text::_('COM_JOOMLAIDP_MSG_10'),'error');
        	return;
        }

        $sp_name = $post['sp_upload_name'];

        if (isset($file['metadata_file']) || isset($post['metadata_url'])) {
            if(!empty($file['metadata_file']['tmp_name'])) {
                $file = @file_get_contents( $file['metadata_file']['tmp_name']);
            }
            else {
                $url = filter_var($post['metadata_url'],FILTER_SANITIZE_URL);
                $arrContextOptions=array(
                    "ssl"=>array(
                        "verify_peer"=>false,
                        "verify_peer_name"=>false,
                    ),
                );
                if(empty($url)) {
                    $this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=sp', Text::_('COM_JOOMLAIDP_MSG_11'),'error');
                    return;
                }
                else {
                    $file = file_get_contents($url, false, stream_context_create($arrContextOptions));
                }
            }

            if($file)
            {
                $this->uploadMetadata($file, $sp_name);
            }else
            {
                $this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=sp', Text::_('COM_JOOMLAIDP_MSG_11'),'error');
                return;
            }
            
        }
	}

	function uploadMetadata($file, $sp_name){

		$app = Factory::getApplication();
        $input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
        $post = ($input && $input->post) ? $input->post->getArray() : [];
        if(count($post) == 0){
            $this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=sp');
            return;
        }
		$document = new DOMDocument();
        $document->loadXML( $file );
        restore_error_handler();
        $first_child = $document->firstChild;

        if( !empty( $first_child ) ) {
            $metadata = new MetadataReader($document);
            $service_providers = $metadata->getServiceProviders();
            if( empty( $service_providers ) ) {
                $this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=sp',  Text::_('COM_JOOMLAIDP_MSG_12'),'error');
                return;
            }
            foreach( $service_providers as $key => $sp ) {
                $issuer = $sp->getEntityID();
                $acs_url = $sp->getAcsURL();
                $is_assertion_signed = $sp->getAssertionsSigned() == 'true' ? TRUE : FALSE;
            }
            $data = new stdClass();
			$data->id = 1;
			$data->sp_name = $sp_name;
			$data->sp_entityid = $issuer;
			$data->acs_url = $acs_url;
			$data->nameid_format = empty($post['nameid_format']) ? 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified' : $post['nameid_format'];
        
			$data->nameid_attribute = empty($post['nameid_attribute']) ? 'emailAddress' : $post['nameid_attribute'];
			$data->assertion_signed = $is_assertion_signed;
			$data->enabled = TRUE;
		
		    $db = Factory::getDBO();
		    $this->updateOrInsertRecord($db, '#__miniorangesamlidp', $data);
            IDP_Utilities::isValidCheck($sp_name, $acs_url,'Save Details','');
		    $message = Text::_('COM_JOOMLAIDP_MSG_7') .' (' . $sp_name . ') '.Text::_('COM_JOOMLAIDP_MSG_8');
		    $this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=sp', $message );
            return;
        }
        else {
        	$this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=sp', Text::_('COM_JOOMLAIDP_MSG_13'),'error');
        	return;
        }
	}

	function updateIdpEntityId()
	{
		$app = Factory::getApplication();
        $input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
        $post = ($input && $input->post) ? $input->post->getArray() : [];
        if(count($post) == 0){
            $this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=idp');
            return;
        }
		$newIdp = $post['mo_saml_idp_entity_id'];

        $nameOfDatabase = '#__miniorange_saml_idp_customer';
        $updateFieldsArray = array(
            'idp_entity_id'  => $newIdp,
        );

        IDP_Utilities::updateDatabaseQuery($nameOfDatabase, $updateFieldsArray);
		$this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=idp',Text::_('COM_JOOMLAIDP_MSG_14'));
	}
	
    function requestForDemoPlan()
    {
        $app = Factory::getApplication();
        $input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
        $post = ($input && $input->post) ? $input->post->getArray() : [];
        if ((!isset($post['email'])) || (!isset($post['plan'])) || (!isset($post['description']))) {
            $this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=request_demo', Text::_('COM_JOOMLAIDP_MSG_1'),'error');
            return;
        }
        $email = $post['email'];
        $plan = $post['plan'];
        $description = trim($post['description']);
        $demo = $post['demo'];

        if (!isset($plan) || empty($description)) {
            $this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=overview', Text::_('COM_JOOMLAIDP_MSG_1'), 'error');
            return;
        }

        $customer = new MoSamlIdpCustomer();
        $response = json_decode($customer->request_for_trial($email, $plan, $demo, $description));

        if ($response->status != 'ERROR')
            $this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=overview', Text::_('COM_JOOMLAIDP_MSG_15').'&nbsp;'.$demo.'&nbsp;'.Text::_('COM_JOOMLAIDP_MSG_16'));
        else {
            $this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=overview', Text::_('COM_JOOMLAIDP_MSG_17'), 'error');
            return;
        }

    }
	
	function saveCustomerConfigurations($email, $id, $apiKey, $token, $phone) {
        $databaseName = '#__miniorange_saml_idp_customer';
        $updateFieldsArray = array(
            'email'               => $email,
            'customer_key'        => $id,
            'api_key'             => $apiKey,
            'customer_token'      => $token,
            'admin_phone'         => $phone,
            'login_status'        => 1,
            'registration_status' => 'SUCCESS',
            'password'            => '',
            'email_count'         => 0,
            'sms_count'           => 0,
        );
        IDP_Utilities::updateDatabaseQuery($databaseName, $updateFieldsArray);
	}

    function saveAdminMail()
    {
        $app = Factory::getApplication();
        $input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
        $post = ($input && $input->post) ? $input->post->getArray() : [];
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $fields = array(
            $db->quoteName('email') . ' = '.$db->quote($post['admin_email']),

        );

        $conditions = array(
            $db->quoteName('id') . ' = 1'
        );

        $query->update($db->quoteName('#__miniorange_saml_idp_customer'))->set($fields)->where($conditions);
        $db->setQuery($query);
        $result = $db->execute();
        $this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=sp',Text::_('COM_JOOMLAIDP_MSG_18'));
        return;
    }
	
	function contactUs(){
        $app = Factory::getApplication();
        $input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
        $post = ($input && $input->post) ? $input->post->getArray() : [];
        if( MoSamlIdpUtility::checkEmptyOrNull( $post['mo_saml_query_email'] ) || MoSamlIdpUtility::checkEmptyOrNull( trim($post['mo_saml_query_email'])) ) {
            $this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup',  Text::_('COM_JOOMLAIDP_MSG_P'), 'error');
            return;
        } else{
            $query = $post['mo_saml_query'];
            $email = $post['mo_saml_query_email'];
            $phone = $post['mo_saml_query_phone'];

            if(isset($post['mo_saml_select_plan']) && !empty($post['mo_saml_select_plan'] && $post['mo_saml_select_plan'] != 'none')
                || isset($post['number_of_users']) && !empty($post['number_of_users']))
            {
                $number_users = isset($post['number_of_users']) ? $post['number_of_users'] : '';
                if(empty($number_users)){
                    $this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup',  Text::_('COM_JOOMLAIDP_MSG_Q'), 'error');
                    return;
                }
                $plan_name = $post['mo_saml_select_plan'];
                $query = "Plan Name : ".$plan_name.", Users : ".$number_users.' '.$query;
            }

            $contact_us = new MoSamlIdpCustomer();
            $submited = json_decode($contact_us->submit_contact_us($email, $phone, $query),true);
            if(json_last_error() == JSON_ERROR_NONE) {
                if(is_array($submited) && array_key_exists('status', $submited) && $submited['status'] == 'ERROR'){
                    $this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup', $submited['message'],'error');
                }else{
                    if ( $submited == false ) {
                        $this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=overview',  Text::_('COM_JOOMLAIDP_MSG_R'),'error');
                    } else {
                        $this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=overview',  Text::_('COM_JOOMLAIDP_MSG_S'));
                    }
                }
            }
        }
    }

    function importExportConfiguration()
    {
        $idp_config=IDP_Utilities::fetchDatabaseValues('#__miniorangesamlidp', 'loadAssoc');

        if (empty($idp_config['sp_entityid']) || empty($idp_config['acs_url'])) {
            $this->setRedirect('index.php?option=com_joomlaidp&tab=com_miniorange_saml&tab-panel=sp', Text::_('COM_JOOMLAIDP_MSG_U'), 'error');
            return;
        }
        $app = Factory::getApplication();
        $input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
        $post = ($input && $input->post) ? $input->post->getArray() : [];
        require_once JPATH_SITE . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_joomlaidp' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'export.php';

        define("Tab_Class_Names", serialize(array(
            "sp_info" => 'mo_sp_info'
        )));

        $tab_class_name = unserialize(Tab_Class_Names);
        $configuration_array = array();
        foreach ($tab_class_name as $key => $value) {
            $configuration_array[$key] = $this->mo_get_configuration_array($value);
        }

        if ($configuration_array) {
            // Convert to premium plugin format while keeping all original fields
            $premium_format = array(
                array(
                    'id' => 1,
                    'sp_info' => $configuration_array['sp_info'],
                    'role_restriction' => array(),
                    'relay_restriction' => array(),
                    'attribute_mapping' => array()
                )
            );
            
            header("Content-Disposition: attachment; filename=miniorange-idp-config.json");
            echo(json_encode($premium_format, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            exit;
        }
        $this->setRedirect('index.php?option=com_joomlaidp&tab=com_miniorange_saml&tab-panel=sp', Text::_('COM_JOOMLAIDP_MSG_V'));
        return;
    }

    function mo_get_configuration_array( $class_name) {
        if($class_name=='mo_sp_info'){
            $customerResult = IDP_Utilities::fetchDatabaseValues('#__miniorangesamlidp', 'loadAssoc');
        }

		$class_object = call_user_func( $class_name . '::getConstants' );
        $mo_array = array();
        foreach ( $class_object as $key => $value ) {
            if(!empty($customerResult))
            {
                if(!empty($customerResult[$value]))
                {
                    $mo_array[$key] = $customerResult[$value];
                }
                
            }
        }
        return $mo_array;
	}

    public function resetLogs(): void
    {
        $db = Factory::getDbo();
        $countQuery = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__mo_idp_logs'));
        $db->setQuery($countQuery);
        $logCount = $db->loadResult();
        if ($logCount > 0) {
            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__mo_idp_logs'));
            $db->setQuery($query);
            $db->execute();
            $this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=errorlog', Text::_('COM_MINIORANGE_LOGGER_RESET_MESSAGE'));
        } else {
            $this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=errorlog', Text::_('COM_MINIORANGE_LOGGER_NO_LOGS_TO_RESET'));
        }
    }

    public function downloadLogs(): void
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__mo_idp_logs'))
            ->order('timestamp DESC');
        $db->setQuery($query);
        $logs = $db->loadObjectList();
        if (empty($logs)) {
        $this->setRedirect(
            'index.php?option=com_joomlaidp&view=accountsetup&tab-panel=errorlog',
            Text::_('COM_MINIORANGE_LOGGER_DOWNLOAD_MESSAGE')
        );
        return;
        }
        $fileName = 'miniorange_logs_' . date('Y-m-d_H-i-s') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Timestamp', 'Log Level', 'Code', 'Message']);
        foreach ($logs as $log) {
            $logData = json_decode($log->message, true);
            fputcsv($output, [
                $log->timestamp,
                strtoupper($log->log_level),
                $logData['code'] ?? '-',
                $logData['issue'] ?? $log->message
            ]);
        }
        fclose($output);
        jexit();
    }

}