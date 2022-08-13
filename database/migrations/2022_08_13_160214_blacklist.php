<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('blacklist', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('domain', 2048);
            $table->timestamps();
        });

        foreach ([
                     'amazon.com',
                     'walmart.com',
                     'wikipedia.com',
                     'sina.com.cn',
                     'apple.com',
                     'sohu.com',
                     'bing.com',
                     'google.com',
                     'gmail.com',
                     'twitter.com',
                     'facebook.com',
                     'taoboa.com',
                     'ask.com',
                     'baidu.com',
                     'microsoft.com',
                     'qq.com',
                     'live.com',
                     'wikipedia.com',
                     'wikipedia.org',
                     'archive.org',

                 ] as $host) {
            $sql = "insert into blacklists (host) values(?)";
            $params = [trim(strtolower($host))];
            DB::update($sql);

        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('blacklist');
    }
};
