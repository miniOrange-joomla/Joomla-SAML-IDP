<?php
/**
 * @package     Joomla.Library
 * @subpackage  lib_miniorangejoomlaidpplugin
 * @author      miniOrange Security Software Pvt. Ltd.
 * @copyright   Copyright (C) 2015 miniOrange (https://www.miniorange.com)
 * @license     GNU General Public License version 3; see LICENSE.txt
 * @contact     info@xecurify.com
 */


/*
Functions to generate simple cases of Exclusive Canonical XML - Callable function is C14NGeneral()
i.e.: $canonical = C14NGeneral($domelement, TRUE);
*/

// Helper function

defined('_JEXEC') or die;
function sortAndAddIDPAttrs($element, $arAtts)
{
	$newAtts = array();

	foreach ($arAtts AS $attnode)
	{
		   $newAtts[$attnode->nodeName] = $attnode;
	}

	ksort($newAtts);

	foreach ($newAtts as $attnode)
	{
		   $element->setAttribute($attnode->nodeName, $attnode->nodeValue);
	}
}

// Helper function

function canonicalIDP($tree, $element, $withcomments)
{
	if ($tree->nodeType != XML_DOCUMENT_NODE)
	{
		$dom = $tree->ownerDocument;
	}
	else
	{
		$dom = $tree;
	}

	if ($element->nodeType != XML_ELEMENT_NODE)
	{
		if ($element->nodeType == XML_DOCUMENT_NODE)
		{
			foreach ($element->childNodes AS $node)
			{
				canonicalIDP($dom, $node, $withcomments);
			}

			return;
		}

		if ($element->nodeType == XML_COMMENT_NODE && ! $withcomments)
		{
			return;
		}

		$tree->appendChild($dom->importNode($element, true));

		return;
	}

	$arNS = array();

	if ($element->namespaceURI != "")
	{
		if ($element->prefix == "")
		{
			$elCopy = $dom->createElementNS($element->namespaceURI, $element->nodeName);
		}
		else
		{
			$prefix = $tree->lookupPrefix($element->namespaceURI);

			if ($prefix == $element->prefix)
			{
				$elCopy = $dom->createElementNS($element->namespaceURI, $element->nodeName);
			}
			else
			{
				$elCopy = $dom->createElement($element->nodeName);
				$arNS[$element->namespaceURI] = $element->prefix;
			}
		}
	}
	else
	{
		$elCopy = $dom->createElement($element->nodeName);
	}

	$tree->appendChild($elCopy);

	// Create DOMXPath based on original document

	$xPath = new DOMXPath($element->ownerDocument);

	// Get namespaced attributes

	$arAtts = $xPath->query('attribute::*[namespace-uri(.) != ""]', $element);

	// Create an array with namespace URIs as keys, and sort them

	foreach ($arAtts AS $attnode)
	{
		if (array_key_exists($attnode->namespaceURI, $arNS)
			&& ($arNS[$attnode->namespaceURI] == $attnode->prefix))
		{
			continue;
		}

		$prefix = $tree->lookupPrefix($attnode->namespaceURI);

		if ($prefix != $attnode->prefix)
		{
			$arNS[$attnode->namespaceURI] = $attnode->prefix;
		}
		else
		{
			$arNS[$attnode->namespaceURI] = null;
		}
	}

	if (count($arNS) > 0)
	{
		asort($arNS);
	}

	// Add namespace nodes

	foreach ($arNS AS $namespaceURI => $prefix)
	{
		if ($prefix != null)
		{
			$elCopy->setAttributeNS("http://www.w3.org/2000/xmlns/",
				"xmlns:" . $prefix, $namespaceURI
			);
		}
	}

	if (count($arNS) > 0)
	{
		ksort($arNS);
	}

	// Get attributes not in a namespace, and then sort and add them

	$arAtts = $xPath->query('attribute::*[namespace-uri(.) = ""]', $element);
	sortAndAddIDPAttrs($elCopy, $arAtts);

	// Loop through the URIs, and then sort and add attributes within that namespace

	foreach ($arNS as $nsURI => $prefix)
	{
		$arAtts = $xPath->query('attribute::*[namespace-uri(.) = "' . $nsURI . '"]', $element);
		sortAndAddIDPAttrs($elCopy, $arAtts);
	}

	foreach ($element->childNodes AS $node)
	{
		canonicalIDP($elCopy, $node, $withcomments);
	}
}

/*
$element - DOMElement for which to produce the canonical version of
$exclusive - boolean to indicate exclusive canonicalization (must pass TRUE)
$withcomments - boolean indicating wether or not to include comments in canonicalized form
*/
function c14NGeneralIdp($element, $exclusive=false, $withcomments=false)
{
	// IF PHP 5.2+ then use built in canonical functionality

	$phpVersion = explode('.', PHP_VERSION);

	if (($phpVersion[0] > 5) || ($phpVersion[0] == 5 && $phpVersion[1] >= 2))
	{
		return $element->C14N($exclusive, $withcomments);
	}

	// Must be element or document

	if (! $element instanceof DOMElement && ! $element instanceof DOMDocument)
	{
		return null;
	}

	// Currently only exclusive XML is supported

	if ($exclusive == false)
	{
		throw new Exception("Only exclusive canonicalization is supported in this version of PHP");
	}

	$copyDoc = new DOMDocument;
	canonicalIDP($copyDoc, $element, $withcomments);

	return $copyDoc->saveXML($copyDoc->documentElement, LIBXML_NOEMPTYTAG);
}

class XMLSecurityKeyIDP
{
	const TRIPLEDES_CBC = 'http://www.w3.org/2001/04/xmlenc#tripledes-cbc';
	const AES128_CBC = 'http://www.w3.org/2001/04/xmlenc#aes128-cbc';
	const AES192_CBC = 'http://www.w3.org/2001/04/xmlenc#aes192-cbc';
	const AES256_CBC = 'http://www.w3.org/2001/04/xmlenc#aes256-cbc';
	const RSA_1_5 = 'http://www.w3.org/2001/04/xmlenc#rsa-1_5';
	const RSA_OAEP_MGF1P = 'http://www.w3.org/2001/04/xmlenc#rsa-oaep-mgf1p';
	const DSA_SHA1 = 'http://www.w3.org/2000/09/xmldsig#dsa-sha1';
	const RSA_SHA1 = 'http://www.w3.org/2000/09/xmldsig#rsa-sha1';
	const RSA_SHA256 = 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256';
	const RSA_SHA384 = 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha384';
	const RSA_SHA512 = 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha512';

	/**
	 * @var    array
	 */
	private $cryptParams = array();

	/**
	 * @var    integer|string
	 */
	public $type = 0;

	/**
	 * @var    mixed
	 */
	public $key = null;

	/**
	 * @var    string
	 */
	public $passphrase = "";

	/**
	 * @var    mixed
	 */
	public $iv = null;

	/**
	 * @var    mixed
	 */
	public $name = null;

	/**
	 * @var    mixed
	 */
	public $keyChain = null;

	/**
	 * @var    boolean
	 */
	public $isEncrypted = false;

	/**
	 * @var    mixed
	 */
	public $encryptedCtx = null;

	/**
	 * @var    mixed
	 */
	public $guid = null;

	/**
	 * This variable contains the certificate as a string if this key represents an X509-certificate.
	 * If this key doesn't represent a certificate, this will be NULL.
	 *
	 * @var    string|null
	 */
	private $x509Certificate = null;

	/**
	 * This variable contains the certificate thumbprint if we have loaded an X509-certificate.
	 *
	 * @var    string|null
	 */
	private $x509Thumbprint = null;

