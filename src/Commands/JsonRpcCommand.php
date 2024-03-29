<?php


namespace VinyVicente\JsonRpc\Commands;

use Illuminate\Console\Command;
use Swoole\Process;

class JsonRpcCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swoole:jsonrpc {action : start|stop|restart|reload}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Swoole JSON-RPC Server controller.';

    /**
     * The console command action. start|stop|restart|reload
     *
     * @var string
     */
    protected $action;

    /**
     *
     * The pid.
     *
     * @var int
     */
    protected $pid;

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->initAction();
        $this->runAction();
    }

    /**
     * Run action.
     */
    protected function runAction()
    {
        $this->{$this->action}();
    }

    /**
     * Run swoole_jsonrpc_server.
     */
    protected function start()
    {
        if ($this->isRunning($this->getPid())) {
            $this->error('Failed! swoole_jsonrpc_server process is already running.');
            exit(1);
        }

        $this->info('Starting swoole jsonrpc server...');

        $this->info('> (You can run this command to ensure the ' .
            'swoole_jsonrpc_server process is running: ps aux|grep "swoole")');

        $this->laravel->make('swoole.jsonrpc')->run();
    }

    /**
     * Stop swoole_jsonrpc_server.
     */
    protected function stop()
    {
        $pid = $this->getPid();

        if (! $this->isRunning($pid)) {
            $this->error("Failed! There is no swoole_jsonrpc_server process running.");
            exit(1);
        }

        $this->info('Stopping swoole_jsonrpc_server...');

        $isRunning = $this->killProcess($pid, SIGTERM, 15);

        if ($isRunning) {
            $this->error('Unable to stop the swoole_jsonrpc_server process.');
            exit(1);
        }

        // I don't known why Swoole didn't trigger "onShutdown" after sending SIGTERM.
        // So we should manually remove the pid file.
        $this->removePidFile();

        $this->info('> success');
    }

    /**
     * Restart swoole jsonrpc server.
     */
    protected function restart()
    {
        $pid = $this->getPid();

        if ($this->isRunning($pid)) {
            $this->stop();
        }

        $this->start();
    }

    /**
     * Reload.
     */
    protected function reload()
    {
        $pid = $this->getPid();

        if (! $this->isRunning($pid)) {
            $this->error("Failed! There is no swoole_jsonrpc_server process running.");
            exit(1);
        }

        $this->info('Reloading swoole_jsonrpc_server...');

        $isRunning = $this->killProcess($pid, SIGUSR1);

        if (! $isRunning) {
            $this->error('> failure');
            exit(1);
        }

        $this->info('> success');
    }

    /**
     * Initialize command action.
     */
    protected function initAction()
    {
        $this->action = $this->argument('action');

        if (! in_array($this->action, ['start', 'stop', 'restart', 'reload'])) {
            $this->error("Invalid argument '{$this->action}'. Expected 'start', 'stop', 'restart' or 'reload'.");
            exit(1);
        }
    }

    /**
     * If Swoole process is running.
     *
     * @param int $pid
     * @return bool
     */
    protected function isRunning($pid)
    {
        if (! $pid) {
            return false;
        }

        Process::kill($pid, 0);

        return ! swoole_errno();
    }

    /**
     * Kill process.
     *
     * @param int $pid
     * @param int $sig
     * @param int $wait
     * @return bool
     */
    protected function killProcess($pid, $sig, $wait = 0)
    {
        Process::kill($pid, $sig);

        if ($wait) {
            $start = time();

            do {
                if (! $this->isRunning($pid)) {
                    break;
                }

                usleep(100000);
            } while (time() < $start + $wait);
        }

        return $this->isRunning($pid);
    }

    /**
     * Get pid.
     *
     * @return int|null
     */
    protected function getPid()
    {
        if ($this->pid) {
            return $this->pid;
        }

        $pid = null;
        $path = $this->getPidPath();

        if (file_exists($path)) {
            $pid = (int) file_get_contents($path);

            if (! $pid) {
                $this->removePidFile();
            } else {
                $this->pid = $pid;
            }
        }

        return $this->pid;
    }

    /**
     * Get Pid file path.
     *
     * @return string
     */
    protected function getPidPath()
    {
        return $this->laravel['config']->get('jsonrpc.server.options.pid_file');
    }

    /**
     * Remove Pid file.
     */
    protected function removePidFile()
    {
        if (file_exists($this->getPidPath())) {
            unlink($this->getPidPath());
        }
    }
}
