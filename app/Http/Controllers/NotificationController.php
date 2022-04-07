<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerNotification;
use App\Models\StoreNotification;
use App\Events\NotificationEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NotificationController extends Controller
{
    //
    public function syncNotification(Request $req)
    {
        event(new NotificationEvent($req->user, $req->type, $req->content));
    }

    function addCustomerNotification(Request $req)
    {
        $customerNotification = new CustomerNotification();
        $customerNotification->uuid = Str::uuid();
        $customerNotification->customer = $req->customer;
        $customerNotification->type = $req->type;
        $customerNotification->content = $req->content;

        $customerNotification->save();

        return ["sucess" => "success"];
    }

    function addStoreNotification(Request $req)
    {
        $storeNotification = new StoreNotification();
        $storeNotification->uuid = Str::uuid();
        $storeNotification->store = $req->store;
        $storeNotification->type = $req->type;
        $storeNotification->content = $req->content;

        $storeNotification->save();
        return ["sucess" => "success"];
    }

    function getCustomerNotification(Request $req)
    {
        $result = CustomerNotification::where("customer", $req->customer)->orderBy("status", "desc")->get();
        return $result;
    }


    function getStoreNotification(Request $req)
    {
        $result = StoreNotification::where("store", $req->store)->get();
        return $result;
    }

    function getCustomerUnreadNotificationCount(Request $req)
    {
        $result = CustomerNotification::where("customer", $req->customer)->where("status", 1)->get()->count();
        return $result;
    }

    function getStoreUnreadNotificationCount(Request $req)
    {
        $result = StoreNotification::where("store", $req->store)->where("status", 1)->get();
        return $result;
    }

    function deleteCustomerNotification(Request $req)
    {
        $result = CustomerNotification::where("uuid", $req->uuid)->first();
        return $result;
    }

    function deleteStoreNotification(Request $req)
    {
        $result = StoreNotification::where("uuid", $req->uuid)->first();
        return $result;
    }
}
