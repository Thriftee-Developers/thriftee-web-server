<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rating;
use Illuminate\Support\Str;
class RatingController extends Controller
{
    //
    function addRating(Request $req){
        $rating = new Rating();
        $rating->uuid=Str::uuid();
        $rating->customer=$req->customer;
        $rating->store=$req->store;
        $rating->rate=$req->rate;

        $rating->save();
    }

    function getRatingByStore(Request $req){
        $result = Rating::where("store", $req->store)->get();
        return $result;
    }

    function getRatingByCustomerAndStore(Request $req){
        $result = Rating::where("customer",$req->customer)->where("store",$req->store)->get();
        return $result;
    }
}
