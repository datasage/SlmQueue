<?php

namespace SlmQueue\Strategy;

use Laminas\ServiceManager\AbstractPluginManager;
use SlmQueue\Exception\RuntimeException;

class StrategyPluginManager extends AbstractPluginManager
{
    /**
     * @var bool
     */
    protected $shareByDefault = false;

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function validate($instance): void
    {
        if ($instance instanceof AbstractStrategy) {
            return; // we're okay
        }

        throw new RuntimeException(sprintf(
            'Plugin of type %s is invalid; must extend SlmQueue\Strategy\AbstractStrategy',
            (is_object($instance) ? get_class($instance) : gettype($instance))
        ));
    }
}
