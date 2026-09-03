<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Model;

use PHPUnit\Framework\TestCase;
use Protung\OpenApiGenerator\Model\Path\Output;
use Protung\OpenApiGenerator\Model\Path\Output\RFC7807ErrorOutput;
use Protung\OpenApiGenerator\Model\Path\Output\SymfonyValidationErrorOutput;
use Protung\OpenApiGenerator\Model\Response;
use Psl;

final class ResponseTest extends TestCase
{
    public function testWithDescriptionUpdatesDescription(): void
    {
        $response = Response::for202()->withDescription('test description');

        self::assertSame('test description', $response->description());
    }

    public function testFor400DefaultsToValidationErrorOutput(): void
    {
        $response = Response::for400();

        self::assertSame(400, $response->statusCode());
        self::assertSame(
            'Returned when the request payload could not be parsed or when it failed validation',
            $response->description(),
        );

        $outputs = $response->outputs();
        self::assertCount(1, $outputs);
        self::assertInstanceOf(SymfonyValidationErrorOutput::class, $outputs[0]);
        self::assertSame([Output::CONTENT_TYPE_APPLICATION_JSON], $outputs[0]->contentTypes());
        self::assertSame(400, $outputs[0]->example()['status']);
    }

    public function testTheStatusCodeOfTheResponseIsPassedDownToTheOutput(): void
    {
        $outputs = Response::for400(SymfonyValidationErrorOutput::create())->outputs();

        self::assertCount(1, $outputs);
        self::assertInstanceOf(SymfonyValidationErrorOutput::class, $outputs[0]);
        self::assertSame(400, $outputs[0]->statusCode());
        self::assertSame(400, $outputs[0]->example()['status']);
    }

    public function testAnOutputAttachedToTwoResponsesKeepsTheStatusCodeOfEachOfThem(): void
    {
        $output = SymfonyValidationErrorOutput::create();

        $badRequest    = Response::for400($output);
        $unprocessable = Response::for422($output);

        self::assertSame(400, Psl\Type\instance_of(SymfonyValidationErrorOutput::class)->coerce($badRequest->outputs()[0])->statusCode());
        self::assertSame(422, Psl\Type\instance_of(SymfonyValidationErrorOutput::class)->coerce($unprocessable->outputs()[0])->statusCode());
        self::assertSame(422, $output->statusCode());
    }

    public function testFor422DefaultsToValidationErrorOutputWithSymfonyDefaultStatusCode(): void
    {
        $response = Response::for422();

        self::assertSame(422, $response->statusCode());
        self::assertSame('Returned when the request payload failed validation', $response->description());

        $outputs = $response->outputs();
        self::assertCount(1, $outputs);
        self::assertInstanceOf(SymfonyValidationErrorOutput::class, $outputs[0]);
        self::assertSame(422, $outputs[0]->example()['status']);
    }

    public function testFor400KeepsExplicitlyGivenOutput(): void
    {
        $output = RFC7807ErrorOutput::for400();

        $response = Response::for400($output);

        self::assertSame([$output], $response->outputs());
        self::assertSame(
            [Output::CONTENT_TYPE_APPLICATION_PROBLEM_JSON],
            $output->contentTypes(),
        );
    }

    public function testContentTypesCanBeDeclaredOnTheErrorOutputs(): void
    {
        $output = SymfonyValidationErrorOutput::create()->withContentTypes(
            Output::CONTENT_TYPE_APPLICATION_PROBLEM_JSON,
            Output::CONTENT_TYPE_APPLICATION_JSON,
        );

        self::assertSame(
            [
                Output::CONTENT_TYPE_APPLICATION_PROBLEM_JSON,
                Output::CONTENT_TYPE_APPLICATION_JSON,
            ],
            $output->contentTypes(),
        );
    }
}
