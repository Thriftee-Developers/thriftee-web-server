<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Store;
use Illuminate\Support\Facades\Hash;

class StoreController extends Controller
{
    //
    function getStores()
    {
        return "Stores";
    }

    function addStore(Request $req)
    {
        $store = new Store;
        $store -> uuid = $req -> input('uuid');
        $store -> store_name = $req -> input('store_name');
        $store -> image_uri = $req -> input('image_uri');
        $store -> description = $req -> input('description');

        $store -> username = $req -> input('username');
        $store -> password = Hash::make($req -> input('password'));
        $store -> save();
        return $store;
    }
}
