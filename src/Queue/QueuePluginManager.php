<?php

namespace SlmQueue\Queue;

use Laminas\ServiceManager\AbstractPluginManager;

/**
 * @method \SlmQueue\Queue\QueueInterface get($name, ?array $options = null)
 */
class QueuePluginManager extends AbstractPluginManager
{
    protected $instanceOf = QueueInterface::class;
}
