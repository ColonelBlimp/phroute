<?php
declare(strict_types = 1);
namespace Phroute\Phroute\Definition;

/**
 * @author Marc L. Veary
 * @namespace Phroute\Phroute\Definition
 * @package Phroute\Phroute
 */
interface FilterDefinitionInterface
{
    /**
     * Retrieves the name of the filter.
     * @return string
     */
    function getName(): string;

    /**
     * Execute the filter's logic
     * @param mixed ...$vars
     * @return mixed|null If <code>null</code> is returned, Phroute continues to process the route otherwise
     *                    execution is stopped. Any thing other than <code>null</code> returned from a filter will
     *                    prevent the route handler from being dispatched.
     */
    function execute(...$vars);
}
