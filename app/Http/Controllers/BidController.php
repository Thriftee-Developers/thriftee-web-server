<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bid;
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

        $bid->save();
        return ["success"=>"Bid added successfully."];
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
            ->get();
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
