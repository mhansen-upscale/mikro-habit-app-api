<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HabitEntry extends Model
{

    protected $guarded = [];

    protected $casts = [
      "done_at" => "date",
      "value" => "integer"
    ];
}
