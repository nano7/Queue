<?php namespace Nano7\Queue\Console;

use Nano7\Console\Command;
use Nano7\Console\DaemonControl;

class QueueAbortCommand extends Command
{
    use DaemonControl;

    /**
     * @var string
     */
    public $flowControlName = 'queue.daemon';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:abort';

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
        $this->quit();

        $this->info('Queue daemon aborted');
    }
}