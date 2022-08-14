<?php

namespace App\Console\Commands;

use App\Jobs\DownloadUrlJob;
use App\Models\Url;
use App\Services\QueueService;
use App\Services\RedisListService;
use Illuminate\Console\Command;

class PopUrlQueue extends Command
{
    protected $signature = 'url:pop';

    public function handle()
    {


        foreach (Url::queuePopGet() as $url) {
            if (!RedisListService::instance()->isInList(RedisListService::QUEUED, $url->id)) {
                QueueService::instance()->sendToQueue(
                    DownloadUrlJob::class,
                    ['urlId' => $url->id],
                    'downloads'
                );
            }
        }
    }
}
