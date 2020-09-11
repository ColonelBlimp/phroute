<?php
declare(strict_types=1);
namespace Phroute\Phroute\Definition;

interface DefinitionSourceInterface
{
    /**
     * Add a route. This DOES NOT check for duplicate routes. If route already exists for the given httpMethod, then
     * it will be overwitten.
     * @param string $httpMethod
     * @param string $route
     * @param mixed $handler
     */
    function addRoute(string $httpMethod, string $route, $handler): void;

    /**
     * @param FilterDefinitionInterface $filter
     */
    function addBeforeFilter(FilterDefinitionInterface $filter): void;

    /**
     * @param FilterDefinitionInterface $filter
     */
    function addAfterFilter(FilterDefinitionInterface $filter): void;

    /**
     * Retrieves an array of both 'before' and 'after' filters.
     * @return array<mixed> May return an empty array.
     */
    function getFilters(): array;
}
