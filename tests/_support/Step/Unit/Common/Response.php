<?php


namespace Step\Unit\Common;

use Codeception\Scenario;
use craft\test\Craft;
use flipbox\saml\core\AbstractPlugin;
use flipbox\saml\core\helpers\ClaimTypes;
use flipbox\saml\core\helpers\MessageHelper;
use flipbox\saml\core\helpers\SecurityHelper;
use flipbox\saml\sp\records\ProviderIdentityRecord;
use flipbox\saml\sp\records\ProviderRecord;
use SAML2\Assertion;
use SAML2\Constants;
use SAML2\EncryptedAssertion;
use SAML2\Response as SamlResponse;
use SAML2\XML\saml\Issuer;
use SAML2\XML\saml\NameID;
use SAML2\XML\saml\SubjectConfirmation;
use SAML2\XML\saml\SubjectConfirmationData;
use yii\helpers\StringHelper;

class Response extends \UnitTester
{
    /**
     * @var AbstractPlugin
     */
    private $module;
    /**
     * @var Metadata
     */
    private $metadataFactory;

    const ENDPOINT = "http://sp.localhost:9090/sso/login";

    private $userAttributes = [
        ClaimTypes::EMAIL_ADDRESS => [
            'me@example.com',
        ],
        ClaimTypes::GIVEN_NAME => [
            'Wick',
        ],
        ClaimTypes::SURNAME => [
            'Wick',
        ],
        'groups' => [
            'Åqua',
            'Teal',
        ],
    ];

    public function getEmail()
    {
        return $this->userAttributes[ClaimTypes::EMAIL_ADDRESS][0];
    }

    public function getFirstName()
    {
        return $this->userAttributes[ClaimTypes::GIVEN_NAME][0];
    }

    public function getLastName()
    {
        return $this->userAttributes[ClaimTypes::SURNAME][0];
    }

    public function getGroups()
    {
        return $this->userAttributes['groups'];
    }

    public function setAttributeMapping(ProviderRecord $providerRecord)
    {
        $providerRecord->setMapping([
            [
                'attributeName' => ClaimTypes::EMAIL_ADDRESS,
                'craftProperty' => 'email',
            ],
            [
                'attributeName' => ClaimTypes::GIVEN_NAME,
                'craftProperty' => 'firstName',
            ],
            [
                'attributeName' => ClaimTypes::SURNAME,
                'craftProperty' => 'lastName',
            ],
        ]);

    }

    public function __construct(AbstractPlugin $module, Metadata $metadataFactory, Scenario $scenario)
    {
        $this->module = $module;
        $this->metadataFactory = $metadataFactory;
        parent::__construct($scenario);
    }

