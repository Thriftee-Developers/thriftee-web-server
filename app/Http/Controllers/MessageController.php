<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MessageController extends Controller
{
    function sendChat(Request $req)
    {
        event(new Message($req->customer, $req->store, $req->sender, $req->content));
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
