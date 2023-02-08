<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Integration\Fixtures\TestSchemaGeneration\Model\JMS;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Protung\OpenApiGenerator\Tests\Integration\Fixtures\TestSchemaGeneration\Model\Enum\BackedEnum;

final class ComplexObject
{
    /** @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingAnyTypeHint */
    public $unknownProperty;

    public string $stringProperty = 'stringProperty';

    public string $stringPropertyWithCustomGroup = 'stringPropertyWithCustomGroup';

    public string $stringPropertyWithCustomName = 'stringPropertyWithCustomName';

    public string $stringPropertyForOldVersion = 'stringPropertyForOldVersion';

    public string|null $nullableStringProperty = null;

    public int $intProperty = 1;

    public float $floatProperty = 1.23;

    public bool $boolProperty = true;

    /** @var string[] */
    public array $arrayProperty = ['test'];

    public ChildObject|null $childObjectProperty = null;

    public InlineObject|null $inlineObjectProperty = null;

    /** @var ChildObject[] */
    public array $arrayOfChildObjectsProperty = [];

    public DateTime|null $dateTimeProperty = null;

    public DateTimeImmutable|null $dateTimeImmutableProperty = null;

    public DateTimeInterface|null $dateTimeInterfaceProperty = null;

    public DateTimeInterface|null $dateProperty = null;

    public BackedEnum|null $backedEnum = null;

    /**
     * @todo add test with actual union type
     * @var int|string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
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
