<?php


namespace flipbox\saml\sp\validators;

use flipbox\saml\core\records\AbstractProvider;
use SAML2\Assertion\Validation\Result as AssertionResult;
use SAML2\Configuration\Destination;
use SAML2\Response as SamlResponse;
use SAML2\Response\Validation\ConstraintValidator\DestinationMatches;
use SAML2\Response\Validation\ConstraintValidator\IsSuccessful;
use SAML2\Response\Validation\Result as ResponseResult;

class Response
{
    /**
     * @var AbstractProvider
     */
    private $identityProvider;

    /**
     * @var AbstractProvider
     */
    private $serviceProvider;

    /**
     * @var bool
     */
    private $requireResponseToBeSigned = true;
    /**
     * @var bool
     */
    private $requireAssertionsToBeSigned = true;

    /**
     * @var array
     */
    private $validators = [];

    /**
     * Response constructor.
     * @param AbstractProvider $identityProvider
     * @param AbstractProvider $serviceProvider
     */
    public function __construct(
        AbstractProvider $identityProvider,
        AbstractProvider $serviceProvider,
        $requireResponseToBeSigned = true,
        $requireAssertionsToBeSigned = true,
    ) {
        $this->identityProvider = $identityProvider;
        $this->serviceProvider = $serviceProvider;
        $this->requireResponseToBeSigned = $requireResponseToBeSigned;
        $this->requireAssertionsToBeSigned = $requireAssertionsToBeSigned;

        $this->addValidators();
    }

    private function addValidators()
    {
        $this->validators = [
            new IsSuccessful(),
            new DestinationMatches(
                new Destination(
                    $this->serviceProvider->firstSpAcsService()->getLocation()
                )
            ),

        ];
        if ($keyStore = $this->identityProvider->signingXMLSecurityKeyStore()) {
            $this->validators[] = new SignedElement($keyStore, $this->requireResponseToBeSigned, "Response");
        } elseif ($this->requireResponseToBeSigned) {
            throw new \Exception("Response must be signed");
        }
    }

    /**
     * @param $response
     * @return ResponseResult
     */
    public function validate($response): ResponseResult
    {
        $responseResult = new ResponseResult();
        foreach ($this->validators as $validator) {
            $validator->validate($response, $responseResult);
        }

        $this->validateAssertions($response, $responseResult);


        return $responseResult;
    }

    /**
     * @param SamlResponse $response
     * @param ResponseResult $responseResult
     */
    protected function validateAssertions(SamlResponse $response, ResponseResult $responseResult)
    {
        $assertionResult = null;
        foreach ($response->getAssertions() as $assertion) {
            $validator = new Assertion(
                $response,
                $this->identityProvider,
                $this->serviceProvider,
                $this->requireAssertionsToBeSigned
            );

            $assertionResult = $validator->validate($assertion);

            $this->addErrorsToResult($responseResult, $assertionResult);
        }
    }

    /**
     * @param ResponseResult $responseResult
     * @param AssertionResult $assertionResult
     */
    private function addErrorsToResult(ResponseResult $responseResult, AssertionResult $assertionResult)
    {
        foreach ($assertionResult->getErrors() as $error) {
            $responseResult->addError($error);
        }
    }
}
