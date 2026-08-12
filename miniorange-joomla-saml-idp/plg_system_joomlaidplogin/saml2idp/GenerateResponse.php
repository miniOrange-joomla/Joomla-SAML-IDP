<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  plg_system_joomlaidplogin
 * @author      miniOrange Security Software Pvt. Ltd.
 * @copyright   Copyright (C) 2015 miniOrange (https://www.miniorange.com)
 * @license     GNU General Public License version 3; see LICENSE.txt
 * @contact     info@xecurify.com
 */

defined('_JEXEC') or die;

class GenerateResponse
{
	/**
	 * The SAML response DOM document.
	 *
	 * @var  DOMDocument
	 */
	private $xml;

	/**
	 * Assertion consumer service URL.
	 *
	 * @var  string
	 */
	private $acsUrl;

	/**
	 * The response issuer.
	 *
	 * @var  string
	 */
	private $issuer;

	/**
	 * The SP audience.
	 *
	 * @var  string
	 */
	private $audience;

	/**
	 * The username sent in the assertion.
	 *
	 * @var  string
	 */
	private $username;

	/**
	 * The email sent in the assertion.
	 *
	 * @var  string
	 */
	private $email;

	/**
	 * The NameID attribute.
	 *
	 * @var  string
	 */
	private $nameIdAttr;

	/**
	 * The NameID attribute format.
	 *
	 * @var  string
	 */
	private $nameIdAttrFormat;

	/**
	 * The InResponseTo request identifier.
	 *
	 * @var  string
	 */
	private $inResponseTo;

	/**
	 * The assertion subject node.
	 *
	 * @var  DOMElement
	 */
	private $subject;

	/**
	 * Whether the assertion should be signed.
	 *
	 * @var  boolean
	 */
	private $moIdpAssertionSigned;

	public function __construct($email, $username, $acsUrl, $issuer, $audience, $nameIdAttr = null, $nameIdAttrFormat = null, $moIdpAssertionSigned = null, $inResponseTo = null)
	{
		$this->xml = new DOMDocument('1.0', 'utf-8');
		$this->acsUrl = $acsUrl;
		$this->issuer = $issuer;
		$this->audience = $audience;
		$this->email = $email;
		$this->username = $username;
		$this->nameIdAttr = $nameIdAttr;
		$this->nameIdAttrFormat = $nameIdAttrFormat;
		$this->moIdpAssertionSigned = $moIdpAssertionSigned;
		$this->inResponseTo = $inResponseTo;
	}

	public function createSamlResponse()
	{
		$responseParams = $this->getResponseParams();

		$resp = $this->createResponseElement($responseParams);
		$this->xml->appendChild($resp);

		$issuer = $this->buildIssuer();
		$resp->appendChild($issuer);

		$status = $this->buildStatus();
		$resp->appendChild($status);

		$statusCode = $this->buildStatusCode();
		$status->appendChild($statusCode);

		$assertion = $this->buildAssertion($responseParams);
		$resp->appendChild($assertion);

		if ($this->moIdpAssertionSigned)
		{
			$privateKey = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'cert' . DIRECTORY_SEPARATOR . 'idp-signing.key';
			$this->signNode($privateKey, $assertion, $this->subject, $responseParams);
		}

		$samlResponse = $this->xml->saveXML();

		return $samlResponse;
	}

	public function getResponseParams()
	{
		$responseParams = array();
		$time = time();
		$responseParams['IssueInstant'] = str_replace('+00:00', 'Z', gmdate('c', $time));
		$responseParams['NotOnOrAfter'] = str_replace('+00:00', 'Z', gmdate('c', $time + 300));
		$responseParams['NotBefore'] = str_replace('+00:00', 'Z', gmdate('c', $time - 30));
		$responseParams['AuthnInstant'] = str_replace('+00:00', 'Z', gmdate('c', $time - 120));
		$responseParams['SessionNotOnOrAfter'] = str_replace('+00:00', 'Z', gmdate('c', $time + 3600 * 8));
		$responseParams['ID'] = $this->generateUniqueID(40);
		$responseParams['AssertID'] = $this->generateUniqueID(40);
		$responseParams['Issuer'] = $this->issuer;
		$publicKey = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'cert' . DIRECTORY_SEPARATOR . 'idp-signing.crt';
		$objKey = new XMLSecurityKeyIDP(XMLSecurityKeyIDP::RSA_SHA256, array('type' => 'public'));
		$objKey->loadKey($publicKey, true, true);
		$responseParams['x509'] = $objKey->getX509Certificate();

		return $responseParams;
	}

	public function createResponseElement($responseParams)
	{
		$resp = $this->xml->createElementNS('urn:oasis:names:tc:SAML:2.0:protocol', 'samlp:Response');
		$resp->setAttribute('ID', $responseParams['ID']);
		$resp->setAttribute('Version', '2.0');
		$resp->setAttribute('IssueInstant', $responseParams['IssueInstant']);
		$resp->setAttribute('Destination', $this->acsUrl);

		if (isset($this->inResponseTo) && !is_null($this->inResponseTo))
		{
			$resp->setAttribute('InResponseTo', $this->inResponseTo);
		}

		return $resp;
	}

	public function buildIssuer()
	{
		$issuer = $this->xml->createElementNS('urn:oasis:names:tc:SAML:2.0:assertion', 'saml:Issuer', $this->issuer);

		return $issuer;
	}

	public function buildStatus()
	{
		$status = $this->xml->createElementNS('urn:oasis:names:tc:SAML:2.0:protocol', 'samlp:Status');

		return $status;
	}

	public function buildStatusCode()
	{
		$statusCode = $this->xml->createElementNS('urn:oasis:names:tc:SAML:2.0:protocol', 'samlp:StatusCode');
		$statusCode->setAttribute('Value', 'urn:oasis:names:tc:SAML:2.0:status:Success');

		return $statusCode;
	}

	public function buildAssertion($responseParams)
	{
		$assertion = $this->xml->createElementNS('urn:oasis:names:tc:SAML:2.0:assertion', 'saml:Assertion');
		$assertion->setAttribute('ID', $responseParams['AssertID']);
		$assertion->setAttribute('IssueInstant', $responseParams['IssueInstant']);
		$assertion->setAttribute('Version', '2.0');

		$issuer = $this->buildIssuer($responseParams);
		$assertion->appendChild($issuer);

		$subject = $this->buildSubject($responseParams);
		$this->subject = $subject;
		$assertion->appendChild($subject);

		$condition = $this->buildCondition($responseParams);
		$assertion->appendChild($condition);

		$authnstat = $this->buildAuthnStatement($responseParams);
		$assertion->appendChild($authnstat);

		return $assertion;
	}

	public function buildSubject($responseParams)
	{
		$subject = $this->xml->createElement('saml:Subject');
		$nameid = $this->buildNameIdentifier();
		$subject->appendChild($nameid);
		$confirmation = $this->buildSubjectConfirmation($responseParams);
		$subject->appendChild($confirmation);

		return $subject;
	}

	public function buildNameIdentifier()
	{
		if ($this->nameIdAttr === 'emailAddress')
		{
			$nameid = $this->xml->createElement('saml:NameID', $this->email);
		}
		else
		{
			$nameid = $this->xml->createElement('saml:NameID', $this->username);
		}

		if (empty($this->nameIdAttrFormat))
		{
			$nameid->setAttribute('Format', 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress');
		}
		else
		{
			$nameid->setAttribute('Format', $this->nameIdAttrFormat);
		}

		$nameid->setAttribute('SPNameQualifier', $this->audience);

		return $nameid;
	}

	public function buildSubjectConfirmation($responseParams)
	{
		$confirmation = $this->xml->createElement('saml:SubjectConfirmation');
		$confirmation->setAttribute('Method', 'urn:oasis:names:tc:SAML:2.0:cm:bearer');
		$confirmationdata = $this->getSubjectConfirmationData($responseParams);
		$confirmation->appendChild($confirmationdata);

		return $confirmation;
	}

	public function getSubjectConfirmationData($responseParams)
	{
		$confirmationdata = $this->xml->createElement('saml:SubjectConfirmationData');
		$confirmationdata->setAttribute('NotOnOrAfter', $responseParams['NotOnOrAfter']);
		$confirmationdata->setAttribute('Recipient', $this->acsUrl);

		if (isset($this->inResponseTo) && !is_null($this->inResponseTo))
		{
			$confirmationdata->setAttribute('InResponseTo', $this->inResponseTo);
		}

		return $confirmationdata;
	}

	public function buildCondition($responseParams)
	{
		$condition = $this->xml->createElement('saml:Conditions');
		$condition->setAttribute('NotBefore', $responseParams['NotBefore']);
		$condition->setAttribute('NotOnOrAfter', $responseParams['NotOnOrAfter']);

		$audiencer = $this->buildAudienceRestriction();
		$condition->appendChild($audiencer);

		return $condition;
	}

	public function buildAudienceRestriction()
	{
		$audiencer = $this->xml->createElement('saml:AudienceRestriction');
		$audience = $this->xml->createElement('saml:Audience', $this->audience);
		$audiencer->appendChild($audience);

		return $audiencer;
	}

	public function buildAuthnStatement($responseParams)
	{
		$authnstat = $this->xml->createElement('saml:AuthnStatement');
		$authnstat->setAttribute('AuthnInstant', $responseParams['AuthnInstant']);
		$authnstat->setAttribute('SessionIndex', '_' . $this->generateUniqueID(30));
		$authnstat->setAttribute('SessionNotOnOrAfter', $responseParams['SessionNotOnOrAfter']);

		$authncontext = $this->xml->createElement('saml:AuthnContext');
		$authncontextRef = $this->xml->createElement('saml:AuthnContextClassRef', 'urn:oasis:names:tc:SAML:2.0:ac:classes:PasswordProtectedTransport');
		$authncontext->appendChild($authncontextRef);
		$authnstat->appendChild($authncontext);

		return $authnstat;
	}

	public function signNode($privateKey, $node, $subject, $responseParams)
	{
		$objKey = new XMLSecurityKeyIDP(XMLSecurityKeyIDP::RSA_SHA256, array('type' => 'private'));
		$objKey->loadKey($privateKey, true);

		$objXMLSecDSig = new XMLSecurityDSigIDP;
		$objXMLSecDSig->setCanonicalMethod(XMLSecurityDSigIDP::EXC_C14N);

		$objXMLSecDSig->addReferenceList(
			array($node),
			XMLSecurityDSigIDP::SHA256,
			array('http://www.w3.org/2000/09/xmldsig#enveloped-signature', XMLSecurityDSigIDP::EXC_C14N),
			array('id_name' => 'ID', 'overwrite' => false)
		);
		$objXMLSecDSig->sign($objKey);
		$objXMLSecDSig->add509Cert($responseParams['x509']);
		$objXMLSecDSig->insertSignature($node, $subject);
	}

	public function generateUniqueID($length)
	{
		$bytes = random_bytes((int) ceil($length / 2));
		$uniqueID = substr(bin2hex($bytes), 0, $length);

		return 'a' . $uniqueID;
	}
}
