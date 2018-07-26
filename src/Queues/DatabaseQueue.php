<?php namespace Nano7\Queue\Queues;

use Carbon\Carbon;
use Nano7\Queue\Jobs\DatabaseJob;
use Nano7\Foundation\Application;
use Nano7\Database\Query\Builder;
use Nano7\Queue\Contracts\JobContract;
use Nano7\Database\ConnectionInterface;
use Nano7\Queue\Jobs\DatabaseJobRecord;

class DatabaseQueue extends Queue
{
    /**
     * The database connection instance.
     *
     * @var ConnectionInterface
     */
    protected $connection;

    /**
     * The database collection that holds the jobs.
     *
     * @var string
     */
    protected $collection = '';

    /**
     * The name of the default queue.
     *
     * @var string
     */
    protected $default;

    /**
     * The expiration time of a job.
     *
     * @var int|null
     */
    protected $retryAfter = 60;

    /**
     * @param Application $app
     * @param ConnectionInterface $connection
     * @param $collection
     * @param string $default
     * @param int $retryAfter
     */
    public function __construct(Application $app, ConnectionInterface $connection, $collection, $default = 'default', $retryAfter = 60)
    {
        parent::__construct($app);

        $this->connection = $connection;
        $this->collection = $collection;
        $this->default = $default;
        $this->retryAfter = $retryAfter;
    }

    /**
     * Get the size of the queue.
     *
     * @param  string  $queue
     * @return int
     */
    public function size($queue = null)
    {
        return $this->store()->where('queue', $this->getQueue($queue))->count();
    }

    /**
     * Release a reserved job back onto the queue.
     *
     * @param  string  $queue
     * @param  DatabaseJobRecord  $job
     * @param  int  $delay
     * @return mixed
     */
    public function release($queue, $job, $delay)
    {
        return $this->pushToDatabase($queue, $job->payload, $delay, $job->attempts);
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
        return $this->pushToDatabase($queue, $this->createPayload($job, $data));
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
        return $this->pushToDatabase($queue, $payload);
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
        return $this->pushToDatabase($queue, $this->createPayload($job, $data), $delay);
    }

    /**
     * Pop the next job off of the queue.
     *
     * @param  string  $queue
     * @return JobContract
     */
    public function pop($queue = null)
    {
        $queue = $this->getQueue($queue);

        return $this->connection->transaction(function () use ($queue) {
            if ($job = $this->getNextAvailableJob($queue)) {
                return $this->marshalJob($queue, $job);
            }

            return null;
        });
    }

    /**
     * @return Builder
     */
    protected function store()
    {
        $query = $this->connection->collection($this->collection);

        return $query;
    }

    /**
     * Create an array to insert for the given job.
     *
     * @param  string|null  $queue
     * @param  string  $payload
     * @param  int  $availableAt
     * @param  int  $attempts
     * @return array
     */
    protected function buildDatabaseRecord($queue, $payload, $availableAt, $attempts = 0)
    {
        return [
            'queue' => $queue,
            'attempts' => $attempts,
            'reserved_at' => null,
            'available_at' => $availableAt,
            'created_at' => $this->currentTime(),
            'payload' => $payload,
        ];
    }

    /**
     * Push a raw payload to the database with a given delay.
     *
     * @param  string|null  $queue
     * @param  string  $payload
     * @param  \DateTimeInterface|\DateInterval|int  $delay
     * @param  int  $attempts
     * @return mixed
     */
    protected function pushToDatabase($queue, $payload, $delay = 0, $attempts = 0)
    {
        return $this->store()->insertGetId($this->buildDatabaseRecord(
            $this->getQueue($queue), $payload, $this->availableAt($delay), $attempts
        ));
    }

    /**
     * Get the next available job for the queue.
     *
     * @param  string|null  $queue
     * @return DatabaseJobRecord|null
     */
    protected function getNextAvailableJob($queue)
    {
        $query = $this->store()->where('queue', $this->getQueue($queue));
        $query->where(function ($query) {
            $this->isAvailable($query);
            $this->isReservedButExpired($query);
        });
        $query->orderBy('id', 'asc');

        $job = $query->first();

        return $job ? new DatabaseJobRecord((object) $job) : null;
    }

    /**
     * Modify the query to check for available jobs.
     *
     * @param  Builder  $query
     * @return void
     */
    protected function isAvailable($query)
    {
        $query->where(function ($query) {
            $query->whereNull('reserved_at')
                ->where('available_at', '<=', $this->currentTime());
        });
    }

    /**
     * Modify the query to check for jobs that are reserved but have expired.
     *
     * @param  Builder  $query
     * @return void
     */
    protected function isReservedButExpired($query)
    {
        $expiration = Carbon::now()->subSeconds($this->retryAfter)->getTimestamp();

        $query->orWhere(function ($query) use ($expiration) {
            $query->where('reserved_at', '<=', $expiration);
        });
    }

    /**
     * Marshal the reserved job into a DatabaseJob instance.
     *
     * @param  string  $queue
     * @param  DatabaseJobRecord  $job
     * @return DatabaseJob
     */
    protected function marshalJob($queue, $job)
    {
        $job = $this->markJobAsReserved($job);

        return new DatabaseJob($this->app, $this, $job, $queue);
    }

    /**
     * Mark the given job ID as reserved.
     *
     * @param  DatabaseJobRecord  $job
     * @return DatabaseJobRecord
     */
    protected function markJobAsReserved($job)
    {
        $this->store()->where('id', $job->id)->update([
            'reserved_at' => $job->touch(),
            'attempts' => $job->increment(),
        ]);

        return $job;
    }

    /**
     * Delete a reserved job from the queue.
     *
     * @param  string  $queue
     * @param  string  $id
     * @return void
     * @throws \Exception|\Throwable
     */
    public function deleteReserved($queue, $id)
    {
        $this->connection->transaction(function () use ($id) {
            $this->store()->where('id', $id)->delete();
        });
    }

    /**
     * Get the queue or return the default.
     *
     * @param  string|null  $queue
     * @return string
     */
    public function getQueue($queue)
    {
        return $queue ?: $this->default;
    }
}
