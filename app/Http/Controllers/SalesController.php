<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Bid;
use App\Models\Biddings;
use App\Models\Store;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{

    function filterSoldItemsAdminSale(Request $req)
    {

        $result = $this->getProductDetails("= 'success'",  str_replace("'", "\'", $req->search), $req->start_date, $req->end_date);
        return $result;
    }

    function filterUnclaimedItemsAdminSale(Request $req)
    {
        $result = $this->getProductDetails('= "under_transaction"', str_replace("'", "\'", $req->search), $req->start_date, $req->end_date);
        return $result;
    }

    function getProductDetails($status, $value, $start_date, $end_date)
    {
        if ($value == "") {
            $value = "All";
        }
        if ($value == "All") {
            $showAll = "OR categories.name != 'all'";
        } else {
            $showAll = "";
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
                GROUP_CONCAT(DISTINCT categories.name SEPARATOR ', ') as categories_name,

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

            WHERE ( biddings.status $status AND ((biddings.end_time >= '$start_date' AND biddings.end_time <= '$end_date') OR products.product_id LIKE '%$value%' OR products.name LIKE '%$value%' OR stores.store_name LIKE '%$value%' OR categories.name LIKE '%$value%' $showAll))
            GROUP BY productcategories.product
            "
        );
        return $result;
    }

    function filterUserWithUnclaimItems()
    {
        $result = DB::select(
            "SELECT
                customers.uuid,
                customers.fname,
                customers.lname,
                bids.highest,
                products.product_id,
                products.name,
                stores.store_name,
                GROUP_CONCAT(DISTINCT categories.name SEPARATOR ', ') as categories_name
            FROM customers

            -- INNER JOIN bids
            -- (
            --     SELECT uuid, bidding, customer, MAX(amount) AS highest
            --     FROM bids
            --     )
            -- ON bids.customer = customers.uuid
            
            LEFT OUTER JOIN (
                SELECT customer, bidding, MAX(amount) AS highest
                FROM bids
                GROUP BY bidding
            ) bids
            ON bids.customer = customers.uuid

            LEFT JOIN biddings
            ON bids.bidding = biddings.uuid

            LEFT JOIN products
            ON biddings.product = products.uuid

            INNER JOIN stores
            ON products.store = stores.uuid

            INNER JOIN productcategories
            ON products.uuid = productcategories.product

            LEFT JOIN categories
            ON categories.uuid = productcategories.product_category

            WHERE biddings.status = 'under_transaction'
            GROUP BY productcategories.product
            "
        );

        return $result;
    }
}
