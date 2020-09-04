<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model;

use cebe\openapi\spec\Reference;
use cebe\openapi\spec\Schema;
use RuntimeException;

use function array_key_exists;
use function implode;
use function md5;
use function serialize;
use function spl_object_hash;
use function sprintf;

final class ModelRegistry
{
    /** @var array<string,Model> */
    private array $models = [];

    /** @var array<string,ReferenceModel> */
    private array $referencedModels = [];

    public function schemaExistsForDefinition(Definition $definition): bool
    {
        return array_key_exists(
            $this->definitionKey($definition),
            $this->models
        );
    }

    private function getModelWithDefinition(Definition $definition): Model
    {
        if ($this->schemaExistsForDefinition($definition)) {
            return $this->models[$this->definitionKey($definition)];
        }

        throw new RuntimeException(
            sprintf(
                'Model with class name "%s" and serialization groups "%s" does not exist.',
                $definition->className(),
                implode(', ', $definition->serializationGroups())
            )
        );
    }

    public function getSchema(Definition $definition): Schema
    {
        return $this->getModelWithDefinition($definition)->schema();
    }

    public function addSchema(Definition $definition, Schema $schema): void
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

        $this->models[$this->definitionKey($definition)] = new Model($definition, $schema);
    }

    public function createReference(Definition $definition, string $referencePath): Reference
    {
        $hash = $this->definitionKey($definition);

        foreach ($this->referencedModels as $referencedModel) {
            if ($referencedModel->referencePath() === $referencePath && ! $referencedModel->definition()->equals($definition)) {
                throw new RuntimeException(
                    sprintf(
                        'Reference path "%s" for definition with class name "%s" and serialization groups "%s" is already taken.',
                        $referencePath,
                        $definition->className(),
                        implode(', ', $definition->serializationGroups())
                    )
                );
            }
        }

        $this->referencedModels[$hash] = new ReferenceModel(
            $this->getModelWithDefinition($definition),
            $referencePath
        );

        return new Reference(['$ref' => $referencePath]);
    }

    /**
     * @return ReferenceModel[]
     */
    public function referencedModels(): array
    {
        return $this->referencedModels;
    }

    /**
     * @psalm-pure
     */
    private function definitionKey(Definition $definition): string
    {
        $exampleObject = $definition->exampleObject();
        $objectHash    = $exampleObject !== null ? spl_object_hash($exampleObject) : null;

        return md5(
            serialize(
                [
                    $definition->className(),
                    $definition->serializationGroups(),
                    $objectHash,
                ]
            )
        );
    }
}
