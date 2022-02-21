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

    function addProduct(Request $req){
        $product = new Product;
        $product->uuid=$req->uuid;
        $product->store=$req->store;
        $product->product_id=$req->product_id;
        $product->name=$req->name;
        $product->description=$req->description;

        $error = "";
        if($this->checkProductID($req->product_id)){
            $product->save();
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

    function checkProductID(Request $req){
        $product = Product::where("product_id", $req->product_id)->get();
        if(count($product) > 0 ) return false;
        return true;
    }
}
