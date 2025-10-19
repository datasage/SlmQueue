<?php

namespace SlmQueue\Job;

use Laminas\ServiceManager\AbstractPluginManager;

class JobPluginManager extends AbstractPluginManager
{
    public function __construct($configInstanceOrParentLocator = null, array $config = [])
    {
        $config['shared_by_default'] = false;
        parent::__construct($configInstanceOrParentLocator, $config);
    }


    /**
     * @inheritdoc
     *
     * @param string $id
     * @param array  $options
     * @param bool   $usePeeringServiceManagers
     * @return JobInterface
     */
    #[\Override]
    public function get($id, $options = [], $usePeeringServiceManagers = true): JobInterface
    {
        // parent::get calls validate() so we're sure $instance is a JobInterface
        $instance = parent::get($id, $options, $usePeeringServiceManagers);
        $instance->setMetadata('__name__', $id);

        return $instance;
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function validate($instance): void
    {
        if ($instance instanceof JobInterface) {
            return; // we're okay
        }

        throw new Exception\RuntimeException(sprintf(
            'Plugin of type %s is invalid; must implement SlmQueue\Job\JobInterface',
            (is_object($instance) ? get_class($instance) : gettype($instance)),
        ));
    }
}
