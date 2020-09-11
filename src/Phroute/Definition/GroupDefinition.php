<?php
declare(strict_types=1);
namespace Phroute\Phroute\Definition;

use Phroute\Phroute\RouteCollector;

/**
 * @author Marc L. Veary
 * @namespace Phroute\Phroute\RouteCollector
 * @package Phroute\Phroute
 */
final class GroupDefinition extends DefinitionSourceAbstract implements GroupDefinitionInterface
{
    /**
     * @var string
     */
    private $prefix;

    /**
     * @var array<mixed>
     */
    private $routes = [];

    /**
     * Constructor
     * @param string $prefix The prefix for this route group.
     *
     * <code><br><br>
     * $router->group(['prefix' => $prefix], ...)
     * </code>
     */
    function __construct(string $prefix) {
        $this->prefix = $prefix;
    }

    function addRoute(string $httpMethod, string $route, $handler): void {
        $this->routes[$httpMethod][$route] = $handler;
    }

    function execute(RouteCollector $collector): void {
        foreach ($this->routes as $httpMethod => $def) {
            foreach ($def as $route => $handler) {
                $collector->addRoute($httpMethod, $route, $handler);
            }
        }
    }

    function getPrefix(): string {
        return $this->prefix;
    }
}
