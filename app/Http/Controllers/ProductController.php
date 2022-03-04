<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

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
        $product = new Product;
        $product->uuid=$req->uuid;
        $product->store=$req->store;
        $product->product_id=$req->product_id;
        $product->name=$req->name;
        $product->description=$req->description;

        // if($req->hasFile('image')) {
        //     $req->validate([
        //         'image' => 'mimes:jpeg,bmp,png' // Only allow .jpg, .bmp and .png file types.
        //     ]);

        //     $req->file->store('product', 'public');
        // }

        if($this->checkProductID($req->product_id)){
            $product->save();
            $error = "success";
        }
        else{
            $error = "The product ID is not unique.";
        }
        return $error;
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
