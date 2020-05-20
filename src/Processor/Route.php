<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Processor;

use Speicher210\OpenApiGenerator\Describer\Form\FormFactory;
use Speicher210\OpenApiGenerator\Describer\Output;
use Speicher210\OpenApiGenerator\Describer\Query;
use Speicher210\OpenApiGenerator\Describer\RequestBodyContent;
use Speicher210\OpenApiGenerator\Model\ErrorResponseOutput;
use Speicher210\OpenApiGenerator\Model\FormDefinition;
use cebe\openapi\spec\Operation;
use cebe\openapi\spec\Parameter;
use cebe\openapi\spec\PathItem;
use cebe\openapi\spec\RequestBody;
use cebe\openapi\spec\Response;
use cebe\openapi\spec\Responses;
use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Route as SymfonyRoute;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

final class Route
{
    private const PARAMETER_LOCATION_PATH = 'path';

    private Query $queryDescriber;

    private RequestBodyContent $requestBodyContentDescriber;

    private Output $outputDescriber;

    private OptionsResolver $optionResolver;

    private RouteCollection $routeCollection;

    private FormFactory $formFactory;

    public function __construct(
        RouterInterface $router,
        Query $queryDescriber,
        RequestBodyContent $requestBodyContentDescriber,
        Output $outputDescriber,
        FormFactory $formFactory
    ) {
        $this->routeCollection = $router->getRouteCollection();

        $this->queryDescriber = $queryDescriber;
        $this->requestBodyContentDescriber = $requestBodyContentDescriber;
        $this->outputDescriber = $outputDescriber;
        $this->formFactory = $formFactory;

        $this->optionResolver = new OptionsResolver();
        $this->configureOptions();
    }

    /**
     * @param mixed[] $pathsConfig
     *
     * @return PathItem[]
     */
    public function processRoutes(array $pathsConfig): array
    {
        $paths = [];
        foreach ($pathsConfig as $pathName => $pathConfig) {
            $route = $this->routeCollection->get($pathName);

            if ($route === null) {
                throw new \InvalidArgumentException(
                    \sprintf('Defined "%s" route in API doc configuration does not exist.', $pathName)
                );
            }

            $routePath = $this->prepareRoutePath($route->getPath());
            $paths[$routePath] = $paths[$routePath] ?? new PathItem([]);

            $this->processRoute($paths[$routePath], $route, $pathConfig);
        }

        return $paths;
    }

    /**
     * @param mixed[] $config
     */
    private function processRoute(PathItem $path, SymfonyRoute $route, array $config): void
    {
        $config = $this->optionResolver->resolve($config);
        foreach ($route->getMethods() as $method) {
            $operation = new Operation(
                \array_filter(
                    [
                        'summary' => $config['summary'],
                        'description' => $config['description'],
                        'tags' => $config['tag'],
                        'deprecated' => $config['deprecated'],
                        'security' => $config['security'],
                        'parameters' => [],
                    ],
                    static fn($value) => $value !== null
                )
            );

            $this->processPathParameters($operation, $route);
            $this->processInput($config, $operation, $route, $method);
            $this->processResponses($operation, $route, $config['responses']);

            $path->{\strtolower($method)} = $operation;
        }
    }

    private function processPathParameters(Operation $operation, SymfonyRoute $route): void
    {
        $parameters = [];
        foreach ($route->compile()->getPathVariables() as $pathVariable) {
            $requirement = $route->getRequirement($pathVariable);

            if ($pathVariable === '_format') {
                continue;
            }

            $parameter = new Parameter(['name' => $pathVariable, 'in' => self::PARAMETER_LOCATION_PATH]);
            $parameter->required = true;

            $parameterSchema = new Schema(
                [
                    'type' => Type::STRING,
                ]
            );
            if ($requirement !== null) {
                if (\strpos($requirement, '|') !== false) {
                    $parameterSchema->enum = (\explode('|', $requirement));
                } else {
                    $parameterSchema->pattern = $requirement;
                }
            }

            $parameter->schema = $parameterSchema;

            $parameters[] = $parameter;
        }

        $operation->parameters = $parameters;
    }

    /**
     * @param mixed[] $config
     */
    private function processInput(array $config, Operation $operation, SymfonyRoute $route, string $httpMethod): void
    {
        if ($config['input'] instanceof FormDefinition) {
            $formDefinition = $config['input'];
        } else {
            $formDefinition = $this->getFormAttachedToRoute($route);
        }

        if ($formDefinition === null) {
            return;
        }

        if ($httpMethod === 'GET') {
            $this->processQueryParameters($operation, $formDefinition);
        } else {
            $this->processRequestBody($operation, $formDefinition, $httpMethod);
        }
    }

