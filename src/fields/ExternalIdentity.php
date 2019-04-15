<?php

namespace flipbox\saml\sp\fields;

use craft\base\ElementInterface;
use craft\helpers\UrlHelper;
use flipbox\saml\core\fields\AbstractExternalIdentity;
use flipbox\saml\sp\traits\SamlPluginEnsured;
use yii\db\Query;

class ExternalIdentity extends AbstractExternalIdentity
{

    use SamlPluginEnsured;

}
