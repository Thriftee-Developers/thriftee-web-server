<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

use App\Models\Bid;
use App\Models\Biddings;
use App\Models\CustomerNotification;
use App\Models\Follower;
use App\Models\ProductCategory;
use App\Models\ProductCondition;
use App\Models\ProductImage;
use App\Models\ProductTag;
use App\Models\Store;

class ProductController extends Controller
{
    function search(Request $req)
    {
        $store = DB::select(
            "SELECT
                stores.store_name,
                stores.uuid,
                stores.store_id,
                Count(DISTINCT ratings.uuid) as rating_count,
                Count(DISTINCT products.uuid) as product_count,
                AVG(ratings.rate) as rating
            FROM stores

            LEFT JOIN ratings
            ON stores.uuid = ratings.store

            LEFT JOIN products
            ON  products.store = stores.uuid

            WHERE stores.store_name LIKE '%$req->search%' OR stores.store_id LIKE '%$req->search%'

            GROUP BY stores.uuid"
        );

        $result = [
            "store_result" => $store,
            "product_result" => $this->getProductDetails($req->search),
        ];
        return $result;
    }

    function getProductDetails($value)
    {
        $result = DB::select(
            "SELECT DISTINCT
                products.product_id,
                products.name,
                products.description,
                products.store,
                stores.uuid as store_uuid,
                stores.store_name,
                productimages.path as image_path,

                biddings.uuid as bidding_uuid,
                biddings.minimum,
                biddings.increment,
                biddings.claim,
                biddings.start_time,
                biddings.end_time,
                biddings.status,

                mBids.highest as bid_highest

            FROM products

            INNER JOIN stores
            ON products.store = stores.uuid

            INNER JOIN productcategories
            ON products.uuid = productcategories.product

            LEFT JOIN categories
            ON categories.uuid = productcategories.product_category

            LEFT JOIN (
                SELECT path, product, MIN(name) AS name FROM productimages GROUP BY product
            ) productimages
            ON productimages.product = products.uuid

            LEFT JOIN (
                SELECT *, MAX(created_at) AS max_created_at FROM biddings GROUP BY product
            ) biddings
            ON biddings.product = products.uuid

            LEFT JOIN (
                SELECT bidding, MAX(amount) AS highest
                FROM bids
                GROUP BY bidding
            ) mBids
            ON mBids.bidding = biddings.uuid

            WHERE (products.name LIKE '%$value%' OR products.product_id LIKE '%$value%' OR products.tags LIKE '%$value%' OR categories.name LIKE '%$value%')
            "
        );
        return $result;
    }

    function getAllProducts()
    {
        $result = Product::all();
        return $result;
    }

    function getProduct(Request $req)
    {
        $result = Product::where("uuid", $req->uuid)->first();
        return $result;
    }

    function getProductByID(Request $req)
    {
        $result = Product::select([
            'products.*',
            'stores.uuid as store_uuid',
            'stores.store_id as store_id',
            'stores.store_name'
        ])
            ->where("product_id", $req->product_id)
            ->join('stores', 'stores.uuid', 'products.store')
            ->first();

        $categories = ProductCategory
            ::where('product', $result->uuid)
            ->leftJoin('categories','categories.uuid','productcategories.product_category')
            ->get();
        $result->categories = $categories;

        $condition = ProductCondition
            ::where('product', $result->uuid)
            ->leftJoin('conditions','conditions.uuid','productconditions.product_condition')
            ->first();
        $result->condition = $condition;

        $images = ProductImage
            ::where('product', $result->uuid)
            ->orderBy('name', 'ASC')
            ->get();
        $result->images = $images;

        return $result;
    }

    function getStoreActiveProducts(Request $req)
    {
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
                products.store = '$req->store'
                AND products.status = 'active'

            GROUP BY products.uuid"
        );

        foreach ($bidding as $item) {
            $result = Biddings
                ::where('product', $item->uuid)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($result) {
                $bids = Bid
                    ::where('bidding', $result->uuid)
                    ->orderBy('amount', 'desc')
                    ->get();

                $result->bids = $bids;
            }

            $item->latestbidding = $result;
        }

