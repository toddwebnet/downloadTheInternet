<?php

namespace App\Services;

use App\Traits\Singleton;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\DispatchesJobs;

class QueueService
{

    use DispatchesJobs, Queueable, Singleton;

    const DEFAULT_QUEUE = 'default';

    const AVAILABLE_QUEUES = [
        'default', 'urls', 'downloads', 'procs'
    ];

    public function sendToQueue($class, $args = null, $queue = null, $runNow = false)
    {
        if ($queue == null) {
            $queue = self::DEFAULT_QUEUE;
        }

        $job = new $class($args);

        if ($runNow === true) {
            $this->dispatchSync($job->onQueue($queue));
        } else {
            $this->dispatch($job->onQueue($queue));
        }
    }

}