    public function createSuccessfulResponse(
        ProviderRecord $identityProvider,
        ProviderRecord $serviceProvider,
        $encrypted = false
    )
    {
        $response = new SamlResponse();
        $issuer = new Issuer();
        $issuer->setFormat(Constants::NAMEID_ENTITY);
        $issuer->setValue($identityProvider->getEntityId());
        $response->setIssuer(
            $issuer
        );

        $response->setId($requestId = MessageHelper::generateId());
        $response->setDestination(
            static::ENDPOINT
        );
        $response->setStatus(
            [
                'Code' => Constants::STATUS_SUCCESS,
                'Message' => Constants::STATUS_SUCCESS,
            ]
        );

        $response->setIssueInstant(
            (new \DateTime())->getTimestamp()
        );
        $subjectConfirmation = new SubjectConfirmation();
        $subjectConfirmation->setSubjectConfirmationData(
            $subjectConfirmationData = new SubjectConfirmationData()
        );
        $subjectConfirmationData->setNotOnOrAfter(
            (new \DateTime())->getTimestamp()
        );

        // Add assertion
        $assertion = new Assertion();
        $issuer = new Issuer();
        $issuer->setValue(
            $identityProvider->getEntityId()
        );
        $assertion->setIssuer(
            $issuer
        );

        $nameId = new NameID();
        $nameId->setValue($this->getEmail());
        $assertion->setNameId($nameId);

        $assertion->setSubjectConfirmation(
            [
                $this->createSubjectConfirmation(
                    $serviceProvider,
                    $identityProvider
                ),
            ]
        );

        $assertion->setValidAudiences([
            $serviceProvider->getEntityId(),
            static::ENDPOINT
        ]);

        $assertion->setNotBefore(
            (new \DateTime(
                '-5 minutes'
            ))->getTimestamp()
        );

        $assertion->setNotOnOrAfter(
            (new \DateTime(
                '+5 minutes'
            ))->getTimestamp()
        );

        $sessionEnd = (new \DateTime())->setTimestamp(
            (new \DateTime('+1 hour'))->getTimestamp()
        );

        /**
         * Add AuthnStatement attributes and AuthnContext
         */
        $assertion->setAuthnInstant((new \DateTime())->getTimestamp());
        $assertion->setSessionNotOnOrAfter(
            $sessionEnd->getTimestamp()
        );

        $assertion->setSessionIndex(
            MessageHelper::generateId()
        );

        $assertion->setAuthnContextClassRef(
            Constants::AC_PASSWORD_PROTECTED_TRANSPORT
        );

        $assertion->setSignatureKey(
            $this->metadataFactory->idpPrivateKey()
        );

        $idpCert = file_get_contents(
            codecept_data_dir() . '/keypairs/saml-idp.crt'
        );

        $assertion->setCertificates(
            [
                $idpCert,
            ]
        );

        $assertion->setAttributes($this->userAttributes);

        // Encrypt Assertions
        if ($encrypted) {
            $unencrypted = $assertion;

            if (is_null($serviceProvider->encryptionKey())) {
                throw new \Exception('No encryption key found for the service provider.');
            }
            $unencrypted->setEncryptionKey(
                $serviceProvider->encryptionKey()
            );

            $assertion = new EncryptedAssertion();
            $assertion->setAssertion(
                $unencrypted,
                $serviceProvider->encryptionKey()
            );
        }

        $response->setAssertions(
            [
                $assertion,
            ]
        );
        $response->setSignatureKey(
            $this->metadataFactory->idpPrivateKey()
        );

        $response->setCertificates(
            [
                $idpCert,
            ]
        );

        return $response;
    }

    protected function createSubjectConfirmation(
        ProviderRecord $serviceProvider,
        ProviderRecord $identityProvider
    )
    {
        /**
         * Subject Confirmation
         * Reference: https://stackoverflow.com/a/29546696/1590910
         *
         * The times in the <SubjectConfirmationData> signals for how long time assertion can be tied to the subject.
         * In Web SSO where the subject confirmation method "bearer" is usually used, it means that within this time
         * we can trust that the assertion applies to the one providing the assertion. The assertion might be valid
         * for a longer time, but we must create a session within this time frame. This is described in the Web SSO
         * Profile section 4.1.4.3. The times in <SubjectConfirmationData> must fall within the interval of
         * those in <Conditions>.
         */

        $subjectConfirmation = new SubjectConfirmation();

        $subjectConfirmation->setMethod(
            Constants::CM_BEARER
        );

        // Add Subject Confirmation Data
        $subjectConfirmation->setSubjectConfirmationData(
            $subjectConfirmationData = new SubjectConfirmationData()
        );

        $subjectConfirmationData->setNotOnOrAfter(
            (new \DateTime(
                '+5 minutes'
            ))->getTimestamp()
        );

        $subjectConfirmationData->setRecipient(
            static::ENDPOINT
        );

        $subjectConfirmation->setNameID(
            $nameId = new NameID()
        );

        $nameId->setFormat(Constants::NAMEID_UNSPECIFIED);
        $nameId->setNameQualifier(
            $identityProvider->getEntityId()
        );

        $nameId->setValue(
            $this->userAttributes[ClaimTypes::EMAIL_ADDRESS][0]
        );

        return $subjectConfirmation;
    }
}