<?php

namespace App\Services;

use App\Mails\RegisterUserMail;
use App\Mails\WelcomeUserRegistrationMail;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UserService
{
    /**
     * @param array $data
     * @param int|null $id
     * @return User
     * @throws ValidationException
     */
    public static function upsert(array $data, int|null $id = null)
    {
        if(!empty($id)) {
            $user = User::find($id);
            $data['id'] = $id;
        }

        Validator::make($data, [
            'name' => 'required',
            'email' => 'required_without:id|unique:users,email',
            'password' => 'required_without:id|min:6'
        ])->validate();

        DB::beginTransaction();

        if(!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        if(empty($user)) {

            $user = new User($data);
            $user->save();

            Mail::to($user->getAttribute("email"))->send(new WelcomeUserRegistrationMail($user));
            Mail::to($user->getAttribute("email"))->send(new RegisterUserMail($user));

        } else {
            $user->update($data);
        }

        DB::commit();

        return $user;
    }
}
