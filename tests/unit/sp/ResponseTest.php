<?php


namespace tests\unit\sp;

use Codeception\Scenario;
use Codeception\Test\Unit;
use craft\elements\User;
use craft\models\UserGroup;
use flipbox\saml\core\exceptions\InvalidMessage;
use flipbox\saml\core\helpers\ClaimTypes;
use flipbox\saml\sp\models\Settings;
use flipbox\saml\sp\records\ProviderRecord;
use flipbox\saml\sp\Saml;
use SAML2\Assertion;
use SAML2\DOMDocumentFactory;
use SAML2\EncryptedAssertion;
use SAML2\Message;
use Step\Unit\Common\Metadata;
use Step\Unit\Common\Response;
use Step\Unit\Common\SamlPlugin;
use flipbox\saml\sp\validators\Response as ResponseValidator;

class ResponseTest extends Unit
{

    /**
     * @var Saml
     */
    private $module;

    /**
     * @var Metadata
     */
    private $metadataFactory;
    /**
     * @var SamlPlugin
     */
    private $pluginHelper;

    /**
     * @var Response
     */
    private $responseFactory;

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _before()
    {
        $this->module = new Saml('saml-sp');

        $scenario = new Scenario($this);

        $this->metadataFactory = new Metadata($this->module, $scenario);
        $this->pluginHelper = new SamlPlugin($this->module, $scenario);
        $this->responseFactory = new Response(
            $this->module,
            $this->metadataFactory,
            $scenario
        );
    }

    private function getIdp()
    {
        $idp = $this->metadataFactory->createTheirProviderWithSigningKey($this->module);
        $this->responseFactory->setAttributeMapping(
            $idp
        );
        return $idp;
    }

    private function getSp()
    {
        return $this->metadataFactory->createMyProviderWithKey($this->module);
    }

    private function getResponse(ProviderRecord $idp, ProviderRecord $sp, $encrypted = false)
    {
        $response = $this->responseFactory->createSuccessfulResponse(
            $idp,
            $sp,
            $encrypted
        );

        $signedMsg = $response->toSignedXML();

        $document = DOMDocumentFactory::fromString(
            $signedMsg->ownerDocument->saveXML()
        );

        if (!$document->firstChild instanceof \DOMElement) {
            throw new \Exception('Malformed SAML message received.');
        }
        $idpCert = file_get_contents(
            codecept_data_dir() . '/keypairs/saml-idp.crt'
        );

        /**
         * @var \SAML2\Response $returnResponse
         */
        $returnResponse = Message::fromXML($document->firstChild);
        $returnResponse->setSignatureKey(
            $this->metadataFactory->idpPrivateKey()
        );
        $returnResponse->setCertificates([
            $idpCert
        ]);

        $firstAssertion =$returnResponse->getAssertions()[0];
        if(!($firstAssertion instanceof EncryptedAssertion)) {

            $firstAssertion->setSignatureKey(
                $this->metadataFactory->idpPrivateKey()
            );
            $firstAssertion->setCertificates([
                $idpCert
            ]);
        }

        return $returnResponse;
    }

    private function getUser(){
        return new User([
            'firstName' => 'Damien',
            'lastName' => 'Smrt',
            'email' => 'damien@flipboxdigital.com',
            'username' => 'damien@flipboxdigital.com',
        ]);
    }

