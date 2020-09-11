<?php
declare(strict_types=1);
namespace Phroute\Phroute;

final class RouteDataArray implements RouteDataInterface {

    /**
     * @var array<mixed>
     */
    private $variableRoutes;

    /**
     * @var array<mixed>
     */
    private $staticRoutes;

    /**
     * @var array<mixed>
     */
    private $filters;

    /**
     * @param array<mixed> $staticRoutes
     * @param array<mixed> $variableRoutes
     * @param array<mixed> $filters
     */
    function __construct(array $staticRoutes, array $variableRoutes, array $filters) {
        $this->staticRoutes = $staticRoutes;
        $this->variableRoutes = $variableRoutes;
        $this->filters = $filters;
    }

    /**
     * @return array<mixed>
     */
    function getStaticRoutes(): array {
        return $this->staticRoutes;
    }

    /**
     * @return array<mixed>
     */
    function getVariableRoutes(): array {
        return $this->variableRoutes;
    }

    /**
     * @return array<mixed>
     */
    function getFilters(): array {
        return $this->filters;
    }
}
