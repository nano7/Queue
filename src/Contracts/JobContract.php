<?php namespace Nano7\Queue\Contracts;

interface JobContract
{
    /**
     * Get the job identifier.
     *
     * @return string
     */
    public function getJobId();

    /**
     * Get the raw body string for the job.
     *
     * @return string
     */
    public function getRawBody();

    /**
     * Get the number of times the job has been attempted.
     *
     * @return int
     */
    public function attempts();

    /**
     * Release the job back into the queue.
     *
     * @param  int   $delay
     * @return void
     */
    public function release($delay = 0);

    /**
     * Delete the job from the queue.
     *
     * @return void
     */
    public function delete();
}