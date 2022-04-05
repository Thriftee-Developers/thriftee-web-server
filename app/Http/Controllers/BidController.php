<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bid;
use App\Events\BidEvent;
use App\Models\Biddings;
use Illuminate\Support\Str;

class BidController extends Controller
{
    //

    function syncBid(Request $req)
    {
        event(new BidEvent($req->bidding, $req->customer, $req->amount, $req->noOfBid));
        return "success";
    }

    function addBid(Request $req)
    {
        $bid = new Bid();
        $bid->uuid = Str::uuid();
        $bid->bidding = $req->bidding;
        $bid->customer = $req->customer;
        $bid->amount = $req->amount;
        $bid->date = $req->date;
        $result = Biddings::where("uuid", $req->bidding)->first();
        $highestBid = $this->getHighestBidByBidding($req);
        if ($highestBid != "") {
            if ($req->amount >= ($highestBid->amount + $result->increment)) {
                $bid->save();
                return ["success" => "success"];
            } else {
                return ["error" => "Not enough bid amount."];
            }
        } else {
            if ($req->amount >= ($result->minimum)) {
                $bid->save();
                return ["success" => "success"];
            } else {
                return ["error" => "Bid Failed! Not Enough bid."];
            }
        }
    }

    function getBid(Request $req)
    {
        $result = Bid::where("uuid", $req->uuid)->first();
        return $result;
    }

    function getHighestBidByBidding(Request $req)
    {
        $result = Bid::where("bidding", $req->bidding)->orderBy("amount", "desc")->first();
        return $result;
    }

    function getTotalNumberOfBids(Request $req)
    {
        $result = Bid::where("bidding", $req->bidding)->get()->count();
        return $result;
    }

    function getAllBidByCustomer(Request $req)
    {
        $result = Bid::join("biddings", "bids.bidding", "=", "biddings.uuid")
            ->join("products", "biddings.product", "=", "products.uuid")
            ->where("customer", $req->customer)->orderBy("date", "desc")->get()->groupBy("bidding");
        return $result->toArray();
    }

    function getBidByProduct(Request $req)
    {
        $result = Bid::join("biddings", "bids.bidding", "=", "biddings.uuid")
            ->where("product", $req->product)
            ->get();
        return $result;
    }

    function getHighestBidByProduct(Request $req)
    {
        $result = Bid::join("biddings", "bids.bidding", "=", "biddings.uuid")
            ->where("product", $req->product)
            ->orderBy("amount", "desc")
            ->first();
        return $result;
    }

    function getBidByProductAndCustomer(Request $req)
    {
        $result = Bid::join("biddings", "bids.bidding", "=", "biddings.uuid")
            ->where("product", $req->product)
            ->where("customer", $req->customer)
            ->orderBy("amount", "desc")
            ->get();
        return $result;
    }

    function getBidByBiddingAndCustomer(Request $req)
    {
        $result = Bid::where("bidding", $req->bidding)
            ->where("customer", $req->customer)
            ->orderBy("amount", "desc")
            ->get();
        return $result;
    }
}
