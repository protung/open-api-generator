<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Describer\InputDescriber;

use cebe\openapi\spec\MediaType;
use cebe\openapi\spec\Operation;
use cebe\openapi\spec\RequestBody;
use cebe\openapi\spec\Schema;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Protung\OpenApiGenerator\Describer\InputDescriber\SymfonyMappedPayloadInputDescriber;
use Protung\OpenApiGenerator\Model\Path\Input\SymfonyMappedPayloadInput;
use Protung\OpenApiGenerator\Tests\Describer\InputDescriber\Fixtures\NestedPayloadRequest;
use Protung\OpenApiGenerator\Tests\Describer\InputDescriber\Fixtures\OptionalMembersRequest;
use Protung\OpenApiGenerator\Tests\Describer\InputDescriber\Fixtures\PairRequest;
use Protung\OpenApiGenerator\Tests\Describer\InputDescriber\Fixtures\SaveCopyTextActivityRequest;
use Protung\OpenApiGenerator\Tests\Describer\InputDescriber\Fixtures\SignInRequest;
use Psl;
use Psl\Json;
use Symfony\Component\Validator\ValidatorBuilder;

final class SymfonyMappedPayloadInputDescriberTest extends TestCase
{
    public function testDescribesLengthLimitsOfTheMappedPayload(): void
    {
        self::assertRequestBodySchema(
            PairRequest::class,
            <<<'JSON'
            {
                "required": ["pairingCode"],
                "type": "object",
                "properties": {
                    "pairingCode": {"type": "string", "minLength": 1, "maxLength": 26}
                }
            }
            JSON,
        );
    }

    public function testDescribesEveryConstructorPropertyOfTheMappedPayload(): void
    {
        self::assertRequestBodySchema(
            SignInRequest::class,
            <<<'JSON'
            {
                "required": ["username", "password"],
                "type": "object",
                "properties": {
                    "username": {"type": "string", "minLength": 1, "maxLength": 100},
                    "password": {"type": "string", "minLength": 1}
                }
            }
            JSON,
        );
    }

    public function testTheStricterOfNotBlankAndLengthWins(): void
    {
        self::assertRequestBodySchema(
            SaveCopyTextActivityRequest::class,
            <<<'JSON'
            {
                "required": ["content", "pageUrl"],
                "type": "object",
                "properties": {
                    "content": {"type": "string", "minLength": 1, "maxLength": 65535},
                    "pageUrl": {"type": "string", "minLength": 1, "maxLength": 8192}
                }
            }
            JSON,
        );
    }

    public function testAPropertyIsOnlyRequiredWhenItCanNotBeLeftOut(): void
    {
        self::assertRequestBodySchema(
            OptionalMembersRequest::class,
            <<<'JSON'
            {
                "required": ["name"],
                "type": "object",
                "properties": {
                    "name": {"type": "string", "minLength": 1},
                    "retryCount": {"type": "integer", "nullable": true},
                    "verbose": {"type": "boolean"},
                    "nickname": {"type": "string", "nullable": true, "minLength": 3}
                }
            }
            JSON,
        );
    }

    public function testAPropertyWhichIsNotAScalarPointsAtTheEscapeHatch(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Can not describe property "pair" of payload'
            . ' "Protung\OpenApiGenerator\Tests\Describer\InputDescriber\Fixtures\NestedPayloadRequest",'
            . ' only string, int, bool and float are supported,'
            . ' "Protung\OpenApiGenerator\Tests\Describer\InputDescriber\Fixtures\PairRequest" given.'
            . ' Describe this body with BodyInput::withIOFields() instead.',
        );

        self::describeOperation(NestedPayloadRequest::class);
    }

    public function testBodyInputIsNotAllowedInGetRequests(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Body input is not allowed in GET requests.');

        self::describeOperation(PairRequest::class, 'GET');
    }

    /**
     * @param class-string $className
     */
    private static function assertRequestBodySchema(string $className, string $expectedSchema): void
    {
        $requestBody = Psl\Type\instance_of(RequestBody::class)->coerce(
            self::describeOperation($className)->requestBody,
        );
        $mediaType   = Psl\Type\instance_of(MediaType::class)->coerce($requestBody->content['application/json']);
        $schema      = Psl\Type\instance_of(Schema::class)->coerce($mediaType->schema);

        self::assertJsonStringEqualsJsonString($expectedSchema, Json\encode($schema->getSerializableData()));
    }

    /**
     * @param class-string $className
     */
    private static function describeOperation(string $className, string $httpMethod = 'POST'): Operation
    {
        $validator = (new ValidatorBuilder())->enableAttributeMapping()->getValidator();

        $operation = new Operation(['responses' => []]);

        (new SymfonyMappedPayloadInputDescriber($validator))->describe(
            SymfonyMappedPayloadInput::forClass($className),
            $operation,
            $httpMethod,
        );

        return $operation;
    }
}
