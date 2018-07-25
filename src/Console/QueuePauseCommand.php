<?php namespace Nano7\Queue\Console;

use Nano7\Console\Command;
use Nano7\Console\DaemonControl;

class QueuePauseCommand extends Command
{
    use DaemonControl;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:pause {mode? : The mode pause on or off}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pause daemon queue';

    /**
     * Run command.
     */
    public function handle()
    {
        $this->flowControlName = 'queue.daemon';
        $this->prepareFiles();

        $start = (! ($this->argument('mode') == 'off'));

        if ($start) {
            $this->pause();
            $this->info('Queue daemon paused');
        } else {
            $this->start();
            $this->info('Queue daemon started');
        }
    }
}