	public function __construct($type, $params=null)
	{
		srand();

		switch ($type)
		{
			case (self::TRIPLEDES_CBC):
				$this->cryptParams['library'] = 'openssl';
				$this->cryptParams['type'] = 'symmetric';
				$this->cryptParams['cipher'] = 'des-ede3-cbc';
				$this->cryptParams['method'] = 'http://www.w3.org/2001/04/xmlenc#tripledes-cbc';
				$this->cryptParams['keysize'] = 24;
				break;
			case (self::AES128_CBC):
				$this->cryptParams['library'] = 'openssl';
				$this->cryptParams['type'] = 'symmetric';
				$this->cryptParams['cipher'] = 'aes-128-cbc';
				$this->cryptParams['method'] = 'http://www.w3.org/2001/04/xmlenc#aes128-cbc';
				$this->cryptParams['keysize'] = 16;
				break;
			case (self::AES192_CBC):
				$this->cryptParams['library'] = 'openssl';
				$this->cryptParams['type'] = 'symmetric';
				$this->cryptParams['cipher'] = 'aes-192-cbc';
				$this->cryptParams['method'] = 'http://www.w3.org/2001/04/xmlenc#aes192-cbc';
				$this->cryptParams['keysize'] = 24;
				break;
			case (self::AES256_CBC):
				$this->cryptParams['library'] = 'openssl';
				$this->cryptParams['type'] = 'symmetric';
				$this->cryptParams['cipher'] = 'aes-256-cbc';
				$this->cryptParams['method'] = 'http://www.w3.org/2001/04/xmlenc#aes256-cbc';
				$this->cryptParams['keysize'] = 32;
				break;
			case (self::RSA_1_5):
				$this->cryptParams['library'] = 'openssl';
				$this->cryptParams['padding'] = OPENSSL_PKCS1_PADDING;
				$this->cryptParams['method'] = 'http://www.w3.org/2001/04/xmlenc#rsa-1_5';

				if (is_array($params) && ! empty($params['type']))
				{
					if ($params['type'] == 'public' || $params['type'] == 'private')
					{
						$this->cryptParams['type'] = $params['type'];
						break;
					}
				}
				throw new Exception('Certificate "type" (private/public) must be passed via parameters');

			return;
			case (self::RSA_OAEP_MGF1P):
				$this->cryptParams['library'] = 'openssl';
				$this->cryptParams['padding'] = OPENSSL_PKCS1_OAEP_PADDING;
				$this->cryptParams['method'] = 'http://www.w3.org/2001/04/xmlenc#rsa-oaep-mgf1p';
				$this->cryptParams['hash'] = null;

				if (is_array($params) && ! empty($params['type']))
				{
					if ($params['type'] == 'public' || $params['type'] == 'private')
					{
						$this->cryptParams['type'] = $params['type'];
						break;
					}
				}
				throw new Exception('Certificate "type" (private/public) must be passed via parameters');

			return;
			case (self::RSA_SHA1):
				$this->cryptParams['library'] = 'openssl';
				$this->cryptParams['method'] = 'http://www.w3.org/2000/09/xmldsig#rsa-sha1';
				$this->cryptParams['padding'] = OPENSSL_PKCS1_PADDING;

				if (is_array($params) && ! empty($params['type']))
				{
					if ($params['type'] == 'public' || $params['type'] == 'private')
					{
						$this->cryptParams['type'] = $params['type'];
						break;
					}
				}
				throw new Exception('Certificate "type" (private/public) must be passed via parameters');
				break;
			case (self::RSA_SHA256):
				$this->cryptParams['library'] = 'openssl';
				$this->cryptParams['method'] = 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256';
				$this->cryptParams['padding'] = OPENSSL_PKCS1_PADDING;
				$this->cryptParams['digest'] = 'SHA256';

				if (is_array($params) && ! empty($params['type']))
				{
					if ($params['type'] == 'public' || $params['type'] == 'private')
					{
						$this->cryptParams['type'] = $params['type'];
						break;
					}
				}
				throw new Exception('Certificate "type" (private/public) must be passed via parameters');
				break;
			case (self::RSA_SHA384):
				$this->cryptParams['library'] = 'openssl';
				$this->cryptParams['method'] = 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha384';
				$this->cryptParams['padding'] = OPENSSL_PKCS1_PADDING;
				$this->cryptParams['digest'] = 'SHA384';

				if (is_array($params) && ! empty($params['type']))
				{
					if ($params['type'] == 'public' || $params['type'] == 'private')
					{
						$this->cryptParams['type'] = $params['type'];
						break;
					}
				}

			case (self::RSA_SHA512):
				$this->cryptParams['library'] = 'openssl';
				$this->cryptParams['method'] = 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha512';
				$this->cryptParams['padding'] = OPENSSL_PKCS1_PADDING;
				$this->cryptParams['digest'] = 'SHA512';

				if (is_array($params) && ! empty($params['type']))
				{
					if ($params['type'] == 'public' || $params['type'] == 'private')
					{
						$this->cryptParams['type'] = $params['type'];
						break;
					}
				}

			default:
				throw new Exception('Invalid Key Type');

			return;
		}

		$this->type = $type;
	}

	/**
	 * Retrieve the key size for the symmetric encryption algorithm..
	 *
	 * If the key size is unknown, or this isn't a symmetric encryption algorithm,
	 * NULL is returned.
	 *
	 * @return int|NULL  The number of bytes in the key.
	 */
	public function getSymmetricKeySize()
	{
		if (! isset($this->cryptParams['keysize']))
		{
			return null;
		}

		return $this->cryptParams['keysize'];
	}

	public function generateSessionKey()
	{
		if (!isset($this->cryptParams['keysize']))
		{
			throw new Exception('Unknown key size for type "' . $this->type . '".');
		}

		$keysize = $this->cryptParams['keysize'];

		if (function_exists('random_bytes'))
		{
			$key = random_bytes($keysize);
		}
		elseif (function_exists('openssl_random_pseudo_bytes'))
		{
			$key = openssl_random_pseudo_bytes($keysize);
		}
		else
		{
			throw new Exception('Unable to generate a secure session key.');
		}

		if ($this->type === self::TRIPLEDES_CBC)
		{
			/*
			 Make sure that the generated key has the proper parity bits set.
			 * Mcrypt doesn't care about the parity bits, but others may care.
			*/
			for ($i = 0; $i < strlen($key); $i++)
			{
				$byte = ord($key[$i]) & 0xfe;
				$parity = 1;

				for ($j = 1; $j < 8; $j++)
				{
					$parity ^= ($byte >> $j) & 1;
				}

				$byte |= $parity;
				$key[$i] = chr($byte);
			}
		}

		$this->key = $key;

		return $key;
	}

	public static function getRawThumbprint($cert)
	{

		$arCert = explode("\n", $cert);
		$data = '';
		$inData = false;

		foreach ($arCert AS $curData)
		{
			if (! $inData)
			{
				if (strncmp($curData, '-----BEGIN CERTIFICATE', 22) == 0)
				{
					$inData = true;
				}
			}
			else
			{
				if (strncmp($curData, '-----END CERTIFICATE', 20) == 0)
				{
					$inData = false;
					break;
				}

				$data .= trim($curData);
			}
		}

		if (! empty($data))
		{
			return strtolower(sha1(base64_decode($data)));
		}

		return null;
	}

	public function loadKey($key, $isFile=false, $isCert = false)
	{
		if ($isFile)
		{
			$this->key = file_get_contents($key);
		}
		else
		{
			$this->key = $key;
		}

		if ($isCert)
		{
			$this->key = openssl_x509_read($this->key);
			openssl_x509_export($this->key, $strCert);
			$this->x509Certificate = $strCert;
			$this->key = $strCert;
		}
		else
		{
			$this->x509Certificate = null;
		}

		if ($this->cryptParams['library'] == 'openssl')
		{
			if ($this->cryptParams['type'] == 'public')
			{
				if ($isCert)
				{
					// Load the thumbprint if this is an X509 certificate.

					$this->x509Thumbprint = self::getRawThumbprint($this->key);
				}

				$this->key = openssl_get_publickey($this->key);
			}
			else
			{
				$this->key = openssl_get_privatekey($this->key, $this->passphrase);
			}
		}
	}

	private function encryptSymmetric($data)
	{
		$ivLength = openssl_cipher_iv_length($this->cryptParams['cipher']);

		if ($ivLength === false)
		{
			throw new Exception('Unknown OpenSSL cipher "' . $this->cryptParams['cipher'] . '".');
		}

		$this->iv = openssl_random_pseudo_bytes($ivLength);
		$encryptedData = openssl_encrypt($data, $this->cryptParams['cipher'], $this->key, OPENSSL_RAW_DATA, $this->iv);

		if ($encryptedData === false)
		{
			throw new Exception('Failure encrypting Data');
		}

		return $this->iv . $encryptedData;
	}

	private function decryptSymmetric($data)
	{
		$ivLength = openssl_cipher_iv_length($this->cryptParams['cipher']);

		if ($ivLength === false)
		{
			throw new Exception('Unknown OpenSSL cipher "' . $this->cryptParams['cipher'] . '".');
		}

		$this->iv = substr($data, 0, $ivLength);
		$data = substr($data, $ivLength);
		$decryptedData = openssl_decrypt($data, $this->cryptParams['cipher'], $this->key, OPENSSL_RAW_DATA, $this->iv);

		if ($decryptedData === false)
		{
			throw new Exception('Failure decrypting Data');
		}

		return $decryptedData;
	}

