<?php

namespace App\Models;

use App\Models\Traits\HasUuidPk;
use Illuminate\Database\Eloquent\Model;

class Url extends Model
{
    use HasUuidPk;

    protected $fillable = ['id', 'url', 'domain_id'];

    public function invalidate()
    {
        $this->is_valid = false;
        $this->save();
    }

    public static function queuePopGet()
    {
        return self::whereNull('last_refreshed')
            ->where('is_skipped', 0)
            ->take(10)
            ->get();
    }
}
