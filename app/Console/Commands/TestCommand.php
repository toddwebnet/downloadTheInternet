<?php

namespace App\Console\Commands;

use App\Models\Domain;
use App\Models\Url;
use App\Services\UrlParserService;
use App\Services\UrlService;
use Illuminate\Console\Command;

class TestCommand extends Command
{
    protected $signature = 'test';

    public function handle()
    {

      $stream = UrlService::instance()->getAndSaveUrl('186461b4-0531-4664-b4bc-809d6b455538');



    }
}
