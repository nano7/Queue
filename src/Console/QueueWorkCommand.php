<?php namespace Nano7\Queue\Console;

use Nano7\Console\Command;
use Nano7\Console\Traits\DaemonControl;

class QueueWorkCommand extends Command
{
    use DaemonControl;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:work
                           {connection? : The name of the queue connection to work}
                           {--queue= : The names of the queues to work}
                           {--once : Only process the next job on the queue}
                           {--memory=128 : The memory limit in megabytes}
                           {--sleep=3 : Number of seconds to sleep when no job is available}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Work next queue';

    /**
     * Run command.
     */
    public function handle()
    {
        $this->flowControlName = 'queue.daemon';

        $connection = $this->argument('connection');
        $queue = $this->option('queue');
        $once = $this->option('once');

        // Verificar se deve executar somente o proximo job
        if ($once === true) {
            $this->runWork($connection, $queue);
            return;
        }

        // Executar como daemon
        $sleep = intval($this->option('sleep'));
        $memory = intval($this->option('memory'));

        $this->runDaemon(function () use ($connection, $queue) {
            return $this->runWork($connection, $queue);
        }, $sleep, $memory);
    }

    /**
     * @param $connection
     * @param $queue
     */
    protected function runWork($connection, $queue)
    {
        $job = queue($connection)->pop($queue);
        if (is_null($job)) {
            return false;
        }

        $this->output->write('Run job...');
        try {
            $job->call();

            $this->info('OK');
        } catch (\Exception $e) {
            $this->error('ERROR');
        }

        return null;
    }
}