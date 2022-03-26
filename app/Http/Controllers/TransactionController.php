<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    //
    function addTransaction(Request $req)
    {
        $transaction = new Transaction();
        $transaction->uuid = Str::uuid();
        $transaction->bidding = $req->bidding;
        $transaction->customer = $req->customer;
        $transaction->billing_method = $req->billing_method;
        $transaction->bid = $req->bid;
        $transaction->description = $req->description;
        $transaction->country = $req->country;
        $transaction->state = $req->state;
        $transaction->city = $req->city;
        $transaction->street = $req->street;
        $transaction->status = "no_payment";

        $transaction->save();
        return ["success" => "success"];
    }

    function getTransaction(Request $req)
    {
        $transaction = Transaction::where([
            ['customer', $req->customer],
            ['bidding', $req->bidding]
        ])->first();

        return $transaction;
    }

    function updatePaymentMethod(Request $req)
    {
        $transaction = Transaction::where("uuid", $req->uuid)->first();
        if ($transaction) {
            $result = $transaction->update(["billing_method" => $req->billing_method]);
            if ($result) {
                return ["success" => "success"];
            } else {
                return ["error" => "Error updating billing methods!"];
            }
        } else {
            return ["error" => "Transaction not found!"];
        }
    }

    function sendReference(Request $req)
    {
        $transaction = Transaction::where("uuid", $req->uuid)->first();
        if ($transaction) {
            $result = $transaction->update(["reference" => $req->reference]);
            if ($result) {
                $transaction->update(["status" => "payment_verification"]);
                return ["success" => "success"];
            } else {
                return ["error" => "Error updating reference!"];
            }
        } else {
            return ["error" => "Transaction not found!"];
        }
    }

    function cancelReference(Request $req)
    {
        $transaction = Transaction::where("uuid", $req->uuid)->first();
        if ($transaction) {
            $result = $transaction->update(["reference" => ""]);
            if ($result) {
                $transaction->update(["status" => "no_payment"]);
                return ["success" => "success"];
            } else {
                return ["error" => "Error cancelling reference!"];
            }
        } else {
            return ["error" => "Transaction not found!"];
        }
    }
    function validatePayment(Request $req)
    {
        $transaction = Transaction::where("uuid", $req->uuid)->first();
        if ($transaction) {
            $result = $transaction->update(["status" => "payment_valid"]);
            if ($result) {
                return ["success" => "success"];
            } else {
                return ["error" => "Error updating status!"];
            }
        } else {
            return ["error" => "Transaction not found!"];
        }
    }
    function invalidPayment(Request $req)
    {
        $transaction = Transaction::where("uuid", $req->uuid)->first();
        if ($transaction) {
            $result = $transaction->update(["status" => "invalid_payment"]);
            if ($result) {
                return ["success" => "success"];
            } else {
                return ["error" => "Error updating status!"];
            }
        } else {
            return ["error" => "Transaction not found!"];
        }
    }

    function closeTransaction(Request $req)
    {
        $transaction = Transaction::where("uuid", $req->uuid)->first();
        if ($transaction) {
            $result = $transaction->update(["status" => "success"]);
            if ($result) {
                return ["success" => "success"];
            } else {
                return ["error" => "Error updating status!"];
            }
        } else {
            return ["error" => "Transaction not found!"];
        }
    }

    function cancelTransaction(Request $req)
    {
        $transaction = Transaction::where("uuid", $req->uuid)->first();
        if ($transaction) {
            $result = $transaction->update(["status" => "cancelled"]);
            if ($result) {
                return ["success" => "success"];
            } else {
                return ["error" => "Error updating status!"];
            }
        } else {
            return ["error" => "Transaction not found!"];
        }
    }
}
