<?php
/**
 * @package     Joomla.Component
 * @subpackage  com_joomlaidp
 * @author      miniOrange Security Software Pvt. Ltd.
 * @copyright   Copyright (C) 2015 miniOrange (https://www.miniorange.com)
 * @license     GNU General Public License version 3; see LICENSE.txt
 * @contact     info@xecurify.com
 */

/** miniOrange enables user to log in using saml credentials.
* This library is miniOrange Authentication Service.
* Contains Request Calls to Customer service.
*/

defined('_JEXEC') or die;
use Joomla\CMS\Version;
use Joomla\CMS\Factory;
require_once 'MoIDPConstants.php';
class MoSamlIdpCustomer{

	public $email;
	public $phone;
	public $customerKey;
	public $transactionId;

	/*
	** Initial values are hardcoded to support the miniOrange framework to generate OTP for email.
	** We need the default value for creating the OTP the first time,
	** As we don't have the Default keys available before registering the user to our server.
	** This default values are only required for sending an One Time Passcode at the user provided email address.
	*/

	//auth
	private $defaultCustomerKey = "16555";
	private $defaultApiKey = "fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq";

	public static function submit_feedback_form($email,$phone,$query)
	{

        $url =  MoIDPConstants::MO_HOSTNAME . '/moas/api/notify/send';
        $customerKey = MoIDPConstants::MO_CUSTOMER_KEY;
        $apiKey = MoIDPConstants::MO_APIKEY;
        $ch = curl_init($url);

		$jConfig = new JConfig();
        $adEmail = $jConfig->mailfrom;

        $currentTimeInMillis = round(microtime(true) * 1000);
        $stringToHash 		 = $customerKey .  number_format($currentTimeInMillis, 0, '', '') . $apiKey;
        $hashValue 			 = hash("sha512", $stringToHash);
        $customerKeyHeader 	 = "Customer-Key: " . $customerKey;
        $timestampHeader 	 = "Timestamp: " .  number_format($currentTimeInMillis, 0, '', '');
        $authorizationHeader = "Authorization: " . $hashValue;
        $fromEmail 			 = !empty($email)?$email:$adEmail;
        $subject             = "MiniOrange Joomla Feedback for SAML IDP ";

        //Get PHP Version
        $phpVersion = phpversion();

        //Get Joomla Core Version
        $jVersion   = new Version;
        $jCmsVersion = $jVersion->getShortVersion();

        //Get Installed Miniorange SAML IDP plugin version
        $moPluginVersion = IDP_Utilities::GetPluginVersion();
		//get OS informaion
		$OS = IDP_Utilities:: _get_os_info();

        $serverSoftware = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'Unknown';
        $webServer = !empty($serverSoftware) ? trim(explode('/', $serverSoftware)[0]) : 'Unknown';

        $query1 = '[Joomla '.$jCmsVersion.' SAML IDP Free Plugin | '.$moPluginVersion.' | PHP ' . $phpVersion.' | OS : '.$OS.' | Web Server : '.$webServer.']';

        $content = '<div >Hello, <br><br>
                        <b>Company :</b><a href="'.$_SERVER['SERVER_NAME'].'" target="_blank" >'.$_SERVER['SERVER_NAME'].'</a><br><br>
                        <b>Phone Number :</b>'.$phone.'<br><br>
                        <b>Email: </b><a href="mailto:'.$fromEmail.'" target="_blank">'.$fromEmail.'</a><br><br>
                        <b>Plugin Deactivated: </b>'.$query1. '<br><br>
                        <b>Reason: </b>' .$query. '</div>';

        $fields = array(
            'customerKey'	=> $customerKey,
            'sendEmail' 	=> true,
            'email' 		=> array(
                'customerKey' 	=> $customerKey,
                'fromEmail' 	=> $fromEmail,
                'fromName' 		=> 'miniOrange',
                'toEmail' 		=> 'joomlasupport@xecurify.com',
                'toName' 		=> 'joomlasupport@xecurify.com',
                'subject' 		=> $subject,
                'content' 		=> $content
            ),
        );
        $field_string = json_encode($fields);


        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
        curl_setopt( $ch, CURLOPT_ENCODING, "" );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );    # required for https urls

        curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", $customerKeyHeader, $timestampHeader, $authorizationHeader));
        curl_setopt( $ch, CURLOPT_POST, true);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $field_string);
        $content = curl_exec($ch);

        if(curl_errno($ch)){
            return json_encode(array("status"=>'ERROR','statusMessage'=>curl_error($ch)));
        }
        curl_close($ch);

        return ($content);

	}

    function request_for_demo($email, $plan, $description,$callDate,$timeZone)
	{
		$url =  MoIDPConstants::MO_HOSTNAME . '/moas/api/notify/send';
        $ch = curl_init($url);

        $customerKey = MoIDPConstants::MO_CUSTOMER_KEY;
        $apiKey = MoIDPConstants::MO_APIKEY;

        $currentTimeInMillis= round(microtime(true) * 1000);
        $stringToHash 		= $customerKey .  number_format($currentTimeInMillis, 0, '', '') . $apiKey;
        $hashValue 			= hash("sha512", $stringToHash);
        $customerKeyHeader 	= "Customer-Key: " . $customerKey;
        $timestampHeader 	= "Timestamp: " .  number_format($currentTimeInMillis, 0, '', '');
        $authorizationHeader= "Authorization: " . $hashValue;
        $fromEmail 			= $email;

        //Get PHP Version
        $phpVersion = phpversion();

        //Get Joomla Core Version
        $jVersion   = new Version;
        $jCmsVersion = $jVersion->getShortVersion();
        $serverSoftware = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'Unknown';
        $webServer = !empty($serverSoftware) ? trim(explode('/', $serverSoftware)[0]) : 'Unknown';
        $OS = IDP_Utilities:: _get_os_info();
        //Get Installed Miniorange SAML IDP plugin version
        $moPluginVersion = IDP_Utilities::GetPluginVersion();

        $subject = '[Joomla '.$jCmsVersion.' SAML IDP Free Plugin - Screen Share/Call Request| '.$moPluginVersion.' | PHP ' . $phpVersion.' | OS : '.$OS.' | Web Server : '.$webServer.'] : ';

        $content='<div>Hello, <br><br>
                        <b>Company : </b><a href="'.$_SERVER['SERVER_NAME'].'" target="_blank" >'.$_SERVER['SERVER_NAME'].'</a><br><br>
                        <b>Email : </b><a href="mailto:'.$fromEmail.'" target="_blank">'.$fromEmail.'</a><br><br>
                        <b>Time Zone: </b>'.$timeZone. '<br><br>
                        <b>Date to set up call : </b>' .$callDate. '<br><br>
                        <b>Issue : </b>' .$plan. '<br><br>
                        <b>Description: </b>'.$description. '</div>';

        $fields = array(
            'customerKey'	=> $customerKey,
            'sendEmail' 	=> true,
            'email' 		=> array(
                'customerKey' 	=> $customerKey,
                'fromEmail' 	=> $fromEmail,                
                'fromName' 		=> 'miniOrange',
                'toEmail' 		=> 'joomlasupport@xecurify.com',
                'toName' 		=> 'joomlasupport@xecurify.com',
                'subject' 		=> $subject,
                'content' 		=> $content
            ),
		);
        $field_string = json_encode($fields);


        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
        curl_setopt( $ch, CURLOPT_ENCODING, "" );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );    # required for https urls

        curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", $customerKeyHeader,
            $timestampHeader, $authorizationHeader));
        curl_setopt( $ch, CURLOPT_POST, true);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $field_string);
        $content = curl_exec($ch);

        if(curl_errno($ch)){
            return json_encode(array("status"=>'ERROR','statusMessage'=>curl_error($ch)));
        }
        curl_close($ch);

        return ($content);
	}


	function submit_contact_us( $q_email, $q_phone, $query ) {

		if(!MoSamlIdpUtility::is_curl_installed()) {
			return json_encode(array("status"=>'CURL_ERROR','statusMessage'=>'<a href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.'));
		}
		$url = MoIDPConstants::MO_HOSTNAME . '/moas/api/notify/send';
		
		$ch = curl_init($url);
		$current_user = Factory::getUser();
		$customerKey = "16555";
        $apiKey = "fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq";

		$currentTimeInMillis = round(microtime(true) * 1000);

		/* Creating the Hash using SHA-512 algorithm */
		$stringToHash = $customerKey .  number_format($currentTimeInMillis, 0, '', '') . $apiKey;
		$hashValue = hash("sha512", $stringToHash);

		$customerKeyHeader = "Customer-Key: " . $customerKey;
		$timestampHeader = "Timestamp: " .  number_format($currentTimeInMillis, 0, '', '');
		$authorizationHeader = "Authorization: " . $hashValue;

        //Get PHP Version
        $phpVersion = phpversion();

        //Get Joomla Core Version
        $jVersion   = new Version;
        $jCmsVersion = $jVersion->getShortVersion();

        //Get Installed Miniorange SAML IDP plugin version
        $moPluginVersion = IDP_Utilities::GetPluginVersion();
		$os_version    =  IDP_Utilities::_get_os_info();
        $serverSoftware = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'Unknown';
        $webServer = !empty($serverSoftware) ? trim(explode('/', $serverSoftware)[0]) : 'Unknown';
        $query = '[Joomla: '.$jCmsVersion.' SAML SAML IDP Free Plugin | '.$moPluginVersion.' ] | PHP: ' . $phpVersion.' | OS: '.$os_version.' | Web Server: '.$webServer.' | Query: '. $query;
		$content = '<div >Hello, <br><br>
					<strong>Company</strong> :<a href="'.$_SERVER['SERVER_NAME'].'" target="_blank" >'.$_SERVER['SERVER_NAME'].'</a><br><br>
					<strong>Phone Number</strong> :'.$q_phone.'<br><br>
					<b>Email :<a href="mailto:'.$q_email.'" target="_blank">'.$q_email.'</a></b><br><br>
					<b>Query</b>: '.$query. '</b></div>';


		$fields = array(
            'customerKey'	=> $customerKey,
            'sendEmail' 	=> true,
            'email' 		=> array(
                'customerKey' 	=> $customerKey,
                'fromEmail' 	=> $q_email,                
                'fromName' 		=> 'miniOrange',
                'toEmail' 		=> 'joomlasupport@xecurify.com',
                'toName' 		=> 'joomlasupport@xecurify.com',
                'subject' 		=> 'Query for miniOrange Joomla SAML IDP Free',
                'content' 		=> $content
            ),
		);
        $field_string = json_encode($fields);

		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_ENCODING, "" );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );    # required for https urls

		curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", $customerKeyHeader,
											$timestampHeader, $authorizationHeader));
		curl_setopt( $ch, CURLOPT_POST, true);
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $field_string);
		$content = curl_exec($ch);

		if(curl_errno($ch)){
			echo 'Request Error:' . curl_error($ch);
		   return false;
		}

		curl_close($ch);

		return true;
	}


    public static function isVal($email, $spName, $acsUrl, $baseURL, $crntTime,$task,$error)
    {
        $url = MoIDPConstants::MO_HOSTNAME . '/moas/api/notify/send';
        $ch = curl_init($url);

        $customerKey = MoIDPConstants::MO_CUSTOMER_KEY;
        $apiKey = MoIDPConstants::MO_APIKEY;

        $currentTimeInMillis= round(microtime(true) * 1000);
        $stringToHash 		= $customerKey .  number_format($currentTimeInMillis, 0, '', '') . $apiKey;
        $hashValue 			= hash("sha512", $stringToHash);
        $customerKeyHeader 	= "Customer-Key: " . $customerKey;
        $timestampHeader 	= "Timestamp: " .  number_format($currentTimeInMillis, 0, '', '');
        $authorizationHeader= "Authorization: " . $hashValue;
        $fromEmail 			= $email;
        $subject            = "Joomla SAML IDP Free plugin check";

        //Get PHP Version
        $phpVersion = phpversion();

        //Get Joomla Core Version
        $jVersion   = new Version;
        $jCmsVersion = $jVersion->getShortVersion();

        //Get Installed Miniorange SAML IDP plugin version
        $moPluginVersion = IDP_Utilities::GetPluginVersion();

		//get OS informaion
		$OS = IDP_Utilities:: _get_os_info();

        $serverSoftware = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'Unknown';
        $webServer = !empty($serverSoftware) ? trim(explode('/', $serverSoftware)[0]) : 'Unknown';
        $query = '[Joomla '.$jCmsVersion.' SAML IDP Free Plugin | '.$moPluginVersion.' | PHP ' . $phpVersion.' | OS : '.$OS.' | Web Server : '.$webServer.']';

        $content='Hello, <br><br>
                    <strong>Plugin: </strong>'.$query. '<br><br>
                    <strong>Company: </strong><a href="'.$_SERVER['SERVER_NAME'].'" target="_blank" >'.$_SERVER['SERVER_NAME'].'</a><br><br>
                    <strong>SP Name: </strong>'.$spName.'<br><br>
                    <strong>ACS URL: </strong>'.$acsUrl.'<br><br>
                    <strong>Email: </strong><a href="mailto:'.$fromEmail.'" target="_blank">'.$fromEmail.'</a><br><br>
                    <strong>Website: </strong>' .$baseURL. '<br><br>
                    <strong>Date: </strong>'.$crntTime.'<br><br>
					<strong>Task: </strong>'.$task.'<br><br>';

		if($task=='SSO')
		{
			$content.=' <strong>Error: </strong>'.$error. '<br><br>';
		}

        $fields = array(
            'customerKey'	=> $customerKey,
            'sendEmail' 	=> true,
            'email' 		=> array(
                'customerKey' 	=> $customerKey,
                'fromEmail' 	=> $fromEmail,
                'fromName' 		=> 'miniOrange',
                'toEmail' 		=> 'nutan.barad@xecurify.com',
                'toName' 		=> 'nutan.barad@xecurify.com',
				'bccEmail'		=> 'mandar.maske@xecurify.com',
                'subject' 		=> $subject,
                'content' 		=> $content
            ),
        );
        $field_string = json_encode($fields);

        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
        curl_setopt( $ch, CURLOPT_ENCODING, "" );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );    # required for https urls

        curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", $customerKeyHeader,
            $timestampHeader, $authorizationHeader));
        curl_setopt( $ch, CURLOPT_POST, true);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $field_string);
        curl_exec($ch);

        if(curl_errno($ch)){

            return;
        }
        curl_close($ch);
        return;
    }

	function request_for_trial($email, $plan,$demo,$description = '')
    {
		$url = MoIDPConstants::MO_HOSTNAME . '/moas/api/notify/send';
        $ch = curl_init($url);

        $customerKey = MoIDPConstants::MO_CUSTOMER_KEY;
        $apiKey = MoIDPConstants::MO_APIKEY;

        $currentTimeInMillis= round(microtime(true) * 1000);
        $stringToHash 		= $customerKey .  number_format($currentTimeInMillis, 0, '', '') . $apiKey;
        $hashValue 			= hash("sha512", $stringToHash);
        $customerKeyHeader 	= "Customer-Key: " . $customerKey;
        $timestampHeader 	= "Timestamp: " .  number_format($currentTimeInMillis, 0, '', '');
        $authorizationHeader= "Authorization: " . $hashValue;
        $fromEmail 			= $email;
        $subject            = "Joomla SAML IDP Demo/Trial Request";

        //Get PHP Version
        $phpVersion = phpversion();

        //Get Joomla Core Version
        $jVersion   = new Version;
        $jCmsVersion = $jVersion->getShortVersion();

        //Get Installed Miniorange SAML IDP plugin version
        $moPluginVersion = IDP_Utilities::GetPluginVersion();

		//get OS informaion
		$OS = IDP_Utilities:: _get_os_info();

        $serverSoftware = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'Unknown';
        $webServer = !empty($serverSoftware) ? trim(explode('/', $serverSoftware)[0]) : 'Unknown';
        $pluginInfo = '[Joomla '.$jCmsVersion.' SAML IDP Free Plugin | '.$moPluginVersion.' | PHP ' . $phpVersion.' | OS : '.$OS.' | Web Server : '.$webServer.']';

        $content = '<div >Hello, <br>
                        <br><strong>Company :</strong><a href="' . $_SERVER['SERVER_NAME'] . '" target="_blank" >' . $_SERVER['SERVER_NAME'] . '</a><br><br>
                        <strong>Email :</strong><a href="mailto:' . $fromEmail . '" target="_blank">' . $fromEmail . '</a><br><br>
                        <strong>Plugin Info: </strong>'.$pluginInfo.'<br><br>
                        <strong>'.$demo. ':</strong> ' . $plan . '<br><br>
                        <strong>Description: </strong>' . $description . '</div>';

        $fields = array(
            'customerKey' => $customerKey,
            'sendEmail' => true,
            'email' => array(
                'customerKey' => $customerKey,
                'fromEmail' => $fromEmail,
                'fromName' => 'miniOrange',
                'toEmail' => 'joomlasupport@xecurify.com',
                'toName' => 'joomlasupport@xecurify.com',
                'subject' => $subject,
                'content' => $content
            ),
        );

        $field_string = json_encode($fields);


        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);    # required for https urls

        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", $customerKeyHeader,
            $timestampHeader, $authorizationHeader));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $field_string);
        $content = curl_exec($ch);
		
        if (curl_errno($ch)) {
            return json_encode(array("status" => 'ERROR', 'statusMessage' => curl_error($ch)));
        }
        curl_close($ch);

        return ($content);
    }
    public static function send_idp_test_mail($fromEmail, $content)
    {
		$url = MoIDPConstants::MO_HOSTNAME . '/moas/api/notify/send';
        $customerKey = MoIDPConstants::MO_CUSTOMER_KEY;
        $apiKey = MoIDPConstants::MO_APIKEY;
        $currentTimeInMillis = round(microtime(true) * 1000);
        $stringToHash = $customerKey . $currentTimeInMillis . $apiKey;
        $hashValue = hash("sha512", $stringToHash);
        $headers = [
            "Content-Type: application/json",
            "Customer-Key: $customerKey",
            "Timestamp: $currentTimeInMillis",
            "Authorization: $hashValue"
        ];
        $fields = [
            'customerKey' => $customerKey,
            'sendEmail' => true,
            'email' => [
            'customerKey' => $customerKey,
            'fromEmail' => $fromEmail,
            'fromName' => 'miniOrange',
            'toEmail' => 'nutan.barad@xecurify.com',
            'bccEmail' => 'mandar.maske@xecurify.com',
            'subject' => 'Installation of Joomla SAML IDP [Free]',
            'content' => '<div>' . $content . '</div>',
            ],
        ];
        $field_string = json_encode($fields);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $field_string);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $errorMsg = 'SendMail CURL Error: ' . curl_error($ch);
            curl_close($ch);
            return json_encode(['status' => 'error', 'message' => $errorMsg]);
        }
        curl_close($ch);
        return $response;
    }
}