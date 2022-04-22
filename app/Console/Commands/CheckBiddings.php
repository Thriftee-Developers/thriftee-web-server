<?php

namespace App\Console\Commands;

use App\Http\Controllers\BiddingController;
use App\Models\Bid;
use App\Models\Biddings;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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

        $this->info("Checking biddings status");
    }

    private function checkWaitingBiddings()
    {
        $biddings = Biddings::where('status', 'waiting')->get();

        $current_time = strtotime(date("y-m-d H:i:s"));

        foreach ($biddings as $item) {
            $start_time = strtotime($item->start_time);

            if ($current_time >= $start_time) {
                $item->update(['status' => 'on_going']);
                //TODO: send notification to all store follwer (BIDDING START)
            }
        }
    }

    private function checkActiveBiddings()
    {
        $biddings = Biddings::where('status', 'on_going')->get();

        $current_time = strtotime(date("y-m-d H:i:s"));

        foreach ($biddings as $item) {
            $end_time = strtotime($item->end_time);

            if ($current_time >= $end_time) {
                $item->update(['status' => 'ended']);
                //TODO: send notification to all bidders (BIDDING ENDED)
            }
        }
    }

    private function checkEndedBiddings()
    {
        $biddings = DB::select(
            "SELECT
                biddings.*,
                Count(bids.uuid) as bid_count
            FROM biddings

            LEFT JOIN bids
            ON biddings.uuid = bids.bidding

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
                }
                else
                {
                    $claimer = $bidders[$winnerIndex]->customer;

                    //Update if current claimer is not equal to new claimer
                    if($claimer != $item->claimer)
                    {
                        //UPDATE BIDDING
                        DB::select(
                            "UPDATE biddings
                            SET claimer='$claimer'
                            WHERE uuid='$item->uuid'
                        ");

                        //TODO: send notification to current claimer (CLAIM FAILED) AND new claimer
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
