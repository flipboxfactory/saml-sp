<?php


namespace tests\unit\sp;

use Codeception\Scenario;
use Codeception\Test\Unit;
use flipbox\saml\sp\Saml;
use Step\Unit\Common\Metadata;
use Step\Unit\Common\SamlPlugin;

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
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
//    protected function _before()
//    {
//        $this->module = new Saml('saml-sp');
//
//        $scenario = new Scenario($this);
//
//        $this->metadataFactory = new Metadata($scenario);
//        $this->pluginHelper = new SamlPlugin($scenario);
//    }
//
//
//    public function testResponseValidators()
//    {
//    }
}