<?php

$scenario_maker = function ($worker_type, $queue_key, $defaults_key, $process_count_key) {
    return [
        [
            // expected queue config
            [
                'host'               => null,
                'port'               => 5672,
                'username'           => null,
                'password'           => null,
                'queue_name'         => "hodor-minimal",
                'worker_name'        => "minimal",
                'fetch_count'        => 1,
                'process_count'      => 5,
                'worker_type'        => $worker_type,
            ],
            // queue name to test
            "{$worker_type}-minimal",
            // config array passed to Config object
            [
                'queue_defaults' => [$process_count_key   => 5],
                $defaults_key => [],
                $queue_key => [
                    'minimal' => [],
                ],
            ],
        ],
        [
            // expected queue config
            [
                'host'               => 'localhost',
                'port'               => 5673,
                'username'           => 'hare',
                'password'           => 'turtle',
                'queue_name'         => "willis-test-queue-defaults",
                'worker_name'        => "test-queue-defaults",
                'fetch_count'        => 1,
                'process_count'      => 10,
                'worker_type'        => $worker_type,
            ],
            // queue name to test
            "{$worker_type}-test-queue-defaults",
            // config array passed to Config object
            [
                'queue_defaults' => [
                    'host'               => 'localhost',
                    'port'               => 5673,
                    'username'           => 'hare',
                    'password'           => 'turtle',
                    'queue_prefix'       => 'willis-',
                    $process_count_key   => 10,
                ],
                $defaults_key => [],
                $queue_key    => [
                    'test-queue-defaults' => [],
                ],
            ],
        ],
        [
            // expected queue config
            [
                'host'               => '127.0.0.1',
                'port'               => 5674,
                'username'           => 'hared',
                'password'           => 'turtled',
                'queue_name'         => "willis-hodor-test-worker-queue-defaults",
                'worker_name'        => "test-worker-queue-defaults",
                'fetch_count'        => 1,
                'process_count'      => 15,
                'worker_type'        => $worker_type,
            ],
            // queue name to test
            "{$worker_type}-test-worker-queue-defaults",
            // config array passed to Config object
            [
                'queue_defaults' => [
                    'host'               => 'localhost',
                    'port'               => 5673,
                    'username'           => 'hare',
                    'password'           => 'turtle',
                    'queue_prefix'       => 'willis-',
                    $process_count_key   => 10,
                ],
                $defaults_key => [
                    'host'               => '127.0.0.1',
                    'port'               => 5674,
                    'username'           => 'hared',
                    'password'           => 'turtled',
                    'queue_prefix'       => 'willis-hodor-',
                    $process_count_key   => 15,
                ],
                $queue_key => [
                    'test-worker-queue-defaults' => [],
                ],
            ],
        ],
        [
            // expected queue config
            [
                'host'               => '192.168.1.1',
                'port'               => 5675,
                'username'           => 'fast',
                'password'           => 'slow',
                'queue_name'         => "hold-the-door-test-worker-queue",
                'worker_name'        => "test-worker-queue",
                'fetch_count'        => 1,
                'process_count'      => 20,
                'worker_type'        => $worker_type,
            ],
            // queue name to test
            "{$worker_type}-test-worker-queue",
            // config array passed to Config object
            [
                'queue_defaults' => [
                    'host'               => 'localhost',
                    'port'               => 5673,
                    'username'           => 'hare',
                    'password'           => 'turtle',
                    'queue_prefix'       => 'willis-',
                    $process_count_key   => 10,
                ],
                $defaults_key => [
                    'host'               => '127.0.0.1',
                    'port'               => 5674,
                    'username'           => 'hared',
                    'password'           => 'turtled',
                    'queue_prefix'       => 'willis-hodor-',
                    $process_count_key   => 15,
                ],
                $queue_key => [
                    'test-worker-queue' => [
                        'host'               => '192.168.1.1',
                        'port'               => 5675,
                        'username'           => 'fast',
                        'password'           => 'slow',
                        'queue_prefix'       => 'hold-the-door-',
                        $process_count_key   => 20,
                    ],
                ],
            ],
        ],
        [
            // expected queue config
            [
                'host'               => '192.168.1.1',
                'port'               => 5675,
                'username'           => 'fast',
                'password'           => 'slow',
                'queue_name'         => "hold-the-door-defaults-are-optional",
                'worker_name'        => "defaults-are-optional",
                'fetch_count'        => 1,
                'process_count'      => 20,
                'worker_type'        => $worker_type,
            ],
            // queue name to test
            "{$worker_type}-defaults-are-optional",
            // config array passed to Config object
            [
                $queue_key => [
                    'defaults-are-optional' => [
                        'host'               => '192.168.1.1',
                        'port'               => 5675,
                        'username'           => 'fast',
                        'password'           => 'slow',
                        'queue_prefix'       => 'hold-the-door-',
                        $process_count_key   => 20,
                    ],
                ],
            ],
        ],
    ];
};

return array_merge(
    $scenario_maker('worker', 'worker_queues', 'worker_queue_defaults', 'workers_per_server'),
    $scenario_maker('bufferer', 'buffer_queues', 'buffer_queue_defaults', 'bufferers_per_server')
);
