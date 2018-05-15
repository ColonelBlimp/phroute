<?php declare(strict_types = 1);

namespace Phroute\Phroute\Definition;

/**
 * @author Marc L. Veary
 * @namespace Phroute\Phroute\Definition
 * @package Phroute\Phroute
 */
abstract class DefinitionSourceAbstract implements DefinitionSourceInterface
{
    /**
     * @var array
     */
    private $filters = [];

    /**
     * {@inheritDoc}
     * @see \Phroute\Phroute\Definition\DefinitionSourceInterface::addBeforeFilter()
     */
    public function addBeforeFilter(FilterDefinitionInterface $filter): void
    {
        $this->filters['before'] = $filter->getName();
    }

    /**
     * {@inheritDoc}
     * @see \Phroute\Phroute\Definition\DefinitionSourceInterface::addAfterFilter()
     */
    public function addAfterFilter(FilterDefinitionInterface $filter): void
    {
        $this->filters['after'] = $filter->getName();
    }

    /**
     * {@inheritDoc}
     * @see \Phroute\Phroute\Definition\DefinitionSourceInterface::getFilters()
     */
    public function getFilters(): array
    {
        return $this->filters;
    }
}
