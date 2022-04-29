<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Bid;
use App\Models\Biddings;
use App\Models\Store;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    //

    function filterSoldItemsAdminSale(Request $req)
    {
        $result = $this->getProductDetails($req->search, $req->start_date, $req->end_date);
        return $result;
    }

    function getProductDetails($value, $start_date, $end_date)
    {
        if ($value == "") {
            $value = "|||";
        }
        $result = DB::select(
            "SELECT
                products.product_id,
                products.name,
                products.description,
                products.store,
                stores.uuid as store_uuid,
                stores.store_name,
                productimages.path as image_path,
                group_concat(categories.name) as categories_name,

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
            -- biddings.status = 'success' AND 
            WHERE ((biddings.end_time >= '$start_date' AND biddings.end_time <= '$end_date') OR stores.store_name LIKE '%$value$' OR categories.name LIKE '%$value%')
            GROUP BY productcategories.product
            "
        );
        return $result;
    }
}
