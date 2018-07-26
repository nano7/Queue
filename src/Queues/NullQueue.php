<?php namespace Nano7\Queue\Queues;

use Nano7\Foundation\Application;
use Nano7\Queue\Contracts\JobContract;

class NullQueue extends Queue
{
    /**
     * Get the size of the queue.
     *
     * @param  string  $queue
     * @return int
     */
    public function size($queue = null)
    {
        return 0;
    }

    /**
     * Push a new job onto the queue.
     *
     * @param  string  $job
     * @param  array   $data
     * @param  string  $queue
     * @return mixed
     */
    public function push($job, $data = [], $queue = null)
    {
        return '';
    }

    /**
     * Push a raw payload onto the queue.
     *
     * @param  string  $payload
     * @param  string  $queue
     * @param  array   $options
     * @return mixed
     */
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        return '';
    }

    /**
     * Push a new job onto the queue after a delay.
     *
     * @param  \DateTimeInterface|\DateInterval|int  $delay
     * @param  string  $job
     * @param  array   $data
     * @param  string  $queue
     * @return mixed
     */
    public function later($delay, $job, $data = [], $queue = null)
    {
        return '';
    }

    /**
     * Pop the next job off of the queue.
     *
     * @param  string  $queue
     * @return JobContract
     */
    public function pop($queue = null)
    {
        return null;
    }
}
