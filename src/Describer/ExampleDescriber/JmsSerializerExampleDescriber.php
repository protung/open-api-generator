<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer\ExampleDescriber;

use cebe\openapi\spec\Schema;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Psl;
use Speicher210\OpenApiGenerator\Model\Path\Output;

final class JmsSerializerExampleDescriber implements ExampleDescriber
{
    private Serializer $jmsSerializer;

    public function __construct(Serializer $jmsSerializer)
    {
        $this->jmsSerializer = $jmsSerializer;
    }

    public function describe(Schema $schema, Output $output): void
    {
        $output = Psl\Type\instance_of(Output\ObjectOutput::class)->coerce($output);

        $serializationContext = new SerializationContext();
        $serializationContext->setGroups($output->serializationGroups());
        $serializationContext->setSerializeNull(true);

        $exampleObject = $output->example();
        if ($exampleObject === null) {
            return;
        }

        $schema->example = $this->jmsSerializer->toArray($exampleObject, $serializationContext);
    }

    public function supports(Output $output): bool
    {
        return $output instanceof Output\ObjectOutput;
    }
}
