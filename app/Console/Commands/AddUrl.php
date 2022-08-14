<?php

namespace App\Console\Commands;

use App\Jobs\AddUrlJob;
use App\Services\QueueService;
use App\Services\UrlParserService;
use Illuminate\Console\Command;

class AddUrl extends Command
{
    protected $signature = 'url:add {url}';

    public function handle()
    {
        QueueService::instance()->sendToQueue(
            AddUrlJob::class, $this->arguments(), 'url'
        );

    }
}
