<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Entry extends Model
{

    protected $guarded = [];

    protected $casts = [
      "done_at" => "date",
      "value" => "integer"
    ];
}
