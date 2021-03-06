<?php

namespace App\Http\Controllers;

use App\Models\Bid;
use App\Models\Biddings;
use App\Models\Customer;
use App\Models\CustomerNotification;
use App\Models\Notification;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Store;
use App\Models\StoreBillingMethod;
use App\Models\StoreNotification;
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

        //Update Bidding Status
        $bidding = Biddings::select(['biddings.*'])
            ->rightJoin('bids','bids.bidding','=','biddings.uuid')
            ->where('bids.uuid',$req->bid)
            ->first();
        $bidding->update(['status'=>'under_transaction']);

        //Update Product Status
        $product = Product::where('uuid', $bidding->product)->first();
        $product->update(['status'=>'under_transaction']);

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

    function getStoreNoPayment(Request $req)
    {
        $transactions = Transaction
            ::select(
                'transactions.*',
                'bids.bidding',
                'bids.customer')
            ->join('bids','bids.uuid','=','transactions.bid')
            ->join('storebillingmethods', 'storebillingmethods.uuid', '=', 'transactions.billing_method')
            ->where('storebillingmethods.store', $req->store)
            ->where(function($query) {
                $query->where('transactions.status', 'no_payment')
                ->orWhere('transactions.status', 'invalid_payment');
            })
            ->get();

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

    function getStoreForValidation(Request $req)
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
                ['transactions.status','for_validation']
            ])->get();

        foreach($transactions as $item) {
            $customer = Customer::where('uuid', $item->customer)->first();
            $item->customer = $customer;

            $bidding = Biddings::where('uuid', $item->bidding)->first();
            $item->bidding = $bidding;

            $product = Product::where('uuid', $bidding->product)->first();
            $image = ProductImage::where('product', $product->uuid)
                ->orderBy('name','ASC')
                ->first();
            $product->image = $image->path;
            $item->product = $product;

            $bid = Bid::where('uuid', $item->bid)->first();
            $item->bid = $bid;

            $billingmethod = StoreBillingMethod::where('uuid', $item->billing_method)->first();
            $item->billing_method = $billingmethod;
        }

        return $transactions;
    }

    function getStoreCompletedTransactions(Request $req)
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
                ['transactions.status','complete']
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

    function getStoreCancelledTransactions(Request $req)
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
                ['transactions.status','cancelled']
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

        $transaction = Transaction
            ::select(
                'transactions.*',
                'bids.customer',
                'customers.fname',
                'customers.lname',
                'products.name as product_name',
                'storebillingmethods.store'
                )
            ->leftJoin('storebillingmethods', 'storebillingmethods.uuid', '=', 'transactions.billing_method')
            ->leftJoin('bids','bids.uuid','=','transactions.bid')
            ->leftJoin('customers','customers.uuid','=','bids.customer')
            ->leftJoin('biddings','biddings.uuid','=','bids.bidding')
            ->leftJoin('products','products.uuid','=','biddings.product')
            ->where("transactions.uuid", $req->uuid)
            ->first();

        if ($transaction) {
            $result = $transaction->update(["reference" => $req->reference]);
            if ($result) {
                $transaction->update(["status" => "for_validation"]);

                //Send notif to store
                $notif = new StoreNotification();
                $notif->uuid = Str::uuid();
                $notif->store = $transaction->store;
                $notif->type = 'payment';
                $notif->date = date("Y-m-d H:i:s");
                $notif->details = json_encode([
                    "customer" => $transaction->customer,
                    "name" => $transaction->fname.' '.$transaction->lname,
                    "transaction" => $transaction->uuid,
                    "product_name" => $transaction->product_name
                ]);
                $result = $notif->save();

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
        //get store
        $store = Store::where('uuid', $req->store)->first();

        //check store password
        if($store && Hash::check($req->password, $store->password)) {
            $transaction = Transaction
                ::select(
                    'transactions.*',
                    'biddings.uuid as bidding',
                    'products.name as product_name',
                    'biddings.uuid as bidding'
                )
                ->leftJoin('bids','bids.uuid','=','transactions.bid')
                ->leftJoin('biddings','biddings.uuid','=','bids.bidding')
                ->leftJoin('products','products.uuid','=','biddings.product')

                ->where("transactions.uuid", $req->transaction)
                ->first();
            if ($transaction) {

                //Update Transaction Status
                $transaction->update([
                    "status" => "complete",
                    "validate_at" => date("Y-m-d H:i:s")
                ]);

                //Update Bidding
                $bidding = Biddings::where('uuid', $transaction->bidding)->first();
                $bidding->update(['status' => 'success']);

                //Update Product Status
                $product = Product::where('uuid', $bidding->product)->first();
                $product->update(['status'=>'sold']);

                //Send notification
                $notif = new CustomerNotification();
                $notif->uuid = Str::uuid();
                $notif->customer = $req->customer;
                $notif->type = 'payment_validate';
                $notif->date = date("Y-m-d H:i:s");
                $notif->details = json_encode([
                    "customer" => $req->customer,
                    "transaction" => $transaction->uuid,
                    "product_name" => $transaction->product_name,
                    "store_name" =>  $store->store_name,
                    "bidding" =>  $transaction->bidding
                ]);

                $result = $notif->save();

                if ($result) {
                    return [
                        "success" => "success"
                    ];
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

    function revokePayment(Request $req)
    {
        //get store
        $store = Store::where('uuid', $req->store)->first();

        //check store password
        if($store && Hash::check($req->password, $store->password)) {
            $transaction = Transaction
                ::select(
                    'transactions.*',
                    'products.name as product_name',
                    'biddings.uuid as bidding'
                )
                ->leftJoin('bids','bids.uuid','=','transactions.bid')
                ->leftJoin('biddings','biddings.uuid','=','bids.bidding')
                ->leftJoin('products','products.uuid','=','biddings.product')

                ->where("transactions.uuid", $req->transaction)
                ->first();

            if ($transaction) {
                //Update status
                $result = $transaction->update(["status" => "invalid_payment"]);

                //Send notification
                $notif = new CustomerNotification();
                $notif->uuid = Str::uuid();
                $notif->customer = $req->customer;
                $notif->type = 'payment_revoked';
                $notif->date = date("Y-m-d H:i:s");
                $notif->details = json_encode([
                    "customer" => $req->customer,
                    "transaction" => $transaction->uuid,
                    "product_name" => $transaction->product_name,
                    "store_name" =>  $store->store_name,
                    "bidding" =>  $transaction->bidding
                ]);
                $result = $notif->save();

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

    function cancelPayment(Request $req)
    {
        $transaction = Transaction::where("uuid", $req->uuid)->first();
        if ($transaction) {
            $result = $transaction->update([
                "status" => "no_payment",
                "reference" => null,
            ]);
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
