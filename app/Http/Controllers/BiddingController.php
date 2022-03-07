<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Biddings;

class BiddingController extends Controller
{
    function getAllBidding(){
        $result =  Biddings::all();
        return $result;
    }

    function getBidding(Request $req){
        $result = Biddings::where("uuid", $req->uuid)->first();
        return $result;
    }

    function getAllBiddingByProduct(Request $req){
        $result = Biddings::where("product", $req->product)->get();
        return $result;
    }
    
    function addBidding(Request $req){
        $bidding = new Biddings();
        $bidding->uuid=$req->uuid;
        $bidding->product=$req->product;
        $bidding->minimum=$req->minimum;
        $bidding->increment=$req->increment;
        $bidding->claim=$req->claim;
        $bidding->start_time=$req->start_time;
        $bidding->end_time=$req->end_time;
        $bidding->created_at=$req->created_at;
        $bidding->status=$req->status;
        
        $bidding->save();
        return "success";
    }

    function updateBidding(Request $req){
        $bidding = Biddings::where('uuid',$req->uuid)->first();
        
            $result = $bidding->update([
                "minimum" => $req->minimum,
                "increment" => $req->increment,
                "claim" => $req->claim,
                "start_time" => $req->start_time,
                "end_time" => $req->end_time,
                "created_at" => $req->created_at,
                "status" => $req->status,
            ]);
        
    }
}
