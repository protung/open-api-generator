<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model;

use cebe\openapi\spec\Reference;
use cebe\openapi\spec\Schema;
use RuntimeException;
use Speicher210\OpenApiGenerator\Resolver\DefinitionName;
use function implode;
use function sprintf;

final class ModelRegistry
{
    /** @var Model[] */
    private array $models = [];

    private DefinitionName $definitionNameResolver;

    public function __construct(DefinitionName $definitionNameResolver)
    {
        $this->definitionNameResolver = $definitionNameResolver;
    }

    public function schemaExistsForDefinition(Definition $definition) : bool
    {
        foreach ($this->models as $model) {
            if ($definition->equals($model->definition())) {
                return true;
            }
        }

        return false;
    }

    private function getModelWithDefinition(Definition $definition) : Model
    {
        foreach ($this->models as $model) {
            if ($definition->equals($model->definition())) {
                return $model;
            }
        }

        throw new RuntimeException(
            sprintf(
                'Model with class name "%s" and serialization groups "%s" does not exist.',
                $definition->className(),
                implode(', ', $definition->serializationGroups())
            )
        );
    }

    public function getSchema(Definition $definition) : Schema
    {
        return $this->getModelWithDefinition($definition)->schema();
    }

    public function getReference(Definition $definition) : Reference
    {
        $model     = $this->getModelWithDefinition($definition);
        $reference = $model->reference();
        if ($reference === null) {
            $reference = new Reference(['$ref' => $this->definitionNameResolver->getReference($definition)]);
            $model->setReference($reference);
        }

        return $reference;
    }

    public function addSchema(Definition $definition, Schema $schema, bool $asReference) : void
    {
        if ($this->schemaExistsForDefinition($definition)) {
            throw new RuntimeException(
                sprintf(
                    'Model with class name "%s" and serialization groups "%s" already exists.',
                    $definition->className(),
                    implode(', ', $definition->serializationGroups())
                )
            );
        }

        $this->models[] = new Model(
            $definition,
            $schema,
            $asReference ? new Reference(['$ref' => $this->definitionNameResolver->getReference($definition)]) : null
        );
    }

    /**
     * @return Model[]
     */
    public function models() : array
    {
        return $this->models;
    }
}
