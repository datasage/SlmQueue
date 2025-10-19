<?php

namespace SlmQueueTest\Factory;

use Laminas\Test\Util\ModuleLoader;
use PHPUnit\Framework\TestCase;
use SlmQueue\Controller\Plugin\QueuePlugin;
use SlmQueue\Factory\QueueControllerPluginFactory;

class QueueControllerPluginFactoryTest extends TestCase
{
    public function testCreateService(): void
    {
        $moduleLoader = new ModuleLoader(include __DIR__ . '/../TestConfiguration.php.dist');
        $serviceManager = $moduleLoader->getServiceManager();

        $factory = new QueueControllerPluginFactory();

        $queueControllerPluginFactory = $factory($serviceManager, QueuePlugin::class);
        static::assertInstanceOf(QueuePlugin::class, $queueControllerPluginFactory);
    }
}
