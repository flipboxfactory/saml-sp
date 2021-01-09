<?php


namespace tests\unit\sp;

use Codeception\Scenario;
use Codeception\Test\Unit;
use craft\elements\User;
use flipbox\saml\core\records\AbstractProvider;
use flipbox\saml\core\records\AbstractProviderIdentity;
use flipbox\saml\sp\records\ProviderIdentityRecord;
use flipbox\saml\sp\Saml;
use Step\Unit\Common\Metadata;
use Step\Unit\Common\Response;
use Step\Unit\Common\SamlPlugin;

class MyProviderIdentityRecordTest extends Unit
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
    private function getIdp()
    {
        $idp = $this->metadataFactory->createTheirProviderWithSigningKey($this->module);

        $this->responseFactory->setAttributeMapping(
            $idp
        );
        return $idp;
    }
    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _before()
    {
        $this->module = new Saml('saml-sp');

        $scenario = new Scenario($this);

        $this->metadataFactory = new Metadata($this->module, $scenario);
        $this->metadataFactory = new Metadata($this->module, $scenario);
        $this->pluginHelper = new SamlPlugin($this->module, $scenario);
        $this->responseFactory = new Response(
            $this->module,
            $this->metadataFactory,
            $scenario
        );
    }

    public function testProviderIdentityType()
    {
        $recordClass = $this->module->getProviderIdentityRecordClass();
        $this->assertInstanceOf(
            AbstractProviderIdentity::class,
            new $recordClass
        );
    }

//    public function testProviderIdentityFind()
//    {
//        $idRecord = new ProviderIdentityRecord();
//        $idp = $this->getIdp();
////        $idp->save();
//        $idRecord->providerId = $idp->id;
//        $idRecord->nameId = 'damien@flipboxdigital.com';
//        $user =new User([
//            'firstName' => 'Damien',
//            'lastName' => 'Smrt',
//            'email' => 'damien@flipboxdigital.com',
//            'username' => 'damien@flipboxdigital.com',
//        ]);
//        $user->status = User::STATUS_ACTIVE;
//        $idRecord->user = $user;
//       \Craft::$app->elements->saveElement($user);
//        $this->module->getLogin()->byIdentity(
//            $idRecord
//        );
//
//        $this->assertInstanceOf(
//            User::class,
//            $user
//        );
//    }

}