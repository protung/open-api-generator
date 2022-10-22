<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Describer\OutputDescriber;

use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type;
use Protung\OpenApiGenerator\Assert\Assert;
use Protung\OpenApiGenerator\Model\Path\Output;
use Protung\OpenApiGenerator\Model\Path\Output\PaginatedOutput;
use Psl;

use function array_fill_keys;
use function array_map;
use function count;

final class PaginatedOutputDescriber implements OutputDescriber
{
    private \Protung\OpenApiGenerator\Describer\OutputDescriber $outputDescriber;

    public function __construct(\Protung\OpenApiGenerator\Describer\OutputDescriber $outputDescriber)
    {
        $this->outputDescriber = $outputDescriber;
    }

    public function describe(Output $output): Schema
    {
        $output = Psl\Type\instance_of(PaginatedOutput::class)->coerce($output);

        return new Schema(
            [
                'properties' => [
                    'page' => new Schema(['type' => Type::INTEGER]),
                    'limit' => new Schema(['type' => Type::INTEGER]),
                    'pages' => new Schema(['type' => Type::INTEGER]),
                    'total' => new Schema(['type' => Type::INTEGER]),
                    '_links' => $this->createLinksSchema(),
                    '_embedded' => $this->createEmbeddedSchema($output),
                ],
                'required' => ['page', 'limit', 'pages', 'total', '_links', '_embedded'],
            ],
        );
    }

    private function createLinksSchema(): Schema
    {
        return new Schema(
            [
                'properties' => array_fill_keys(
                    ['self', 'first', 'last', 'previous', 'next'],
                    new Schema(
                        [
                            'properties' => ['href' => new Schema(['type' => Type::STRING])],
                            'type' => Type::OBJECT,
                        ],
                    ),
                ),
                'type' => Type::OBJECT,
                'required' => [
                    'self',
                    'first',
                ],
            ],
        );
    }

    private function createEmbeddedSchema(PaginatedOutput $output): Schema
    {
        $resourcesSchema = new Schema(['type' => Type::ARRAY]);

        $resources = array_map(
            function (Output $resource) {
                return $this->outputDescriber->describe($resource);
            },
            $output->embedded(),
        );

        Assert::minCount($resources, 1);

        if (count($resources) > 1) {
            $resourcesSchema->items = new Schema(['oneOf' => $resources]);
        } else {
            $resourcesSchema->items = $resources[0];
        }

        return new Schema(
            [
                'type' => Type::OBJECT,
                'properties' => [$output->resourcesKey() => $resourcesSchema],
                'required' => [$output->resourcesKey()],
            ],
        );
    }

    public function supports(Output $output): bool
    {
        return $output instanceof PaginatedOutput;
    }
}
