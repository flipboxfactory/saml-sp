<?php

namespace flipbox\saml\sp\tests\services;

use Codeception\Scenario;
use Codeception\Test\Unit;
use craft\elements\User;
use flipbox\saml\sp\records\ProviderRecord;
use flipbox\saml\sp\Saml;
use flipbox\saml\sp\services\Login;
use Step\Unit\Common\Metadata;
use Step\Unit\Common\Response;
use Step\Unit\Common\SamlPlugin;

class LoginTest extends Unit
{

    /**
     * @var Saml
     */
    private $module;

    /**
     * @var Metadata
     *
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

    private function getUser(){
        return new User([
            'firstName' => 'Damien',
            'lastName' => 'Smrt',
            'email' => 'damien@flipboxdigital.com',
            'username' => 'damien@flipboxdigital.com',
        ]);
    }

    private function getResponse(ProviderRecord $idp, ProviderRecord $sp, $encrypted = false)
    {
        return $this->responseFactory->createSuccessfulResponse(
            $idp,
            $sp,
            $encrypted
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

    public function testConstants()
    {
        $this->assertEquals(Login::EVENT_BEFORE_RESPONSE_TO_USER, 'eventBeforeResponseToUser');
        $this->assertEquals(Login::EVENT_AFTER_RESPONSE_TO_USER, 'eventAfterResponseToUser');
    }



    public function testTransformToUser(){
        $this->pluginHelper->installIfNeeded();
        $this->module->loadSaml2Container();

        $idp = $this->getIdp();
        $sp = $this->getSp();
        $user = $this->module->getLogin()->transformToUser(
            $this->getUser(),
            $this->getResponse(
                $idp,
                $sp
            ),
            $idp,
            $sp,
            $this->module->getSettings()
        );

        $this->assertInstanceOf(
            User::class,
            $user
        );
    }
}