<?php

namespace App\Services;

use App\Mails\RegisterUserMail;
use App\Mails\WelcomeUserRegistrationMail;
use App\Models\Entry;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class EntryService
{
    /**
     * @param array $data
     * @param int|null $id
     * @return Entry
     * @throws ValidationException
     */
    public static function upsert(array $data, int|null $id = null)
    {
        if(!empty($id)) {
            $entry = Entry::find($id);
            $data['id'] = $id;
        }

        Validator::make($data, [
            'habit_id' => 'required',
            'value' => 'required',
        ])->validate();

        DB::beginTransaction();

        if(empty($data['done_at'])) {
            $data['done_at'] = $entry->done_at ?? date("Y-m-d");
        }

        if(empty($entry)) {

            $entry = new Entry($data);
            $entry->save();
        } else {
            $entry->update($data);
        }

        DB::commit();

        return $entry;
    }
}
