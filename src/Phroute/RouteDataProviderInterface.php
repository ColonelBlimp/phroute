<?php
declare(strict_types=1);
namespace Phroute\Phroute;

/**
 * Interface RouteDataProviderInterface
 * @package Phroute\Phroute
 */
interface RouteDataProviderInterface {

    /**
     * @return mixed
     */
    function getData();
}
