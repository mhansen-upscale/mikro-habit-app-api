<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {

    $inputs = array_merge(
        $request->route()->parameters(),
        $request->query()
    );

    $validated = validator($inputs, [
        'id' => 'required|integer|exists:users,id',
        'hash' => 'required|string',
        'expires' => 'required|string',
        'signature' => 'required|string',
    ])->validate();

    if (! URL::hasValidSignature($request)) {
        abort(403, 'Invalid or expired verification link.');
    }

    $info = Carbon::parse((int)$validated['expires']);
    if($info->isPast()) {
        abort(403, 'Expired verification link.');
    }

    $user = User::findOrFail($validated['id']);
    $user->markEmailAsVerified();

    return view("welcome");

})->name('verify');


