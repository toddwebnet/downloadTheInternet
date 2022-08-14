<?php

namespace App\Jobs;

use App\Services\UrlService;

class AddUrlJob extends BaseJob
{

    protected array $requiredKeys = ['url'];

    public function handle()
    {
        $url = $this->args['url'];
        $parentUrlId = isset($this->args['sourceUrlId']) ? $this->args['parentUrlId'] : null;
        $label = isset($this->args['label']) ? $this->args['label'] : null;
        UrlService::instance()->addUrl($url, $parentUrlId, $label);
    }
}
