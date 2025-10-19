<?php

namespace SlmQueueTest\Worker;

use Laminas\EventManager\EventManager;
use Laminas\EventManager\ResponseCollection;
use PHPUnit\Framework\TestCase;
use SlmQueue\Job\JobInterface;
use SlmQueue\Queue\QueueInterface;
use SlmQueue\Strategy\MaxRunsStrategy;
use SlmQueue\Worker\AbstractWorker;
use SlmQueue\Worker\Event\BootstrapEvent;
use SlmQueue\Worker\Event\FinishEvent;
use SlmQueue\Worker\Event\ProcessQueueEvent;
use SlmQueue\Worker\Event\ProcessStateEvent;
use SlmQueue\Worker\Event\WorkerEventInterface;
use SlmQueue\Worker\Result\ExitWorkerLoopResult;
use SlmQueue\Worker\Result\ProcessStateResult;
use SlmQueue\Worker\WorkerInterface;
use SlmQueueTest\Asset\SimpleWorker;

class AbstractWorkerTest extends TestCase
{
    /** @var SimpleWorker */
    protected $worker;
    protected $queue;
    protected $job;
    protected $maxRuns;

    public function setUp(): void
    {
        $this->worker = new SimpleWorker();
        $this->queue = $this->createMock(QueueInterface::class);
        $this->job = $this->createMock(JobInterface::class);

        // set max runs so our tests won't run forever
        $this->maxRuns = new MaxRunsStrategy();
        $this->maxRuns->setMaxRuns(1);
        $this->maxRuns->attach($this->worker->getEventManager());
    }

    public function testCorrectIdentifiersAreSetToEventManager(): void
    {
        /** @var EventManager $eventManager */
        $eventManager = $this->worker->getEventManager();

        static::assertTrue(in_array(AbstractWorker::class, $eventManager->getIdentifiers()));
        static::assertTrue(in_array(SimpleWorker::class, $eventManager->getIdentifiers()));
        static::assertTrue(in_array(WorkerInterface::class, $eventManager->getIdentifiers()));
    }

    public function testWorkerLoopEvents(): void
    {
        $eventManager = $this->createMock('Laminas\EventManager\EventManager');
        $this->worker = new SimpleWorker($eventManager);

        // BootstrapEvent, FinishEvent, and ProcessStateEvent
        $called = [];
        $eventManager->expects($this->exactly(3))
            ->method('triggerEvent')
            ->willReturnCallback(function ($event) use (&$called) {
                $called[] = get_class($event);
                if ($event instanceof ProcessStateEvent) {
                    $response = new ResponseCollection();
                    $response->push(ProcessStateResult::withState('some strategy state'));
                    $response->push(ProcessStateResult::withState('another strategy state'));
                    return $response;
                }
                return null;
            });

        // triggerEventUntil calls
        $response1 = new ResponseCollection();
        $response1->push(null);
        $response2 = new ResponseCollection();
        $response2->push(ExitWorkerLoopResult::withReason('some exit reason'));
        $response2->setStopped(true);
        $i = 0;
        $eventManager->expects($this->exactly(2))
            ->method('triggerEventUntil')
            ->willReturnCallback(function ($callback, $event) use (&$i, $response1, $response2) {
                $this->assertInstanceOf(ProcessQueueEvent::class, $event);
                return $i++ === 0 ? $response1 : $response2;
            });

        $result = $this->worker->processQueue($this->queue);

        static::assertEquals(["some strategy state", "another strategy state", "some exit reason"], $result);
    }

    public function testProcessQueueSetsOptionsOnProcessQueueEvent(): void
    {
        /** @var EventManager $eventManager */
        $eventManager = $this->worker->getEventManager();

        $options = ['foo' => 'bar'];

        $eventManager->attach(
            WorkerEventInterface::EVENT_PROCESS_QUEUE,
            function (ProcessQueueEvent $e) use ($options) {
                static::assertEquals($options, $e->getOptions());
            }
        );

        $this->worker->processQueue($this->queue, $options);
    }
}
