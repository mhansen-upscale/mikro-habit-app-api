<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reminder extends Model
{

    protected $guarded = [];

    protected $casts = [
      "habit_id" => "integer",
      "hour" => "integer",
      "minute" => "integer",
      "days_mask" => "integer",
      "enabled" => "boolean",
    ];

}