        return $bidding;
    }

    function getStoreArchivedProducts(Request $req)
    {
        $result = Product::where([
            ['store', $req->store],
            ['status', 'archived']
        ])->get();

        return $result;
    }

    function getStoreCompletedProducts(Request $req)
    {
        $result = Product::where([
            ['store', $req->store],
            ['status', 'sold']
        ])->get();

        return $result;
    }

    function addProduct(Request $req)
    {
        $product = new Product;
        $product->uuid = Str::uuid();
        $product->store = $req->store;
        $product->product_id = $req->product_id;
        $product->name = $req->name;
        $product->description = $req->description;
        $product->tags = $req->tags;

        if ($this->checkProductID($req->product_id)) {
            if ($product->save()) {
                $conditionCtrl = new ConditionController();
                $conditionCtrl->addProductCondition($product->uuid, $req->condition);

                $categoryCtrl = new CategoryController();
                $categories = json_decode($req->categories);
                foreach ($categories as $category) {
                    $categoryCtrl->addProductCategory($product->uuid, $category);
                }

                // $tagCtrl = new TagController();
                // $tags = json_decode($req->tags);
                // foreach ($tags as $tag) {
                //     $tagCtrl->addProductTag($product->uuid, $tag);
                // }

                $mediaCtrl = new MediaController();
                $images = $req->allFiles();
                $mediaCtrl->uploadProductImages($images, $product->uuid);

                $biddingCtrl = new BiddingController();
                $req->product = $product->uuid;
                $biddingCtrl->addBidding($req);


                $followers = Follower::where('store', $req->store)->get();
                $store = Store::where('uuid', $req->store)->first();

                foreach ($followers as $item) {
                    $notif = new CustomerNotification();
                    $notif->uuid = Str::uuid();
                    $notif->customer = $item->customer;
                    $notif->type = 'new_product';
                    $notif->details = json_encode([
                        "store" => $req->store,
                        "store_name" => $store->store_name,
                        "product_name" => $req->name,
                        "product_id" => $req->product_id
                    ]);
                    $notif->date = date("Y-m-d H:i:s");

                    $notif->save();
                }

                return ["success" => "success"];
            } else {
                return ["error" => "Error saving product."];
            }
        } else {
            return ["error" => "The product ID is not unique."];
        }
    }

    function rebidProduct(Request $req)
    {
        $product = Product::where('uuid', $req->product)->first();

        if($product) {
            $biddingCtrl = new BiddingController();
            $add = $biddingCtrl->addBidding($req);

            if($add) {
                $update = $product->update('status', 'for_bidding');

                if($update) {
                    return ["success" => "success"];
                }
                else {
                    return ["error" => "Product status not updated"];
                }
            }
            else {
                return ["error" => "Bidding not created"];
            }
        }
        else {
            return ["error" => "Product not found"];
        }


    }

    function deleteProduct(Request $req)
    {
        $result = Product::where('uuid', $req->uuid)->delete();
        return $result;
    }

    function deleteAllProduct()
    {
        $result = Bid::query()->delete();
        if ($result != "") {
            echo "Bid Deleted!";
            $result = Biddings::query()->delete();
            if ($result != "") {
                echo "Biddings Deleted";
                $result = ProductCategory::query()->delete();
                if ($result != "") {
                    echo "ProductCategory Deleted!";
                    $result = ProductCondition::query()->delete();
                    if ($result != "") {
                        echo "ProductCondition Deleted!";
                        $result = ProductImage::query()->delete();
                        if ($result != "") {
                            echo "ProductImage Deleted!";
                            $result = ProductTag::query()->delete();
                            if ($result != "") {
                                echo "ProductTag Deleted!";
                                $result = Product::query()->delete();
                                if ($result != "") {
                                    echo "Prodyct Deleted!";
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    function checkProductID($product_id)
    {
        $product = Product::where("product_id", $product_id)->get();
        if (count($product) > 0) return false;
        return true;
    }
}
