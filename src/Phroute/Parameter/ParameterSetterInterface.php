<?php declare(strict_types=1);

namespace Phroute\Phroute\Parameter;

/**
 * Implementing this interface on a Controller passed to Phroute will allow the extra parameters passed to the
 * <code>Dispatcher::dispatch(...)</code> method to be passed to the Controller.
 *
 * @author Marc L. Veary
 * @namespace Phroute\Phroute\Parameter
 * @package Phroute\Phroute
 */
interface ParameterSetterInterface
{
    /**
     * @param mixed ...$params
     */
    function setParameters(...$params): void;
}
