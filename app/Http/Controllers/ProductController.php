<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

use App\Models\Bid;
use App\Models\Biddings;
use App\Models\ProductCategory;
use App\Models\ProductCondition;
use App\Models\ProductImage;
use App\Models\ProductTag;

class ProductController extends Controller
{
    function getAllProducts(){
        $result = Product::all();
        return $result;
    }

    function getProduct(Request $req){
        $result = Product::where("uuid", $req->uuid)->first();
        return $result;
    }

    function getProductByID(Request $req){
        $result = Product::where("product_id", $req->product_id)->first();
        return $result;
    }

    function getStoreActiveProducts(Request $req){
        $bidding = DB::select(
            "SELECT
                products.*,
                Count(biddings.uuid) as bidding_count,
                productimages.path as image
            FROM products

            -- Get bidding counts
            LEFT JOIN biddings
            ON biddings.product = products.uuid

            -- Get Latest Bidding
            -- LEFT JOIN (
            --     SELECT *, MAX(end_time) as latest_bidding FROM biddings GROUP BY product
            -- ) latestbidding
            -- ON latestbidding.product = products.uuid

            -- Get Latest bidding's highest bid
            -- LEFT OUTER JOIN (
            --     SELECT bidding, MAX(amount) AS highest
            --     FROM bids
            --     GROUP BY bidding
            -- ) mBids
            -- ON mBids.bidding = latestbidding.uuid

            -- Get product's first image
            LEFT JOIN (
                SELECT path, product, MIN(name) AS name FROM productimages GROUP BY product
            ) productimages
            ON productimages.product = products.uuid

            WHERE
                products.store = '".$req->store."'
                AND products.status = 0

            GROUP BY products.uuid"
        );

        foreach($bidding as $item) {
            $result = Biddings
                ::where('product',$item->uuid)
                ->orderBy('created_at', 'desc')
                ->first();

            if($result) {
                $bids = Bid
                    ::where('bidding', $result->uuid)
                    ->orderBy('amount', 'desc')
                    ->get();

                $result->bids = $bids;
            }

            $item->latest_bidding = $result;
        }

        return $bidding;
    }

    function getStoreArchivedProducts(Request $req){
        $result = Product::where([
            ['store', $req->store],
            ['status', -1]
        ])->get();

        return $result;
    }

    function getStoreCompletedProducts(Request $req){
        $result = Product::where([
            ['store', $req->store],
            ['status', 1]
        ])->get();

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
        $result = Bid::query()->delete();
        if($result != ""){
            echo "Bid Deleted!";
            $result = Biddings::query()->delete();
            if($result != ""){
                echo "Biddings Deleted";
                $result = ProductCategory::query()->delete();
                if($result != ""){
                    echo "ProductCategory Deleted!";
                    $result = ProductCondition::query()->delete();
                    if($result != ""){
                        echo "ProductCondition Deleted!";
                        $result = ProductImage::query()->delete();
                        if($result != ""){
                            echo "ProductImage Deleted!";
                            $result = ProductTag::query()->delete();
                            if($result != ""){
                                echo "ProductTag Deleted!";
                                $result = Product::query()->delete();
                                if($result!=""){
                                    echo "Prodyct Deleted!";
                                }
                            }
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
