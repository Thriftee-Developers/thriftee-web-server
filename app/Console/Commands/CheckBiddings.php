<?php

namespace App\Console\Commands;

use App\Http\Controllers\BiddingController;
use Illuminate\Console\Command;

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
        $biddingCtrl = new BiddingController();
        $biddingCtrl->checkWaitingBiddings();
        $biddingCtrl->checkActiveBiddings();
    }
}
