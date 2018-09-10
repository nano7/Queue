<?php namespace Nano7\Queue;

use Nano7\Support\Arr;
use Aws\Sqs\SqsClient;
use Nano7\Queue\Queues\DatabaseQueue;
use Nano7\Queue\Queues\SqsQueue;
use Nano7\Queue\Queues\NullQueue;
use Nano7\Foundation\Support\ServiceProvider;

class QueueServiceProviders extends ServiceProvider
{
    /**
     * Register objetos base.
     */
    public function register()
    {
        $this->registerManager();

        $this->command('\Nano7\Queue\Console\QueueAbortCommand');
        $this->command('\Nano7\Queue\Console\QueuePauseCommand');
        $this->command('\Nano7\Queue\Console\QueueWorkCommand');
    }

    /**
     * Register manager.
     */
    protected function registerManager()
    {
        $this->app->singleton('queue', function () {
            $manager = new QueueManager($this->app, $this->app['config']->get('queue', []));

            // Driver Null
            $this->registerNullQueue($manager);

            // Driver Database
            $this->registerDatabaseQueue($manager);

            // Driver Aws SQS
            $this->registerAwsSqsQueue($manager);

            return $manager;
        });
    }

    /**
     * Register driver NULL.
     *
     * @param QueueManager $manager
     */
    protected function registerNullQueue(QueueManager $manager)
    {
        $manager->extend('null', function($app, $config) {
            return new NullQueue($app);
        });
    }

    /**
     * Register driver Database.
     *
     * @param QueueManager $manager
     */
    protected function registerDatabaseQueue(QueueManager $manager)
    {
        $manager->extend('database', function($app, $config) {
            return new DatabaseQueue(
                $app,
                $app['db']->connection($config['connection'] ? $config['connection'] : null),
                $config['collection'] ? $config['collection'] : 'jobs',
                $config['queue'] ? $config['queue'] : 'default',
                $config['retry_after'] ? $config['retry_after'] : 60
            );
        });
    }

    /**
     * Register driver AWS SQS.
     *
     * @param QueueManager $manager
     */
    protected function registerAwsSqsQueue(QueueManager $manager)
    {
        $manager->extend('sqs', function($app, $config) {

            $config = array_merge([
                'version' => 'latest',
                'http' => [
                    'timeout' => 60,
                    'connect_timeout' => 60,
                ],
            ], $config);

            if ($config['key'] && $config['secret']) {
                $config['credentials'] = Arr::only($config, ['key', 'secret']);
            }

            $client = new SqsClient($config);

            return new SqsQueue($app, $client, $config['queue'], $config['prefix']);
        });
    }
}