	private function encryptOpenSSL($data)
	{
		if ($this->cryptParams['type'] == 'public')
		{
			if (! openssl_public_encrypt($data, $encryptedData, $this->key, $this->cryptParams['padding']))
			{
				throw new Exception('Failure encrypting Data');

				return;
			}
		}
		else
		{
			if (! openssl_private_encrypt($data, $encryptedData, $this->key, $this->cryptParams['padding']))
			{
				throw new Exception('Failure encrypting Data');

				return;
			}
		}

		return $encryptedData;
	}

	private function decryptOpenSSL($data)
	{
		if ($this->cryptParams['type'] == 'public')
		{
			if (! openssl_public_decrypt($data, $decrypted, $this->key, $this->cryptParams['padding']))
			{
				throw new Exception('Failure decrypting Data');

				return;
			}
		}
		else
		{
			if (! openssl_private_decrypt($data, $decrypted, $this->key, $this->cryptParams['padding']))
			{
				throw new Exception('Failure decrypting Data');

				return;
			}
		}

		return $decrypted;
	}

	private function signOpenSSL($data)
	{
		$algo = OPENSSL_ALGO_SHA1;

		if (! empty($this->cryptParams['digest']))
		{
			$algo = $this->cryptParams['digest'];
		}

		if (! openssl_sign($data, $signature, $this->key, $algo))
		{
			throw new Exception('Failure Signing Data: ' . openssl_error_string() . ' - ' . $algo);

			return;
		}

		return $signature;
	}

	private function verifyOpenSSL($data, $signature)
	{
		$algo = OPENSSL_ALGO_SHA1;

		if (! empty($this->cryptParams['digest']))
		{
			$algo = $this->cryptParams['digest'];
		}

		return openssl_verify($data, $signature, $this->key, $algo);
	}

	public function encryptData($data)
	{
		if (isset($this->cryptParams['type']) && $this->cryptParams['type'] === 'symmetric')
		{
			return $this->encryptSymmetric($data);
		}

		switch ($this->cryptParams['library'])
		{
			case 'openssl':
				return $this->encryptOpenSSL($data);
				break;
		}
	}

	public function decryptData($data)
	{
		if (isset($this->cryptParams['type']) && $this->cryptParams['type'] === 'symmetric')
		{
			return $this->decryptSymmetric($data);
		}

		switch ($this->cryptParams['library'])
		{
			case 'openssl':
				return $this->decryptOpenSSL($data);
				break;
		}
	}

	public function signData($data)
	{
		switch ($this->cryptParams['library'])
		{
			case 'openssl':
				return $this->signOpenSSL($data);
				break;
		}
	}

	public function verifySignature($data, $signature)
	{
		switch ($this->cryptParams['library'])
		{
			case 'openssl':
				return $this->verifyOpenSSL($data, $signature);
				break;
		}
	}

	public function getAlgorith()
	{
		return $this->cryptParams['method'];
	}

	public static function makeAsnSegment($type, $string)
	{
		switch ($type)
		{
			case 0x02:
				if (ord($string) > 0x7f)
				{
					$string = chr(0) . $string;
				}
				break;
			case 0x03:
				$string = chr(0) . $string;
				break;
		}

		$length = strlen($string);

		if ($length < 128)
		{
			$output = sprintf("%c%c%s", $type, $length, $string);
		}
		elseif ($length < 0x0100)
		{
			$output = sprintf("%c%c%c%s", $type, 0x81, $length, $string);
		}
		elseif ($length < 0x010000)
		{
			$output = sprintf("%c%c%c%c%s", $type, 0x82, $length / 0x0100, $length % 0x0100, $string);
		}
		else
		{
			$output = null;
		}

		return($output);
	}

	// Modulus and Exponent must already be base64 decoded

	public static function convertRSA($modulus, $exponent)
	{
		// Make an ASN publicKeyInfo

		$exponentEncoding = self::makeAsnSegment(0x02, $exponent);
		$modulusEncoding = self::makeAsnSegment(0x02, $modulus);
		$sequenceEncoding = self::makeAsnSegment(0x30, $modulusEncoding . $exponentEncoding);
		$bitstringEncoding = self::makeAsnSegment(0x03, $sequenceEncoding);
		$rsaAlgorithmIdentifier = pack("H*", "300D06092A864886F70D0101010500");
		$publicKeyInfo = self::makeAsnSegment(0x30, $rsaAlgorithmIdentifier . $bitstringEncoding);

		// Encode the publicKeyInfo in base64 and add PEM brackets

		$publicKeyInfoBase64 = base64_encode($publicKeyInfo);
		$encoding = "-----BEGIN PUBLIC KEY-----\n";
		$offset = 0;

		while ($segment = substr($publicKeyInfoBase64, $offset, 64))
		{
			$encoding = $encoding . $segment . "\n";
			$offset += 64;
		}

		return $encoding . "-----END PUBLIC KEY-----\n";
	}

	public function serializeKey($parent)
	{

	}



	/**
	 * Retrieve the X509 certificate this key represents.
	 *
	 * Will return the X509 certificate in PEM-format if this key represents
	 * an X509 certificate.
	 *
	 * @return  The X509 certificate or NULL if this key doesn't represent an X509-certificate.
	 */
	public function getX509Certificate()
	{
		return $this->x509Certificate;
	}

	/*
	 Get the thumbprint of this X509 certificate.
	 *
	 * Returns:
	 *  The thumbprint as a lowercase 40-character hexadecimal number, or NULL
	 *  if this isn't a X509 certificate.
	 */
	public function getX509Thumbprint()
	{
		return $this->x509Thumbprint;
	}


	/**
	 * Create key from an EncryptedKey-element.
	 *
	 * @param DOMElement $element  The EncryptedKey-element.
	 * @return XMLSecurityKeyIDP  The new key.
	 */
	public static function fromEncryptedKeyElement(DOMElement $element)
	{

		$objenc = new XMLSecEncIDP;
		$objenc->setNode($element);

		if (! $objKey = $objenc->locateKey())
		{
			throw new Exception("Unable to locate algorithm for this Encrypted Key");
		}

		$objKey->isEncrypted = true;
		$objKey->encryptedCtx = $objenc;
		XMLSecEncIDP::staticLocateKeyInfo($objKey, $element);

		return $objKey;
	}

}

class XMLSecurityDSigIDP
{
	const XMLDSIGNS = 'http://www.w3.org/2000/09/xmldsig#';
	const SHA1 = 'http://www.w3.org/2000/09/xmldsig#sha1';
	const SHA256 = 'http://www.w3.org/2001/04/xmlenc#sha256';
	const SHA384 = 'http://www.w3.org/2001/04/xmldsig-more#sha384';
	const SHA512 = 'http://www.w3.org/2001/04/xmlenc#sha512';
	const RIPEMD160 = 'http://www.w3.org/2001/04/xmlenc#ripemd160';

	const C14N = 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315';
	const C14N_COMMENTS = 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315#WithComments';
	const EXC_C14N = 'http://www.w3.org/2001/10/xml-exc-c14n#';
	const EXC_C14N_COMMENTS = 'http://www.w3.org/2001/10/xml-exc-c14n#WithComments';

	const TEMPLATE = '<ds:Signature xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
  <ds:SignedInfo>
    <ds:SignatureMethod />
  </ds:SignedInfo>
</ds:Signature>';

	/**
	 * @var    DOMElement|null
	 */
	public $sigNode = null;

	/**
	 * @var    array
	 */
	public $idKeys = array();

	/**
	 * @var    array
	 */
	public $idNS = array();

	/**
	 * @var    string|null
	 */
	private $signedInfo = null;

	/**
	 * @var    DOMXPath|null
	 */
	private $xPathCtx = null;

	/**
	 * @var    string|null
	 */
	private $canonicalMethod = null;

	/**
	 * @var    string
	 */
	private $prefix = 'ds';

	/**
	 * @var    string
	 */
	private $searchpfx = 'secdsig';

	/**
	 * This variable contains an associative array of validated nodes.
	 *
	 * @var    array|null
	 */
	private $validatedNodes = null;

	public function __construct()
	{
		$sigdoc = new DOMDocument;
		$sigdoc->loadXML(self::TEMPLATE);
		$this->sigNode = $sigdoc->documentElement;
	}

