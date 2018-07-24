<?php

if (! function_exists('queue')) {
    /**
     * @param null $connection
     * @return \Nano7\Queue\QueueManager|\Nano7\Queue\Contracts\QueueContract
     */
    function queue($connection = null)
    {
        $queue = app('queue');

        if (is_null($connection)) {
            return $queue;
        }

        return $queue->connection($connection);
    }
}