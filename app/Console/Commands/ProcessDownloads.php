<?php

namespace App\Console\Commands;

use App\Services\HtmlProcessService;
use App\Services\RedisListService;
use Illuminate\Console\Command;

class ProcessDownloads extends Command
{
    protected $signature = 'url:proc';

    public function handle()
    {
        RedisListService::instance()->clearList(RedisListService::PROCESSING);
        HtmlProcessService::instance()->scanForFiles();
    }
}
