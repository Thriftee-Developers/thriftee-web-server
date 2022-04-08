<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerNotification;
use App\Models\StoreNotification;
use App\Events\NotificationEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NotificationController extends Controller
{
    //
    public function syncNotification(Request $req)
    {
        event(new NotificationEvent($req->user, $req->type, $req->details));
    }

    function addNotification(Request $req)
    {
        if ($req->type == "store") {

            $customerNotification = new CustomerNotification();
            $customerNotification->uuid = Str::uuid();
            $customerNotification->customer = $req->customer;
            $customerNotification->type = $req->type;
            $customerNotification->details = $req->details;
            $customerNotification->date = date("Y-m-d H:i:s");

            $customerNotification->save();
            return ["sucess" => "success"];
        } else {
            $storeNotification = new StoreNotification();
            $storeNotification->uuid = Str::uuid();
            $storeNotification->store = $req->store;
            $storeNotification->type = $req->type;
            $storeNotification->details = $req->details;
            $storeNotification->date = date("Y-m-d H:i:s");

            $storeNotification->save();
            return ["sucess" => "success"];
        }
        return ["error" => "Adding failed!"];
    }

    function getNotifications(Request $req)
    {
        if ($req->type == "store") {
            return StoreNotification::where("store", $req->uuid)->orderBy(array("status" => "desc", "date" => "desc"))->orderByget();
        } else {
            return CustomerNotification::where("customer", $req->uuid)->orderBy([["status" => "desc"], ["date" => "desc"]])->get();
        }
        return ["error" => "Customer or store not found!"];
    }

    function updateNotificationsStatus(Request $req)
    {
        if ($req->type == "store") {
            StoreNotification::where("store", $req->uuid)
                ->whereIn('status', [0])
                ->update(array('status' => "1"));
            return ["success" => "success"];
        } else {
            CustomerNotification::where("customer", $req->uuid)
                ->whereIn('status', [0])
                ->update(array('status' => "1"));
            return ["success" => "success"];
        }
        return ["error" => "Customer or store not found!"];
    }


    function getUnreadNotificationCount(Request $req)
    {
        if ($req->type == "store") {
            return StoreNotification::where("store", $req->uuid)->where("status", 0)->get();
        } else {
            return CustomerNotification::where("customer", $req->uuid)->where("status", 0)->get()->count();
        }
        return ["error" => "Customer or store not found!"];
    }

    function deleteNotification(Request $req)
    {
        if ($req->type == "store") {
            return StoreNotification::where("uuid", $req->uuid)->first();
        } else {
            return CustomerNotification::where("uuid", $req->uuid)->first();
        }
        return ["error" => "Customer or store not found!"];
    }
}
