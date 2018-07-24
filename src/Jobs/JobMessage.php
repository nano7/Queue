<?php namespace Nano7\Queue\Jobs;

use Nano7\Foundation\Support\Arr;
use Nano7\Queue\Contracts\JobContract;

class JobMessage
{
    /**
     * @var JobContract
     */
    protected $job;

    /**
     * @var string
     */
    protected $action = '';

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @param $action
     * @param array $data
     */
    public function __construct($job, $action, $data = [])
    {
        $this->job;
        $this->action = $action;
        $this->data = $data;
    }

    /**
     * @return JobContract
     */
    public function job()
    {
        return $this->job;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed
     */
    public function data($key, $default = null)
    {
        return Arr::get($this->data, $key, $default);
    }

    public function call()
    {

    }
}