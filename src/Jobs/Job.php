<?php namespace Nano7\Queue\Jobs;

use Nano7\Foundation\Application;
use Nano7\Foundation\Support\Arr;

abstract class Job
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * Indicates if the job has been deleted.
     *
     * @var bool
     */
    protected $deleted = false;

    /**
     * Indicates if the job has been released.
     *
     * @var bool
     */
    protected $released = false;

    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Delete the job from the queue.
     *
     * @return void
     */
    public function delete()
    {
        $this->deleted = true;
    }

    /**
     * Determine if the job has been deleted.
     *
     * @return bool
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    /**
     * Release the job back into the queue.
     *
     * @param  int   $delay
     * @return void
     */
    public function release($delay = 0)
    {
        $this->released = true;
    }

    /**
     * Determine if the job was released back into the queue.
     *
     * @return bool
     */
    public function isReleased()
    {
        return $this->released;
    }

    /**
     * Determine if the job has been deleted or released.
     *
     * @return bool
     */
    public function isDeletedOrReleased()
    {
        return $this->isDeleted() || $this->isReleased();
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function call()
    {
        $json = json_decode($this->getRawBody(), true);
        if (is_null($json)) {
            throw new \Exception('Unable to JSON decode payload.');
        }

        $job = Arr::get($json, 'job.action');
        if (is_null($job)) {
            throw new \Exception('Unable to JSON decode payload. Invalid job action');
        }

        $data = Arr::get($json, 'data', []);

        return $this->app->call($job, [$this, $data], 'handle');
    }
}