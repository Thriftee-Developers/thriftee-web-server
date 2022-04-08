<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Events\MessageEvent as Chat;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ChatBox;
use App\Models\MessageStatus;

class MessageController extends Controller
{
    function sendChat(Request $req)
    {
        return ["success" => "send success"];
    }
    //
    function sendMessage(Request $req)
    {
        //Found if the data will be duplicate
        $storeChatBox = ChatBox::where("store", $req->store)
            ->where("customer", $req->customer)
            ->where("owner_type", "store")
            ->first();

        $customerChatBox = ChatBox::where("store", $req->store)
            ->where("customer", $req->customer)
            ->where("owner_type", "customer")
            ->first();

        //Ad message in messages
        $message = new Message();
        $message->uuid = Str::uuid();
        $message->customer = $req->customer;
        $message->store = $req->store;
        $message->sender = $req->sender;
        $message->content = $req->content;

        $message->save();

        //Add store status in message_status
        $storeStatus = new MessageStatus();
        $storeStatus->uuid = Str::uuid();
        $storeStatus->message = $message->uuid;

        //Set store status to 1
        if ($req->sender == "store") {
            $storeStatus->status = "1";
        }

        if ($storeChatBox) {
            $storeStatus->chatbox = $storeChatBox->uuid;
        } else {
            $chatBox = new ChatBox();
            $chatBox->owner_type = "store";
            $chatBox->customer = $req->customer;
            $chatBox->store = $req->store;
            $chatBox->save();
        }

        $storeStatus->save();

        $customerStatus = new MessageStatus();
        $customerStatus->uuid = Str::uuid();
        $customerStatus->message = $message->uuid;

        if ($req->sender == "customer") {
            $customerStatus->status = "1";
        }

        if ($customerChatBox) {
            $customerStatus->chatbox = $customerChatBox->uuid;
        } else {
            $chatBox = new ChatBox();
            $chatBox->owner_type = "customer";
            $chatBox->customer = $req->customer;
            $chatBox->store = $req->store;
            $chatBox->save();
        }

        $customerStatus->save();


        event(new Chat($req->customer, $req->store, $req->sender, $req->content));
        return ["success" => "success"];
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
}
