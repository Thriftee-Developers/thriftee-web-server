<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;

class UserController extends Controller
{
    //
    function addUser(Request $req)
    {
        $user = new User();
        $user->uuid = Str::uuid();
        $user->username = $req->username;
        $user->password = $req->password;

        if ($this->checkCredentials($req->username)) {
            $user->save();
            return ["success" => "success"];
        } else {
            return ["error" => "Username is already registered!"];
        }
    }

    function checkCredentials($username)
    {
        $customer = User::where('username', $username)->get();
        if (count($customer) > 0) return false;
        else return true;
    }

    function loginUser(Request $req)
    {
        $user = User::where('username', $req->username)->first();
        if ($user) {
            if ($req->password == $user->password) {
                return $user;
            } else {
                return ["error" => "Incorrect username or password!"];
            }
        } else {
            return ["error" => "There's no account associated with this username"];
        }
    }

    function deleteUser(Request $req)
    {
        $result = User::where('uuid', $req->uuid)->delete();
        return $result;
    }
}
