<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\FeaturedProduct;

class FeaturedProductController extends Controller
{
    function getAllFeaturedProduct(){
        $result = FeaturedProduct::join("product","products.uuid", "=","featuredProducts.product")->get();
        return $result;
    }
    //
    function addFeaturedProduct(Request $req){
        $featuredProduct = new FeaturedProduct();
        $featuredProduct->product = $req->product;
        $featuredProduct->description = $req->description;

        if($this->checkFeaturedProduct($req->product)){
            $featuredProduct->save();
            return ["success" => "success"];
        }else{
            return ["error" => "Product is featured."];
        }
    }

    function updateFeaturedProduct(Request $req){
        $featuredProduct = FeaturedProduct::where("product",$req->product)->first();
        if($featuredProduct){
            $result = $featuredProduct->update(["description"=> $req->description]);
            return ["success" => "success"];
        }else{
            return ["error" => "There's no match product id."];
        }
    }

    function checkFeaturedProduct($uuid){
        $result = FeaturedProduct::where("product",$uuid)->get();
        if(count($result)>0){
            return false;
        }
        return true;
    }

    function deleteFeaturedProduct(Request $req){
        $result = FeaturedProduct::where("product",$req->product)->delete();
        return $result;
    }
}
