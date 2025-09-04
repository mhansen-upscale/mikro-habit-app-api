<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Device extends Model
{

    protected $guarded = [];

    protected $casts = [
        "last_seen_at" => "datetime",
        "user_id" => "integer",
    ];
}
