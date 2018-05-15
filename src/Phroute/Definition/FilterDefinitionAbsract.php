<?php declare(strict_types=1);

namespace Phroute\Phroute\Definition;

/**
 * A filter definition should extend this class.
 * @author Marc L. Veary
 * @namespace Phroute\Phroute\Definition
 * @package Phroute\Phroute
 */
abstract class FilterDefinitionAbsract implements FilterDefinitionInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * Constructor signature.
     * @param string $name The name of the filter.
     */
    final public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritDoc}
     * @see \Phroute\Phroute\Definition\FilterDefinitionInterface::getName()
     */
    final public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     * @see \Phroute\Phroute\Definition\FilterDefinitionInterface::execute()
     */
    abstract public function execute(...$vars);
}
