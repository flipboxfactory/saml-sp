<?php

namespace Step\Unit\Common;


use Codeception\Scenario;
use flipbox\saml\core\AbstractPlugin;

class SamlPlugin extends \UnitTester
{
    private $module;

    public function __construct(AbstractPlugin $module, Scenario $scenario)
    {
        $this->module = $module;
        parent::__construct($scenario);
    }

    public function installIfNeeded()
    {
        if (! \Craft::$app->plugins->isPluginInstalled($this->module->getHandle())) {
            \Craft::$app->plugins->installPlugin($this->module->getHandle());
        }

    }

}