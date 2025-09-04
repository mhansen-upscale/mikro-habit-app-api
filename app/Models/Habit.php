<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Habit extends Model
{

    protected $guarded = [];

    protected $casts = [
      "cycle_length" => "integer",
      "cycle_success_threshold" => "integer",
      "cycle_started_at" => "date",
      "grace_total" => "integer",
      "grace_used" => "integer",
      "target_min" => "integer",
      "is_active" => "boolean",
      "user_id" => "integer",
    ];

    /**
     * @return HasMany
     */
    public function entries() : HasMany
    {
        return $this->hasMany(Entry::class);
    }
}
