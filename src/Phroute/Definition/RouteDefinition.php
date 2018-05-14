<?php declare(strict_types = 1);

namespace Phroute\Phroute\Definition;

use Phroute\Phroute\Route;

final class RouteDefinition implements RouteDefinitionInterface
{
    /**
     * @var array
     */
    private $routes = [
        Route::GET => [],
        Route::POST => []
    ];

    /**
     * {@inheritDoc}
     * @see \Phroute\Phroute\Definition\DefinitionSourceInterface::addRoute()
     */
    public function addRoute(string $httpMethod, string $route, $handler): void
    {
        $this->routes[$httpMethod][$route] = $handler;
    }

    /**
     * {@inheritDoc}
     * @see \Phroute\Phroute\Definition\RouteDefinitionInterface::getRoutes()
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}
