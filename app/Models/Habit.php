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
      "completed" => "boolean",
      "user_id" => "integer",
    ];

    protected $appends = [
        "progress_bar_value",
        "has_progress_bar_value",
        "done_today",
        "streaks",
        "completed"
    ];

    /**
     * @return HasMany
     */
    public function entries() : HasMany
    {
        return $this->hasMany(Entry::class);
    }

    public function getProgressBarValueAttribute(): float
    {
        $entries = $this->entries()->count();
        return $entries == 0 ? 0 : round($entries / $this->getAttribute("cycle_length"), 2);
    }

    public function getCompletedAttribute(): bool
    {
        return $this->entries()->count() == $this->getAttribute("cycle_length");
    }

    public function getHasProgressBarValueAttribute(): bool
    {
        $entries = $this->entries()->count();
        return !($entries == 0);
    }

    public function getDoneTodayAttribute(): bool
    {
        $today = date("Y-m-d");
        $entryToday = $this->entries()->where("done_at", $today)->count();

        return $entryToday > 0;
    }

    public function getStreaksAttribute(): array
    {
        $entries = $this->entries()->get();

        $current = 0;
        $max = 0;

        $prev = null;

        foreach ($entries as $entry) {

            if ($prev && date('Y-m-d', strtotime($prev.' +1 day')) == strtotime($entry->done_at)) {
                $current++;
            } else {
                $current = 1;
            }

            $max = max($max, $current);
            $prev = $entry->done_at;
        }

        return [
            "current" => $current,
            "max" => $max,
        ];
    }

}
