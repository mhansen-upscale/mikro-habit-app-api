<?php

namespace App\Services;

use App\Mails\RegisterUserMail;
use App\Mails\WelcomeUserRegistrationMail;
use App\Models\Device;
use App\Models\Reminder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DeviceService
{
    /**
     * @param array $data
     * @param int|null $id
     * @return Device
     * @throws ValidationException
     */
    public static function upsert(array $data, int|null $id = null)
    {
        if(!empty($id)) {
            $device = Device::find($id);
            $data['id'] = $id;
        }

        Validator::make($data, [
            'user_id' => 'required',
            'fcm_token' => 'required_without:id|unique:devices,fcm_token,'. $id,
            'platform' => 'required',
            'last_seen_at' => 'sometimes|nullable|date'
        ])->validate();

        DB::beginTransaction();

        if(empty($data['last_seen_at'])) {
            $data['last_seen_at'] = date("Y-m-d H:i:s");
        }

        if(empty($device)) {

            $device = new Device($data);
            $device->save();

        } else {
            $device->update($data);
        }

        DB::commit();

        return $device;
    }
}
