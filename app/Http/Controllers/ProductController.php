<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Str;

use App\Models\Bid;
use App\Models\Biddings;
use App\Models\ProductCategory;
use App\Models\ProductCondition;
use App\Models\ProductImage;
use App\Models\Tag;

class ProductController extends Controller
{
    function getAllProducts(){
        $result = Product::all();
        return $result;
    }

    function getProduct(Request $req){
        $result = Product::where("uuid", $req->uuid)->get();
        return $result;
    }

    function getProductByID(Request $req){
        $result = Product::where("product_id", $req->product_id)->first();
        return $result;
    }

    function getConditionByProduct(Request $req){
        $result = Product::join("productconditions", "productconditions.product","=","products.uuid")->where("product", $req->product)->get();
        return $result;
    }

    function getStoreProducts(Request $req){
        $result = Product::where('store', $req->store)->get();
        return $result;
    }

    function addProduct(Request $req){
        $product = new Product;
        $product->uuid=Str::uuid();
        $product->store=$req->store;
        $product->product_id=$req->product_id;
        $product->name=$req->name;
        $product->description=$req->description;

        if($this->checkProductID($req->product_id)){
            if($product->save()) {
                $conditionCtrl = new ConditionController();
                $conditionCtrl->addProductCondition($product->uuid, $req->condition);

                $categoryCtrl = new CategoryController();
                $categories = json_decode($req->categories);
                foreach($categories as $category) {
                    $categoryCtrl->addProductCategory($product->uuid, $category);
                }

                $tagCtrl = new TagController();
                $tags = json_decode($req->tags);
                foreach($tags as $tag) {
                    $tagCtrl->addProductTag($product->uuid, $tag);
                }

                $mediaCtrl = new MediaController();
                $images = $req->allFiles();
                $mediaCtrl->uploadProductImages($images, $product->uuid);

                $biddingCtrl = new BiddingController();
                $req->product = $product->uuid;
                $biddingCtrl->addBidding($req);

                return ["success" => "success"];
            }
            else {
                return ["error" => "Error saving product."];
            }

        }
        else{
            return ["error" => "The product ID is not unique."];
        }
    }

    function deleteProduct(Request $req)
    {
        $result = Product::where('uuid', $req->uuid)->delete();
        return $result;
    }

    function deleteAllProduct(){
        $result = Bid::truncate();
        if($result == ""){
            $result = Biddings::truncate();
            if($result == ""){
                $result = ProductCategory::truncate();
                if($result == ""){
                    $result = ProductCondition::truncate();
                    if($result == ""){
                        $result = ProductImage::truncate();
                        if($result == ""){

                        }
                    }
                }
            }
        }
    }

    function checkProductID($product_id){
        $product = Product::where("product_id", $product_id)->get();
        if(count($product) > 0 ) return false;
        return true;
    }
}
