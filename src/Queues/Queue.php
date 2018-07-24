<?php namespace Nano7\Queue\Queues;

use Carbon\Carbon;
use Nano7\Foundation\Application;

abstract class Queue
{
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
     * Get the number of seconds until the given DateTime.
     *
     * @param  \DateTimeInterface|\DateInterval|int  $delay
     * @return int
     */
    protected function secondsUntil($delay)
    {
        $delay = $this->parseDateInterval($delay);

        return $delay instanceof DateTimeInterface
            ? max(0, $delay->getTimestamp() - $this->currentTime())
            : (int) $delay;
    }

    /**
     * Get the "available at" UNIX timestamp.
     *
     * @param  \DateTimeInterface|\DateInterval|int  $delay
     * @return int
     */
    protected function availableAt($delay = 0)
    {
        $delay = $this->parseDateInterval($delay);

        return $delay instanceof DateTimeInterface
            ? $delay->getTimestamp()
            : Carbon::now()->addSeconds($delay)->getTimestamp();
    }

    /**
     * If the given value is an interval, convert it to a DateTime instance.
     *
     * @param  \DateTimeInterface|\DateInterval|int  $delay
     * @return \DateTimeInterface|int
     */
    protected function parseDateInterval($delay)
    {
        if ($delay instanceof DateInterval) {
            $delay = Carbon::now()->add($delay);
        }

        return $delay;
    }

    /**
     * Get the current system time as a UNIX timestamp.
     *
     * @return int
     */
    protected function currentTime()
    {
        return Carbon::now()->getTimestamp();
    }

    /**
     * @param $job
     * @param array $data
     * @return string
     * @throws InvalidPayloadException
     */
    protected function createPayload($job, $data = [])
    {
        $message = (object) [
            'job' => (object) [
                'action' => $job,
            ],

            'data' => $data,
        ];

        $payload = json_encode($message);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidPayloadException(
                'Unable to JSON encode payload. Error code: '.json_last_error()
            );
        }

        return $payload;
    }
}