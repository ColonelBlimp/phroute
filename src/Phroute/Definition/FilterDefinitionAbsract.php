<?php
declare(strict_types=1);
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
    final function __construct(string $name) {
        $this->name = $name;
    }

    final function getName(): string {
        return $this->name;
    }

    abstract function execute(...$vars);
}
