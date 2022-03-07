<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Biddings;

class BiddingController extends Controller
{
    //
    function addBidding(Request $req){
        $bidding = new Biddings();
        // echo $req;
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
