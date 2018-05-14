<?php declare(strict_types=1);

namespace Phroute\Phroute\Definition;

class FilterDefinition implements FilterDefinitionInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * Constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritDoc}
     * @see \Phroute\Phroute\Definition\FilterDefinitionInterface::getName()
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     * @see \Phroute\Phroute\Definition\FilterDefinitionInterface::filterCallback()
     */
    public function filterCallback(...$vars)
    {
        //FIXME: Need to pass parameters into this method
        return 'Please Login';
    }
}
