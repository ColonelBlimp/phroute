<?php
declare(strict_types=1);
namespace Phroute\Phroute\Definition;

use Phroute\Phroute\Route;

/**
 * @author Marc L. Veary
 * @namespace Phroute\Phroute\Definition
 * @package Phroute\Phroute
 */
final class RouteDefinition extends DefinitionSourceAbstract implements RouteDefinitionInterface
{
    /**
     * @var array<mixed>
     */
    private $routes = [
        Route::GET => [],
        Route::POST => []
    ];

    function addRoute(string $httpMethod, string $route, $handler): void {
        $this->routes[$httpMethod][$route] = $handler;
    }

    function getRoutes(): array {
        return $this->routes;
    }
}
