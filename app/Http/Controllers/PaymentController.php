<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    //
    function addPayment(Request $req)
    {
        $payment = new Payment();
        $payment->uuid = Str::uuid();
        $payment->bidding = $req->bidding;
        $payment->customer = $req->customer;
        $payment->store = $req->store;
        $payment->billingmethod = $req->billingmethod;
        $payment->reference = $req->reference;

        $payment->save();

        return ["success" => "success"];
    }

    function getByCustomer(Request $req)
    {
        $result = Payment::where("customer", $req->customer)->get();
        return $result;
    }
    function getByStore(Request $req)
    {
        $result = Payment::where("store", $req->store)->get();
        return $result;
    }
    function getByCustomerAndStore(Request $req)
    {
        $result = Payment::where("customer", $req->customer)
            ->where("store", $req->store)
            ->get();
        return $result;
    }
}
