<?php namespace Nano7\Queue;

use Aws\Sqs\SqsClient;
use Nano7\Queue\Queues\SqsQueue;
use Nano7\Foundation\Support\Arr;
use Nano7\Foundation\Support\ServiceProvider;

class QueueServiceProviders extends ServiceProvider
{
    /**
     * Register objetos base.
     */
    public function register()
    {
        $this->registerManager();
    }

    /**
     * Register manager.
     */
    protected function registerManager()
    {
        $this->app->singleton('queue', function () {
            $manager = new QueueManager($this->app, $this->app['config']->get('queue', []));

            // Driver Aws SQS
            $this->registerAwsSqs($manager);

            return $manager;
        });
    }

    /**
     * Register driver AWS SQS.
     *
     * @param QueueManager $manager
     */
    protected function registerAwsSqs(QueueManager $manager)
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