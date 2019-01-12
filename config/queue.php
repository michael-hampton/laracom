<?php

return [
    /*
      |--------------------------------------------------------------------------
      | Default Queue Driver
      |--------------------------------------------------------------------------
      |
      | The Laravel queue API supports a variety of back-ends via an unified
      | API, giving you convenient access to each back-end using the same
      | syntax for each one. Here you may set the default queue driver.
      |
      | Supported: "null", "sync", "database", "beanstalkd",
      |            "sqs", "redis"
      |
     */

    'default' => env('QUEUE_DRIVER', 'rabbitmq'),
    /*
      |--------------------------------------------------------------------------
      | Queue Connections
      |--------------------------------------------------------------------------
      |
      | Here you may configure the connection information for each server that
      | is used by your application. A default configuration has been added
      | for each back-end shipped with Laravel. You are free to add more.
      |
     */
    'connections' => [
        'sync' => [
            'driver' => 'sync',
        ],
        'database' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'default',
            'expire' => 60,
        ],
        'rabbitmq' => [
            'factory_class' => \Enqueue\AmqpLib\AmqpConnectionFactory::class,
            'dsn' => null,
            'host' => 'localhost',
            'port' => 15672,
            'login' => 'guest',
            'password' => 'guest',
            'queue' => env('RABBITMQ_QUEUE', 'default'),
            'vhost' => '/',
            'options' => [
                'exchange' => [
                    'name' => null,
                    'declare' => true,
                    'type' => \Interop\Amqp\Impl\AmqpTopic::TYPE_DIRECT,
                    'passive' => false,
                    'durable' => true,
                    'auto_delete' => false,
                ],
                'queue' => [
                    'name' => 'mike',
                    'declare' => true,
                    'bind' => true,
                    'passive' => false,
                    'durable' => true,
                    'exclusive' => false,
                    'auto_delete' => false,
                    'arguments' => '[]',
                ],
            ],
            /*
             * Determine the number of seconds to sleep if there's an error communicating with rabbitmq
             * If set to false, it'll throw an exception rather than doing the sleep for X seconds.
             */
            'sleep_on_error' => env('RABBITMQ_ERROR_SLEEP', 20),
            /*
             * Optional SSL params if an SSL connection is used
             * Using an SSL connection will also require to configure your RabbitMQ to enable SSL. More details can be founds here: https://www.rabbitmq.com/ssl.html
             */
            'ssl_params' => [
                'ssl_on' => false,
                'cafile' => null,
                'local_cert' => null,
                'local_key' => null,
                'verify_peer' => true,
                'passphrase' => null,
            ],
        ],
        'beanstalkd' => [
            'driver' => 'beanstalkd',
            'host' => 'localhost',
            'queue' => 'default',
            'ttr' => 60,
        ],
        'sqs' => [
            'driver' => 'sqs',
            'key' => 'your-public-key',
            'secret' => 'your-secret-key',
            'prefix' => 'https://sqs.us-east-1.amazonaws.com/your-account-id',
            'queue' => 'your-queue-name',
            'region' => 'us-east-1',
        ],
        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => 'default',
            'expire' => 60,
        ],
    ],
    /*
      |--------------------------------------------------------------------------
      | Failed Queue Jobs
      |--------------------------------------------------------------------------
      |
      | These options configure the behavior of failed queue job logging so you
      | can control which database and table are used to store the jobs that
      | have failed. You may change them to any database / table you wish.
      |
     */
    'failed' => [
        'database' => env('DB_CONNECTION', 'mysql'),
        'table' => 'failed_jobs',
    ],
];
