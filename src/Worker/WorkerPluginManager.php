<?php

namespace SlmQueue\Worker;

use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use SlmQueue\Exception\RuntimeException;
use SlmQueue\Factory\WorkerAbstractFactory;
use SlmQueue\Worker\WorkerInterface;

/**
 * @method WorkerInterface get(string $id, ?array $options = null)
 */
class WorkerPluginManager extends AbstractPluginManager
{
    public function __construct($configInstanceOrParentLocator = null, array $config = [])
    {
        $config['abstract_factories'][] = WorkerAbstractFactory::class;
        parent::__construct($configInstanceOrParentLocator, $config);
    }

    #[\Override]
    public function validate(mixed $instance): void
    {
        if ($instance instanceof WorkerInterface) {
            return; // we're okay!
        }

        throw new InvalidServiceException(sprintf(
            'Plugin of type %s is invalid; must implement SlmQueue\Queue\WorkerInterface',
            (is_object($instance) ? get_class($instance) : gettype($instance))
        ));
    }
}
