<?php

namespace App\Http\Controllers;

use App\Models\Bid;
use Illuminate\Http\Request;
use App\Models\Biddings;
use App\Models\Transaction;
use DateTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BiddingController extends Controller
{
    function getAllBidding()
    {
        $this->checkWaitingBiddings();
        $this->checkActiveBiddings();
        $result =  Biddings::all();
        return $result;
    }

    function getBidding(Request $req)
    {
        $this->checkBiddingStatus($req->uuid);
        $result = Biddings::where("uuid", $req->uuid)->first();
        return $result;
    }

    function getSpecificBiddingData()
    {
        $result = Biddings::join("products", "products.uuid", "=", "biddings.product")
            ->join("stores", "stores.uuid", "=", "products.store")
            ->select("biddings.uuid as bidding", "products.product_id", "products.name", "stores.store_name")
            ->get();
        return $result;
    }

    function getOnGoingBiddings()
    {
        $this->checkWaitingBiddings();
        $this->checkActiveBiddings();
        $result = Biddings::where("status","on_going")->get();
        return $result;
    }

    function getWaitingBiddings()
    {
        $this->checkWaitingBiddings();
        $this->checkActiveBiddings();
        $result = Biddings::where("status","waiting")->get();
        return $result;
    }

    function getBiddingByProduct(Request $req)
    {
        $bidding = Biddings::where("product", $req->product)->first();
        $this->checkBiddingStatus($bidding->uuid);
        $result = Biddings::where("uuid", $bidding->uuid)->first();

        return $result;
    }

    function getLatestBiddingByProduct(Request $req)
    {
        $bidding = Biddings::where("product", $req->product)
            ->orderBy('created_at', 'desc')
            ->first();

        $this->checkBiddingStatus($bidding->uuid);
        $result = Biddings::where("uuid", $bidding->uuid)->first();

        return $result;
    }


    function getBiddingsByStore(Request $req)
    {
        $this->checkWaitingBiddings();
        $this->checkActiveBiddings();
        $result = Biddings::join("products", "biddings.product", "=", "products.uuid")->where("store", $req->uuid)->get();
        return $result;
    }

    function addBidding(Request $req)
    {
        $bidding = new Biddings();
        $bidding->uuid = Str::uuid();
        $bidding->product = $req->product;
        $bidding->minimum = $req->minimum;
        $bidding->increment = $req->increment;
        $bidding->claim = $req->claim;
        $bidding->created_at = $req->created_at;
        $bidding->start_time = $req->start_time;
        $bidding->end_time = $req->end_time;
        $bidding->status = "waiting";

        $bidding->save();
        return ["success" => "success"];
    }

    function updateBidding(Request $req)
    {
        $bidding = Biddings::where('uuid', $req->uuid)->first();

        $result = $bidding->update([
            "minimum" => $req->minimum,
            "increment" => $req->increment,
            "claim" => $req->claim,
            "start_time" => $req->start_time,
            "end_time" => $req->end_time,
            "status" => $req->status,
        ]);
        return ["success" => $result];
    }

    function getPopularBidding()
    {
        $this->checkWaitingBiddings();
        $this->checkActiveBiddings();

        $bidding = DB::select(
            "SELECT
                biddings.*,
                products.product_id,
                products.name,
                products.description,
                products.store,
                stores.store_name,
                productimages.path,
                mBids.highest as highest_bid,
                Count(bids.uuid) as bid_count
            FROM biddings

            LEFT JOIN bids
            ON biddings.uuid = bids.bidding

            INNER JOIN products
            ON biddings.product = products.uuid

            INNER JOIN stores
            ON products.store = stores.uuid

            LEFT JOIN (
                SELECT path, product, MIN(name) AS name FROM productimages GROUP BY product
            ) productimages
            ON productimages.product = products.uuid

            LEFT OUTER JOIN (
                SELECT bidding, MAX(amount) AS highest
                FROM bids
                GROUP BY bidding
            ) mBids
            ON mBids.bidding = biddings.uuid

            WHERE biddings.status = 'on_going'

            GROUP BY biddings.uuid
            ORDER BY bid_count DESC"
        );

        return $bidding;
    }

    //if bidder has 2 bids it counts as 1
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

    function getBiddingWinner(Request $req)
    {
        $bidding = Biddings::where([
            ['uuid', $req->bidding],
            ['status', '<>', -1]
        ])->first();

        if (!$bidding) {
            return [
                "status" => "no_claim"
            ];
        }



        $start_time = strtotime($bidding->start_time);
        $end_time = strtotime($bidding->end_time);
        $current_time = strtotime(date("y-m-d H:i:s"));



        if ($current_time > $end_time) {

            $transaction = Transaction
                ::join('bids', 'bids.uuid', '=', 'transactions.bid')
                ->where([
                    ['bids.bidding', $req->bidding],
                    ['transactions.status', '<>', 'cancelled'],
                ])->first();

            $hoursdiff = ($current_time - $end_time) / 3600;
            //48 hrs = 2 days
            $winnerIndex = $hoursdiff / 48;

            $bidder = $this->getBidders($req->bidding);

            $bids = Bid::where('bidding', $req->bidding)
                ->orderBy('date', 'desc')
                ->get()
                ->groupBy('customer');

            if ($transaction) {
                return [
                    "status" => "claiming",
                    "claimer" => $transaction->customer,
                    "winner" => ((int) $winnerIndex),
                    "bids" => json_decode($bids),
                    "bidder" => $bidder
                ];
            } else {

                //No Claims
                if ($winnerIndex > count($bidder)) {
                    //TODO Product must archive
                    //Update Status
                    $result = $bidding->update(['status' => "claim_failed"]);
                    return [
                        "status" => "no_claim",
                        "message" => $result
                    ];
                } else {
                    return [
                        "status" => "ended",
                        "winner" => ((int) $winnerIndex),
                        "bids" => json_decode($bids),
                        "bidder" => $bidder
                    ];
                }
            }
        } else {
            if ($current_time >= $start_time) {
                return [
                    "status" => "on_going",
                ];
            } else {
                return [
                    "status" => "waiting",
                ];
            }
        }
    }


    //Check And Update Bidding Status
    function checkBiddingStatus ($uuid) {
        $bidding = Biddings::where('uuid', $uuid)->first();

        $current_time = strtotime(date("y-m-d H:i:s"));
        $start_time = strtotime($bidding->start_time);
        $end_time = strtotime($bidding->end_time);

        if($bidding->status == 'waiting') {
            if($current_time >= $end_time) {
                $bidding->update(['status' => 'ended']);
            }
            else if($current_time >= $start_time){
                $bidding->update(['status' => 'on_going']);
            }
        }
        else if($bidding->status == 'on_going') {
            if($current_time >= $end_time) {
                $bidding->update(['status' => 'ended']);
            }
        }
    }

    function checkWaitingBiddings () {
        $biddings = Biddings::where('status','on_going')->get();

        $current_time = strtotime(date("y-m-d H:i:s"));

        foreach($biddings as $item) {
            $start_time = strtotime($item->start_time);
            $end_time = strtotime($item->end_time);

            if($current_time >= $end_time) {
                $item->update(['status' => 'ended']);
            }
            else if($current_time >= $start_time){
                $item->update(['status' => 'on_going']);
            }
        }
    }

    function checkActiveBiddings () {
        $biddings = Biddings::where('status','on_going')->get();

        $current_time = strtotime(date("y-m-d H:i:s"));

        foreach($biddings as $item) {
            $end_time = strtotime($item->end_time);

            if($current_time >= $end_time) {
                $item->update(['status' => 'ended']);
            }
        }
    }
}
