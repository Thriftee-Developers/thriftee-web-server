<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\FeaturedProduct;
use App\Models\Products;
use App\Models\ProductImage;

class FeaturedProductController extends Controller
{
    function getAllFeaturedProduct(){
        $featuredProducts = FeaturedProduct::join("biddings","biddings.uuid","=","featuredproducts.bidding")
                                ->join("products","products.uuid","=","biddings.product")
                                ->get();
        $result = array();
        $i = 0;
        foreach($featuredProducts as $value){
            $productImage = ProductImage::where("product",$value->product)->first();
            $result[$i] = [
                "name"=>$featuredProducts[$i]->name,
                "description"=>$featuredProducts[$i]->description,
                "path"=>$productImage->path
            ];
            $i = 1;
        }

        return $result;
    }
    //
    function addFeaturedProduct(Request $req){
        $featuredProducts = new FeaturedProduct();
        $featuredProducts->bidding = $req->bidding;
        $featuredProducts->description = $req->description;

        if($this->checkFeaturedProducts($req->bidding)){
            $featuredProducts->save();
            return ["success" => "success"];
        }else{
            return ["error" => "Products is featured."];
        }
    }

    function updateFeaturedProducts(Request $req){
        $featuredProducts = FeaturedProduct::where("bidding",$req->bidding)->first();
        if($featuredProducts){
            $result = $featuredProducts->update(["description"=> $req->description]);
            return ["success" => "success"];
        }else{
            return ["error" => "There's no match bidding id."];
        }
    }

    function checkFeaturedProducts($uuid){
        $result = FeaturedProduct::where("bidding",$uuid)->get();
        if(count($result)>0){
            return false;
        }
        return true;
    }

    function deleteFeaturedProducts(Request $req){
        $result = FeaturedProduct::where("bidding",$req->bidding)->delete();
        return $result;
    }
}
