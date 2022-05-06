<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\FeaturedProduct;
use App\Models\Products;
use App\Models\ProductImage;
use Illuminate\Support\Facades\DB;

class FeaturedProductController extends Controller
{
    function getAllFeaturedProduct()
    {
        $result = DB::select(
            "SELECT
                products.product_id,
                products.name,
                featuredproducts.description,
                stores.store_name,
                products.store,
                stores.uuid as store_uuid,
                productimages.path as path,

                biddings.uuid as bidding_uuid,
                biddings.minimum,
                biddings.increment,
                biddings.claim,
                biddings.start_time,
                biddings.end_time,
                biddings.status

            FROM featuredproducts

            LEFT JOIN biddings
            ON biddings.uuid = featuredproducts.bidding

            INNER JOIN products
            ON biddings.product = products.uuid

            INNER JOIN stores
            ON products.store = stores.uuid

            LEFT JOIN (
                SELECT path, product, MIN(name) AS name FROM productimages GROUP BY product
            ) productimages
            ON productimages.product = products.uuid
            "
        );


        return $result;
    }
    //
    function addFeaturedProduct(Request $req)
    {
        $featuredProducts = new FeaturedProduct();
        $featuredProducts->bidding = $req->bidding;
        $featuredProducts->description = $req->description;

        if ($this->checkFeaturedProducts($req->bidding)) {
            $featuredProducts->save();
            return ["success" => "success"];
        }

        return ["error" => "Products is featured."];
    }

    function updateFeaturedProduct(Request $req)
    {
        $featuredProducts = FeaturedProduct::where("bidding", $req->bidding)->first();
        if ($featuredProducts) {
            $result = $featuredProducts->update(["description" => $req->description]);
            return ["success" => "success"];
        } else {
            return ["error" => "There's no match bidding id."];
        }
    }

    function checkFeaturedProducts($uuid)
    {
        $result = FeaturedProduct::where("bidding", $uuid)->get();
        if (count($result) > 0) {
            return false;
        }
        return true;
    }

    function deleteFeaturedProduct(Request $req)
    {
        $result = FeaturedProduct::where("bidding", $req->bidding)->delete();
        return $result;
    }
}
