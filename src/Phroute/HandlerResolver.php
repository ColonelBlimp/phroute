<?php
declare(strict_types=1);
namespace Phroute\Phroute;

use Phroute\Phroute\Parameter\ParameterSetterInterface;

final class HandlerResolver implements HandlerResolverInterface
{
    function resolve($handler, ...$params): callable
    {
        if (\is_array($handler) && \is_string($handler[0])) {
            $handler[0] = new $handler[0];
            if ($handler[0] instanceof ParameterSetterInterface) {
                // strip the outer array to get at the parameters
                $handler[0]->setParameters($params[0][0]);
            }
        }
        return $handler;
    }
}
