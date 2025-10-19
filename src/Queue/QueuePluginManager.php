<?php

namespace SlmQueue\Queue;

use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;

/**
 * @method \SlmQueue\Queue\QueueInterface get($id, ?array $options = null)
 */
class QueuePluginManager extends AbstractPluginManager
{
    #[\Override]
    public function validate($instance): void
    {
        if ($instance instanceof QueueInterface) {
            return; // we're okay!
        }

        throw new InvalidServiceException(sprintf(
            'Plugin of type %s is invalid; must implement SlmQueue\Queue\QueueInterface',
            (is_object($instance) ? get_class($instance) : gettype($instance))
        ));
    }
}
