<?php
/**
 * @package     Joomla.Component
 * @subpackage  com_joomlaidp
 * @author      miniOrange Security Software Pvt. Ltd.
 * @copyright   Copyright (C) 2015 miniOrange (https://www.miniorange.com)
 * @license     GNU General Public License version 3; see LICENSE.txt
 * @contact     info@xecurify.com
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;

include_once JPATH_SITE . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components'
	. DIRECTORY_SEPARATOR . 'com_joomlaidp' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'DbHelper.php';

class JoomlaIdpControllerAccountSetup extends FormController
{
	public function __construct($config = array())
	{
		$config['view_list'] = 'accountsetup';
		parent::__construct($config);
	}

	public function saveServiceProvider()
	{
		$app = Factory::getApplication();
		$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
		$post = ($input && $input->post) ? $input->post->getArray() : array();

		if (!isset($post['sp_name']) && !isset($post['sp_entityid']) && !isset($post['acs_url']))
		{
			$this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=sp');

			return;
		}

		$isDelete = isset($post['mo_saml_delete']) ? $post['mo_saml_delete'] : '';
		$spId = isset($post['sp_id']) ? (int) $post['sp_id'] : 0;

		if ($isDelete == 'Delete SP Configuration')
		{
			if ($spId > 0)
			{
				$db = MoSamlIdpDb::getDb();
				$query = $db->getQuery(true);
				$query->delete($db->quoteName('#__miniorangesamlidp'))
					->where($db->quoteName('id') . ' = ' . $spId);
				$db->setQuery($query);
				$db->execute();
			}
			else
			{
				$data = $this->buildSpDataObject(
					array(
						'id'                => 1,
						'sp_name'           => '',
						'sp_entityid'       => '',
						'acs_url'           => '',
						'nameid_format'     => '',
						'nameid_attribute'  => '',
						'default_relay_state' => '',
						'assertion_signed'  => 0,
						'enabled'           => 0,
					)
				);

				$db = MoSamlIdpDb::getDb();
				$this->updateOrInsertRecord($db, '#__miniorangesamlidp', $data);
			}

			$message = Text::_('COM_JOOMLAIDP_MSG_5');
			$this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=sp', $message);
		}
		else
		{
			$spName = isset($post['sp_name']) ? $post['sp_name'] : '';
			$issuer = isset($post['sp_entityid']) ? $post['sp_entityid'] : '';
			$acsUrl = isset($post['acs_url']) ? $post['acs_url'] : '';
			$nameIdFormat = isset($post['nameid_format']) ? $post['nameid_format'] : '';
			$defaultRelayState = isset($post['default_relay_state']) ? $post['default_relay_state'] : '';
			$assertionSigned = isset($post['assertion_signed']) ? $post['assertion_signed'] : 0;

			if (empty($spName) || empty($issuer) || empty($acsUrl) || empty($nameIdFormat))
			{
				$message = Text::_('COM_JOOMLAIDP_MSG_6');
				$this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=sp', $message, 'error');

				return;
			}

			$spName = strtolower(trim($spName));
			$issuer = trim($issuer);
			$acsUrl = trim($acsUrl);

			$data = $this->buildSpDataObject(
				array(
					'id'                  => $spId > 0 ? $spId : 1,
					'sp_name'             => $spName,
					'sp_entityid'         => $issuer,
					'acs_url'             => $acsUrl,
					'nameid_format'       => $nameIdFormat,
					'default_relay_state' => $defaultRelayState,
					'assertion_signed'    => $assertionSigned,
					'enabled'             => true,
				)
			);

			$db = MoSamlIdpDb::getDb();
			$this->updateOrInsertRecord($db, '#__miniorangesamlidp', $data);

			IDP_Utilities::isValidCheck($spName, $acsUrl, 'Save Details', '');
			$message = Text::_('COM_JOOMLAIDP_MSG_7') . ' (' . $spName . ') ' . Text::_('COM_JOOMLAIDP_MSG_8');
			$this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=sp', $message);
		}
	}

	public function deleteServiceProvider()
	{
		$app = Factory::getApplication();
		$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
		$post = ($input && $input->post) ? $input->post->getArray() : array();
		$spId = isset($post['sp_id']) ? (int) $post['sp_id'] : 0;

		if ($spId > 0)
		{
			$db = MoSamlIdpDb::getDb();
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__miniorangesamlidp'))
				->where($db->quoteName('id') . ' = ' . $spId);
			$db->setQuery($query);
			$db->execute();

			$message = Text::_('COM_JOOMLAIDP_MSG_5');
			$this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=sp', $message);
		}
		else
		{
			$this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=sp', Text::_('COM_JOOMLAIDP_MSG_6'), 'error');
		}
	}

	public function updateOrInsertRecord($db, $table, $data)
	{
		$recordId = isset($data->id) ? $data->id : 1;
		$query = $db->getQuery(true);
		$query->select('id')
			->from($db->quoteName($table))
			->where($db->quoteName('id') . ' = ' . (int) $recordId);
		$db->setQuery($query);
		$result = $db->loadResult();

		if ($result)
		{
			$db->updateObject($table, $data, 'id', true);
		}
		else
		{
			if ($table === '#__miniorangesamlidp')
			{
				$this->ensureSpRecordDefaults($data);
			}

			$db->insertObject($table, $data, 'id');
		}
	}

	public function updateNameId()
	{
		$app = Factory::getApplication();
		$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
		$post = ($input && $input->post) ? $input->post->getArray() : array();

		if (!isset($post['nameid_attribute']))
		{
			$this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=advance_mapping');

			return;
		}

		$nameIdAttribute = empty($post['nameid_attribute']) ? 'emailAddress' : $post['nameid_attribute'];
		$data = $this->buildSpDataObject(
			array(
				'id'               => 1,
				'nameid_attribute' => $nameIdAttribute,
			)
		);
		$db = MoSamlIdpDb::getDb();
		$this->updateOrInsertRecord($db, '#__miniorangesamlidp', $data);
		$message = Text::_('COM_JOOMLAIDP_MSG_W');
		$this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=advance_mapping', $message);
	}

	public function handleUploadMetadata()
	{
		require_once JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'MetadataReader.php';

		$app = Factory::getApplication();
		$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
		$post = ($input && $input->post) ? $input->post->getArray() : array();

		if (count($post) == 0)
		{
			$this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=sp');

			return;
		}

		$file = $input->files->getArray();

		if (!isset($post['sp_upload_name']) || empty($post['sp_upload_name']))
		{
			$this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=sp', Text::_('COM_JOOMLAIDP_MSG_10'), 'error');

			return;
		}

		$spName = $post['sp_upload_name'];

		if (isset($file['metadata_file']) || isset($post['metadata_url']))
		{
			if (!empty($file['metadata_file']['tmp_name']))
			{
				$file = @file_get_contents($file['metadata_file']['tmp_name']);
			}
			else
			{
				$url = filter_var($post['metadata_url'], FILTER_SANITIZE_URL);
				$arrContextOptions = array(
					'ssl' => array(
						'verify_peer'      => false,
						'verify_peer_name' => false,
					),
				);

				if (empty($url))
				{
					$this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=sp', Text::_('COM_JOOMLAIDP_MSG_11'), 'error');

					return;
				}

				$file = file_get_contents($url, false, stream_context_create($arrContextOptions));
			}

			if ($file)
			{
				$this->uploadMetadata($file, $spName);
			}
			else
			{
				$this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=sp', Text::_('COM_JOOMLAIDP_MSG_11'), 'error');

				return;
			}
		}
	}

	public function uploadMetadata($file, $spName)
	{
		$app = Factory::getApplication();
		$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
		$post = ($input && $input->post) ? $input->post->getArray() : array();

		if (count($post) == 0)
		{
			$this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=sp');

			return;
		}

		$document = new DOMDocument;
		$document->loadXML($file);
		restore_error_handler();
		$firstChild = $document->firstChild;

		if (!empty($firstChild))
		{
			$metadata = new MetadataReader($document);
			$serviceProviders = $metadata->getServiceProviders();

			if (empty($serviceProviders))
			{
				$this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=sp', Text::_('COM_JOOMLAIDP_MSG_12'), 'error');

				return;
			}

			foreach ($serviceProviders as $key => $sp)
			{
				$issuer = $sp->getEntityID();
				$acsUrl = $sp->getAcsURL();
				$isAssertionSigned = $sp->getAssertionsSigned() == 'true' ? true : false;
			}

			$nameIdFormat = empty($post['nameid_format'])
				? 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified'
				: $post['nameid_format'];
			$nameIdAttribute = empty($post['nameid_attribute']) ? 'emailAddress' : $post['nameid_attribute'];

			$data = $this->buildSpDataObject(
				array(
					'id'               => 1,
					'sp_name'          => $spName,
					'sp_entityid'      => $issuer,
					'acs_url'          => $acsUrl,
					'nameid_format'    => $nameIdFormat,
					'nameid_attribute' => $nameIdAttribute,
					'assertion_signed' => $isAssertionSigned,
					'enabled'          => true,
				)
			);

			$db = MoSamlIdpDb::getDb();
			$this->updateOrInsertRecord($db, '#__miniorangesamlidp', $data);
			IDP_Utilities::isValidCheck($spName, $acsUrl, 'Save Details', '');
			$message = Text::_('COM_JOOMLAIDP_MSG_7') . ' (' . $spName . ') ' . Text::_('COM_JOOMLAIDP_MSG_8');
			$this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=sp', $message);

			return;
		}

		$this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=sp', Text::_('COM_JOOMLAIDP_MSG_13'), 'error');
	}

	public function updateIdpEntityId()
	{
		$app = Factory::getApplication();
		$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
		$post = ($input && $input->post) ? $input->post->getArray() : array();

		if (count($post) == 0)
		{
			$this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=idp');

			return;
		}

		$newIdp = $post['mo_saml_idp_entity_id'];
		$nameOfDatabase = '#__miniorange_saml_idp_customer';
		$updateFieldsArray = array(
			'idp_entity_id' => $newIdp,
		);

		IDP_Utilities::updateDatabaseQuery($nameOfDatabase, $updateFieldsArray);
		$this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=idp', Text::_('COM_JOOMLAIDP_MSG_14'));
	}

	public function requestForDemoPlan()
	{
		$app = Factory::getApplication();
		$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
		$post = ($input && $input->post) ? $input->post->getArray() : array();

		if ((!isset($post['email'])) || (!isset($post['plan'])) || (!isset($post['description'])))
		{
			$this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=request_demo', Text::_('COM_JOOMLAIDP_MSG_1'), 'error');

			return;
		}

		$email = $post['email'];
		$plan = $post['plan'];
		$description = isset($post['description']) ? trim($post['description']) : '';
		$demo = $post['demo'];

		if (!isset($plan) || MoSamlIdpUtility::checkEmptyOrNull($description))
		{
			$this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=request_demo', Text::_('COM_JOOMLAIDP_MSG_1'), 'error');

			return;
		}

		$customer = new MoSamlIdpCustomer;
		$response = json_decode($customer->requestForTrial($email, $plan, $demo, $description));

		if ($response->status != 'ERROR')
		{
			$this->setRedirect(
				'index.php?option=com_joomlaidp&view=accountsetup&tab-panel=overview',
				Text::_('COM_JOOMLAIDP_MSG_15') . '&nbsp;' . $demo . '&nbsp;' . Text::_('COM_JOOMLAIDP_MSG_16')
			);
		}
		else
		{
			$this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=overview', Text::_('COM_JOOMLAIDP_MSG_17'), 'error');
		}
	}

	public function saveCustomerConfigurations($email, $id, $apiKey, $token, $phone)
	{
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

	public function saveAdminMail()
	{
		$app = Factory::getApplication();
		$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
		$post = ($input && $input->post) ? $input->post->getArray() : array();
		$db = MoSamlIdpDb::getDb();
		$query = $db->getQuery(true);
		$fields = array(
			$db->quoteName('email') . ' = ' . $db->quote($post['admin_email']),
		);
		$conditions = array(
			$db->quoteName('id') . ' = 1',
		);

		$query->update($db->quoteName('#__miniorange_saml_idp_customer'))->set($fields)->where($conditions);
		$db->setQuery($query);
		$db->execute();
		$this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=sp', Text::_('COM_JOOMLAIDP_MSG_18'));
	}

	public function contactUs()
	{
		$app = Factory::getApplication();
		$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
		$post = ($input && $input->post) ? $input->post->getArray() : array();
		$supportRedirect = 'index.php?option=com_joomlaidp&view=accountsetup&tab-panel=support_tab';

		if (MoSamlIdpUtility::checkEmptyOrNull($post['mo_saml_query_email'])
			|| MoSamlIdpUtility::checkEmptyOrNull(trim($post['mo_saml_query_email'])))
		{
			$this->setRedirect($supportRedirect, Text::_('COM_JOOMLAIDP_MSG_P'), 'error');

			return;
		}

		$email = trim($post['mo_saml_query_email']);

		if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/\.[a-zA-Z]{2,}$/', $email))
		{
			$this->setRedirect($supportRedirect, Text::_('COM_JOOMLAIDP_MSG_INVALID_EMAIL'), 'error');

			return;
		}

		$query = isset($post['mo_saml_query']) ? trim($post['mo_saml_query']) : '';

		if (MoSamlIdpUtility::checkEmptyOrNull($query))
		{
			$this->setRedirect($supportRedirect, Text::_('COM_JOOMLAIDP_MSG_QUERY_REQUIRED'), 'error');

			return;
		}

		$countryCode = isset($post['mo_saml_country_code']) ? trim($post['mo_saml_country_code']) : '';
		$phoneNum = isset($post['mo_saml_query_phone']) ? trim($post['mo_saml_query_phone']) : '';
		$phone = ($countryCode !== '' && $phoneNum !== '' && strpos($phoneNum, '+') !== 0) ? $countryCode . ' ' . $phoneNum : $phoneNum;

		if ((isset($post['mo_saml_select_plan']) && !empty($post['mo_saml_select_plan']) && $post['mo_saml_select_plan'] != 'none')
			|| (isset($post['number_of_users']) && !empty($post['number_of_users'])))
		{
			$numberUsers = isset($post['number_of_users']) ? $post['number_of_users'] : '';

			if (empty($numberUsers))
			{
				$this->setRedirect($supportRedirect, Text::_('COM_JOOMLAIDP_MSG_Q'), 'error');

				return;
			}

			$planName = $post['mo_saml_select_plan'];
			$query = 'Plan Name : ' . $planName . ', Users : ' . $numberUsers . ' ' . $query;
		}

		$contactUs = new MoSamlIdpCustomer;
		$submitted = json_decode($contactUs->submitContactUs($email, $phone, $query), true);

		if (json_last_error() == JSON_ERROR_NONE)
		{
			if (is_array($submitted) && array_key_exists('status', $submitted) && $submitted['status'] == 'ERROR')
			{
				$this->setRedirect($supportRedirect, $submitted['message'], 'error');
			}
			elseif ($submitted == false)
			{
				$this->setRedirect($supportRedirect, Text::_('COM_JOOMLAIDP_MSG_R'), 'error');
			}
			else
			{
				$this->setRedirect($supportRedirect, Text::_('COM_JOOMLAIDP_MSG_S'));
			}
		}
	}

	public function importExportConfiguration()
	{
		$idpConfig = IDP_Utilities::fetchDatabaseValues('#__miniorangesamlidp', 'loadAssoc');

		if (empty($idpConfig['sp_entityid']) || empty($idpConfig['acs_url']))
		{
			$this->setRedirect('index.php?option=com_joomlaidp&tab=com_miniorange_saml&tab-panel=sp', Text::_('COM_JOOMLAIDP_MSG_U'), 'error');

			return;
		}

		require_once JPATH_SITE . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components'
			. DIRECTORY_SEPARATOR . 'com_joomlaidp' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'export.php';

		$tabClassName = array(
			'sp_info' => 'MoSpInfo',
		);
		$configurationArray = array();

		foreach ($tabClassName as $key => $value)
		{
			$configurationArray[$key] = $this->moGetConfigurationArray($value);
		}

		if ($configurationArray)
		{
			$premiumFormat = array(
				array(
					'id'                 => 1,
					'sp_info'            => $configurationArray['sp_info'],
					'role_restriction'   => array(),
					'relay_restriction'  => array(),
					'attribute_mapping'  => array(),
				),
			);

			header('Content-Disposition: attachment; filename=miniorange-idp-config.json');
			echo json_encode($premiumFormat, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
			exit;
		}

		$this->setRedirect('index.php?option=com_joomlaidp&tab=com_miniorange_saml&tab-panel=sp', Text::_('COM_JOOMLAIDP_MSG_V'));
	}

	public function moGetConfigurationArray($className)
	{
		$customerResult = array();

		if ($className == 'MoSpInfo')
		{
			$customerResult = IDP_Utilities::fetchDatabaseValues('#__miniorangesamlidp', 'loadAssoc');
		}

		$classObject = call_user_func($className . '::getConstants');
		$moArray = array();

		foreach ($classObject as $key => $value)
		{
			if (!empty($customerResult) && !empty($customerResult[$value]))
			{
				$moArray[$key] = $customerResult[$value];
			}
		}

		return $moArray;
	}

	public function resetLogs(): void
	{
		$db = MoSamlIdpDb::getDb();
		$countQuery = $db->getQuery(true)
			->select('COUNT(*)')
			->from($db->quoteName('#__mo_idp_logs'));
		$db->setQuery($countQuery);
		$logCount = $db->loadResult();

		if ($logCount > 0)
		{
			$query = $db->getQuery(true)
				->delete($db->quoteName('#__mo_idp_logs'));
			$db->setQuery($query);
			$db->execute();
			$this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=errorlog', Text::_('COM_MINIORANGE_LOGGER_RESET_MESSAGE'));
		}
		else
		{
			$this->setRedirect('index.php?option=com_joomlaidp&view=accountsetup&tab-panel=errorlog', Text::_('COM_MINIORANGE_LOGGER_NO_LOGS_TO_RESET'));
		}
	}

	public function refreshLogs(): void
	{
		$this->setRedirect(
			'index.php?option=com_joomlaidp&view=accountsetup&tab-panel=errorlog',
			Text::_('COM_MINIORANGE_LOGGER_REFRESH_MESSAGE')
		);
	}

	public function downloadLogs(): void
	{
		$db = MoSamlIdpDb::getDb();
		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__mo_idp_logs'))
			->order('timestamp DESC');
		$db->setQuery($query);
		$logs = $db->loadObjectList();

		if (empty($logs))
		{
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
		fputcsv($output, array('Timestamp', 'Log Level', 'Code', 'Message'), ',', '"', '\\');

		foreach ($logs as $log)
		{
			$logData = json_decode($log->message, true);
			$logLevelProperty = 'log_level';
			fputcsv(
				$output,
				array(
					$log->timestamp,
					strtoupper($log->$logLevelProperty),
					$logData['code'] ?? '-',
					$logData['issue'] ?? $log->message,
				),
				',',
				'"',
				'\\'
			);
		}

		fclose($output);
		jexit();
	}

	private function buildSpDataObject(array $properties): stdClass
	{
		$data = new stdClass;

		foreach ($properties as $key => $value)
		{
			$property = $key;
			$data->$property = $value;
		}

		return $data;
	}

	private function ensureSpRecordDefaults(object $data): void
	{
		$defaults = array(
			'sp_name'             => '',
			'sp_entityid'         => '',
			'acs_url'             => '',
			'default_relay_state' => '',
			'nameid_format'       => '',
			'nameid_attribute'    => '',
			'enabled'             => 0,
			'assertion_signed'    => 0,
		);

		foreach ($defaults as $key => $default)
		{
			$property = $key;

			if (!property_exists($data, $key) || empty($data->$property))
			{
				$data->$property = $default;
			}
		}
	}
}