    /**
     * @throws \ReflectionException
     */
    protected static function getMethod(string $class, string $name): \ReflectionMethod
    {
        $class = new \ReflectionClass($class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }


    /**
     * @throws \ReflectionException
     */
    public function testUserSync()
    {
        $this->pluginHelper->installIfNeeded();
        $this->module->loadSaml2Container();

        $idp = $this->getIdp();
        $sp = $this->getSp();

        $response = $this->getResponse(
            $idp,
            $sp
        );

        $user = $this->getUser();

        $foo = self::getMethod(\flipbox\saml\sp\services\login\User::class, 'construct');
        $obj = new \flipbox\saml\sp\services\login\User();
        
        $foo->invokeArgs($obj,
            [
                $user,
                $response,
                $idp,
                $sp,
                $this->module->getSettings()
            ]
        );


        $this->assertSame(
            $user->firstName,
            $this->responseFactory->getFirstName()
        );

        $this->assertSame(
            $user->lastName,
            $this->responseFactory->getLastName()
        );

        $this->assertSame(
            $user->email,
            $this->responseFactory->getEmail()
        );

//        $identity =
//            Saml::getInstance()->getProviderIdentity()->getByUserAndResponse(
//            $user,
//            $response,
//            $sp,
//            $idp
//        );


//        // TODO fix this
//        $this->expectException(UserException::class);
//        Saml::getInstance()->getLogin()->byIdentity($identity);

    }

    public function testResponseNotAfterValidationFailed(){
        $this->pluginHelper->installIfNeeded();
        $this->module->loadSaml2Container();

        $idp = $this->getIdp();
        $sp = $this->getSp();

        $response = $this->getResponse(
            $idp,
            $sp
        );

        // set expiration to 5 minutes ago
        $response->getAssertions()[0]->setNotOnOrAfter(
            (new \DateTime('-5 minutes'))->getTimestamp()
        );

        $validator = new ResponseValidator(
            $idp,
            $sp
        );

        $result = $validator->validate($response);

        $this->assertEquals(
            1,
            count($result->getErrors())
        );
    }

    public function testResponseSignatureValidationFailed(){
        $this->pluginHelper->installIfNeeded();
        $this->module->loadSaml2Container();

        $idp = $this->getIdp();
        $sp = $this->getSp();

        $response = $this->getResponse(
            $idp,
            $sp
        );

        $response->setSignatureKey();

        $signedMsg = $response->toSignedXML();

        // Manipulate Response XML after it was signed
        $responseString = str_replace("Wick", "what",$signedMsg->ownerDocument->saveXML());

        $document = DOMDocumentFactory::fromString($responseString);
        if (!$document->firstChild instanceof \DOMElement) {
            throw new \Exception('Malformed SAML message received.');
        }

        /**
         * @var \SAML2\Response $msg
         */
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            "Reference validation failed"
        );
        $newResponse = Message::fromXML($document->firstChild);

        $validator = new ResponseValidator(
            $idp,
            $sp
        );


        $validator->validate($newResponse);
    }

    public function testResponseWithSignatureRequiredValidation(){
        $this->pluginHelper->installIfNeeded();
        $this->module->loadSaml2Container();

        $idp = $this->getIdp();
        $sp = $this->getSp();

        $response = $this->getResponse(
            $idp,
            $sp
        );
        $unsigned = $response->toUnsignedXML();
        $document = DOMDocumentFactory::fromString($unsigned->ownerDocument->saveXML());
        if (!$document->firstChild instanceof \DOMElement) {
            throw new \Exception('Malformed SAML message received.');
        }
        $newResponse = Message::fromXML($document->firstChild);

        $validator = new ResponseValidator(
            $idp,
            $sp,
            true,
            true
        );

        $this->expectException(\Exception::class);
        $validator->validate($newResponse);

        $validator = new ResponseValidator(
            $idp,
            $sp,
            false,
            true
        );
//        $this->expectException(\Exception::class);
        $validator->validate($newResponse);

    }

    public function testResponseValidation(){
        $this->pluginHelper->installIfNeeded();
        $this->module->loadSaml2Container();

        $idp = $this->getIdp();
        $sp = $this->getSp();

        $response = $this->getResponse(
            $idp,
            $sp
        );

        $validator = new ResponseValidator(
            $idp,
            $sp
        );

        $result = $validator->validate($response);

        $this->assertEmpty(
            $result->getErrors()
        );
    }

    public function testGetByUserAndResponse(){
        $this->pluginHelper->installIfNeeded();
        $this->module->loadSaml2Container();

        $idp = $this->getIdp();
        $sp = $this->getSp();

        $response = $this->getResponse(
            $idp,
            $sp
        );
        Saml::getInstance()->getProviderIdentity()->getByUserAndResponse(
            $this->getUser(),
            $response,
            $sp,
            $idp
        );
    }

    public function testEmptyAssertion()
    {
        $idp = $this->getIdp();
        $sp = $this->getSp();

        $response = $this->getResponse(
            $idp,
            $sp
        );
        $response->setAssertions([]);

        $this->expectException(InvalidMessage::class);

        Saml::getInstance()->getUser()->getFirstAssertion(
            $response,
            $sp
        );
    }

