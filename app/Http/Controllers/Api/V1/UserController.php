<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;

class UserController extends Controller
{
    public function index()
    {
        $user = User::findOrFail(Auth::id());
        return response($user, 200);
    }

    public function update(Request $request)
    {
        $this->validate($request, [
            'name' => 'string|min:3|max:255',
            'timezone' => 'string|min:3|max:150',
        ]);

        $user = User::findOrFail(Auth::id());

        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('timezone')) {
            $user->timezone = $request->timezone;
        }

        $user->save();

        return response($user, 200);
    }
}
