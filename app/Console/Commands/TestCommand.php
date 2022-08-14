<?php

namespace App\Console\Commands;

use App\Models\Domain;
use App\Models\Url;
use App\Services\RedisListService;
use App\Services\UrlParserService;
use App\Services\UrlService;
use Illuminate\Console\Command;

class TestCommand extends Command
{
    protected $signature = 'test';

    public function handle()
    {

        RedisListService::instance()->clearList('james');
        $list = RedisListService::instance()->getList('james');
        foreach ([
                     'dog', 'cat', 'snake', 'cow', 'rabbit'
                 ] as $key) {
            RedisListService::instance()->addToList('james', $key);
        }

        dump(RedisListService::instance()->getList('james'));
        RedisListService::instance()->removeFromList('james', 'snake');
        RedisListService::instance()->removeFromList('james', 'rabbit');
        dump(RedisListService::instance()->getList('james'));


    }
}
