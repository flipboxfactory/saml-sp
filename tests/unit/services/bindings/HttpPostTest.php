<?php

namespace flipbox\saml\sp\tests\services;

use Codeception\Test\Unit;
use flipbox\saml\sp\services\bindings\HttpPost;

class HttpPostTest extends Unit
{
    public function testGetTemplatePath()
    {
        $this->assertEquals(
            'saml-sp/_components/post-binding-submit.twig',
            (new HttpPost)->getTemplatePath()
        );
    }
}