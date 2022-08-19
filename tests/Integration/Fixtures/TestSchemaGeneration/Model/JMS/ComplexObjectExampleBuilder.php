<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Integration\Fixtures\TestSchemaGeneration\Model\JMS;

use DateTime;

final class ComplexObjectExampleBuilder
{
    private function __construct()
    {
    }

    public static function create(): ComplexObject
    {
        $object = new ComplexObject();

        $object->childObjectProperty           = new ChildObject();
        $object->inlineObjectProperty          = new InlineObject();
        $object->arrayOfChildObjectsProperty[] = new ChildObject();
        $object->dateTimeProperty              = new DateTime('2020-06-01 14:00');

        return $object;
    }
}
