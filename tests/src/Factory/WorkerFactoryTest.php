<?php

namespace SlmQueueTest\Factory;

use Laminas\Test\Util\ModuleLoader;
use PHPUnit\Framework\TestCase;
use SlmQueue\Factory\WorkerAbstractFactory;
use SlmQueueTest\Util\ServiceManagerFactory;

class WorkerFactoryTest extends TestCase
{
    public function testCreateService(): void
    {
        $moduleLoader = new ModuleLoader(include __DIR__ . '/../TestConfiguration.php.dist');
        $sm = $moduleLoader->getServiceManager();

        $factory = new WorkerAbstractFactory();
        $worker = $factory->__invoke($sm, 'SlmQueueTest\Asset\SimpleWorker');

        static::assertInstanceOf('SlmQueueTest\Asset\SimpleWorker', $worker);
    }
}
