<?php namespace Nano7\Queue\Jobs;

use Nano7\Support\Arr;
use Aws\Sqs\SqsClient;
use Nano7\Foundation\Application;

class SqsJob extends Job
{
    /**
     * @var SqsClient
     */
    protected $sqs;

    /**
     * The Amazon SQS job instance.
     *
     * @var array
     */
    protected $job;

    /**
     * @var string
     */
    protected $queue = '';

    /**
     * @param Application $app
     * @param SqsClient $sqs
     * @param array $job
     * @param $queue
     */
    public function __construct(Application $app, SqsClient $sqs, array $job, $queue)
    {
        parent::__construct($app);

        $this->sqs = $sqs;
        $this->job = $job;
        $this->queue = $queue;
    }

    /**
     * Get the job identifier.
     *
     * @return string
     */
    public function getJobId()
    {
        return Arr::get($this->job, 'MessageId');
    }

    /**
     * Get the raw body string for the job.
     *
     * @return string
     */
    public function getRawBody()
    {
        return Arr::get($this->job, 'Body', '');
    }

    /**
     * Get the number of times the job has been attempted.
     *
     * @return int
     */
    public function attempts()
    {
        return (int) Arr::get($this->job, 'Attributes.ApproximateReceiveCount', 0);
    }

    /**
     * Release the job back into the queue.
     *
     * @param  int $delay
     * @return void
     */
    public function release($delay = 0)
    {
        if ($this->isDeleted()) {
            return;
        }

        parent::release($delay);

        $this->sqs->changeMessageVisibility([
            'QueueUrl' => $this->queue,
            'ReceiptHandle' => $this->job['ReceiptHandle'],
            'VisibilityTimeout' => $delay,
        ]);
    }

    /**
     * Delete the job from the queue.
     *
     * @return void
     */
    public function delete()
    {
        if ($this->isDeleted()) {
            return;
        }

        parent::delete();

        $this->sqs->deleteMessage([
            'QueueUrl' => $this->queue, 'ReceiptHandle' => $this->job['ReceiptHandle'],
        ]);
    }
}