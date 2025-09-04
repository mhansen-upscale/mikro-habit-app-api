<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Habit;
use App\Models\HabitEntry;
use App\Services\DataService;
use App\Services\HabitEntryService;
use App\Services\HabitService;
use App\Structs\ResponseCode;
use Illuminate\Http\Request;

class HabitEntriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, int $id) : mixed
    {
        try {

            $queryData = $request->all();
            $queryData["perPage"] = 0;

            $extraFilter = [
                "container" => [
                    "type" => "where",
                    "queries" => [
                        [
                            "type" => "where",
                            "key" => "habit_id",
                            "criteria" => $id,
                            "operator" => "="
                        ]
                    ]
                ]
            ];

            $data = DataService::getInstance(HabitEntry::class, $queryData, $extraFilter)->getResult();

            return response([
                "data" => $data,
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

    /**
     * Display the specified resource.
     */
    public function get(Request $request, int $id,  int $entryId)
    {
        try {

            $relations = $request->input("relations") ?? [];

            if(is_string($relations)) {
                $relations = json_decode($relations, TRUE);
            }

            $data = HabitEntry::with($relations)->where("habit_id", $id)->findOrFail($entryId);

            return response([
                "data" => $data,
                "success" => true,
                "message" => NULL,
            ], ResponseCode::SUCCESS);

        } catch (\Exception $e) {

            return response([
                "data" => NULL,
                "success" => false,
                "error" => $e->getMessage(),
                "message" => trans("errors.show.default")
            ], ResponseCode::BAD_REQUEST);

        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function create(Request $request)
    {
        try {

            $habit = HabitEntryService::upsert($request->all());

            return response([
                "data" => $habit,
                "success" => true,
                "message" => trans("success.create.default"),
            ], ResponseCode::UPSERT);

        } catch (\Exception $e) {

            return response([
                "data" => NULL,
                "success" => false,
                "error" => $e->getMessage(),
                "message" => trans("errors.store.default")
            ], ResponseCode::BAD_REQUEST);

        }
    }

    /**
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function update(Request $request, int $entryId)
    {
        try {

            $habit = HabitEntryService::upsert($request->all(), $entryId);

            return response([
                "data" => $habit,
                "success" => true,
                "message" => trans("success.update.default"),
            ], ResponseCode::UPSERT);

        } catch (\Exception $e) {

            return response([
                "data" => NULL,
                "success" => false,
                "error" => $e->getMessage(),
                "message" => trans("errors.update.default")
            ], ResponseCode::BAD_REQUEST);

        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(int $entryId)
    {
        try {

            $habit = HabitEntry::findOrFail($entryId);
            $habit->forceDelete();

            return response([
                "data" => [],
                "success" => true,
                "message" => trans("success.destroy.default"),
            ], ResponseCode::SUCCESS);

        } catch (\Exception $e) {

            return response([
                "data" => NULL,
                "success" => false,
                "error" => $e->getMessage(),
                "message" => trans("errors.destory.default")
            ], ResponseCode::BAD_REQUEST);

        }
    }


}
