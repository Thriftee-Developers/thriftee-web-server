<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StoreBillingMethod;
use Illuminate\Support\Str;

class StoreBillingMethodController extends Controller
{
    function getAllStoreBilling(){
        $result = StoreBillingMethod::all();
        return $result;
    }

    function getStoreBilling(Request $req){
        $result = StoreBillingMethod::where("uuid",uuid)->get();
        return $result;
    }
    //
    function addStoreBilling(Request $req){
        $storeBilling = new StoreBilling();
        $storeBilling->uuid = Str::uuid();
        $storeBiling->store = $req->store;
        $storeBiling->description=$req->description;
        $storeBiling->account_name=$req->account_name;
        $storeBilling->account_no=$req->account_no;
        
        $storeBiling->save();
    }


}
