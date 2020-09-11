<?php
declare(strict_types=1);
namespace Phroute\Phroute\Definition;

/**
 * @author Marc L. Veary
 * @namespace Phroute\Phroute\Definition
 * @package Phroute\Phroute
 */
abstract class DefinitionSourceAbstract implements DefinitionSourceInterface
{
    /**
     * @var array<mixed>
     */
    private $filters = [];


    function addBeforeFilter(FilterDefinitionInterface $filter): void {
        $this->filters['before'] = $filter->getName();
    }

    function addAfterFilter(FilterDefinitionInterface $filter): void {
        $this->filters['after'] = $filter->getName();
    }

    function getFilters(): array {
        return $this->filters;
    }
}
