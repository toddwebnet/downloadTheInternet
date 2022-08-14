<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UrlDownload extends Model
{
    protected $fillable = ['url_id', 'content_url', 'content'];
}
