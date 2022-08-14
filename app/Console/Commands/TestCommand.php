<?php

namespace App\Console\Commands;

use App\Models\Domain;
use App\Models\Url;
use App\Services\HtmlProcessService;
use App\Services\RedisListService;
use App\Services\UrlParserService;
use App\Services\UrlService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class TestCommand extends Command
{
    protected $signature = 'test';

    public function handle()
    {

        Artisan::call('migrate:refresh');
        Artisan::call('url:add', ['url' => 'http://www.infowars.com']);
        sleep(1);
        Artisan::call('url:pop');
        sleep(1);
        Artisan::call('url:proc');


    }
}
