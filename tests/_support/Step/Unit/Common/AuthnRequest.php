<?php


namespace Step\Unit\Common;

use Codeception\Scenario;
use flipbox\saml\core\AbstractPlugin;
use SAML2\AuthnRequest as SamlAuthnRequest;
use SAML2\XML\saml\SubjectConfirmation;
use SAML2\XML\saml\SubjectConfirmationData;

class AuthnRequest extends \UnitTester
{
    /**
     * @var AbstractPlugin
     */
    private $module;
    /**
     * @var Metadata
     */
    private $metadataFactory;

    public function __construct(AbstractPlugin $module, Metadata $metadataFactory, Scenario $scenario)
    {
        $this->module = $module;
        $this->metadataFactory = $metadataFactory;
        parent::__construct($scenario);
    }

    public function createAuthnRequest()
    {
        $authnRequest = new SamlAuthnRequest();
        $authnRequest->setSignatureKey(
            $this->metadataFactory->spPrivateKey()
        );
        $subjectConfirmation = new SubjectConfirmation();
        $subjectConfirmation->setSubjectConfirmationData(
            $subjectConfirmationData = new SubjectConfirmationData()
        );
        $subjectConfirmationData->setNotOnOrAfter(
            (new \DateTime())->getTimestamp()
        );

        return $authnRequest;
    }
}