	private function resetXPathObj()
	{
		$this->xPathCtx = null;
	}

	private function getXPathObj()
	{
		if (empty($this->xPathCtx) && ! empty($this->sigNode))
		{
			$xpath = new DOMXPath($this->sigNode->ownerDocument);
			$xpath->registerNamespace('secdsig', self::XMLDSIGNS);
			$this->xPathCtx = $xpath;
		}

		return $this->xPathCtx;
	}

	public static function generateGuid($prefix='pfx')
	{
		$uuid = bin2hex(random_bytes(16));
		$guid = $prefix . substr($uuid, 0, 8) . "-" .
				substr($uuid, 8, 4) . "-" .
				substr($uuid, 12, 4) . "-" .
				substr($uuid, 16, 4) . "-" .
				substr($uuid, 20, 12);

		return $guid;
	}

	public function locateSignature($objDoc)
	{
		if ($objDoc instanceof DOMDocument)
		{
			$doc = $objDoc;
		}
		else
		{
			$doc = $objDoc->ownerDocument;
		}

		if ($doc)
		{
			$xpath = new DOMXPath($doc);
			$xpath->registerNamespace('secdsig', self::XMLDSIGNS);
			$query = ".//secdsig:Signature";
			$nodeset = $xpath->query($query, $objDoc);
			$this->sigNode = $nodeset->item(0);

			return $this->sigNode;
		}

		return null;
	}

	public function createNewSignNode($name, $value=null)
	{
		$doc = $this->sigNode->ownerDocument;

		if (! is_null($value))
		{
			$node = $doc->createElementNS(self::XMLDSIGNS, $this->prefix . ':' . $name, $value);
		}
		else
		{
			$node = $doc->createElementNS(self::XMLDSIGNS, $this->prefix . ':' . $name);
		}

		return $node;
	}

	public function setCanonicalMethod($method)
	{
		switch ($method)
		{
			case 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315':
			case 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315#WithComments':
			case 'http://www.w3.org/2001/10/xml-exc-c14n#':
			case 'http://www.w3.org/2001/10/xml-exc-c14n#WithComments':
				$this->canonicalMethod = $method;
				break;
			default:
				throw new Exception('Invalid Canonical Method');
		}

		if ($xpath = $this->getXPathObj())
		{
			$query = './' . $this->searchpfx . ':SignedInfo';
			$nodeset = $xpath->query($query, $this->sigNode);

			if ($sinfo = $nodeset->item(0))
			{
				$query = './' . $this->searchpfx . 'CanonicalizationMethod';
				$nodeset = $xpath->query($query, $sinfo);

				if (! ($canonNode = $nodeset->item(0)))
				{
					$canonNode = $this->createNewSignNode('CanonicalizationMethod');
					$sinfo->insertBefore($canonNode, $sinfo->firstChild);
				}

				$canonNode->setAttribute('Algorithm', $this->canonicalMethod);
			}
		}
	}

	private function canonicalizeData($node, $canonicalmethod, $arXPath=null, $prefixList=null)
	{
		$exclusive = false;
		$withComments = false;

		switch ($canonicalmethod)
		{
			case 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315':
				$exclusive = false;
				$withComments = false;
				break;
			case 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315#WithComments':
				$withComments = true;
				break;
			case 'http://www.w3.org/2001/10/xml-exc-c14n#':
				$exclusive = true;
				break;
			case 'http://www.w3.org/2001/10/xml-exc-c14n#WithComments':
				$exclusive = true;
				$withComments = true;
				break;
		}

		// Support PHP versions < 5.2 not containing C14N methods in DOM extension

		$phpVersion = explode('.', PHP_VERSION);

		if (($phpVersion[0] < 5) || ($phpVersion[0] == 5 && $phpVersion[1] < 2))
		{
			if (! is_null($arXPath))
			{
				throw new Exception("PHP 5.2.0 or higher is required to perform XPath Transformations");
			}

			return c14NGeneralIdp($node, $exclusive, $withComments);
		}

		return $node->C14N($exclusive, $withComments, $arXPath, $prefixList);
	}

	public function canonicalizeSignedInfo()
	{

		$doc = $this->sigNode->ownerDocument;
		$canonicalmethod = null;

		if ($doc)
		{
			$xpath = $this->getXPathObj();
			$query = "./secdsig:SignedInfo";
			$nodeset = $xpath->query($query, $this->sigNode);

			if ($signInfoNode = $nodeset->item(0))
			{
				$query = "./secdsig:CanonicalizationMethod";
				$nodeset = $xpath->query($query, $signInfoNode);

				if ($canonNode = $nodeset->item(0))
				{
					$canonicalmethod = $canonNode->getAttribute('Algorithm');
				}

				$this->signedInfo = $this->canonicalizeData($signInfoNode, $canonicalmethod);

				return $this->signedInfo;
			}
		}

		return null;
	}

	public function calculateDigest($digestAlgorithm, $data)
	{
		switch ($digestAlgorithm)
		{
			case self::SHA1:
				$alg = 'sha1';
				break;
			case self::SHA256:
				$alg = 'sha256';
				break;
			case self::SHA384:
				$alg = 'sha384';
				break;
			case self::SHA512:
				$alg = 'sha512';
				break;
			case self::RIPEMD160:
				$alg = 'ripemd160';
				break;
			default:
				throw new Exception("Cannot validate digest: Unsupported Algorith <$digestAlgorithm>");
		}

		if (function_exists('hash'))
		{
			return base64_encode(hash($alg, $data, true));
		}
		elseif ($alg === 'sha1')
		{
			return base64_encode(sha1($data, true));
		}
		else
		{
			throw new Exception('xmlseclibs is unable to calculate a digest. Maybe you need the mhash library?');
		}
	}

	public function validateDigest($refNode, $data)
	{
		$xpath = new DOMXPath($refNode->ownerDocument);
		$xpath->registerNamespace('secdsig', self::XMLDSIGNS);
		$query = 'string(./secdsig:DigestMethod/@Algorithm)';
		$digestAlgorithm = $xpath->evaluate($query, $refNode);
		$digValue = $this->calculateDigest($digestAlgorithm, $data);
		$query = 'string(./secdsig:DigestValue)';
		$digestValue = $xpath->evaluate($query, $refNode);

		return ($digValue == $digestValue);
	}

	public function processTransforms($refNode, $objData, $includeCommentNodes = true)
	{
		$data = $objData;
		$xpath = new DOMXPath($refNode->ownerDocument);
		$xpath->registerNamespace('secdsig', self::XMLDSIGNS);
		$query = './secdsig:Transforms/secdsig:Transform';
		$nodelist = $xpath->query($query, $refNode);
		$canonicalMethod = 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315';
		$arXPath = null;
		$prefixList = null;

		foreach ($nodelist AS $transform)
		{
			$algorithm = $transform->getAttribute("Algorithm");

			switch ($algorithm)
			{
				case 'http://www.w3.org/2001/10/xml-exc-c14n#':
				case 'http://www.w3.org/2001/10/xml-exc-c14n#WithComments':

					if (!$includeCommentNodes)
					{
						/*
						 We remove comment nodes by forcing it to use a canonicalization
																								 * Without comments.
						 */
						$canonicalMethod = 'http://www.w3.org/2001/10/xml-exc-c14n#';
					}
					else
					{
						$canonicalMethod = $algorithm;
					}

					$node = $transform->firstChild;

					while ($node)
					{
						if ($node->localName == 'InclusiveNamespaces')
						{
							if ($pfx = $node->getAttribute('PrefixList'))
							{
								$arpfx = array();
								$pfxlist = explode(" ", $pfx);

								foreach ($pfxlist AS $pfx)
								{
									$val = trim($pfx);

									if (! empty($val))
									{
										$arpfx[] = $val;
									}
								}

								if (count($arpfx) > 0)
								{
									$prefixList = $arpfx;
								}
							}

							break;
						}

						$node = $node->nextSibling;
					}
					break;
				case 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315':
				case 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315#WithComments':
					if (!$includeCommentNodes)
					{
						/*
						 We remove comment nodes by forcing it to use a canonicalization
																								 * Without comments.
						 */
						$canonicalMethod = 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315';
					}
					else
					{
						$canonicalMethod = $algorithm;
					}

					break;
				case 'http://www.w3.org/TR/1999/REC-xpath-19991116':
					$node = $transform->firstChild;

					while ($node)
					{
						if ($node->localName == 'XPath')
						{
							$arXPath = array();
							$arXPath['query'] = '(.//. | .//@* | .//namespace::*)[' . $node->nodeValue . ']';
							$arXpath['namespaces'] = array();
							$nslist = $xpath->query('./namespace::*', $node);

							foreach ($nslist AS $nsnode)
							{
								if ($nsnode->localName != "xml")
								{
									$arXPath['namespaces'][$nsnode->localName] = $nsnode->nodeValue;
								}
							}

							break;
						}

						$node = $node->nextSibling;
					}
					break;
			}
		}

		if ($data instanceof DOMNode)
		{
			$data = $this->canonicalizeData($objData, $canonicalMethod, $arXPath, $prefixList);
		}

		return $data;
	}

