<?php declare(strict_types=1);

namespace Phroute\Phroute\Definition;

interface FilterDefinitionInterface
{
    /**
     * Constructor signature.
     * @param string $name The name of the filter.
     */
    public function __construct(string $name);

    /**
     * Retrieves the name of the filter.
     * @return string
     */
    public function getName(): string;

    /**
     * @param mixed ...$vars
     */
    public function filterCallback(...$vars);
}
