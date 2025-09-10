<?php

namespace App\Http\Middleware;

use App\Models\Core\Document;
use App\Models\Core\Organisation;
use App\Models\Core\OrganisationMember;
use App\Models\Core\PoNumber;
use App\Models\Core\PoSubNumber;
use App\Models\Core\User;
use App\Models\Entry;
use App\Models\ERP\Order;
use App\Models\ERP\OrderItem;
use App\Models\ERP\Product;
use App\Models\ERP\Vendor;
use App\Models\Habit;
use App\Models\Reminder;
use App\Structs\ResponseCode;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OwnDataMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userId = $request->user()->id || $request->input("user_id") ?? null;

        if(!empty($request->user()) && !empty($userId)) {

            $userId = $request->user()->id;

            Habit::addGlobalScope('ownDataScope', function (Builder $builder) use ($userId) {
                $builder->where('user_id', $userId);
            });

            $habitIds = Habit::get()->pluck('id')->toArray();

            Entry::addGlobalScope('ownDataScope', function (Builder $builder) use ($habitIds) {
                $builder->wherein('habit_id', $habitIds);
            });

            Reminder::addGlobalScope('ownDataScope', function (Builder $builder) use ($habitIds) {
                $builder->wherein('habit_id', $habitIds);
            });


        } else {

            return response([
                "data" => NULL,
                "success" => false,
                "message" => trans("errors.middleware.not_allowed")
            ], ResponseCode::NOT_ALLOWED);
        }

        return $next($request);
    }
}