	public function processRefNode($refNode)
	{
		$dataObject = null;

		/*
		 * Depending on the URI, we may not want to include comments in the result
		 * See: http://www.w3.org/TR/xmldsig-core/#sec-ReferenceProcessingModel
		 */
		$includeCommentNodes = true;

		if ($uri = $refNode->getAttribute("URI"))
		{
			$arUrl = parse_url($uri);

			if (empty($arUrl['path']))
			{
				if ($identifier = $arUrl['fragment'])
				{
					/*
					 This reference identifies a node with the given id by using
																				 * A URI on the form "#identifier". This should not include comments.
					 */
					$includeCommentNodes = false;

					$xPath = new DOMXPath($refNode->ownerDocument);

					if ($this->idNS && is_array($this->idNS))
					{
						foreach ($this->idNS AS $nspf => $ns)
						{
							$xPath->registerNamespace($nspf, $ns);
						}
					}

					$iDlist = '@Id="' . $identifier . '"';

					if (is_array($this->idKeys))
					{
						foreach ($this->idKeys AS $idKey)
						{
							$iDlist .= " or @$idKey='$identifier'";
						}
					}

					$query = '//*[' . $iDlist . ']';
					$dataObject = $xPath->query($query)->item(0);
				}
				else
				{
					$dataObject = $refNode->ownerDocument;
				}
			}
			else
			{
				$dataObject = file_get_contents($arUrl);
			}
		}
		else
		{
			/*
			 This reference identifies the root node with an empty URI. This should
												 * Not include comments.
			 */
			$includeCommentNodes = false;

			$dataObject = $refNode->ownerDocument;
		}

		$data = $this->processTransforms($refNode, $dataObject, $includeCommentNodes);

		if (!$this->validateDigest($refNode, $data))
		{
			return false;
		}

		if ($dataObject instanceof DOMNode)
		{
			// Add this node to the list of validated nodes.

			if (! empty($identifier))
			{
				$this->validatedNodes[$identifier] = $dataObject;
			}
			else
			{
				$this->validatedNodes[] = $dataObject;
			}
		}

		return true;
	}

	public function getRefNodeID($refNode)
	{
		if ($uri = $refNode->getAttribute("URI"))
		{
			$arUrl = parse_url($uri);

			if (empty($arUrl['path']))
			{
				if ($identifier = $arUrl['fragment'])
				{
					return $identifier;
				}
			}
		}

		return null;
	}

	public function getRefIDs()
	{
		$refids = array();
		$doc = $this->sigNode->ownerDocument;

		$xpath = $this->getXPathObj();
		$query = "./secdsig:SignedInfo/secdsig:Reference";
		$nodeset = $xpath->query($query, $this->sigNode);

		if ($nodeset->length == 0)
		{
			throw new Exception("Reference nodes not found");
		}

		foreach ($nodeset AS $refNode)
		{
			$refids[] = $this->getRefNodeID($refNode);
		}

		return $refids;
	}

	public function validateReference()
	{
		$doc = $this->sigNode->ownerDocument;

		if (! $doc->isSameNode($this->sigNode))
		{
			$this->sigNode->parentNode->removeChild($this->sigNode);
		}

		$xpath = $this->getXPathObj();
		$query = "./secdsig:SignedInfo/secdsig:Reference";
		$nodeset = $xpath->query($query, $this->sigNode);

		if ($nodeset->length == 0)
		{
			throw new Exception("Reference nodes not found");
		}

		// Initialize/reset the list of validated nodes.

		$this->validatedNodes = array();

		foreach ($nodeset AS $refNode)
		{
			if (! $this->processRefNode($refNode))
			{
				// Clear the list of validated nodes.

				$this->validatedNodes = null;
				throw new Exception("Reference validation failed");
			}
		}

		return true;
	}

	private function addRefInternal($sinfoNode, $node, $algorithm, $arTransforms=null, $options=null)
	{
		$prefix = null;
		$prefixNs = null;
		$idName = 'Id';
		$overwriteId  = true;
		$forceUri = false;

		if (is_array($options))
		{
			$prefix = empty($options['prefix']) ? null : $options['prefix'];
			$prefixNs = empty($options['prefix_ns']) ? null : $options['prefix_ns'];
			$idName = empty($options['id_name']) ? 'Id' : $options['id_name'];
			$overwriteId = !isset($options['overwrite']) ? true : (bool) $options['overwrite'];
			$forceUri = !isset($options['force_uri']) ? false : (bool) $options['force_uri'];
		}

		$attname = $idName;

		if (! empty($prefix))
		{
			$attname = $prefix . ':' . $attname;
		}

		$refNode = $this->createNewSignNode('Reference');
		$sinfoNode->appendChild($refNode);

		if (! $node instanceof DOMDocument)
		{
			$uri = null;

			if (! $overwriteId)
			{
				$uri = $node->getAttributeNS($prefixNs, $idName);
			}

			if (empty($uri))
			{
				$uri = self::generateGuid();
				$node->setAttributeNS($prefixNs, $attname, $uri);
			}

			$refNode->setAttribute("URI", '#' . $uri);
		}
		elseif ($forceUri)
		{
			$refNode->setAttribute("URI", '');
		}

		$transNodes = $this->createNewSignNode('Transforms');
		$refNode->appendChild($transNodes);

		if (is_array($arTransforms))
		{
			foreach ($arTransforms AS $transform)
			{
				$transNode = $this->createNewSignNode('Transform');
				$transNodes->appendChild($transNode);

				if (is_array($transform)
					&& (! empty($transform['http://www.w3.org/TR/1999/REC-xpath-19991116']))
					&& (! empty($transform['http://www.w3.org/TR/1999/REC-xpath-19991116']['query'])))
				{
					$transNode->setAttribute('Algorithm', 'http://www.w3.org/TR/1999/REC-xpath-19991116');
					$XPathNode = $this->createNewSignNode('XPath', $transform['http://www.w3.org/TR/1999/REC-xpath-19991116']['query']);
					$transNode->appendChild($XPathNode);

					if (! empty($transform['http://www.w3.org/TR/1999/REC-xpath-19991116']['namespaces']))
					{
						foreach ($transform['http://www.w3.org/TR/1999/REC-xpath-19991116']['namespaces'] AS $prefix => $namespace)
						{
							$XPathNode->setAttributeNS("http://www.w3.org/2000/xmlns/", "xmlns:$prefix", $namespace);
						}
					}
				}
				else
				{
					$transNode->setAttribute('Algorithm', $transform);
				}
			}
		}
		elseif (! empty($this->canonicalMethod))
		{
			$transNode = $this->createNewSignNode('Transform');
			$transNodes->appendChild($transNode);
			$transNode->setAttribute('Algorithm', $this->canonicalMethod);
		}

		$canonicalData = $this->processTransforms($refNode, $node);
		$digValue = $this->calculateDigest($algorithm, $canonicalData);

		$digestMethod = $this->createNewSignNode('DigestMethod');
		$refNode->appendChild($digestMethod);
		$digestMethod->setAttribute('Algorithm', $algorithm);

		$digestValue = $this->createNewSignNode('DigestValue', $digValue);
		$refNode->appendChild($digestValue);
	}

	public function addReference($node, $algorithm, $arTransforms=null, $options=null)
	{
		if ($xpath = $this->getXPathObj())
		{
			$query = "./secdsig:SignedInfo";
			$nodeset = $xpath->query($query, $this->sigNode);

			if ($sInfo = $nodeset->item(0))
			{
				$this->addRefInternal($sInfo, $node, $algorithm, $arTransforms, $options);
			}
		}
	}

