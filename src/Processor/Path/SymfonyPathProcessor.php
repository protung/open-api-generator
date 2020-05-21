<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Processor\Path;

use Assert\Assertion;
use cebe\openapi\spec\Operation;
use cebe\openapi\spec\Parameter;
use cebe\openapi\spec\RequestBody;
use cebe\openapi\spec\Response;
use cebe\openapi\spec\Responses;
use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type;
use Speicher210\OpenApiGenerator\Describer\Form\FormFactory;
use Speicher210\OpenApiGenerator\Describer\Output;
use Speicher210\OpenApiGenerator\Describer\Query;
use Speicher210\OpenApiGenerator\Describer\RequestBodyContent;
use Speicher210\OpenApiGenerator\Model\FormDefinition;
use Speicher210\OpenApiGenerator\Model\Path\Output\ErrorResponse;
use Symfony\Component\Routing\Route as SymfonyRoute;
use Symfony\Component\Routing\RouteCollection;
use function array_map;

final class SymfonyPathProcessor implements PathProcessor
{
    private const PARAMETER_LOCATION_PATH = 'path';

    private Query $queryDescriber;

    private RequestBodyContent $requestBodyContentDescriber;

    private Output $outputDescriber;

    private RouteCollection $routeCollection;

    private FormFactory $formFactory;

    public function __construct(
        RouteCollection $routeCollection,
        Query $queryDescriber,
        RequestBodyContent $requestBodyContentDescriber,
        Output $outputDescriber,
        FormFactory $formFactory
    ) {
        $this->routeCollection             = $routeCollection;
        $this->queryDescriber              = $queryDescriber;
        $this->requestBodyContentDescriber = $requestBodyContentDescriber;
        $this->outputDescriber             = $outputDescriber;
        $this->formFactory                 = $formFactory;
    }

    /**
     * @return PathOperation[]
     */
    public function process(Path $path) : array
    {
        /** @var SymfonyRoutePath $path */
        Assertion::isInstanceOf($path, SymfonyRoutePath::class);

        $route = $this->routeCollection->get($path->routeName());

        if ($route === null) {
            throw new \InvalidArgumentException(
                \sprintf('Defined "%s" route in API doc configuration does not exist.', $path->routeName())
            );
        }

        return $this->processRoute($route, $path);
    }

    /**
     * @return PathOperation[]
     */
    private function processRoute(SymfonyRoute $route, SymfonyRoutePath $path) : array
    {
        $operations = [];
        foreach ($route->getMethods() as $method) {
            $operation = new Operation(
                [
                    'summary' => $path->summary(),
                    'description' => $path->description(),
                    'tags' => $path->tag(),
                    'deprecated' => $path->isDeprecated(),
                    'security' => array_map(static fn ($value) => [$value => []], $path->security()),
                    'parameters' => [],
                ]
            );

            $this->processPathParameters($operation, $route);
            $this->processInput($path, $operation, $method);
            $this->processResponses($operation, $route, $path->responses());

            $operations[] = new PathOperation(
                $method,
                $this->prepareRoutePath($route->getPath()),
                $operation
            );
        }

        return $operations;
    }

    private function processPathParameters(Operation $operation, SymfonyRoute $route) : void
    {
        $parameters = [];
        foreach ($route->compile()->getPathVariables() as $pathVariable) {
            $requirement = $route->getRequirement($pathVariable);

            if ($pathVariable === '_format') {
                continue;
            }

            $parameter           = new Parameter(['name' => $pathVariable, 'in' => self::PARAMETER_LOCATION_PATH]);
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

    private function processInput(SymfonyRoutePath $path, Operation $operation, string $httpMethod) : void
    {
        foreach ($path->input() as $input) {
            if ($httpMethod === 'GET') {
                $this->processQueryParameters($operation, $input);
            } else {
                $this->processRequestBody($operation, $input, $httpMethod);
            }
        }
    }

    private function processQueryParameters(Operation $operation, FormDefinition $formDefinition) : void
    {
        $form = $this->formFactory->create($formDefinition);

        if ($form->count() === 0) {
            return;
        }

        $operation->parameters = \array_merge($operation->parameters, $this->queryDescriber->describe($form));
    }

    private function processRequestBody(Operation $operation, FormDefinition $formDefinition, string $httpMethod) : void
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
    private function processResponses(Operation $operation, SymfonyRoute $route, array $responsesConfig) : void
    {
        if ($operation->responses === null) {
            $operation->responses = new Responses([]);
        }

        foreach ($responsesConfig as $response) {
            \assert($response instanceof \Speicher210\OpenApiGenerator\Model\Response);
            $statusCode = $response->statusCode();
            if ($statusCode >= 500 && $statusCode < 600) {
                throw new \InvalidArgumentException('Response should not be configured for 5xx status codes.');
            }

            // TODO handle form error output
            $output = $response->output();
//            if ($output === null && $statusCode === 400) {
//                $output = $this->getFormAttachedToRoute($route);
//            }

            $responseData = [
                'description' => $response->descriptionText(),
            ];

            if ($output !== null) {
                $responseData['content'] = [
                    Output::RESPONSE_CONTENT_TYPE_APPLICATION_JSON => [
                        'schema' => $this->outputDescriber->describe($output, null),
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
                        'schema' => $this->outputDescriber->describe(ErrorResponse::for500(), null),
                    ],
                ],
            ]
        );
        $operation->responses->addResponse(500, $response);
    }

    private function prepareRoutePath(string $path) : string
    {
        return \str_replace('.{_format}', '', $path);
    }
}
