<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Tests\Integration\Fixtures\TestSchemaGeneration\Model\JMS;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;

final class ComplexObject
{
    /** @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingAnyTypeHint */
    public $unknownProperty;

    public string $stringProperty = 'stringProperty';

    public string $stringPropertyWithCustomGroup = 'stringPropertyWithCustomGroup';

    public string $stringPropertyWithCustomName = 'stringPropertyWithCustomName';

    public string $stringPropertyForOldVersion = 'stringPropertyForOldVersion';

    public ?string $nullableStringProperty = null;

    public int $intProperty = 1;

    public float $floatProperty = 1.23;

    public bool $boolProperty = true;

    /** @var string[] */
    public array $arrayProperty = ['test'];

    public ?ChildObject $childObjectProperty = null;

    public ?InlineObject $inlineObjectProperty = null;

    /** @var ChildObject[] */
    public array $arrayOfChildObjectsProperty = [];

    public ?DateTime $dateTimeProperty = null;

    public ?DateTimeImmutable $dateTimeImmutableProperty = null;

    public ?DateTimeInterface $dateTimeInterfaceProperty = null;

    /** @var int|string */
    public $scalarUnionProperty;

    public function getVirtualProperty(): int
    {
        return 123;
    }

    public function getVirtualPropertyWithCustomName(): string
    {
        return 'testing';
    }
}
