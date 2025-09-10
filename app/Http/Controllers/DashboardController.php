<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Habit;
use App\Services\DataService;
use App\Services\HabitService;
use App\Structs\ResponseCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request) : mixed
    {
        try {

            $totalHabitsCount = Habit::where("is_active", 1)->count();

            $currentOpenHabitsCount = Habit::where("is_active", 1)
                ->withCount('entries')
                ->havingRaw('entries_count < cycle_length')
                ->count();

            $currentCompletedHabitsCount = Habit::withCount('entries')
                ->havingRaw('entries_count = cycle_length')
                ->count();

            return response([
                "data" => [
                    [
                        "name" => trans("stats.current_habit_count"),
                        "value" => $currentOpenHabitsCount
                    ],
                    [
                        "name" => trans("stats.complete_habit_count"),
                        "value" => $currentCompletedHabitsCount
                    ],
                    [
                        "name" => trans("stats.percentage_open_habit_count"),
                        "value" => $currentOpenHabitsCount != 0 ? round($currentOpenHabitsCount / $totalHabitsCount * 100, 2) : 0
                    ],
                    [
                        "name" => trans("stats.percentage_complete_habit_count"),
                        "value" => $currentCompletedHabitsCount != 0 ? round($currentCompletedHabitsCount / $totalHabitsCount * 100, 2) : 0
                    ],
                ],
                "success" => true,
                "message" => NULL,
            ], ResponseCode::SUCCESS);

        } catch (\Exception $e) {

            return response([
                "data" => NULL,
                "success" => false,
                "error" => $e->getMessage(),
                "trace" => $e->getTrace(),
                "message" => trans("errors.index.default")
            ], ResponseCode::BAD_REQUEST);
        }
    }

}
