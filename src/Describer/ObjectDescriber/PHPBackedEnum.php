<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer\ObjectDescriber;

use BackedEnum;
use cebe\openapi\spec\Schema;
use Psl\Vec;
use ReflectionEnum;
use Speicher210\OpenApiGenerator\Describer\ObjectDescriber;
use Speicher210\OpenApiGenerator\Model\Definition;

use function is_subclass_of;

final class PHPBackedEnum implements Describer
{
    public function describeInSchema(Schema $schema, Definition $definition, ObjectDescriber $objectDescriber): void
    {
        $class = $definition->className();

        if (! is_subclass_of($class, BackedEnum::class)) {
            return;
        }

        $reflection = new ReflectionEnum($class);

        $schema->type = match ($reflection->getProperty('value')->getType()?->getName()) {
            'int' => 'integer',
            'string' => 'string',
            default => 'string',
        };
        $schema->enum = Vec\map(
            $class::cases(),
            static fn (BackedEnum $value): int|string => $value->value
        );
    }

    public function supports(Definition $definition): bool
    {
        return is_subclass_of($definition->className(), BackedEnum::class);
    }
}
