<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StoreBillingMethod;
use Illuminate\Support\Str;

class StoreBillingMethodController extends Controller
{
    function getAllStoreBilling()
    {
        $result = StoreBillingMethod::all();
        return $result;
    }

    function getStoreBilling(Request $req)
    {
        $result = StoreBillingMethod::where("uuid", $req->uuid)->get();
        return $result;
    }
    //
    function addStoreBilling(Request $req)
    {
        $storeBilling = new StoreBillingMethod();
        $storeBilling->uuid = Str::uuid();
        $storeBilling->store = $req->store;
        $storeBilling->description = $req->description;
        $storeBilling->account_name = $req->account_name;
        $storeBilling->account_no = $req->account_no;

        $storeBilling->save();
        return ["success" => "success"];
    }
}
