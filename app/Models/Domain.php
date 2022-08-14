<?php

namespace App\Models;

use App\Models\Traits\HasUuidPk;
use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    use HasUuidPk;

    protected $fillable = ['id','domain'];


}
