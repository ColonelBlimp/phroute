<?php declare(strict_types = 1);

namespace Phroute\Phroute\Definition;

use Phroute\Phroute\RouteCollector;

final class GroupDefinition implements GroupDefinitionInterface
{
    /**
     * @var string
     */
    private $prefix;

    /**
     * @var array
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
    public function __construct(string $prefix)
    {
        $this->prefix = $prefix;
    }

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
     * @see \Phroute\Phroute\Definition\GroupDefinitionInterface::groupCallback()
     */
    public function groupCallback(RouteCollector $collector): void
    {
        if (\is_array($this->routes)) {
            foreach ($this->routes as $httpMethod => $def) {
                foreach ($def as $route => $handler) {
                    $collector->addRoute($httpMethod, $route, $handler);
                }
            }
        }
    }

    /**
     * {@inheritDoc}
     * @see \Phroute\Phroute\Definition\GroupDefinitionInterface::getPrefix()
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }
}
