<?php
declare(strict_types=1);
namespace Phroute\Phroute;

/**
 * Interface RouteDataInterface
 * @package Phroute\Phroute
 */
interface RouteDataInterface
{
    /**
     * @return array<mixed>
     */
    function getStaticRoutes(): array;

    /**
     * @return array<mixed>
     */
    function getVariableRoutes(): array;

    /**
     * @return array<mixed>
     */
    function getFilters(): array;
}
