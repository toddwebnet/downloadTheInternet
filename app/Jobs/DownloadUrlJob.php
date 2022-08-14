<?php

namespace App\Jobs;

use App\Services\RedisListService;
use App\Services\UrlService;

class DownloadUrlJob extends BaseJob
{
    protected array $requiredKeys = ['urlId'];
    public function __construct($args)
    {
        $urlId = $args['urlId'];
        RedisListService::instance()->addToList(RedisListService::QUEUED, $urlId);
        RedisListService::instance()->addToList(RedisListService::DOWNLOADING, $urlId);
        parent::__construct($args);
    }

    public function handle()
    {
        $urlId = $this->args['urlId'];
        UrlService::instance()->getAndSaveUrl($urlId);
    }
}
