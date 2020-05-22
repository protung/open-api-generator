<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model\Path;

interface Input
{
    public const LOCATION_HEADERS = 'headers';
    public const LOCATION_PATH    = 'path';
    public const LOCATION_QUERY   = 'query';
    public const LOCATION_BODY    = 'body';

    public const LOCATIONS = [
        self::LOCATION_HEADERS,
        self::LOCATION_PATH,
        self::LOCATION_QUERY,
        self::LOCATION_BODY,
    ];

    public function inHeaders() : bool;

    public function inPath() : bool;

    public function inQuery() : bool;

    public function inBody() : bool;
}
