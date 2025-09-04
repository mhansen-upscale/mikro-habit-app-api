<?php

namespace App\Services;

use App\Mails\RegisterUserMail;
use App\Mails\WelcomeUserRegistrationMail;
use App\Models\HabitEntry;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class HabitEntryService
{
    /**
     * @param array $data
     * @param int|null $id
     * @return HabitEntry
     * @throws ValidationException
     */
    public static function upsert(array $data, int|null $id = null)
    {
        if(!empty($id)) {
            $habitEntry = HabitEntry::find($id);
            $data['id'] = $id;
        }

        Validator::make($data, [
            'habit_id' => 'required',
            'value' => 'required',
        ])->validate();

        DB::beginTransaction();

        if(empty($data['done_at'])) {
            $data['done_at'] = $habitEntry->done_at ?? date("Y-m-d");
        }

        if(empty($habitEntry)) {

            $habitEntry = new HabitEntry($data);
            $habitEntry->save();
        } else {
            $habitEntry->update($data);
        }

        DB::commit();

        return $habitEntry;
    }
}
