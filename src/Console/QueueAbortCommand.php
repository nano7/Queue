<?php namespace Nano7\Queue\Console;

use Nano7\Console\Command;
use Nano7\Console\Traits\DaemonControl;

class QueueAbortCommand extends Command
{
    use DaemonControl;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:abort
                            {--daemon=default : Name of daemon control flow}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Abort daemon queue';

    /**
     * Run command.
     */
    public function handle()
    {
        $this->flowControlName = sprintf('queue.daemon.%s', $this->option('daemon'));
        $this->prepareFiles();

        $this->quit();

        $this->info('Queue daemon aborted');
    }
}