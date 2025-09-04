<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Habit;
use App\Models\Entry;
use App\Services\DataService;
use App\Services\EntryService;
use App\Services\HabitService;
use App\Structs\ResponseCode;
use Illuminate\Http\Request;

class EntriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request) : mixed
    {
        try {

            $queryData = $request->all();
            $data = DataService::getInstance(Entry::class, $queryData)->getResult();

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
    public function get(Request $request, int $id)
    {
        try {

            $relations = $request->input("relations") ?? [];

            if(is_string($relations)) {
                $relations = json_decode($relations, TRUE);
            }

            $data = Entry::with($relations)->findOrFail($id);

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

            $habit = EntryService::upsert($request->all());

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
    public function update(Request $request, int $id)
    {
        try {

            $habit = EntryService::upsert($request->all(), $id);

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
    public function delete(int $id)
    {
        try {

            $habit = Entry::findOrFail($id);
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
