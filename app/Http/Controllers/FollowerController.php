<?php

namespace App\Http\Controllers;

use App\Models\Follower;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FollowerController extends Controller
{
    //
    function getAllFollowers(Request $req)
    {
        $result = Follower::join("customers", "followers.customer", "customers.uuid")
            ->select(
                "followers.*",
                "customers.fname",
                "customers.lname",
                "customers.profile_uri"
            )
            ->where("store", $req->store)
            ->get();
        return $result;
    }

    function followStore(Request $req)
    {
        $follower = new Follower();
        $follower->uuid = Str::uuid();
        $follower->customer = $req->customer;
        $follower->store = $req->store;

        if ($this->checkFollowStatus($req)) {
            return ["error" => "Store has been followed."];
        } else {
            $follower->save();
            return ["success" => "success"];
        }
    }

    function unfollowStore(Request $req)
    {
        $result = Follower::where("customer", $req->customer)
            ->where("store", $req->store)
            ->delete();
        if ($result) {
            return ["success" => "success"];
        } else {
            return ["error" => "UUID not found!"];
        }
    }

    function getStoreFollowers(Request $req)
    {
        $result = Follower::join("customers", "followers.customer", "customers.uuid")
            ->select(
                "followers.*",
                "customers.uuid as customer_uuid",
                "customers.lname",
                "customers.fname"
            )
            ->where("store", $req->store)
            ->get();
        return $result;
    }

    function getFollowedStore(Request $req)
    {
        $result = Follower::join("stores", "followers.store", "=", "stores.uuid")
            ->select(
                "followers.*",
                "stores.store_id",
                "stores.store_name",
                "stores.image_uri",
            )
            ->where("customer", $req->customer)
            ->get();
        return $result;
    }

    function getFollowedStoreCount(Request $req)
    {
        $result = Follower::where("customer", $req->customer)
            ->count();
        return $result;
    }

    function getAllFollowersCount(Request $req)
    {
        $result = Follower::where("store", $req->store)
            ->count();
        return $result;
    }

    function checkFollowStatus(Request $req)
    {
        $result = Follower::where("customer", $req->customer)
            ->where("store", $req->store)
            ->get();
        if (count($result) > 0) {
            return true;
        } else {
            return false;
        }
    }
}