    public function testEncryptedResponse()
    {
        $idp = $this->getIdp();
        $sp = $this->getSp();

        $response = $this->getResponse(
            $idp,
            $sp,
            true
        );

        $assertion = $this->module->getUser()->getFirstAssertion(
            $response,
            $sp
        );

        $this->assertInstanceOf(
            Assertion::class,
            $assertion
        );
    }

    public function testGetByResponse()
    {

        $idp = $this->getIdp();
        $sp = $this->getSp();

        $response = $this->getResponse(
            $idp,
            $sp
        );

//        \Craft::$app->elements->saveElement(new User([
//            'email'=>$this->responseFactory->getEmail(),
//            'username'=>$this->responseFactory->getEmail(),
//            'firstName'=>$this->responseFactory->getFirstName(),
//            'lastName'=>$this->responseFactory->getLastName(),
//        ]));

        $settings = new Settings();

        $user = Saml::getInstance()->getUser()->getByResponse(
            $response,
            $sp,
            $idp,
            $settings
        );

        $this->assertInstanceOf(
            User::class,
            $user
        );


        $response->getAssertions()[0]->setNameId(null);
        $this->expectException(InvalidMessage::class);
        Saml::getInstance()->getUser()->getByResponse(
            $response,
            $sp,
            $idp,
            $settings
        );

        $settings->nameIdAttributeOverride = ClaimTypes::EMAIL_ADDRESS;
        Saml::getInstance()->getUser()->getByResponse(
            $response,
            $sp,
            $idp,
            $settings
        );
    }

    public function testGetByResponseNameIdOverride()
    {

        $idp = $this->getIdp();
        $sp = $this->getSp();

        $response = $this->getResponse(
            $idp,
            $sp
        );
        $settings = new Settings();
        $settings->nameIdAttributeOverride = ClaimTypes::EMAIL_ADDRESS;
        $user = Saml::getInstance()->getUser()->getByResponse(
            $response,
            $sp,
            $idp,
            $settings
        );

        $this->assertInstanceOf(
            User::class,
            $user
        );
    }

    public function testAssertionTrait(){

        $idp = $this->getIdp();
        $sp = $this->getSp();

        $response = $this->getResponse(
            $idp,
            $sp
        );

        $assertion = Saml::getInstance()->getUser()->getFirstAssertion(
            $response,
            $sp
        );

        $this->assertInstanceOf(
            Assertion::class,
            $assertion
        );

        $response->setAssertions([]);

        $assertion = Saml::getInstance()->getUser()->getFirstAssertion(
            $response,
            $sp
        );

    }

    public function testUserGroups(){
        $user = $this->getUser();

        $identityProvider = $this->getIdp();
        $serviceProvider = $this->getSp();

        $response = $this->getResponse(
            $identityProvider,
            $serviceProvider
        );
        $user->email = "me@test2.com";
        $user->username = "me@test2.com";

        \Craft::$app->elements->saveElement($user);

        $settings = new Settings();

        Saml::getInstance()->getUserGroups()->sync(
            $user,
            $response,
            $serviceProvider,
            $settings
        );
    }

    public function testUserDefaultGroups(){
        $user = \Craft::$app->users->getUserByUsernameOrEmail('damien@flipboxdigital.com') ??
            $this->getUser();

        $identityProvider = $this->getIdp();
        $serviceProvider = $this->getSp();

        $response = $this->getResponse(
            $identityProvider,
            $serviceProvider
        );

        if(!$user->getId()){
            if(!\Craft::$app->elements->saveElement($user)) {
                throw new \Exception('Error saving user ' . \json_encode($user->getErrors()));
            }
        }

        \Craft::$app->userGroups->saveGroup(
            $userGroup = new UserGroup([
                'name' => 'Default Group',
                'handle' => 'defaultGroup',
            ])
        );
        $settings = $this->module->getSettings();
        Saml::debug($userGroup->id);

        $settings->defaultGroupAssignments = [
            $userGroup->id
        ];

        Saml::getInstance()->getUserGroups()->sync(
            $user,
            $response,
            $serviceProvider,
            $settings
        );

        $user = \Craft::$app->elements->getElementById($user->getId());
        $this->assertGreaterThan(0, count($user->getGroups()));
        $userGroupAfter = $user->getGroups()[0];

        $this->assertEquals(
            $userGroup->id,
            $userGroupAfter->id
        );
    }
}