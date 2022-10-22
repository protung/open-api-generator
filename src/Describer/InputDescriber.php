<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Describer;

use cebe\openapi\spec\Operation;
use InvalidArgumentException;
use Protung\OpenApiGenerator\Model\Path\Input;
use Psl;

final class InputDescriber
{
    /** @var array<InputDescriber\InputDescriber> */
    private array $inputDescribers;

    public function __construct(InputDescriber\InputDescriber ...$inputDescribers)
    {
        $this->inputDescribers = $inputDescribers;
    }

    public function describe(Operation $operation, Input $input, string $httpMethod): void
    {
        foreach ($this->inputDescribers as $inputDescriber) {
            if ($inputDescriber->supports($input)) {
                $inputDescriber->describe($input, $operation, $httpMethod);

                return;
            }
        }

        throw new InvalidArgumentException(
            Psl\Str\format('Can not handle object to describe of type "%s"', $input::class),
        );
    }
}
