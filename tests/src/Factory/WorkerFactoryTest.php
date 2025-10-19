<?php

namespace SlmQueueTest\Factory;

use SlmQueueTest\Util\ServiceManagerFactory;
use PHPUnit\Framework\TestCase;
use SlmQueue\Factory\WorkerAbstractFactory;

class WorkerFactoryTest extends TestCase
{
    public function testCreateService(): void
    {
        ServiceManagerFactory::setConfig(include __DIR__ . '/../TestConfiguration.php.dist');
        $sm = ServiceManagerFactory::getServiceManager();

        $factory = new WorkerAbstractFactory();
        $worker = $factory->__invoke($sm, 'SlmQueueTest\Asset\SimpleWorker');

        static::assertInstanceOf('SlmQueueTest\Asset\SimpleWorker', $worker);
    }
}
