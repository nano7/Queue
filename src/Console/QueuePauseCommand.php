<?php namespace Nano7\Queue\Console;

use Nano7\Console\Command;
use Nano7\Console\Traits\DaemonControl;

class QueuePauseCommand extends Command
{
    use DaemonControl;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:pause
                            {mode? : The mode pause on or off}
                            {--daemon=default : Name of daemon control flow}';

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
        $this->flowControlName = sprintf('queue.daemon.%s', $this->option('daemon'));
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