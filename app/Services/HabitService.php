<?php

namespace App\Services;

use App\Mails\RegisterUserMail;
use App\Mails\WelcomeUserRegistrationMail;
use App\Models\Habit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class HabitService
{
    /**
     * @param array $data
     * @param int|null $id
     * @return User
     * @throws ValidationException
     * @throws \Exception
     */
    public static function upsert(array $data, int|null $id = null)
    {
        if(!empty($id)) {
            $habit = Habit::find($id);
            $data['id'] = $id;
        }

        Validator::make($data, [
            'name' => 'required',
            'user_id' => 'required_without:id|exists:users,id',
            'unit' => 'required',
            'target_min' => 'required',
            'is_active' => 'sometimes|integer',
            'cycle_length' => 'sometimes|integer',
            'cycle_started_at' => 'sometimes|date',
            'cycle_success_threshold' => 'sometimes|integer',
            'grace_total' => 'sometimes|integer',
            'grace_used' => 'sometimes|integer'
        ])->validate();

        $user = User::find($data['user_id']);
        $habits = $user->habits()->get();

        if(count($habits->where("completed", false)) >= 5) {
            throw new \Exception("user.habits.too_many");
        }

        if(empty($data['cycle_length'])) {
            $data['cycle_length'] = $habit->cycle_length ?? 21;
        }

        if(empty($data['cycle_started_at'])) {
            $data['cycle_started_at'] = $habit->cycle_started_at ?? date("Y-m-d");
        }

        if(empty($data['cycle_success_threshold'])) {
            $data['cycle_success_threshold'] = $habit->cycle_success_threshold ?? 18;
        }

        if(empty($data['grace_total'])) {
            $data['grace_total'] = $habit->grace_total ?? 2;
        }

        if(empty($data['grace_used'])) {
            $data['grace_used'] = $habit->grace_used ?? 0;
        }

        if(empty($data['is_active'])) {
            $data['is_active'] = $habit->is_active ?? 1;
        }


        DB::beginTransaction();

        if(empty($habit)) {

            $habit = new Habit($data);
            $habit->save();

            $reminderData = [
                "habit_id" => $habit->id,
                "hour" => 13,
                "minute" => 30,
                "days_mask" => 127,
                "enabled" => 1
            ];

            ReminderService::upsert($reminderData);

        } else {
            $habit->update($data);
        }

        DB::commit();

        return $habit;
    }
}
