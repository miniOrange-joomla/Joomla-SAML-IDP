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

class MetadataReader
{
    private $serviceProviders;

    public function __construct(\DOMNode $xml = NULL) {
        $this->serviceProviders = array();
        $entityDescriptors = MoSamlIdpUtility::xpQuery($xml, './saml_metadata:EntityDescriptor');
        foreach ($entityDescriptors as $entityDescriptor) {
            $SPSSODescriptor = MoSamlIdpUtility::xpQuery($entityDescriptor, './saml_metadata:SPSSODescriptor');
            if(isset($SPSSODescriptor) && !empty($SPSSODescriptor)){
                array_push($this->serviceProviders, new ServiceProviders($entityDescriptor));
            }
        }
    }

    public function getServiceProviders(){
        return $this->serviceProviders;
    }
}

class ServiceProviders{

    private $entityID;
    private $acsURL;
    private $assertionsSigned;

    public function __construct(\DOMElement $xml = NULL) {

        if ($xml->hasAttribute('entityID')) {
            $this->entityID = $xml->getAttribute('entityID');
        }

        $SPSSODescriptor = MoSamlIdpUtility::xpQuery($xml, './saml_metadata:SPSSODescriptor');

        if (count($SPSSODescriptor) > 1) {
            throw new Exception('More than one <SPSSODescriptor> in <EntityDescriptor>.');
        } elseif (empty($SPSSODescriptor)) {
            throw new Exception('Missing required <SPSSODescriptor> in <EntityDescriptor>.');
        }

        $this->parseAcsURL($SPSSODescriptor);
        $this->assertionsSigned($SPSSODescriptor);
    }

    private function parseAcsURL($SPSSODescriptor){

        $AssertionConsumerService = MoSamlIdpUtility::xpQuery($SPSSODescriptor[0], './saml_metadata:AssertionConsumerService');
        foreach ($AssertionConsumerService as $sign) {
            if($sign->hasAttribute('Location')){
                $this->acsURL = $sign->getAttribute('Location');
            }
        }
    }

    private function assertionsSigned($SPSSODescriptor){

        foreach ($SPSSODescriptor as $sign) {
            if($sign->hasAttribute('WantAssertionsSigned')){
                $this->assertionsSigned = $sign->getAttribute('WantAssertionsSigned');
            }
        }
    }

    public function getEntityID(){
        return $this->entityID;
    }

    public function getAcsURL(){
        return $this->acsURL;
    }

    public function getAssertionsSigned(){
        return $this->assertionsSigned;
    }
}
