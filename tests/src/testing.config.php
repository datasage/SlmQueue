<?php

use SlmQueue\Job\JobPluginManager;
use SlmQueue\Strategy\AttachQueueListenersStrategy;
use SlmQueue\Strategy\InterruptStrategy;
use SlmQueue\Strategy\MaxMemoryStrategy;
use SlmQueue\Strategy\MaxRunsStrategy;
use SlmQueue\Strategy\ProcessQueueStrategy;
use SlmQueueTest\Asset\SimpleQueue;

return [
    'slm_queue' => [
        /**
         * Queues config
         */
        'queue_manager' => [
            'factories' => [
                'basic-queue' => function ($locator) {

                    $jobPluginManager = $locator->get(JobPluginManager::class);

                    return new SimpleQueue(
                        'basic-queue',
                        $jobPluginManager
                    );
                },
            ],
        ],

        'worker_strategies' => [
            'queues' => [
                'basic-queue' => [
                    ProcessQueueStrategy::class,
                    MaxRunsStrategy::class => [
                        'max_runs' => 1,
                    ],
                ],
            ],
        ],
    ],
];
