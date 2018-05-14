<?php declare(strict_types = 1);

namespace Phroute\Phroute\Definition;

use Phroute\Phroute\RouteCollector;

interface GroupDefinitionInterface extends DefinitionSourceInterface
{
    /**
     * Constructor
     * @param string $prefix The prefix for this route group.
     *
     * <code><br><br>
     * $router->group(['prefix' => $prefix], ...)
     * </code>
     */
    public function __construct(string $prefix);

    /**
     * Returns the prefix for this group definition.
     * @return string
     */
    public function getPrefix(): string;

    /**
     * The callback method called by the RouteCollector. The implemetation will provide the route definitions for
     * this group.
     * @param RouteCollector $collector
     */
    public function groupCallback(RouteCollector $collector): void;
}
