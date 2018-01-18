<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/11/18
 * Time: 9:44 PM
 */

namespace flipbox\saml\sp\services\bindings;


use craft\web\Request;
use flipbox\saml\sp\Saml;
use flipbox\saml\sp\services\traits\Security;
use LightSaml\Error\LightSamlBindingException;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Protocol\SamlMessage;
use LightSaml\Model\XmlDSig\SignatureStringReader;
use LightSaml\SamlConstants;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use LightSaml\Credential\X509Certificate;

class HttpRedirect extends AbstractHttpBinding
{
    use Security;

    public function getKey(): XMLSecurityKey
    {
        return \LightSaml\Credential\KeyHelper::createPrivateKey(
            Saml::getInstance()->getSettings()->keyPath,
            '',
            true
        );
    }

    public function getCertificate(): X509Certificate
    {
        return X509Certificate::fromFile(
            Saml::getInstance()->getSettings()->certPath
        );
    }

    /**
     * @param Request $request
     * @return \LightSaml\Model\Protocol\AuthnRequest|\LightSaml\Model\Protocol\LogoutRequest|\LightSaml\Model\Protocol\LogoutResponse|\LightSaml\Model\Protocol\Response|SamlMessage
     */
    public function receive(Request $request)
    {
        $encodedMessage = $this->getMessage($request->getQueryParams());
        $encoding = $this->getEncoding($request->getQueryParams());
        $messageString = $this->decodeMessageString($encodedMessage, $encoding);


        $message = SamlMessage::fromXML($messageString, new DeserializationContext());

        if ($request->getQueryParam('RelayState')) {
            $message->setRelayState($request->getQueryParam('RelayState'));
        }

        $queryData = $this->getSignedQuery($request->getQueryParams());
        $this->loadSignature($message, $queryData);

        return $message;

    }

    /**
     * @param SamlMessage $message
     * @param array $data
     */
    protected function loadSignature(SamlMessage $message, array $data)
    {
        if (array_key_exists('Signature', $data)) {
            if (false == array_key_exists('SigAlg', $data)) {
                throw new LightSamlBindingException('Missing signature algorithm');
            }
            $message->setSignature(
                new SignatureStringReader($data['Signature'], $data['SigAlg'], $data['SignedQuery'])
            );
        }
    }

    /**
     * @param array $data
     * @return array
     */
    protected function getSignedQuery(array $data)
    {
        $sigQuery = $relayState = $sigAlg = '';
        foreach ($data as $name => $value) {
            switch ($name) {
                case 'SAMLRequest':
                case 'SAMLResponse':
                    $sigQuery = $name.'='.$value;
                    break;
                case 'RelayState':
                    $relayState = '&RelayState='.$value;
                    break;
                case 'SigAlg':
                    $sigAlg = '&SigAlg='.$value;
                    break;
            }
        }
        $data['SignedQuery'] = $sigQuery.$relayState.$sigAlg;
        return $data;

    }


    /**
     * @param array $data
     * @return mixed
     */
    protected function getMessage(array $data)
    {
        if (array_key_exists('SAMLRequest', $data)) {
            return $data['SAMLRequest'];
        } elseif (array_key_exists('SAMLResponse', $data)) {
            return $data['SAMLResponse'];
        } else {
            throw new LightSamlBindingException('Missing SAMLRequest or SAMLResponse parameter');
        }
    }

    /**
     * @param array $data
     * @return mixed|string
     */
    protected function getEncoding(array $data)
    {
        if (array_key_exists('SAMLEncoding', $data)) {
            return $data['SAMLEncoding'];
        } else {
            return SamlConstants::ENCODING_DEFLATE;
        }
    }

    /**
     * @param $msg
     * @param $encoding
     * @return string
     */
    protected function decodeMessageString($msg, $encoding)
    {
        $msg = base64_decode($msg);
        switch ($encoding) {
            case SamlConstants::ENCODING_DEFLATE:
                return gzinflate($msg);
                break;
            default:
                throw new LightSamlBindingException(sprintf("Unknown encoding '%s'", $encoding));
        }
    }
}