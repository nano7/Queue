<?php namespace Nano7\Queue\Jobs;

use Nano7\Foundation\Application;
use Nano7\Queue\Queues\DatabaseQueue;
use Nano7\Queue\Contracts\JobContract;

class DatabaseJob extends Job implements JobContract
{
    /**
     * The database queue instance.
     *
     * @var DatabaseQueue
     */
    protected $database;

    /**
     * The database job payload.
     *
     * @var \stdClass
     */
    protected $job;

    /**
     * Create a new job instance.
     *
     * @param  Application $app
     * @param  DatabaseQueue  $database
     * @param  \stdClass  $job
     * @param  string  $queue
     * @return void
     */
    public function __construct(Application $app, DatabaseQueue $database, $job, $queue)
    {
        $this->app = $app;
        $this->job = $job;
        $this->queue = $queue;
        $this->database = $database;
    }

    /**
     * Release the job back into the queue.
     *
     * @param  int  $delay
     * @return mixed
     */
    public function release($delay = 0)
    {
        parent::release($delay);

        $this->delete();

        return $this->database->release($this->queue, $this->job, $delay);
    }

    /**
     * Delete the job from the queue.
     *
     * @return void
     */
    public function delete()
    {
        parent::delete();

        $this->database->deleteReserved($this->queue, $this->job->id);
    }

    /**
     * Get the number of times the job has been attempted.
     *
     * @return int
     */
    public function attempts()
    {
        return (int) $this->job->attempts;
    }

    /**
     * Get the job identifier.
     *
     * @return string
     */
    public function getJobId()
    {
        return $this->job->id;
    }

    /**
     * Get the raw body string for the job.
     *
     * @return string
     */
    public function getRawBody()
    {
        return $this->job->payload;
    }
}
