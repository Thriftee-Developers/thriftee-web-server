<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bid;
use App\Models\Biddings;
use Illuminate\Support\Str;

class BidController extends Controller
{
    //

    function addBid(Request $req){
        $bid = new Bid();
        $bid->uuid=Str::uuid();
        $bid->bidding=$req->bidding;
        $bid->customer=$req->customer;
        $bid->amount=$req->amount;
        $bid->date=$req->date;
        $result = Biddings::where("uuid",$req->bidding)->first();
        $highestBid = Bid::orderBy("amount","desc")->first()->amount;
        if($highestBid!=""){
            if($req->amount >= ($highestBid + $result->increment)){
                $bid->save();
                return ["success"=>"success"];
            }else{
                return ["error"=>"Not enough bid amount."];
            }
        }else{
            if($req->amount >= ($result->minimum)){
                $bid->save();
                return ["success"=>"success"];
            }else{
                return ["error" => "Bid Failed! Not Enough bid."];
            }
        }
    }
            
    function getAllBidByCustomer(Request $req){
        $result = Bid::where("customer",$req->customer)->get();
        return $result;
    }

    function getBidByProduct(Request $req){
        $result = Bid::join("biddings", "bids.bidding", "=", "biddings.uuid")
            ->where("product", $req->product)
            ->get();
        return $result;
    }

    function getHighestBidByProduct(Request $req){
        $result = Bid::join("biddings", "bids.bidding", "=", "biddings.uuid")
            ->where("product", $req->product)
            ->orderBy("amount","desc")
            ->first();
        return $result; 
    }

    function getBidByProductAndCustomer(Request $req){
        $result = Bid::join("biddings", "bids.bidding", "=", "biddings.uuid")
            ->where("product", $req->product)
            ->where("customer", $req->customer)
            ->get();
        return $result;
    }

}
