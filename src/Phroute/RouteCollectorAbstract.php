<?php declare(strict_types=1);

namespace Phroute\Phroute;

use Phroute\Phroute\Exception\BadRouteException;

abstract class RouteCollectorAbstract implements RouteDataProviderInterface
{
    /**
     * @var string
     */
    public const DEFAULT_CONTROLLER_ROUTE = 'index';

    /**
     * @var int
     */
    public const APPROX_CHUNK_SIZE = 10;

    /**
     * @var RouteParser
     */
    protected $routeParser;

    /**
     * @var array
     */
    protected $reverse = [];

    /**
     * @var array
     */
    protected $globalFilters = [];

    /**
     * @var string
     */
    protected $globalRoutePrefix = '';

    /**
     * @var array
     */
    protected $regexToRoutesMap = [];

    /**
     * @var array
     */
    protected $staticRoutes = [];

    /**
     * @var array
     */
    protected $filters = [];

    /**
     * @param RouteParser|null $routeParser
     */
    public function __construct(RouteParser $routeParser = null)
    {
        $this->routeParser = $routeParser ?: new RouteParser();
    }

    /**
     * @param string $httpMethod
     * @param $route
     * @param $handler
     * @param array $filters
     */
    public function addRoute(string $httpMethod, $route, $handler, array $filters = [])
    {
        if (\is_array($route)) {
            list ($route, $name) = $route;
        }

        $route = $this->addPrefix($this->trim($route));

        list ($routeData, $reverseData) = $this->routeParser->parse($route);

        if (isset($name)) {
            $this->reverse[$name] = $reverseData;
        }

        $filters = \array_merge_recursive($this->globalFilters, $filters);

        isset($routeData[1]) ?
        $this->addVariableRoute($httpMethod, $routeData, $handler, $filters) :
        $this->addStaticRoute($httpMethod, $routeData, $handler, $filters);
    }

    /**
     * @param $route
     * @param $handler
     * @param array $filters
     */
    public function get($route, $handler, array $filters = [])
    {
        $this->addRoute(Route::GET, $route, $handler, $filters);
    }

    /**
     * @param $route
     * @param $handler
     * @param array $filters
     */
    public function head($route, $handler, array $filters = [])
    {
        $this->addRoute(Route::HEAD, $route, $handler, $filters);
    }

    /**
     * @param $route
     * @param $handler
     * @param array $filters
     */
    public function post($route, $handler, array $filters = [])
    {
        $this->addRoute(Route::POST, $route, $handler, $filters);
    }

    /**
     * @param $route
     * @param $handler
     * @param array $filters
     */
    public function put($route, $handler, array $filters = [])
    {
        $this->addRoute(Route::PUT, $route, $handler, $filters);
    }

    /**
     * @param $route
     * @param $handler
     * @param array $filters
     */
    public function patch($route, $handler, array $filters = [])
    {
        $this->addRoute(Route::PATCH, $route, $handler, $filters);
    }

    /**
     * @param $route
     * @param $handler
     * @param array $filters
     */
    public function delete($route, $handler, array $filters = [])
    {
        $this->addRoute(Route::DELETE, $route, $handler, $filters);
    }

    /**
     * @param $route
     * @param $handler
     * @param array $filters
     */
    public function options($route, $handler, array $filters = [])
    {
        $this->addRoute(Route::OPTIONS, $route, $handler, $filters);
    }

    /**
     * @param $route
     * @param $handler
     * @param array $filters
     */
    public function any($route, $handler, array $filters = [])
    {
        $this->addRoute(Route::ANY, $route, $handler, $filters);
    }

    /**
     * @param string $route
     * @return string
     */
    protected function addPrefix(string $route): string
    {
        return $this->trim($this->trim($this->globalRoutePrefix) . '/' . $route);
    }

    /**
     * @param string $route
     * @return string
     */
    protected function trim(string $route): string
    {
        return \trim($route, '/');
    }

    /**
     * @param string $httpMethod
     * @param $routeData
     * @param $handler
     * @param $filters
     */
    protected function addStaticRoute(string $httpMethod, $routeData, $handler, $filters): void
    {
        $routeStr = $routeData[0];

        if (isset($this->staticRoutes[$routeStr][$httpMethod]))
        {
            throw new BadRouteException("Cannot register two routes matching '$routeStr' for method '$httpMethod'");
        }

        foreach ($this->regexToRoutesMap as $regex => $routes) {
            if (isset($routes[$httpMethod]) && \preg_match('~^' . $regex . '$~', $routeStr))
            {
                throw new BadRouteException("Static route '$routeStr' is shadowed by previously defined variable route '$regex' for method '$httpMethod'");
            }
        }

        $this->staticRoutes[$routeStr][$httpMethod] = [$handler, $filters, []];
    }

    /**
     * @param string $httpMethod
     * @param $routeData
     * @param $handler
     * @param $filters
     */
    protected function addVariableRoute(string $httpMethod, $routeData, $handler, $filters): void
    {
        list ($regex, $variables) = $routeData;

        if (isset($this->regexToRoutesMap[$regex][$httpMethod]))
        {
            throw new BadRouteException("Cannot register two routes matching '$regex' for method '$httpMethod'");
        }

        $this->regexToRoutesMap[$regex][$httpMethod] = [$handler, $filters, $variables];
    }

}
