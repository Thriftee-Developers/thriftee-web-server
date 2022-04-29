<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categories;
use App\Models\ProductCategory;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    function getAllCategoryName()
    {
        $result = Categories::orderBy("name", "asc")->select("name")->get();
        return $data;
    }

    function getAllCategory()
    {
        $result = Categories::join("productcategories", "productcategories.product_category", "categories.uuid");
        $result = DB::select(
            "SELECT
                categories.uuid,
                categories.name,
                categories.description,
                Count(productcategories.uuid) as count
            FROM categories

            LEFT JOIN productcategories
            ON categories.uuid = productcategories.product_category

            GROUP BY categories.uuid
            ORDER BY categories.name
            "
        );

        return $result;
    }

    function getCategory(Request $req)
    {
        $result = Categories::where("uuid", $req->uuid)->first();
        return $result;
    }

    function addCategory(Request $req)
    {
        $categories = new Categories();
        $categories->uuid = Str::uuid();
        $categories->name = $req->name;
        $categories->description = $req->description;

        if ($this->checkExistingCategory($req)) {
            $categories->save();
            return ["success" => "success"];
        } else {
            return ["error" => "The category is exisitng."];
        }
    }

    function updateCategory(Request $req)
    {
        $category = Categories::where("uuid", $req->uuid)->first();
        if ($category) {
            $result = $category->update([
                "name" => $req->name,
                "description" => $req->description
            ]);
            if ($result) {
                return ["success" => "success"];
            } else {
                return ["error" => "Error updating category!"];
            }
        } else {
            return ["error" => "Category Not Found!"];
        }
    }

    function addProductCategory($product, $category)
    {
        $productCategory = new ProductCategory();
        $productCategory->uuid = Str::uuid();
        $productCategory->product = $product;
        $productCategory->product_category = $category;

        return $productCategory->save();
    }

    function getCategoryByProduct(Request $req)
    {
        $result = ProductCategory::join('categories', 'productcategories.product_category', '=', 'categories.uuid')
            ->where("product", $req->product)
            ->get();
        return $result;
    }

    function getProductsByCategory(Request $req)
    {
        $result = DB::select(
            "SELECT
                categories.*,
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

            FROM categories

            INNER JOIN productcategories
            ON productcategories.product_category = categories.uuid

            INNER JOIN products
            ON productcategories.product = products.uuid

            INNER JOIN stores
            ON products.store = stores.uuid

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

            WHERE categories.uuid='$req->uuid'
            "
        );

        return $result;
        //return $this->paginate($result);
    }

    function paginate($items, $perPage = 15, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

    function checkExistingCategory(Request $req)
    {
        $categories = Categories::where("name", $req->name)->get();
        if (count($categories) > 0) return false;
        return true;
    }

    function getCountCategory()
    {
        $result = ProductCategory::all()->countBy("product_category");
        return $result;
    }

    function deleteCategory(Request $req)
    {
        if ($this->checkUsedCategory($req->uuid)) {
            $result = Categories::where("uuid", $req->uuid)->delete();
            return ["success" => "success"];;
        } else {
            return ["error" => "Used Category!"];
        }
    }

    function checkUsedCategory($uuid)
    {
        $usedCategory = ProductCategory::where("product_category", $uuid)->get()->count();
        if ($usedCategory == 0) {
            return true;
        } else {
            return false;
        }
    }
}
