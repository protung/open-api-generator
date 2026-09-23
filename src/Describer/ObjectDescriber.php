<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Describer;

use cebe\openapi\spec\Reference;
use cebe\openapi\spec\Schema;
use Protung\OpenApiGenerator\Describer\ObjectDescriber\Describer;
use Protung\OpenApiGenerator\Model\Definition;
use Protung\OpenApiGenerator\Model\ModelRegistry;
use Protung\OpenApiGenerator\Resolver\DefinitionName;
use Psl;
use RuntimeException;

use function array_pop;
use function DeepCopy\deep_copy;

final class ObjectDescriber
{
    private ModelRegistry $modelRegistry;

    /** @var array<Describer> */
    private array $describers;

    /** @var list<Definition> */
    private array $definitionsBeingDescribed = [];

    public function __construct(ModelRegistry $modelRegistry, Describer ...$describers)
    {
        $this->modelRegistry = $modelRegistry;
        $this->describers    = $describers;
    }

    public function describe(Definition $definition): Schema
    {
        // A model reaching itself through its properties can not be inlined, the schema would never end.
        if ($this->isBeingDescribed($definition)) {
            return new Schema(
                [
                    'allOf' => [
                        $this->modelRegistry->createReference(
                            $definition,
                            '#/components/schemas/' . DefinitionName::getName($definition),
                        ),
                    ],
                ],
            );
        }

        if (! $this->modelRegistry->schemaExistsForDefinition($definition)) {
            $this->createSchema($definition);
        }

        return Psl\Type\instance_of(Schema::class)->coerce(deep_copy($this->modelRegistry->getSchema($definition)));
    }

    public function describeAsReference(Definition $definition, string $referencePath): Reference
    {
        $this->describe($definition);

        return $this->modelRegistry->createReference($definition, $referencePath);
    }

    /**
     * The schema is registered before it is described, so a model reaching itself can reference it.
     */
    private function createSchema(Definition $definition): void
    {
        foreach ($this->describers as $describer) {
            if ($describer->supports($definition)) {
                $schema = new Schema([]);
                $this->modelRegistry->addSchema($definition, $schema);

                $this->definitionsBeingDescribed[] = $definition;
                try {
                    $describer->describeInSchema($schema, $definition, $this);
                } finally {
                    array_pop($this->definitionsBeingDescribed);
                }

                return;
            }
        }

        throw new RuntimeException(
            Psl\Str\format(
                'Definition with class name "%s" and serialization groups "%s" can not be described.',
                $definition->className(),
                Psl\Str\join($definition->serializationGroups(), ', '),
            ),
        );
    }

    /**
     * Matches definitions the way the model registry tells models apart.
     */
    private function isBeingDescribed(Definition $definition): bool
    {
        return Psl\Iter\any(
            $this->definitionsBeingDescribed,
            static fn (Definition $beingDescribed): bool => $beingDescribed->equals($definition) && $beingDescribed->exampleObject() === $definition->exampleObject(),
        );
    }
}
