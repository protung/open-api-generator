<?php

declare(strict_types=1);

use Speicher210\OpenApiGenerator\Model;
use Speicher210\OpenApiGenerator\Model\Info\Info;

return [
    'info' => new Info(
        'Open API Generator',
        'To specify the API version to use header: `X-Accept-Version: 1.1.0`',
    ),
    'securityDefinitions' => [
        Model\Security\Definition::apiKey('ApiKey', 'X-API-KEY', 'Value for the X-API-KEY header'),
    ],
    'paths' => [
    ],
];