	public function addReferenceList($arNodes, $algorithm, $arTransforms=null, $options=null)
	{
		if ($xpath = $this->getXPathObj())
		{
			$query = "./secdsig:SignedInfo";
			$nodeset = $xpath->query($query, $this->sigNode);

			if ($sInfo = $nodeset->item(0))
			{
				foreach ($arNodes AS $node)
				{
					$this->addRefInternal($sInfo, $node, $algorithm, $arTransforms, $options);
				}
			}
		}
	}

	public function addObject($data, $mimetype=null, $encoding=null)
	{
		$objNode = $this->createNewSignNode('Object');
		$this->sigNode->appendChild($objNode);

		if (! empty($mimetype))
		{
				  $objNode->setAtribute('MimeType', $mimetype);
		}

		if (! empty($encoding))
		{
				  $objNode->setAttribute('Encoding', $encoding);
		}

		if ($data instanceof DOMElement)
		{
			$newData = $this->sigNode->ownerDocument->importNode($data, true);
		}
		else
		{
				  $newData = $this->sigNode->ownerDocument->createTextNode($data);
		}

		$objNode->appendChild($newData);

		return $objNode;
	}

	public function locateKey($node=null)
	{
		if (empty($node))
		{
			$node = $this->sigNode;
		}

		if (! $node instanceof DOMNode)
		{
			return null;
		}

		if ($doc = $node->ownerDocument)
		{
			$xpath = new DOMXPath($doc);
			$xpath->registerNamespace('secdsig', self::XMLDSIGNS);
			$query = "string(./secdsig:SignedInfo/secdsig:SignatureMethod/@Algorithm)";
			$algorithm = $xpath->evaluate($query, $node);

			if ($algorithm)
			{
				try
				{
					$objKey = new XMLSecurityKeyIDP($algorithm, array('type' => 'public'));
				}
				catch (Exception $e)
				{
					return null;
				}

				return $objKey;
			}
		}

		return null;
	}

	public function verify($objKey)
	{
		$doc = $this->sigNode->ownerDocument;
		$xpath = new DOMXPath($doc);
		$xpath->registerNamespace('secdsig', self::XMLDSIGNS);
		$query = "string(./secdsig:SignatureValue)";
		$sigValue = $xpath->evaluate($query, $this->sigNode);

		if (empty($sigValue))
		{
			throw new Exception("Unable to locate SignatureValue");
		}

		return $objKey->verifySignature($this->signedInfo, base64_decode($sigValue));
	}

	public function signData($objKey, $data)
	{
		return $objKey->signData($data);
	}

	public function sign($objKey, $appendToNode = null)
	{
		// If we have a parent node append it now so C14N properly works
		if ($appendToNode != null)
		{
			$this->resetXPathObj();
			$this->appendSignature($appendToNode);
			$this->sigNode = $appendToNode->lastChild;
		}

		if ($xpath = $this->getXPathObj())
		{
			$query = "./secdsig:SignedInfo";
			$nodeset = $xpath->query($query, $this->sigNode);

			if ($sInfo = $nodeset->item(0))
			{
				$query = "./secdsig:SignatureMethod";
				$nodeset = $xpath->query($query, $sInfo);
				$sMethod = $nodeset->item(0);
				$sMethod->setAttribute('Algorithm', $objKey->type);
				$data = $this->canonicalizeData($sInfo, $this->canonicalMethod);
				$sigValue = base64_encode($this->signData($objKey, $data));
				$sigValueNode = $this->createNewSignNode('SignatureValue', $sigValue);

				if ($infoSibling = $sInfo->nextSibling)
				{
					$infoSibling->parentNode->insertBefore($sigValueNode, $infoSibling);
				}
				else
				{
					$this->sigNode->appendChild($sigValueNode);
				}
			}
		}
	}

	public function appendCert()
	{

	}

	public function appendKey($objKey, $parent=null)
	{
		$objKey->serializeKey($parent);
	}


	/**
	 * This function inserts the signature element.
	 *
	 * The signature element will be appended to the element, unless $beforeNode is specified. If $beforeNode
	 * is specified, the signature element will be inserted as the last element before $beforeNode.
	 *
	 * @param $node  The node the signature element should be inserted into.
	 * @param $beforeNode  The node the signature element should be located before.
	 *
	 * @return DOMNode The signature element node
	 */
	public function insertSignature($node, $beforeNode = null)
	{

		$document = $node->ownerDocument;
		$signatureElement = $document->importNode($this->sigNode, true);

		if ($beforeNode == null)
		{
			return $node->insertBefore($signatureElement);
		}
		else
		{
			return $node->insertBefore($signatureElement, $beforeNode);
		}
	}

	public function appendSignature($parentNode, $insertBefore = false)
	{
		$beforeNode = $insertBefore ? $parentNode->firstChild : null;

		return $this->insertSignature($parentNode, $beforeNode);
	}

	public static function get509XCert($cert, $isPEMFormat=true)
	{
		$certs = self::staticGet509XCerts($cert, $isPEMFormat);

		if (! empty($certs))
		{
			return $certs[0];
		}

		return '';
	}

	public static function staticGet509XCerts($certs, $isPEMFormat=true)
	{
		if ($isPEMFormat)
		{
			$data = '';
			$certlist = array();
			$arCert = explode("\n", $certs);
			$inData = false;

			foreach ($arCert AS $curData)
			{
				if (! $inData)
				{
					if (strncmp($curData, '-----BEGIN CERTIFICATE', 22) == 0)
					{
						$inData = true;
					}
				}
				else
				{
					if (strncmp($curData, '-----END CERTIFICATE', 20) == 0)
					{
						$inData = false;
						$certlist[] = $data;
						$data = '';
						continue;
					}

					$data .= trim($curData);
				}
			}

			return $certlist;
		}
		else
		{
			return array($certs);
		}
	}

	public static function staticAdd509Cert($parentRef, $cert, $isPEMFormat=true, $isURL=false, $xpath=null, $options=null)
	{
		if ($isURL)
		{
			$cert = file_get_contents($cert);
		}

		if (! $parentRef instanceof DOMElement)
		{
			throw new Exception('Invalid parent Node parameter');
		}

		$baseDoc = $parentRef->ownerDocument;

		if (empty($xpath))
		{
			$xpath = new DOMXPath($parentRef->ownerDocument);
			$xpath->registerNamespace('secdsig', self::XMLDSIGNS);
		}

		$query = "./secdsig:KeyInfo";
		$nodeset = $xpath->query($query, $parentRef);
		$keyInfo = $nodeset->item(0);

		if (! $keyInfo)
		{
			$inserted = false;
			$keyInfo = $baseDoc->createElementNS(self::XMLDSIGNS, 'ds:KeyInfo');

			$query = "./secdsig:Object";
			$nodeset = $xpath->query($query, $parentRef);

			if ($sObject = $nodeset->item(0))
			{
				$sObject->parentNode->insertBefore($keyInfo, $sObject);
				$inserted = true;
			}

			if (! $inserted)
			{
				$parentRef->appendChild($keyInfo);
			}
		}

		// Add all certs if there are more than one
		$certs = self::staticGet509XCerts($cert, $isPEMFormat);

		// Attach X509 data node
		$x509DataNode = $baseDoc->createElementNS(self::XMLDSIGNS, 'ds:X509Data');
		$keyInfo->appendChild($x509DataNode);

		$issuerSerial = false;
		$subjectName = false;

		if (is_array($options))
		{
			if (! empty($options['issuerSerial']))
			{
				$issuerSerial = true;
			}
		}

		// Attach all certificate nodes and any additional data
		foreach ($certs as $x509Cert)
		{
			if ($issuerSerial)
			{
				if ($certData = openssl_x509_parse("-----BEGIN CERTIFICATE-----\n" . chunk_split($x509Cert, 64, "\n") . "-----END CERTIFICATE-----\n"))
				{
					if ($issuerSerial && ! empty($certData['issuer']) && ! empty($certData['serialNumber']))
					{
						if (is_array($certData['issuer']))
						{
							$parts = array();

							foreach ($certData['issuer'] AS $key => $value)
							{
								array_unshift($parts, "$key=$value" . $issuer);
							}

							$issuerName = implode(',', $parts);
						}
						else
						{
							$issuerName = $certData['issuer'];
						}

						$x509IssuerNode = $baseDoc->createElementNS(self::XMLDSIGNS, 'ds:X509IssuerSerial');
						$x509DataNode->appendChild($x509IssuerNode);

						$x509Node = $baseDoc->createElementNS(self::XMLDSIGNS, 'ds:X509IssuerName', $issuerName);
						$x509IssuerNode->appendChild($x509Node);
						$x509Node = $baseDoc->createElementNS(self::XMLDSIGNS, 'ds:X509SerialNumber', $certData['serialNumber']);
						$x509IssuerNode->appendChild($x509Node);
					}
				}
			}

			$x509CertNode = $baseDoc->createElementNS(self::XMLDSIGNS, 'ds:X509Certificate', $x509Cert);
			$x509DataNode->appendChild($x509CertNode);
		}
	}

