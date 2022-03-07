<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    function getAllProducts(){
        $result = Product::all();
        return $result;
    }

    function getStoreProducts(Request $req){
        $result = Product::where('store', $req->store)->get();
        return $result;
    }

    function addProduct(Request $req){
        // $product = new Product;
        // $product->uuid=Str::uuid();
        // $product->store=$req->store;
        // $product->product_id=$req->product_id;
        // $product->name=$req->name;
        // $product->description=$req->description;

        // if($this->checkProductID($req->product_id)){
        //     if($product->save()) {
        //         $conditionCtrl = new ConditionController();
        //         $conditionCtrl->addProductCondition($product->uuid, $req->condition);

        //         $categoryCtrl = new CategoryController();
        //         foreach($req->categories as $category) {
        //             $categoryCtrl->addProductCategory($product->uuid, $category);
        //         }

        //         $tagCtrl = new TagController();
        //         foreach($req->tags as $tag) {
        //             $tagCtrl->addProductTag($product->uuid, $tag);
        //         }

        //         $mediaCtrl = new MediaController();
        //         $images = $req->allFiles();
        //         $mediaCtrl->uploadProductImages($images, $product->uuid);

        //         $biddingCtrl = new BiddingController();
        //         $req->product = $product->uuid;
        //         $biddingCtrl->addBidding($req);

        //         $error = "success";
        //     }
        //     else {
        //         $error = "Error saving product";
        //     }

        // }
        // else{
        //     $error = "The product ID is not unique.";
        // }

        $res = "";
        $categories = json_decode($req->categories);
        foreach($categories as $category) {
            $res = $res."===".$category;
        }
        return $res;
    }

    function deleteProduct(Request $req)
    {
        $result = Product::where('uuid', $req->uuid)->delete();
        return $result;
    }

    function checkProductID($product_id){
        $product = Product::where("product_id", $product_id)->get();
        if(count($product) > 0 ) return false;
        return true;
    }
}
