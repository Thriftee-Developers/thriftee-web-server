<?php

namespace App\Http\Controllers;

use App\Models\Bid;
use App\Models\Biddings;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Store;
use App\Models\StoreBillingMethod;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    //
    function addTransaction(Request $req)
    {
        $transaction = new Transaction();
        $transaction->uuid = Str::uuid();
        $transaction->billing_method = $req->billing_method;
        $transaction->bid = $req->bid;
        $transaction->description = $req->description;
        $transaction->contact_no = $req->contact_no;
        $transaction->email = $req->email;
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
        $transaction = Transaction
            ::select('transactions.*', 'bids.bidding', 'bids.customer', 'bids.amount')
            ->join('bids','bids.uuid','=','transactions.bid')
            ->where([
                ['bids.customer', $req->customer],
                ['bids.bidding', $req->bidding]
            ])->first();

        return $transaction;
    }

    function getIncompleteTransactions(Request $req)
    {
        $transactions = Transaction
            ::select(
                'transactions.*',
                'bids.bidding',
                'bids.customer')
            ->join('bids','bids.uuid','=','transactions.bid')
            ->join('storebillingmethods', 'storebillingmethods.uuid', '=', 'transactions.billing_method')
            ->where([
                ['storebillingmethods.store', $req->store],
                ['status','<>','complete']
            ])->get();

        foreach($transactions as $item) {
            $customer = Customer::where('uuid', $item->customer)->first();
            $item->customer = $customer;

            $bidding = Biddings::where('uuid', $item->bidding)->first();
            $item->bidding = $bidding;

            $product = Product::where('uuid', $bidding->product)->first();
            $item->product = $product;

            $image = ProductImage::where('product', $product->uuid)->first();
            $item->product->image = $image->path;

            $bid = Bid::where('uuid', $item->bid)->first();
            $item->bid = $bid;

            $billingmethod = StoreBillingMethod::where('uuid', $item->billing_method)->first();
            $item->billing_method = $billingmethod;
        }

        return $transactions;
    }

    function getCompletedTransactions(Request $req)
    {
        $transactions = Transaction
            ::select(
                'transactions.*',
                'bids.bidding',
                'bids.customer')
            ->join('bids','bids.uuid','=','transactions.bid')
            ->join('storebillingmethods', 'storebillingmethods.uuid', '=', 'transactions.billing_method')
            ->where([
                ['storebillingmethods.store', $req->store],
                ['status','complete']
            ])->get();

        foreach($transactions as $item) {
            $customer = Customer::where('uuid', $item->customer)->first();
            $item->customer = $customer;

            $bidding = Biddings::where('uuid', $item->bidding)->first();
            $item->bidding = $bidding;

            $bid = Bid::where('uuid', $item->bid)->first();
            $item->bid = $bid;

            $billingmethod = StoreBillingMethod::where('uuid', $item->billing_method)->first();
            $item->billing_method = $billingmethod;
        }

        return $transactions;
    }

    function updatePaymentMethod(Request $req)
    {
        $transaction = Transaction::where("uuid", $req->uuid)->first();
        if ($transaction) {
            $result = $transaction->update([
                "billing_method" => $req->billing_method,
                "contact_no" => $req->contact_no,
                "email" => $req->email,
                "country" => $req->country,
                "state" => $req->state,
                "city" => $req->city,
                "street" => $req->street
            ]);
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

        //check store password
        // $store = Store::where('uuid', $req->store)->first();

        // if($store && Hash::check($req->password, $store->password)) {
        //     $transaction = Transaction::where("uuid", $req->transaction)->first();
        //     if ($transaction) {
        //         $result = $transaction->update(["status" => "complete"]);
        //         if ($result) {
        //             return ["success" => "success"];
        //         } else {
        //             return ["error" => "Error updating status"];
        //         }
        //     } else {
        //         return ["error" => "Transaction not found"];
        //     }
        // }
        // else {
        //     return ["error" => "Invalid Password"];
        // }

        return date("h:i:sa");
    }
    function invalidPayment(Request $req)
    {
        //check store password
        $store = Store::where('uuid', $req->store)->first();

        if($store && Hash::check($req->password, $store->password)) {
            $transaction = Transaction::where("uuid", $req->transaction)->first();
            if ($transaction) {
                $result = $transaction->update(["status" => "invalid_payment"]);
                if ($result) {
                    return ["success" => "success"];
                } else {
                    return ["error" => "Error updating status"];
                }
            } else {
                return ["error" => "Transaction not found"];
            }
        }
        else {
            return ["error" => "Invalid Password"];
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
