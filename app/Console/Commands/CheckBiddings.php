<?php

namespace App\Console\Commands;

use App\Http\Controllers\BiddingController;
use App\Models\Biddings;
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
        $this->checkWaitingBiddings();
        $this->checkActiveBiddings();

        $this->info("Checking biddings status");
    }

    private function checkWaitingBiddings()
    {
        $biddings = Biddings::where('status', 'waiting')->get();

        $current_time = strtotime(date("y-m-d H:i:s"));

        foreach ($biddings as $item) {
            $start_time = strtotime($item->start_time);
            $end_time = strtotime($item->end_time);

            if ($current_time >= $end_time) {
                $item->update(['status' => 'ended']);
            } else if ($current_time >= $start_time) {
                $item->update(['status' => 'on_going']);
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
            }
        }
    }
}
