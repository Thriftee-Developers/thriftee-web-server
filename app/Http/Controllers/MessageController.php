<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Events\MessageEvent as Chat;
use App\Events\ChatBoxEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ChatBox;
use App\Models\MessageStatus;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    function sendChat(Request $req)
    {
        event(new ChatBoxEvent($req->sender, $req->customer, $req->store));
        event(new Chat($req->customer, $req->store, $req->sender, $req->content));
        return ["success" => "send success"];
    }
    //
    function sendMessage(Request $req)
    {
        //Found if the data will be duplicate
        $storeChatBox = ChatBox::where("store", $req->store)
            ->where("customer", $req->customer)
            ->first();

        $customerChatBox = ChatBox::where("store", $req->store)
            ->where("customer", $req->customer)
            ->where("owner_type", "customer")
            ->first();

        //Ad message in messages
        $message = new Message();
        $message->uuid = Str::uuid();
        $message->sender = $req->sender;
        $message->date = date("Y-m-d H:i:s");
        $message->content = $req->content;

        $message->save();

        //Create Store Chatbox
        if (!$storeChatBox) {
            $storeChatBox = new ChatBox();
            $storeChatBox->uuid = Str::uuid();
            $storeChatBox->owner_type = "store";
            $storeChatBox->customer = $req->customer;
            $storeChatBox->store = $req->store;
            $storeChatBox->save();
        }
        //Create Message Status
        $storeStatus = new MessageStatus();
        $storeStatus->uuid = Str::uuid();
        $storeStatus->message = $message->uuid;
        $storeStatus->chatbox = $storeChatBox->uuid;

        //Set store status to 1
        if ($req->sender == "store") {
            $storeStatus->status = "1";
        }
        $storeStatus->save();


        //Create customer chatbox
        if (!$customerChatBox) {
            $customerChatBox = new ChatBox();
            $customerChatBox->uuid = Str::uuid();
            $customerChatBox->owner_type = "customer";
            $customerChatBox->customer = $req->customer;
            $customerChatBox->store = $req->store;
            $customerChatBox->save();
        }
        //Create Message Status
        $customerStatus = new MessageStatus();
        $customerStatus->uuid = Str::uuid();
        $customerStatus->message = $message->uuid;
        $customerStatus->chatbox = $customerChatBox->uuid;
        //Set Status
        if ($req->sender == "customer") {
            $customerStatus->status = "1";
        }
        $customerStatus->save();

        // event(new Chat($req->customer, $req->store, $req->sender, $req->content));
        return ["success" => "success"];
    }

    // function getMessages(Request $req)
    // {
    //     $result = Message::where("customer", $req->customer)
    //         ->where("store", $req->store)
    //         ->orderBy("date", "asc")
    //         ->get();

    //     return $result;
    // }

    function seenMessages(Request $req)
    {
        $uuids = json_decode($req->messages);

        foreach ($uuids as $uuid) {
            $messages = Message::where('uuid', $uuid);
            $messages->update(['status' => 1]);
        }
    }

    function getChatList(Request $req)
    {
        if ($req->owner == 'customer') {
            $chatlist = ChatBox::select([
                'chatboxes.*',
                'stores.store_name as name',
                'stores.image_uri as profile_uri'
            ])
                ->where([
                    ['customer', $req->user],
                    ['owner_type', 'customer']
                ])
                ->join('stores', 'stores.uuid', 'chatboxes.store')
                ->get();
        } else {
            $chatlist = ChatBox::select([
                'chatboxes.*',
                DB::raw('CONCAT(customers.fname," ",customers.lname) as name'),
                'customers.profile_uri'
            ])
                ->where([
                    ['store', $req->user],
                    ['owner_type', 'store']
                ])
                ->join('customers', 'customers.uuid', 'chatboxes.customer')
                ->get();
        }

        return $chatlist;
    }

    function getMessages(Request $req)
    {

        if ($req->owner == 'customer') {
            $messages = Message::select([
                'messages.*',
                'message_status.chatbox',
                'message_status.status'
            ])
                ->leftJoin('message_status', 'message_status.message', 'messages.uuid')
                ->leftJoin('chatboxes', 'chatboxes.uuid', 'message_status.chatbox')
                ->where([
                    ['chatboxes.customer', $req->user],
                    ['chatboxes.owner_type', 'customer']
                ])
                ->orderBy('messages.date', 'DESC')
                ->get()
                ->groupBy('chatbox');
        } else {
            $messages = Message::select([
                'messages.*',
                'message_status.chatbox',
                'message_status.status'
            ])
                ->leftJoin('message_status', 'message_status.message', 'messages.uuid')
                ->leftJoin('chatboxes', 'chatboxes.uuid', 'message_status.chatbox')
                ->where([
                    ['chatboxes.store', $req->user],
                    ['chatboxes.owner_type', 'store']
                ])
                ->orderBy('messages.date', 'DESC')
                ->get()
                ->groupBy('chatbox');
        }

        return $messages->toArray();
    }
}
