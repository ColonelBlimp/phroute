<?php declare(strict_types = 1);

namespace Phroute\Phroute;

/**
 * Interface RouteDataInterface
 * @package Phroute\Phroute
 */
interface RouteDataInterface
{
    /**
     * @return array
     */
    public function getStaticRoutes(): array;

    /**
     * @return array
     */
    public function getVariableRoutes(): array;

    /**
     * @return array
     */
    public function getFilters(): array;
}
