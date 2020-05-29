<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model;

use cebe\openapi\spec\Reference;
use cebe\openapi\spec\Schema;
use RuntimeException;
use Speicher210\OpenApiGenerator\Resolver\DefinitionName;
use function md5;
use function serialize;
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
            if ($this->hash($definition) === $this->hash($model->definition())) {
                return true;
            }
        }

        return false;
    }

    private function getModelWithDefinition(Definition $definition) : Model
    {
        foreach ($this->models as $model) {
            if ($this->hash($definition) === $this->hash($model->definition())) {
                return $model;
            }
        }

        throw new RuntimeException(
            sprintf('Model with definition name "%s" does not exist.', $this->hash($definition))
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
                sprintf('Model with definition name "%s" already exists.', $this->hash($definition))
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

    private function hash(Definition $definition) : string
    {
        return md5(serialize([$definition->className(), $definition->serializationGroups()]));
    }
}
