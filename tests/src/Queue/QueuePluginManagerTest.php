<?php

namespace SlmQueueTest\Queue;

use Laminas\ServiceManager\ServiceManager;
use Laminas\Test\Util\ModuleLoader;
use PHPUnit\Framework\TestCase;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use SlmQueue\Queue\QueuePluginManager;
use SlmQueueTest\Util\ServiceManagerFactory;
use stdClass;

class QueuePluginManagerTest extends TestCase
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    public function setUp(): void
    {
        parent::setUp();

        $moduleLoader = new ModuleLoader(include __DIR__ . '/../TestConfiguration.php.dist');
        $this->serviceManager = $moduleLoader->getServiceManager();
    }

    public function testCanRetrievePluginManagerWithServiceManager(): void
    {
        $queuePluginManager = $this->serviceManager->get(QueuePluginManager::class);
        static::assertInstanceOf(QueuePluginManager::class, $queuePluginManager);
    }

    public function testAskingTwiceTheSameQueueReturnsTheSameInstance(): void
    {
        $queuePluginManager = $this->serviceManager->get(QueuePluginManager::class);

        $firstInstance = $queuePluginManager->get('basic-queue');
        $secondInstance = $queuePluginManager->get('basic-queue');

        static::assertSame($firstInstance, $secondInstance);
    }

    public function testPluginValidation(): void
    {
        $serviceManager = new ServiceManager();
        $manager = new QueuePluginManager($serviceManager);
        $queue = new stdClass();

        $this->expectException(InvalidServiceException::class);
        $manager->validate($queue);
    }
}
