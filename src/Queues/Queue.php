<?php namespace Nano7\Queue\Queues;

use Carbon\Carbon;
use Nano7\Foundation\Application;
use Nano7\Queue\Contracts\QueueContract;
use Nano7\Foundation\Support\InteractsWithTime;

abstract class Queue implements QueueContract
{
    use InteractsWithTime;

    /**
     * @var Application
     */
    protected $app;

    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Push a new job onto the queue.
     *
     * @param  string  $queue
     * @param  string  $job
     * @param  mixed   $data
     * @return mixed
     */
    public function pushOn($queue, $job, $data = '')
    {
        return $this->push($job, $data, $queue);
    }

    /**
     * Push a new job onto the queue after a delay.
     *
     * @param  string  $queue
     * @param  \DateTimeInterface|\DateInterval|int  $delay
     * @param  string  $job
     * @param  mixed   $data
     * @return mixed
     */
    public function laterOn($queue, $delay, $job, $data = '')
    {
        return $this->later($delay, $job, $data, $queue);
    }

    /**
     * @param $job
     * @param array $data
     * @return string
     * @throws InvalidPayloadException
     */
    protected function createPayload($job, $data = [])
    {
        $message = [
            'job' => [
                'action' => $job,
            ],
            'data' => $data,
        ];

        $payload = json_encode($message);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \Exception('Unable to JSON encode payload. Error code: ' . json_last_error());
        }

        return $payload;
    }
}