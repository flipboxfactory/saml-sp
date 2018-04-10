<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/11/18
 * Time: 8:30 PM
 */

namespace flipbox\saml\sp\services\messages;


use craft\base\Component;
use flipbox\keychain\records\KeyChainRecord;
use flipbox\saml\core\exceptions\InvalidMessage;
use flipbox\saml\core\helpers\SecurityHelper;
use flipbox\saml\sp\Saml;
use LightSaml\Model\Assertion\Assertion;
use LightSaml\Model\Assertion\EncryptedAssertionReader;
use LightSaml\Validator\Model\Assertion\AssertionTimeValidator;
use LightSaml\Validator\Model\Assertion\AssertionValidator;
use LightSaml\Validator\Model\NameId\NameIdValidator;
use LightSaml\Validator\Model\Statement\StatementValidator;
use LightSaml\Validator\Model\Subject\SubjectValidator;

class Response extends Component
{

    /**
     * @param Assertion $assertion
     * @return bool
     */
    public function isValidTimeAssertion(Assertion $assertion)
    {
        $validator = new AssertionTimeValidator();
        $validator->validateTimeRestrictions($assertion, (new \DateTime())->getTimestamp(), 0);
        return true;
    }

    /**
     * @param Assertion $assertion
     * @return bool
     */
    public function isValidAssertion(Assertion $assertion)
    {
        $nameValidator = new NameIdValidator;
        $validator = new AssertionValidator(
            $nameValidator,
            new SubjectValidator($nameValidator),
            new StatementValidator
        );

        $validator->validateAssertion($assertion);

        return true;
    }


    /**
     * @param \LightSaml\Model\Protocol\Response $response
     * @param KeyChainRecord $keyChainRecord
     */
    public function decryptAssertions(\LightSaml\Model\Protocol\Response $response, KeyChainRecord $keyChainRecord)
    {
        $credential = SecurityHelper::createCredential($keyChainRecord);

        $decryptDeserializeContext = new \LightSaml\Model\Context\DeserializationContext();

        /** @var \LightSaml\Model\Assertion\EncryptedAssertionReader $encryptedAssertion */
        foreach ($response->getAllEncryptedAssertions() as $encryptedAssertion) {
            if ($encryptedAssertion instanceof EncryptedAssertionReader) {
                $response->addAssertion(
                    $encryptedAssertion->decryptMultiAssertion([$credential], $decryptDeserializeContext)
                );
            }
        }

    }
}