	public function add509Cert($cert, $isPEMFormat=true, $isURL=false, $options=null)
	{
		if ($xpath = $this->getXPathObj())
		{
			self::staticAdd509Cert($this->sigNode, $cert, $isPEMFormat, $isURL, $xpath, $options);
		}
	}

	/*
	 This function retrieves an associative array of the validated nodes.
	 *
	 * The array will contain the id of the referenced node as the key and the node itself
	 * as the value.
	 *
	 * Returns:
	 *  An associative array of validated nodes or NULL if no nodes have been validated.
	 */
	public function getValidatedNodes()
	{
		return $this->validatedNodes;
	}
}

class XMLSecEncIDP
{
	const TEMPLATE = "<xenc:EncryptedData xmlns:xenc='http://www.w3.org/2001/04/xmlenc#'>
   <xenc:CipherData>
      <xenc:CipherValue></xenc:CipherValue>
   </xenc:CipherData>
</xenc:EncryptedData>";

	const ELEMENT = 'http://www.w3.org/2001/04/xmlenc#Element';
	const CONTENT = 'http://www.w3.org/2001/04/xmlenc#Content';
	const URI = 3;
	const XMLENCNS = 'http://www.w3.org/2001/04/xmlenc#';

	/**
	 * @var    DOMDocument|null
	 */
	private $encdoc = null;

	/**
	 * @var    DOMNode|null
	 */
	private $rawNode = null;

	/**
	 * @var    mixed
	 */
	public $type = null;

	/**
	 * @var    mixed
	 */
	public $encKey = null;

	/**
	 * @var    array
	 */
	private $references = array();

	public function __construct()
	{
		$this->resetTemplate();
	}

	private function resetTemplate()
	{
		$this->encdoc = new DOMDocument;
		$this->encdoc->loadXML(self::TEMPLATE);
	}

	public function addReference($name, $node, $type)
	{
		if (! $node instanceOf DOMNode)
		{
			throw new Exception('$node is not of type DOMNode');
		}

		$curencdoc = $this->encdoc;
		$this->resetTemplate();
		$encdoc = $this->encdoc;
		$this->encdoc = $curencdoc;
		$refuri = XMLSecurityDSigIDP::generateGuid();
		$element = $encdoc->documentElement;
		$element->setAttribute("Id", $refuri);
		$this->references[$name] = array("node" => $node, "type" => $type, "encnode" => $encdoc, "refuri" => $refuri);
	}

	public function setNode($node)
	{
		$this->rawNode = $node;
	}

	/**
	 * Encrypt the selected node with the given key.
	 *
	 * @param XMLSecurityKeyIDP $objKey  The encryption key and algorithm.
	 * @param bool $replace  Whether the encrypted node should be replaced in the original tree. Default is TRUE.
	 * @return DOMElement  The <xenc:EncryptedData>-element.
	 */
	public function encryptNode($objKey, $replace=true)
	{
		$data = '';

		if (empty($this->rawNode))
		{
			throw new Exception('Node to encrypt has not been set');
		}

		if (! $objKey instanceof XMLSecurityKeyIDP)
		{
			throw new Exception('Invalid Key');
		}

		$doc = $this->rawNode->ownerDocument;
		$xPath = new DOMXPath($this->encdoc);
		$objList = $xPath->query('/xenc:EncryptedData/xenc:CipherData/xenc:CipherValue');
		$cipherValue = $objList->item(0);

		if ($cipherValue == null)
		{
			throw new Exception('Error locating CipherValue element within template');
		}

		switch ($this->type)
		{
			case (self::ELEMENT):
				$data = $doc->saveXML($this->rawNode);
				$this->encdoc->documentElement->setAttribute('Type', self::ELEMENT);
				break;
			case (self::CONTENT):
				$children = $this->rawNode->childNodes;

				foreach ($children AS $child)
				{
					$data .= $doc->saveXML($child);
				}

				$this->encdoc->documentElement->setAttribute('Type', self::CONTENT);
				break;
			default:
				throw new Exception('Type is currently not supported');

			return;
		}

		$encMethod = $this->encdoc->documentElement->appendChild($this->encdoc->createElementNS(self::XMLENCNS, 'xenc:EncryptionMethod'));
		$encMethod->setAttribute('Algorithm', $objKey->getAlgorith());
		$cipherValue->parentNode->parentNode->insertBefore($encMethod, $cipherValue->parentNode->parentNode->firstChild);

		$strEncrypt = base64_encode($objKey->encryptData($data));
		$value = $this->encdoc->createTextNode($strEncrypt);
		$cipherValue->appendChild($value);

		if ($replace)
		{
			switch ($this->type)
			{
				case (self::ELEMENT):
					if ($this->rawNode->nodeType == XML_DOCUMENT_NODE)
					{
						return $this->encdoc;
					}

					$importEnc = $this->rawNode->ownerDocument->importNode($this->encdoc->documentElement, true);
					$this->rawNode->parentNode->replaceChild($importEnc, $this->rawNode);

return $importEnc;
					break;
				case (self::CONTENT):
					$importEnc = $this->rawNode->ownerDocument->importNode($this->encdoc->documentElement, true);

					while ($this->rawNode->firstChild)
					{
						$this->rawNode->removeChild($this->rawNode->firstChild);
					}

					$this->rawNode->appendChild($importEnc);

return $importEnc;
					break;
			}
		}
		else
		{
			return $this->encdoc->documentElement;
		}
	}

	public function encryptReferences($objKey)
	{
		$curRawNode = $this->rawNode;
		$curType = $this->type;

		foreach ($this->references AS $name => $reference)
		{
			$this->encdoc = $reference["encnode"];
			$this->rawNode = $reference["node"];
			$this->type = $reference["type"];

			try
			{
				$encNode = $this->encryptNode($objKey);
				$this->references[$name]["encnode"] = $encNode;
			}
			catch (Exception $e)
			{
				$this->rawNode = $curRawNode;
				$this->type = $curType;
				throw $e;
			}
		}

		$this->rawNode = $curRawNode;
		$this->type = $curType;
	}

	/**
	 * Retrieve the CipherValue text from this encrypted node.
	 *
	 * @return string|NULL  The Ciphervalue text, or NULL if no CipherValue is found.
	 */
	public function getCipherValue()
	{
		if (empty($this->rawNode))
		{
			throw new Exception('Node to decrypt has not been set');
		}

		$doc = $this->rawNode->ownerDocument;
		$xPath = new DOMXPath($doc);
		$xPath->registerNamespace('xmlencr', self::XMLENCNS);

		// Only handles embedded content right now and not a reference

		$query = "./xmlencr:CipherData/xmlencr:CipherValue";
		$nodeset = $xPath->query($query, $this->rawNode);
		$node = $nodeset->item(0);

		if (!$node)
		{
				return null;
		}

		return base64_decode($node->nodeValue);
	}

	/**
	 * Decrypt this encrypted node.
	 *
	 * The behaviour of this function depends on the value of $replace.
	 * If $replace is FALSE, we will return the decrypted data as a string.
	 * If $replace is TRUE, we will insert the decrypted element(s) into the
	 * document, and return the decrypted element(s).
	 *
	 * @params XMLSecurityKeyIDP $objKey  The decryption key that should be used when decrypting the node.
	 * @params boolean $replace  Whether we should replace the encrypted node in the XML document with the decrypted data. The default is TRUE.
	 * @return string|DOMElement  The decrypted data.
	 */
	public function decryptNode($objKey, $replace=true)
	{
		if (! $objKey instanceof XMLSecurityKeyIDP)
		{
			throw new Exception('Invalid Key');
		}

		$encryptedData = $this->getCipherValue();

		if ($encryptedData)
		{
			$decrypted = $objKey->decryptData($encryptedData);

			if ($replace)
			{
				switch ($this->type)
				{
					case (self::ELEMENT):
						$newdoc = new DOMDocument;
						$newdoc->loadXML($decrypted);

						if ($this->rawNode->nodeType == XML_DOCUMENT_NODE)
						{
							return $newdoc;
						}

						$importEnc = $this->rawNode->ownerDocument->importNode($newdoc->documentElement, true);
						$this->rawNode->parentNode->replaceChild($importEnc, $this->rawNode);

return $importEnc;
						break;
					case (self::CONTENT):
						if ($this->rawNode->nodeType == XML_DOCUMENT_NODE)
						{
							$doc = $this->rawNode;
						}
						else
						{
							$doc = $this->rawNode->ownerDocument;
						}

						$newFrag = $doc->createDocumentFragment();
						$newFrag->appendXML($decrypted);
						$parent = $this->rawNode->parentNode;
						$parent->replaceChild($newFrag, $this->rawNode);

return $parent;
						break;
					default:
						return $decrypted;
				}
			}
			else
			{
				return $decrypted;
			}
		}
		else
		{
			throw new Exception("Cannot locate encrypted data");
		}
	}

