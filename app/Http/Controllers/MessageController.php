<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Events\MessageEvent as Chat;
use Illuminate\Http\Request;
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

    function seenMessages(Request $req)
    {
        $uuids = json_decode($req->messages);

        foreach ($uuids as $uuid) {
            $messages = Message::where('uuid', $uuid);
            $messages->update(['status' => 1]);
        }
    }
}
