<?php

namespace App\Console\Commands;

use App\Http\Controllers\BiddingController;
use App\Models\Bid;
use App\Models\Biddings;
use App\Models\CustomerNotification;
use App\Models\Follower;
use App\Models\Product;
use App\Models\StoreNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckBiddings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:biddings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check bidding status then update';
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->checkWaitingBiddings();
        $this->checkActiveBiddings();
        $this->checkEndedBiddings();

        $this->info("Bidding status checked");
    }

    private function checkWaitingBiddings()
    {
        $biddings = Biddings::select(
                "biddings.*",
                "products.store",
                "products.product_id",
                "products.name"
            )
            ->where('biddings.status', 'waiting')
            ->leftJoin('products','products.uuid','biddings.product')
            ->get();

        $current_time = strtotime(date("y-m-d H:i:s"));

        foreach ($biddings as $item) {
            $start_time = strtotime($item->start_time);

            if ($current_time >= $start_time) {
                $item->update(['status' => 'on_going']);

                //TODO: CHECK IF NOTIFICATION IS WORKING / BIDDING START
                // $followers = Follower::where('store',$item->store)->get;
                // $customers = array();

                // foreach ($followers as $follower) {
                //     array_push($customers, $follower->customer);
                // }

                // $type = 'bidding_start';
                // $details = [
                //     "bidding" => $item->uuid,
                //     "product" => $item->product,
                //     "product_id" =>  $item->product_id,
                // ];

                // $this->notifyCustomer($customers, $type, $details);


                //NOTIFY STORE WHEN BIDDING STARTS
                $type = "bidding_start";
                $details = [
                    "bidding" => $item->uuid,
                    "product" => $item->product,
                    "product_id" =>  $item->product_id,
                    "product_name" => $item->name
                ];

                $this->notifyStore($item->store, $type, $details);
            }
        }
    }

    private function checkActiveBiddings()
    {
        $biddings = Biddings::select(
            "biddings.*",
            "products.store",
            "products.product_id",
            "products.name"
        )
        ->where('biddings.status', 'on_going')
        ->leftJoin('products','products.uuid','biddings.product')
        ->get();

        $current_time = strtotime(date("y-m-d H:i:s"));

        foreach ($biddings as $item) {
            $end_time = strtotime($item->end_time);

            if ($current_time >= $end_time) {
                $item->update(['status' => 'ended']);

                //TODO: send notification to all bidders (BIDDING ENDED) / CHECK IF WORKING
                $type = 'bidding_ended';
                $details = [
                    "bidding" => $item->uuid,
                    "product" => $item->product,
                    "product_id" =>  $item->product_id,
                    "product_name" => $item->name
                ];

                //NOTIFY CUSTOMER
                $customers = $this->getBidders($item->uuid);
                $this->notifyCustomer($customers, $type, $details);

                //NOTIFY STORE
                $this->notifyStore($item->store, $type, $details);
            }
        }
    }

    private function checkEndedBiddings()
    {
        $biddings = DB::select(
            "SELECT
                biddings.*,
                Count(bids.uuid) as bid_count,
                products.store,
                products.name as product_name,
                products.product_id
            FROM biddings

            LEFT JOIN bids
            ON biddings.uuid = bids.bidding

            LEFT JOIN products
            ON biddings.product = products.uuid

            WHERE biddings.status = 'ended'

            GROUP BY biddings.uuid"
        );

        $current_time = strtotime(date("y-m-d H:i:s"));

        foreach($biddings as $item)
        {
            if($item->bid_count > 0)
            {
                $end_time = strtotime($item->end_time);

                $bidders = $this->getBidders($item->uuid);
                $hoursdiff = ($current_time - $end_time) / 3600;

                //48 hrs = 2 days
                $winnerIndex = $hoursdiff / 48;

                //Failed to claim the product
                if($winnerIndex >= count($bidders))
                {
                    //UPDATE BIDDING
                    DB::select(
                        "UPDATE biddings
                        SET status='claim_failed', claimer=null
                        WHERE uuid='$item->uuid'
                    ");

                    //UPDATE PRODUCT
                    DB::select(
                        "UPDATE products
                        SET status='archived'
                        WHERE uuid='$item->product'
                    ");

                    //TODO: CHECK IF WORKING
                    $type = 'bidding_failed';
                    $details = [
                        "bidding" => $item->uuid,
                        "product" => $item->product,
                        "product_id" =>  $item->product_id,
                        "product_name" => $item->name,
                        "reason" => "claim_failed"
                    ];

                    $this->notifyStore($item->store, $type, $details);
                }
                else
                {
                    $new_claimer = $bidders[$winnerIndex]->customer;

                    //Update if current claimer is not equal to new claimer
                    if($new_claimer != $item->claimer)
                    {
                        //TODO: send notification to current claimer (CLAIM FAILED) AND new claimer // CHECK IF WORKING

                        if($item->claimer) {
                            $type = 'claim_failed';
                            $details = [
                                "bidding" => $item->uuid,
                                "product" => $item->product,
                                "product_id" =>  $item->product_id,
                                "product_name" => $item->name
                            ];

                            $this->notifyCustomer([$item->claimer], $type, $details);
                        }

                        //UPDATE BIDDING
                        DB::select(
                            "UPDATE biddings
                            SET claimer='$new_claimer'
                            WHERE uuid='$item->uuid'
                        ");

                        if($new_claimer) {
                            $type = 'selected_claimer';
                            $details = [
                                "bidding" => $item->bidding,
                                "product" => $item->product
                            ];
                            $this->notifyCustomer([$new_claimer], $type, $details);
                        }

                    }
                }
            }
            else
            {
                //UPDATE BIDDING
                DB::select(
                    "UPDATE biddings
                    SET status='no_bid'
                    WHERE uuid='$item->uuid'
                ");

                //UPDATE PRODUCT
                DB::select(
                    "UPDATE products
                    SET status='archived'
                    WHERE uuid='$item->product'
                ");

                //TODO: CHECK IF WORKING
                $type = 'bidding_failed';
                $details = [
                    "bidding" => $item->uuid,
                    "product" => $item->product,
                    "product_id" =>  $item->product_id,
                    "product_name" => $item->name,
                    "reason" => "no_bid"
                ];

                $this->notifyStore($item->store, $type, $details);
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

    function notifyCustomer ($customers, $type, $details)
    {
        $current_date = date("Y-m-d H:i:s");
        foreach($customers as $item)
        {
            $notif = new CustomerNotification();
            $notif->uuid = Str::uuid();
            $notif->customer = $item;
            $notif->type = $type;
            $notif->date = $current_date;
            $notif->details = json_encode($details);

            $notif->save();
        }

    }

    function notifyStore ($store, $type, $details)
    {
        $notif = new StoreNotification();
        $notif->uuid = Str::uuid();
        $notif->store = $store;
        $notif->type = $type;
        $notif->date = date("Y-m-d H:i:s");
        $notif->details = json_encode([$details]);
        $result = $notif->save();
    }
}
