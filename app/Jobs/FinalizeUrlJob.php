<?php

namespace App\Jobs;

use App\Services\HtmlProcessService;
use App\Services\RedisListService;

class FinalizeUrlJob extends BaseJob
{
    protected array $requiredKeys = ['urlId'];
    public function __construct($args)
    {
        $urlId = $args['urlId'];
        RedisListService::instance()->addToList(RedisListService::FINALIZING, $urlId);
        parent::__construct($args);
    }
    public function handle()
    {
        HtmlProcessService::instance()->finalizeUrl($this->args['urlId']);
    }
}
