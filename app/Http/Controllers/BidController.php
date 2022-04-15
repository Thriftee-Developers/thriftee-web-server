<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bid;
use App\Events\BidEvent;
use App\Models\Biddings;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Store;
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
                    $bidding->update(['claimer' => $req->customer]);
                    return ["success" => "success"];
                } else {
                    return ["error" => "Not enough bid amount."];
                }
            }
            else {
                if ($req->amount >= ($bidding->minimum)) {
                    $bid->save();
                    $bidding->update(['claimer' => $req->customer]);
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

        $result = Bid::select('bids.*')
            ->leftJoin('biddings','biddings.uuid','bids.bidding')
            ->where("customer", $req->customer)
            // ->where(function($query) {
            //     $query->where('biddings.status', 'on_going')
            //     ->orWhere('biddings.status', 'ended')
            //     ->orWhere('biddings.status', 'under_transaction');
            // })
            ->orderBy("date", "desc")
            ->get()
            ->groupBy("bidding");

        $bids = $result->toArray();
        $array = array();

        foreach($bids as $item) {

            $bidding = Biddings::where('uuid', $item[0]['bidding'])->first();

            $product = Product::where('uuid', $bidding->product)->first();

            $image = ProductImage::where('product', $product->uuid)
                ->orderBy('name','ASC')
                ->first();
            $product->image = $image->path;

            $store = Store::where('uuid', $product->store)->first();

            $highestBid = Bid::where('bidding',$bidding->uuid)
                ->orderBy('amount', 'DESC')
                ->first();


            $arr_item = [
                'bidding' => $bidding,
                'highest' => $highestBid,
                'product' => $product,
                'store' => $store,
                'bids' => $item
            ];

            $array[] = $arr_item;
        }

        return $array;

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

    function getCustomerBidStatus(Request $req)
    {
        $bidding = Biddings::where('uuid', $req->bidding)->first();
        $bidders = $this->getBidders($bidding->uuid);

        if($bidding->claimer == $req->customer)
        {
            return [
                "status" => "claimer"
            ];
        }
        else
        {
            $claimerIndex = array_search($bidding->claimer, $bidders);
            $customerIndex = array_search($req->customer, $bidders);

            if($claimerIndex != null)
            {
                if($claimerIndex > $customerIndex)
                {
                    return [
                        "status" => "lose"
                    ];
                }
                else
                {
                    return [
                        "status" => "claim_failed"
                    ];
                }
            }
            else
            {
                return [
                    "status" => "no_bid"
                ];
            }
        }

    }

    function getBidders($bidding)
    {
        $bids = Bid
            ::where('bidding', $bidding)
            ->orderBy('date', 'desc')
            ->get();
        $bidder = array();

        foreach ($bids as $bid) {
            if (!in_array($bid->customer, $bidder)) {
                array_push($bidder, $bid->customer);
            }
        }

        return $bidder;
    }
}
