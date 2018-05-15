<?php declare(strict_types=1);

namespace Phroute\Phroute\Definition;

interface RouteDefinitionInterface extends DefinitionSourceInterface
{
    /**
     * Retrieve a multi-dimensional associative array of all the routes. The top-level array's key is the httpMethod.
     * @return array
     */
    public function getRoutes(): array;
}
