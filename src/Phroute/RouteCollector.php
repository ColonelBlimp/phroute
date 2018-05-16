<?php declare(strict_types=1);

namespace Phroute\Phroute;

use Phroute\Phroute\Definition\FilterDefinitionInterface;
use Phroute\Phroute\Definition\GroupDefinitionInterface;
use Phroute\Phroute\Definition\RouteDefinitionInterface;
use Phroute\Phroute\Exception\BadDefinitionException;
use Phroute\Phroute\Exception\BadRouteException;
use ReflectionClass;
use ReflectionMethod;

/**
 * Class RouteCollector
 * @package Phroute\Phroute
 */
class RouteCollector extends RouteCollectorAbstract
{
    /**
     * Add definitions to the RouteCollector using an array, GroupDefinitionInterface, RouteDefinitionInterface.
     * @param array|object $definition
     * @throws BadDefinitionException
     */
    public function addDefinitions($definition): void
    {
        if ($this->addGroupFilterDefinition($definition)) {
            return;
        }

        if ($definition instanceof RouteDefinitionInterface) {
            $definition = $definition->getRoutes();
        }

        if (!\is_array($definition)) {
            throw new BadDefinitionException();
        }

        foreach ($definition as $httpMethod => $def) {
            foreach ($def as $route => $handler) {
                $this->addRoute($httpMethod, $route, $handler);
            }
        }
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasRoute($name): bool
    {
        return isset($this->reverse[$name]);
    }

    /**
     * @param string $name
     * @param array|null $args
     * @return string
     */
    public function route(string $name, array $args = null): string
    {
        $url = [];

        $replacements = \is_null($args) ? [] : \array_values($args);

        $variable = 0;

        foreach ($this->reverse[$name] as $part) {
            if (!$part['variable'])
            {
                $url[] = $part['value'];
                continue;
            }

            if (isset($replacements[$variable])) {
                if ($part['optional']) {
                    $url[] = '/';
                }

                $url[] = $replacements[$variable++];
                continue;
            }

            if (!$part['optional']) {
                throw new BadRouteException("Expecting route variable '{$part['name']}'");
            }
        }

        return \implode('', $url);
    }

    /**
     * @param array $filters
     * @param \Closure $callback
     */
    public function group(array $filters, \Closure $callback): void
    {
        $oldGlobalFilters = $this->globalFilters;

        $oldGlobalPrefix = $this->globalRoutePrefix;

        $this->globalFilters = \array_merge_recursive($this->globalFilters, \array_intersect_key($filters, [Route::AFTER => 1, Route::BEFORE => 1]));

        // Below cannot assign null to newPrefix otherwise addPrefix errors!
        $newPrefix = isset($filters[Route::PREFIX]) ? $this->trim($filters[Route::PREFIX]) : '';

        $this->globalRoutePrefix = $this->addPrefix($newPrefix);

        $callback($this);

        $this->globalFilters = $oldGlobalFilters;

        $this->globalRoutePrefix = $oldGlobalPrefix;
    }

    /**
     * @param $name
     * @param $handler
     */
    public function filter($name, $handler): void
    {
        $this->filters[$name] = $handler;
    }

    /**
     * @param $route
     * @param $classname
     * @param array $filters
     */
    public function controller($route, $classname, array $filters = [])
    {
        $reflection = new ReflectionClass($classname);

        $validMethods = $this->getValidMethods();

        $sep = $route === '/' ? '' : '/';

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            foreach ($validMethods as $valid) {
                if (\stripos($method->name, $valid) === 0) {
                    $methodName = $this->camelCaseToDashed(\substr($method->name, \strlen($valid)));

                    $params = $this->buildControllerParameters($method);

                    if ($methodName === self::DEFAULT_CONTROLLER_ROUTE) {
                        $this->addRoute($valid, $route . $params, [$classname, $method->name], $filters);
                    }

                    $this->addRoute($valid, $route . $sep . $methodName . $params, [$classname, $method->name], $filters);

                    break;
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getValidMethods(): array
    {
        return [
            Route::ANY,
            Route::GET,
            Route::POST,
            Route::PUT,
            Route::PATCH,
            Route::DELETE,
            Route::HEAD,
            Route::OPTIONS,
        ];
    }

    /**
     * @return RouteDataArray
     */
    public function getData(): RouteDataArray
    {
        if (empty($this->regexToRoutesMap)) {
            return new RouteDataArray($this->staticRoutes, [], $this->filters);
        }

        return new RouteDataArray($this->staticRoutes, $this->generateVariableRouteData(), $this->filters);
    }

    /**
     * Adds a Group or Filter definition.
     * @param mixed $definition
     * @return bool Returns <code>true</code> if a Group of Filter definition was processed,
     *              otherwise <code>false</code>.
     */
    private function addGroupFilterDefinition($definition): bool
    {
        $retval = false;

        if ($definition instanceof FilterDefinitionInterface) {
            $handler = \Closure::fromCallable([$definition, 'execute']);
            $this->filter($definition->getName(), $handler);

            $retval = true;
        } elseif ($definition instanceof GroupDefinitionInterface) {
            $handler = \Closure::fromCallable([$definition, 'execute']);

            $groupDef = [];
            $groupDef['prefix'] = $definition->getPrefix();

            if (!empty($definition->getFilters())) {
                $groupDef = \array_merge($groupDef, $definition->getFilters());
            }

            $this->group($groupDef, $handler);

            $retval = true;
        }

        return $retval;
    }

    /**
     * @param ReflectionMethod $method
     * @return string
     */
    private function buildControllerParameters(ReflectionMethod $method): string
    {
        $params = '';

        foreach ($method->getParameters() as $param) {
            $params .= "/{" . $param->getName() . "}" . ($param->isOptional() ? '?' : '');
        }

        return $params;
    }

    /**
     * @param $string
     * @return string
     */
    private function camelCaseToDashed($string)
    {
        return \strtolower(\preg_replace('/([A-Z])/', '-$1', \lcfirst($string)));
    }

    /**
     * @return array
     */
    private function generateVariableRouteData()
    {
        $chunkSize = $this->computeChunkSize(\count($this->regexToRoutesMap));
        $chunks = \array_chunk($this->regexToRoutesMap, $chunkSize, true);
        return \array_map([$this, 'processChunk'], $chunks);
    }

    /**
     * @param $count
     * @return int
     */
    private function computeChunkSize($count): int
    {
        $numParts = \max(1, \round($count / self::APPROX_CHUNK_SIZE));
        return (int) \ceil($count / $numParts);
    }

    /**
     * @param $regexToRoutesMap
     * @return array
     */
    private function processChunk($regexToRoutesMap): array
    {
        $routeMap = [];
        $regexes = [];
        $numGroups = 0;

        foreach ($regexToRoutesMap as $regex => $routes) {
            $firstRoute = \reset($routes);
            $numVariables = \count($firstRoute[2]);
            $numGroups = \max($numGroups, $numVariables);

            $regexes[] = $regex . \str_repeat('()', $numGroups - $numVariables);

            foreach ($routes as $httpMethod => $route) {
                $routeMap[$numGroups + 1][$httpMethod] = $route;
            }

            $numGroups++;
        }

        $regex = '~^(?|' . \implode('|', $regexes) . ')$~';

        return ['regex' => $regex, 'routeMap' => $routeMap];
    }
}
