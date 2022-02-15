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
        $store -> uuid = $req -> uuid;
        $store -> store_id = $req -> store_id;
        $store -> store_name = $req -> store_name;
        $store -> contact_no = $req -> contact_no;
        $store -> email = $req -> email;
        $store -> province = $req -> province;
        $store -> municipality = $req -> municipality;
        $store -> barangay = $req ->barangay;
        $store -> street = $req -> street;
        $store -> image_uri = '';
        $store -> password = '';
        $store -> status = 0;
        // -1 = terminated
        // 0 = pending(newly created), need to activate or add password
        // 1 = active

        //$store -> password = Hash::make($req -> input('password'));

        $error = "";

        if($this -> checkEmail($req -> email))
        {
            if($this -> checkContactNo($req -> contact_no))
            {
                $store -> save();
            }
            else
            {
                $error = "Contact number is already registered!";
            }
        }
        else
        {
            $error = "Email is already registered!";
        }

        return $error;
    }

    function checkEmail($email)
    {
        $store = Store::where('email', $email)->get();
        if(count($store) > 0) return false;
        else return true;
    }

    function checkContactNo($contact_no)
    {
        $store = Store::where('contact_no', $contact_no)->get();
        if(count($store) > 0) return false;
        else return true;
    }

    function deleteStore(Request $req)
    {
        $store = Store::where('email', $req->email)->get();
        return $store;
    }
}
