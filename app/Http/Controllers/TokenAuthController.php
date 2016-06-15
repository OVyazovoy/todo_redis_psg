<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


use App\Http\Controllers\Controller;

use App\User;
use Illuminate\Support\Facades\Hash;
use  \Illuminate\Support\Facades\Session;
use  \Illuminate\Support\Facades\Redis;

class TokenAuthController extends Controller
{
    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $email = \htmlspecialchars($credentials['email']);
        $users = DB::select('select * from users where email = ? LIMIT 1', array($email));

        if (!isset($users)) {
            return response()->json(['user_not_found'], 401);
        }
        $password_true = Hash::check($credentials['password'], $users[0]->password);

        if ($password_true) {
            $token_key = bin2hex(json_encode($credentials) . time());
            Session::put('token_key', $token_key);

            $user['id'] = $users[0]->id;
            $user['name'] = $users[0]->name;
            $user['email'] = $users[0]->email;

            $redis = Redis::connection();
            $redis->set('token:'.$token_key, json_encode($user));

            return $redis->get('token:'.$token_key);
        }

        return response()->json(['wron_passwod'], 403);
    }

    public function getAuthenticatedUser()
    {
        $token_key = Session::get('token_key');
        if(!isset($token_key)){
            return response()->json(['user_not_found'], 401);
        }
        $redis = Redis::connection();
        $user = $redis->get('token:'.$token_key);
        if(!isset($user)){
            return response()->json(['user_not_found'], 404);
        }

        return $user;
    }

    public function register(Request $request)
    {
        $newuser = $request->all();
        $password = Hash::make($request->input('password'));

        $newuser['password'] = $password;

        return User::create($newuser);
    }
}
