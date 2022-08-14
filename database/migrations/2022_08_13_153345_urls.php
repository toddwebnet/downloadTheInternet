<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('domains', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('domain', 512);
            $table->timestamps();
            $table->index('domain', 'idx_domains_domain');
        });

        Schema::create('urls', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('domain_id')->references('id')->on('domains');
            $table->string('url', 2048);
            $table->boolean('is_valid')->default(false);
            $table->boolean('is_skipped')->default(false);
            $table->timestamp('last_refreshed')->nullable();
            $table->timestamps();
        });

        Schema::create('url_downloads', function (Blueprint $table) {
            $table->id('id');
            $table->foreignUuid('url_id')->references('id')->on('urls');
            $table->string('content_url', 2048);
            $table->json('content');
            $table->timestamps();
        });

        Schema::create('url_links', function (Blueprint $table) {
            $table->id('id');
            $table->foreignUuid('url_id')->references('id')->on('urls');
            $table->foreignUuid('parent_url_id')->references('id')->on('urls');
            $table->json('labels');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('url_links');
        Schema::dropIfExists('url_downloads');
        Schema::dropIfExists('urls');
        Schema::dropIfExists('domains');
    }
};
