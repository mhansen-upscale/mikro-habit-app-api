<?php

namespace App\Services;

use App\Mails\RegisterUserMail;
use App\Mails\WelcomeUserRegistrationMail;
use App\Models\Reminder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ReminderService
{
    /**
     * @param array $data
     * @param int|null $id
     * @return Reminder
     * @throws ValidationException
     */
    public static function upsert(array $data, int|null $id = null)
    {
        if(!empty($id)) {
            $reminder = Reminder::find($id);
            $data['id'] = $id;
        }

        Validator::make($data, [
            'habit_id' => 'required',
            'hour' => 'required',
            'minute' => 'required',
            'days_mask' => 'required',
            'enabled' => 'sometimes|numeric',
        ])->validate();

        DB::beginTransaction();

        if(empty($data['enabled'])) {
            $data['enabled'] = $reminder->enabled ?? 1;
        }

        if(empty($reminder)) {

            $reminder = new Reminder($data);
            $reminder->save();

        } else {
            $reminder->update($data);
        }

        DB::commit();

        return $reminder;
    }
}
