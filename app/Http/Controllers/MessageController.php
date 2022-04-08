<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Events\MessageEvent as Chat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MessageController extends Controller
{
    function sendChat(Request $req)
    {
        event(new Chat($req->customer, $req->store, $req->sender, $req->content));
        return ["success" => "send success"];
    }
    //
    function sendMessage(Request $req)
    {

        $message = new Message();

        $message->uuid = Str::uuid();
        $message->customer = $req->customer;
        $message->store = $req->store;
        $message->sender = $req->sender;
        $message->content = $req->content;

        $message->save();
    }

    function getMessages(Request $req)
    {
        $result = Message::where("customer", $req->customer)
            ->where("store", $req->store)
            ->orderBy("date", "asc")
            ->get();

        return $result;
    }

    function seenMessages(Request $req)
    {
        $uuids = json_decode($req->messages);

        foreach ($uuids as $uuid) {
            $messages = Message::where('uuid', $uuid);
            $messages->update(['status' => 1]);
        }
    }

    function getStoreChatList(Request $req)
    {
        $chatlist = DB::select(
            "SELECT
                messages.customer,
                messages.date,
                CONCAT(customers.fname,' ',customers.lname) as name,
                customers.profile_uri
            FROM (
                SELECT MAX(messages.date) as date, messages.sender, messages.customer
                FROM messages
                WHERE messages.store='$req->store' AND messages.sender='store'
                GROUP BY messages.customer
                ORDER BY date DESC
            ) messages

            LEFT JOIN customers ON customers.uuid = messages.customer"
        );

        return $chatlist;
    }

    function getCustomerChatList(Request $req)
    {
        $chatlist = DB::select(
            "SELECT
                messages.store,
                messages.date,
                stores.store_name as name,
                stores.image_uri as profile_uri
            FROM (
                SELECT MAX(messages.date) as date, messages.sender, messages.store
                FROM messages
                WHERE messages.customer='$req->customer' AND messages.sender='customer'
                GROUP BY messages.store
                ORDER BY date DESC
            ) messages

            LEFT JOIN stores ON stores.uuid = messages.store"
        );

        return $chatlist;
    }
}
