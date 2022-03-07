<?php

namespace App\Http\Controllers;

use App\Models\ProductTag;
use App\Models\Tag;
use Illuminate\Support\Str;

class TagController extends Controller
{
    //
    function addProductTag($product, $tag_name){

        //Check if tag is exist
        $tag = Tag::where('name', $tag_name)->first();

        if(!$tag) {
            //Add New Tag
            $tag = new Tag();
            $tag->uuid = Str::uuid();
            $tag->name = $tag_name;
            $tag->save();
        }

        $productTag = new ProductTag();
        $productTag->uuid = Str::uuid();
        $productTag->product = $product;
        $productTag->product_tag = $tag->uuid;

    }

    function removeProductTag($product) {
        $result = ProductTag::where('product', $product)->get();
        $result->delete();
    }
}
