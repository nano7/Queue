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
     * Determine if the job has been deleted.
     *
     * @return bool
     */
    public function isDeleted();

    /**
     * Determine if the job has been released.
     *
     * @return bool
     */
    public function isReleased();

    /**
     * Determine if the job has been deleted or released.
     *
     * @return bool
     */
    public function isDeletedOrReleased();

    /**
     * Release the job back into the queue (in seconds).
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