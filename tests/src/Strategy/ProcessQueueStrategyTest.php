<?php

namespace SlmQueueTest\Listener\Strategy;

use Laminas\EventManager\EventManagerInterface;
use PHPUnit\Framework\TestCase;
use SlmQueue\Job\JobInterface;
use SlmQueue\Queue\QueueInterface;
use SlmQueue\Strategy\AbstractStrategy;
use SlmQueue\Strategy\ProcessQueueStrategy;
use SlmQueue\Worker\Event\ProcessJobEvent;
use SlmQueue\Worker\Event\ProcessQueueEvent;
use SlmQueue\Worker\Event\WorkerEventInterface;
use SlmQueue\Worker\Result\ExitWorkerLoopResult;
use SlmQueueTest\Asset\JobWithNoReturnType;
use SlmQueueTest\Asset\JobWithVoidReturnType;
use SlmQueueTest\Asset\SimpleJob;
use SlmQueueTest\Asset\SimpleWorker;

class ProcessQueueStrategyTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|QueueInterface  */
    protected $queue;
    protected $worker;
    /** @var ProcessQueueStrategy */
    protected $listener;

    public function setUp(): void
    {
        $this->queue = $this->createMock(QueueInterface::class);
        $this->worker = new SimpleWorker();
        $this->listener = new ProcessQueueStrategy();
    }

    public function testListenerInstanceOfAbstractStrategy(): void
    {
        static::assertInstanceOf(AbstractStrategy::class, $this->listener);
    }

    public function testListensToCorrectEventAtCorrectPriority(): void
    {
        $evm = $this->createMock(EventManagerInterface::class);
        $priority = 1;

        $calls = [];
        $evm->expects($this->exactly(2))
            ->method('attach')
            ->willReturnCallback(function ($event, $listener, $prio) use (&$calls) {
                $calls[] = [$event, $listener, $prio];
            });

        $this->listener->attach($evm, $priority);

        static::assertSame([
            [WorkerEventInterface::EVENT_PROCESS_QUEUE, [$this->listener, 'onJobPop'], $priority],
            [WorkerEventInterface::EVENT_PROCESS_JOB, [$this->listener, 'onJobProcess'], $priority],
        ], $calls);
    }

    public function testJobPopWithEmptyQueueTriggersIdleAndNoExitResultIsReturned(): void
    {
        $popOptions = [];
        $this->queue->expects($this->once())
            ->method('pop')
            ->with($popOptions)
            ->willReturn(null);

        $event = new ProcessQueueEvent($this->worker, $this->queue, $popOptions);

        $triggeredIdle = false;
        $this->worker->getEventManager()->attach(
            WorkerEventInterface::EVENT_PROCESS_IDLE,
            function ($e) use (&$triggeredIdle) {
                $triggeredIdle = true;
            }
        );

        $result = $this->listener->onJobPop($event);

        static::assertNull($result);
        static::assertTrue($triggeredIdle);
        static::assertTrue($event->propagationIsStopped(), "EventPropagation should be stopped");
    }

    public function testJobPopWithEmptyQueueTriggersIdleAndExitResultIsReturned(): void
    {
        $popOptions = [];
        $this->queue->expects($this->once())
            ->method('pop')
            ->with($popOptions)
            ->willReturn(null);

        $event = new ProcessQueueEvent($this->worker, $this->queue, $popOptions);

        $triggeredIdle = false;
        $this->worker->getEventManager()->attach(
            WorkerEventInterface::EVENT_PROCESS_IDLE,
            function ($e) use (&$triggeredIdle) {
                $triggeredIdle = true;

                return ExitWorkerLoopResult::withReason('some reason');
            }
        );

        $result = $this->listener->onJobPop($event);

        static::assertInstanceOf(ExitWorkerLoopResult::class, $result);
        static::assertTrue($triggeredIdle);
        static::assertTrue($event->propagationIsStopped(), "EventPropagation should be stopped");
    }

    /**
     * @dataProvider jobsProvider
     */
    public function testJobPopWithJobTriggersProcessJobEvent(JobInterface $job): void
    {
        $popOptions = [];
        $this->queue->expects($this->once())
            ->method('pop')
            ->with($popOptions)
            ->willReturn($job);

        $event = new ProcessQueueEvent($this->worker, $this->queue, $popOptions);

        $triggeredProcessJobEvent = false;
        $this->worker->getEventManager()->attach(
            WorkerEventInterface::EVENT_PROCESS_JOB,
            function ($e) use (&$triggeredProcessJobEvent) {
                $triggeredProcessJobEvent = true;
            }
        );

        $result = $this->listener->onJobPop($event);

        static::assertNull($result);
        static::assertTrue($triggeredProcessJobEvent);
        static::assertFalse($event->propagationIsStopped(), "EventPropagation should not be stopped");
    }

    /**
     * @dataProvider jobsProvider
     */
    public function testOnJobProcess(JobInterface $job): void
    {
        $event = new ProcessJobEvent($job, $this->worker, $this->queue);

        $this->listener->onJobProcess($event);

        static::assertSame(999, $event->getResult());
        static::assertEquals($job, $event->getJob());
        static::assertSame('bar', $event->getJob()->getMetadata('foo'));
    }

    public static function jobsProvider(): array
    {
        return [
            [new SimpleJob()],
            [new JobWithNoReturnType()],
            [new JobWithVoidReturnType()],
        ];
    }
}
