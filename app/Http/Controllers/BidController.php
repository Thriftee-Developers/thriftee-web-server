<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bid;
use App\Events\BidEvent;
use App\Models\Biddings;
use App\Models\ProductImage;
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

        //Create Bid Data
        $bid = new Bid();
        $bid->uuid = Str::uuid();
        $bid->bidding = $req->bidding;
        $bid->customer = $req->customer;
        $bid->amount = $req->amount;
        $bid->date = $req->date;


        //Check bidding Status

        $biddingCtrl = new BiddingController();
        $biddingCtrl->checkBiddingStatus($req->bidding);
        $bidding = Biddings::where("uuid", $req->bidding)->first();

        if($bidding->status == "on_going") {
            //Check Highest bid
            $highestBid = $this->getHighestBidByBidding($req);

            if ($highestBid != "") {
                if ($req->amount >= ($highestBid->amount + $bidding->increment)) {
                    $bid->save();
                    return ["success" => "success"];
                } else {
                    return ["error" => "Not enough bid amount."];
                }
            } else {
                if ($req->amount >= ($bidding->minimum)) {
                    $bid->save();
                    return ["success" => "success"];
                } else {
                    return ["error" => "Bid Failed! Not Enough bid."];
                }
            }
        }
        else {
            return ["error" => "This bidding has already ended."];
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
        $result = Bid::select([
                'bids.*'
            ])

            ->where("customer", $req->customer)
            ->orderBy("date", "desc")
            ->get()
            ->groupBy("bidding");

        $bids = $result->toArray();
        $array = array();

        foreach($bids as $item) {
            $array[$item]->bids = $item;

            $bidding = Biddings::where('uuid', $item[0]->bidding);
            $array[$item]->bidding = $bidding;

            $product = Biddings::where('uuid', $bidding->product);
            $image = ProductImage::where('product', $product->uuid)
                ->orderBy('name','ASC')
                ->first();
            $product->image = $image->path;
            $array[$item]->product = $product;

            $store = Biddings::where('uuid', $product->store);
            $array[$item]->store = $store;

        }
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