	public function encryptKey($srcKey, $rawKey, $append=true)
	{
		if ((! $srcKey instanceof XMLSecurityKeyIDP) || (! $rawKey instanceof XMLSecurityKeyIDP))
		{
			throw new Exception('Invalid Key');
		}

		$strEncKey = base64_encode($srcKey->encryptData($rawKey->key));
		$root = $this->encdoc->documentElement;
		$encKey = $this->encdoc->createElementNS(self::XMLENCNS, 'xenc:EncryptedKey');

		if ($append)
		{
			$keyInfo = $root->insertBefore($this->encdoc->createElementNS('http://www.w3.org/2000/09/xmldsig#', 'dsig:KeyInfo'), $root->firstChild);
			$keyInfo->appendChild($encKey);
		}
		else
		{
			$this->encKey = $encKey;
		}

		$encMethod = $encKey->appendChild($this->encdoc->createElementNS(self::XMLENCNS, 'xenc:EncryptionMethod'));
		$encMethod->setAttribute('Algorithm', $srcKey->getAlgorith());

		if (! empty($srcKey->name))
		{
			$keyInfo = $encKey->appendChild($this->encdoc->createElementNS('http://www.w3.org/2000/09/xmldsig#', 'dsig:KeyInfo'));
			$keyInfo->appendChild($this->encdoc->createElementNS('http://www.w3.org/2000/09/xmldsig#', 'dsig:KeyName', $srcKey->name));
		}

		$cipherData = $encKey->appendChild($this->encdoc->createElementNS(self::XMLENCNS, 'xenc:CipherData'));
		$cipherData->appendChild($this->encdoc->createElementNS(self::XMLENCNS, 'xenc:CipherValue', $strEncKey));

		if (is_array($this->references) && count($this->references) > 0)
		{
			$refList = $encKey->appendChild($this->encdoc->createElementNS(self::XMLENCNS, 'xenc:ReferenceList'));

			foreach ($this->references AS $name => $reference)
			{
				$refuri = $reference["refuri"];
				$dataRef = $refList->appendChild($this->encdoc->createElementNS(self::XMLENCNS, 'xenc:DataReference'));
				$dataRef->setAttribute("URI", '#' . $refuri);
			}
		}

		return;
	}

	public function decryptKey($encKey)
	{
		if (! $encKey->isEncrypted)
		{
			throw new Exception("Key is not Encrypted");
		}

		if (empty($encKey->key))
		{
			throw new Exception("Key is missing data to perform the decryption");
		}

		return $this->decryptNode($encKey, false);
	}

	public function locateEncryptedData($element)
	{
		if ($element instanceof DOMDocument)
		{
			$doc = $element;
		}
		else
		{
			$doc = $element->ownerDocument;
		}

		if ($doc)
		{
			$xpath = new DOMXPath($doc);
			$query = "//*[local-name()='EncryptedData' and namespace-uri()='" . self::XMLENCNS . "']";
			$nodeset = $xpath->query($query);

			return $nodeset->item(0);
		}

		return null;
	}

	public function locateKey($node=null)
	{
		if (empty($node))
		{
			$node = $this->rawNode;
		}

		if (! $node instanceof DOMNode)
		{
			return null;
		}

		if ($doc = $node->ownerDocument)
		{
			$xpath = new DOMXPath($doc);
			$xpath->registerNamespace('XMLSecEncIDP', self::XMLENCNS);
			$query = ".//XMLSecEncIDP:EncryptionMethod";
			$nodeset = $xpath->query($query, $node);

			if ($encmeth = $nodeset->item(0))
			{
				   $attrAlgorithm = $encmeth->getAttribute("Algorithm");

				try
				{
					$objKey = new XMLSecurityKeyIDP($attrAlgorithm, array('type' => 'private'));
				}
				catch (Exception $e)
				{
					return null;
				}

				return $objKey;
			}
		}

		return null;
	}

	public static function staticLocateKeyInfo($objBaseKey=null, $node=null)
	{
		if (empty($node) || (! $node instanceof DOMNode))
		{
			return null;
		}

		$doc = $node->ownerDocument;

		if (!$doc)
		{
			return null;
		}

		$xpath = new DOMXPath($doc);
		$xpath->registerNamespace('XMLSecEncIDP', self::XMLENCNS);
		$xpath->registerNamespace('xmlsecdsig', XMLSecurityDSigIDP::XMLDSIGNS);
		$query = "./xmlsecdsig:KeyInfo";
		$nodeset = $xpath->query($query, $node);
		$encmeth = $nodeset->item(0);

		if (!$encmeth)
		{
			// No KeyInfo in EncryptedData / EncryptedKey.

			return $objBaseKey;
		}

		foreach ($encmeth->childNodes AS $child)
		{
			switch ($child->localName)
			{
				case 'KeyName':
					if (! empty($objBaseKey))
					{
						$objBaseKey->name = $child->nodeValue;
					}
					break;
				case 'KeyValue':
					foreach ($child->childNodes AS $keyval)
					{
						switch ($keyval->localName)
						{
							case 'DSAKeyValue':
								throw new Exception("DSAKeyValue currently not supported");
								break;
							case 'RSAKeyValue':
								$modulus = null;
								$exponent = null;

								if ($modulusNode = $keyval->getElementsByTagName('Modulus')->item(0))
								{
									$modulus = base64_decode($modulusNode->nodeValue);
								}

								if ($exponentNode = $keyval->getElementsByTagName('Exponent')->item(0))
								{
									$exponent = base64_decode($exponentNode->nodeValue);
								}

								if (empty($modulus) || empty($exponent))
								{
									throw new Exception("Missing Modulus or Exponent");
								}

								$publicKey = XMLSecurityKeyIDP::convertRSA($modulus, $exponent);
								$objBaseKey->loadKey($publicKey);
								break;
						}
					}
					break;
				case 'RetrievalMethod':
					$type = $child->getAttribute('Type');

					if ($type !== 'http://www.w3.org/2001/04/xmlenc#EncryptedKey')
					{
						// Unsupported key type.

						break;
					}

					$uri = $child->getAttribute('URI');

					if ($uri[0] !== '#')
					{
						// URI not a reference - unsupported.

						break;
					}

					$id = substr($uri, 1);

					$query = "//XMLSecEncIDP:EncryptedKey[@Id='$id']";
					$keyElement = $xpath->query($query)->item(0);

					if (!$keyElement)
					{
						throw new Exception("Unable to locate EncryptedKey with @Id='$id'.");
					}

					return XMLSecurityKeyIDP::fromEncryptedKeyElement($keyElement);
				case 'EncryptedKey':
					return XMLSecurityKeyIDP::fromEncryptedKeyElement($child);
				case 'X509Data':
					if ($x509certNodes = $child->getElementsByTagName('X509Certificate'))
					{
						if ($x509certNodes->length > 0)
						{
							$x509cert = $x509certNodes->item(0)->textContent;
							$x509cert = str_replace(array("\r", "\n"), "", $x509cert);
							$x509cert = "-----BEGIN CERTIFICATE-----\n" . chunk_split($x509cert, 64, "\n") . "-----END CERTIFICATE-----\n";
							$objBaseKey->loadKey($x509cert, false, true);
						}
					}
					break;
			}
		}

		return $objBaseKey;
	}

	public function locateKeyInfo($objBaseKey=null, $node=null)
	{
		if (empty($node))
		{
			$node = $this->rawNode;
		}

		return self::staticLocateKeyInfo($objBaseKey, $node);
	}
}
