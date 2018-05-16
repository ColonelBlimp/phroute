<?php declare(strict_types=1);

namespace Phroute\Phroute;

use Phroute\Phroute\Exception\HttpMethodNotAllowedException;
use Phroute\Phroute\Exception\HttpRouteNotFoundException;
use Phroute\Phroute\Parameter\ParameterSetterInterface;

class Dispatcher
{
    /**
     * @var array
     */
    private $staticRouteMap = [];

    /**
     * @var array
     */
    private $variableRouteData;

    /**
     * @var array
     */
    private $filters;

    /**
     * @var HandlerResolver
     */
    private $handlerResolver;

    /**
     * Create a new route dispatcher.
     *
     * @param RouteDataInterface $data
     * @param HandlerResolverInterface|null $resolver
     */
    public function __construct(RouteDataInterface $data, HandlerResolverInterface $resolver = null)
    {
        $this->staticRouteMap = $data->getStaticRoutes();

        $this->variableRouteData = $data->getVariableRoutes();

        $this->filters = $data->getFilters();

        $this->handlerResolver = $resolver ?: new HandlerResolver();
    }

    /**
     * Dispatch a route for the given HTTP Method / URI.
     *
     * @param string $httpMethod
     * @param string $uri
     * @param mixed ...$params Any parameters provided here will be passed to a Controller which implements the
     *                         <code>ParameterSetterInterface</code> interface. This can be useful to pass in the
     *                         an object which represents the requests or other request related items.
     * @return mixed|null
     */
    public function dispatch(string $httpMethod, string $uri, ...$params)
    {
        list ($handler, $filters, $vars) = $this->dispatchRoute($httpMethod, \trim($uri, '/'));

        list ($beforeFilter, $afterFilter) = $this->parseFilters($filters);

        if (($response = $this->dispatchFilters($beforeFilter)) !== null) {
            return $response;
        }

        $resolvedHandler = $this->handlerResolver->resolve($handler);

        if (is_array($resolvedHandler) && $resolvedHandler[0] instanceof ParameterSetterInterface) {
            // strip the outer array to get at the parameters
            $inner = $params[0];
            $resolvedHandler[0]->setParameters($inner);
        }

        $response = \call_user_func_array($resolvedHandler, $vars);

        return $this->dispatchFilters($afterFilter, $response);
    }

    /**
     * Dispatch a route filter.
     *
     * @param $filters
     * @param mixed|null $response
     * @return mixed|null
     */
    private function dispatchFilters($filters, $response = null)
    {
        while ($filter = \array_shift($filters)) {
        	$handler = $this->handlerResolver->resolve($filter);

            if (($filteredResponse = \call_user_func($handler, $response)) !== null) {
                return $filteredResponse;
            }
        }

        return $response;
    }

    /**
     * Normalise the array filters attached to the route and merge with any global filters.
     *
     * @param $filters
     * @return array
     */
    private function parseFilters($filters)
    {
        $beforeFilter = [];
        $afterFilter = [];

        if (isset($filters[Route::BEFORE])) {
            $beforeFilter = \array_intersect_key($this->filters, \array_flip((array) $filters[Route::BEFORE]));
        }

        if (isset($filters[Route::AFTER])) {
            $afterFilter = \array_intersect_key($this->filters, \array_flip((array) $filters[Route::AFTER]));
        }

        return [$beforeFilter, $afterFilter];
    }

    /**
     * Perform the route dispatching. Check static routes first followed by variable routes.
     *
     * @param string $httpMethod
     * @param string $uri
     * @throws Exception\HttpRouteNotFoundException
     */
    private function dispatchRoute(string $httpMethod, string $uri)
    {
        if (isset($this->staticRouteMap[$uri])) {
            return $this->dispatchStaticRoute($httpMethod, $uri);
        }

        return $this->dispatchVariableRoute($httpMethod, $uri);
    }

    /**
     * Handle the dispatching of static routes.
     *
     * @param $httpMethod
     * @param $uri
     * @return mixed
     * @throws Exception\HttpMethodNotAllowedException
     */
    private function dispatchStaticRoute($httpMethod, $uri)
    {
        $routes = $this->staticRouteMap[$uri];

        if (!isset($routes[$httpMethod])) {
            $httpMethod = $this->checkFallbacks($routes, $httpMethod);
        }

        return $routes[$httpMethod];
    }

    /**
     * Check fallback routes: HEAD for GET requests followed by the ANY attachment.
     *
     * @param array $routes
     * @param string $httpMethod
     * @return string
     * @throws Exception\HttpMethodNotAllowedException
     */
    private function checkFallbacks(array $routes, string $httpMethod): string
    {
        $additional = [Route::ANY];

        if ($httpMethod === Route::HEAD) {
            $additional[] = Route::GET;
        }

        foreach ($additional as $method) {
            if (isset($routes[$method])) {
                return $method;
            }
        }

        throw new HttpMethodNotAllowedException('Allow: ' . \implode(', ', \array_keys($routes)));
    }

    /**
     * Handle the dispatching of variable routes.
     *
     * @param $httpMethod
     * @param $uri
     * @throws Exception\HttpMethodNotAllowedException
     * @throws Exception\HttpRouteNotFoundException
     */
    private function dispatchVariableRoute($httpMethod, $uri)
    {
        foreach ($this->variableRouteData as $data) {
            if (!\preg_match($data['regex'], $uri, $matches)) {
                continue;
            }

            $count = \count($matches);

            while (!isset($data['routeMap'][$count++]));

            $routes = $data['routeMap'][$count - 1];

            if (!isset($routes[$httpMethod])) {
                $httpMethod = $this->checkFallbacks($routes, $httpMethod);
            }

            foreach (\array_values($routes[$httpMethod][2]) as $i => $varName) {
                if (!isset($matches[$i + 1]) || $matches[$i + 1] === '') {
                    unset($routes[$httpMethod][2][$varName]);
                    continue;
                }

                $routes[$httpMethod][2][$varName] = $matches[$i + 1];
            }

            return $routes[$httpMethod];
        }

        throw new HttpRouteNotFoundException('Route ' . $uri . ' does not exist');
    }
}
