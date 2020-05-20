<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model;

use Assert\Assertion;

final class SimpleOutput
{
    public const TYPE_STRING = 'string';

    public const TYPE_OBJECT = 'object';

    public const TYPE_INT = 'integer';

    public const TYPE_BOOL = 'boolean';

    /** @var string[] */
    private array $fields;

    private bool $asCollection;

    private bool $asObject;

    /**
     * @param string[] $fields
     */
    public function __construct(array $fields, bool $asCollection = false, bool $asObject = true)
    {
        Assertion::notEmpty($fields);

        $this->asObject = $asObject;
        $this->asCollection = $asCollection;
        $this->fields = $this->normalize($fields);
    }

    /**
     * @return mixed[]
     */
    public function fields(): array
    {
        return $this->fields;
    }

    public function asCollection(): bool
    {
        return $this->asCollection;
    }

    public function asObject(): bool
    {
        return $this->asObject;
    }

    /**
     * @param mixed[] $fields
     *
     * @return mixed[]
     */
    private function normalize(array $fields): array
    {
        if ($this->asObject === false) {
            return \array_values($fields);
        }

        $isAssociative = \count(\array_filter(\array_keys($fields), '\is_string')) > 0;

        if ($isAssociative === false) {
            return \array_fill_keys($fields, self::TYPE_STRING);
        }

        $return = [];
        foreach ($fields as $key => $value) {
            if (\is_numeric($key)) {
                $return[$value] = self::TYPE_STRING;
            } else {
                $return[$key] = $value;
            }
        }

        return $return;
    }
}
