<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Biddings;
use DateTime;
use Illuminate\Support\Str;

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

    function getBiddingByProduct(Request $req){
        $result = Biddings::where("product", $req->product)->get();
        return $result;
    }

    function getLatestBiddingByProduct(Request $req){
        $result = Biddings::
            where("product", $req->product)
            ->orderBy('created_at','desc')
            ->first();
        return $result;
    }


    function getBiddingByStore(Request $req){
        $result = Biddings::join("products","biddings.product", "=", "products.uuid")->where("store", $req->uuid)->get();
        return $result;
    }

    function addBidding(Request $req){
        $bidding = new Biddings();
        $bidding->uuid=Str::uuid();
        $bidding->product=$req->product;
        $bidding->minimum=$req->minimum;
        $bidding->increment=$req->increment;
        $bidding->claim=$req->claim;
        $bidding->created_at=$req->created_at;
        $bidding->start_time=$req->start_time;
        $bidding->end_time=$req->end_time;
        $bidding->status=0;

        $bidding->save();
        return ["success" => "success"];
    }

    function updateBidding(Request $req){
        $bidding = Biddings::where('uuid',$req->uuid)->first();

            $result = $bidding->update([
                "minimum" => $req->minimum,
                "increment" => $req->increment,
                "claim" => $req->claim,
                "start_time" => $req->start_time,
                "end_time" => $req->end_time,
                "status" => $req->status,
            ]);
        return ["success" => "success"];
    }

    function getBiddingWinner(Request $req) {
        $bidding = Biddings::where('uuid',$req->bidding)->first();
        $start_time = strtotime($bidding->start_time);
        $end_time = strtotime($bidding->end_time);
        $current_time = strtotime(date("y-m-d H:i:s"));

        if($current_time > $end_time) {
            return [
                "status" => "ended",
                "start" => $start_time,
                "end" => $end_time,
                "current" => $current_time
            ];
        }
        else {
            if($current_time >= $start_time) {
                return [
                    "status" => "on_going",
                    "start" => $start_time,
                    "end" => $end_time,
                    "current" => $current_time
                ];
            }
            else {
                return [
                    "status" => "waiting",
                    "start" => $start_time,
                    "end" => $end_time,
                    "current" => $current_time
                ];
            }
        }

    }
}
