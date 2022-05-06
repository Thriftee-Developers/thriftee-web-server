<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Content;

class ContentController extends Controller
{
    //
    function getAllContents(Request $req)
    {
        $result = Content::all();
        return $result;
    }

    function addContent(Request $req)
    {
        $content = new Content();
        $content->uuid = Str::uuid();
        $content->title = $req->title;
        $content->subtitle = $req->subtitle;
        $content->content = $req->content;

        $content->save();
    }

    function updateContent(Request $req)
    {
        $content = Content::where("uuid", $req->uuid)->first();
        if ($content) {
            $result = $content->update(["title" => $req->title, "subtitle" => $req->subtitle, "content" => $req->content]);
            return ["success" => "success"];
        } else {
            return ["error" => "There's no match bidding id."];
        }
    }

    function removeContent(Request $req)
    {
        $result = Content::where("uuid", $req->uuid)->delete();

        if ($result) {
            return ['success' => 'success'];
        }
    }
}
