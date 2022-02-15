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
        $store -> store_id = $req -> input('store_id');
        $store -> store_name = $req -> input('store_name');
        $store -> contact_no = $req -> input('contact_no');
        $store -> email = $req -> input('email');
        $store -> province = $req -> input('province');
        $store -> municipality = $req -> input('municipality');
        $store -> barangay = $req -> input('barangay');
        $store -> street = $req -> input('street');
        $store -> image_uri = '';
        $store -> password = '';
        //$store -> password = Hash::make($req -> input('password'));

        $store -> save();
        return true;
    }
}