    private function processQueryParameters(Operation $operation, FormDefinition $formDefinition): void
    {
        $form = $this->formFactory->create($formDefinition);

        if ($form->count() === 0) {
            return;
        }

        $operation->parameters = \array_merge($operation->parameters, $this->queryDescriber->describe($form));
    }

    private function processRequestBody(Operation $operation, FormDefinition $formDefinition, string $httpMethod): void
    {
        $form = $this->formFactory->create($formDefinition);

        if ($form->count() === 0) {
            return;
        }

        $operation->requestBody = new RequestBody(
            [
                'required' => true,
                'content' => $this->requestBodyContentDescriber->describe($form, $httpMethod),
            ]
        );
    }

    /**
     * @param mixed[] $responsesConfig
     */
    private function processResponses(Operation $operation, SymfonyRoute $route, array $responsesConfig): void
    {
        if ($operation->responses === null) {
            $operation->responses = new Responses([]);
        }
        $serializationGroups = null;
        if ($route->hasDefault('_sylius') && isset($route->getDefault('_sylius')['serialization_groups'])) {
            $serializationGroups = $route->getDefault('_sylius')['serialization_groups'];
        }

        foreach ($responsesConfig as $response) {
            \assert($response instanceof \Speicher210\OpenApiGenerator\Model\Response);
            $statusCode = $response->statusCode();
            if ($statusCode >= 500 && $statusCode < 600) {
                throw new \InvalidArgumentException('Response should not be configured for 5xx status codes.');
            }

            $output = $response->output();
            if ($output === null && $statusCode === 400) {
                $output = $this->getFormAttachedToRoute($route);
            }

            $responseData = [
                'description' => $response->descriptionText(),
            ];

            if ($output !== null) {
                $responseData['content'] = [
                    Output::RESPONSE_CONTENT_TYPE_APPLICATION_JSON => [
                        'schema' => $this->outputDescriber->describe($output, $serializationGroups),
                    ],
                ];
            }

            $operation->responses->addResponse($statusCode, new Response($responseData));
        }

        $response = new Response(
            [
                'description' => 'Returned on server error',
                'content' => [
                    Output::RESPONSE_CONTENT_TYPE_APPLICATION_JSON => [
                        'schema' => $this->outputDescriber->describe(ErrorResponseOutput::for500(), null),
                    ],
                ],
            ]
        );
        $operation->responses->addResponse(500, $response);
    }

    private function getFormAttachedToRoute(SymfonyRoute $route): ?FormDefinition
    {
        if ($route->hasDefault('_sylius') && isset($route->getDefault('_sylius')['form'])) {
            $form = $route->getDefault('_sylius')['form'];
            if (\is_string($form)) {
                return new FormDefinition($form);
            }
            if (\is_array($form)) {
                return new FormDefinition($form['type'], $form['options']['validation_groups'] ?? []);
            }
        }

        return null;
    }

    private function prepareRoutePath(string $path): string
    {
        return \str_replace('.{_format}', '', $path);
    }

    private function configureOptions(): void
    {
        $this->optionResolver->setRequired('tag');
        $this->optionResolver->setAllowedTypes('tag', ['string', 'array']);
        $this->optionResolver->setNormalizer(
            'tag',
            static function (Options $options, $value) {
                if (\is_array($value)) {
                    return $value;
                }

                return [$value];
            }
        );
        $this->optionResolver->setRequired('summary');
        $this->optionResolver->setAllowedTypes('summary', 'string');
        $this->optionResolver->setDefault('description', null);
        $this->optionResolver->setAllowedTypes('description', ['null', 'string']);

        $this->optionResolver->setDefault('deprecated', false);

        $this->optionResolver->setDefault('produces', null);
        $this->optionResolver->setAllowedTypes('produces', ['null', 'array']);

        $this->optionResolver->setDefault('security', []);
        $this->optionResolver->setNormalizer(
            'security',
            static function (Options $options, $value) {
                return \array_map(
                    static function ($value) {
                        return [$value => []];
                    },
                    (array) $value
                );
            }
        );

        $this->optionResolver->setDefault('input', null);
        $this->optionResolver->setAllowedTypes('input', ['null', FormDefinition::class]);

        $this->optionResolver->setRequired('responses');
        $this->optionResolver->setAllowedTypes('responses', 'array');
    }
}
