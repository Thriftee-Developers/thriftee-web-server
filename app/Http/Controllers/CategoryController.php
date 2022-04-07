<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categories;
use App\Models\ProductCategory;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    function getAllCategory()
    {
        $result = Categories::all();
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
        if ($this->checkUsedCategory($req->uuid)) {
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
        } else {
            return ["error" => "Used Category!"];
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

    function checkExistingCategory(Request $req)
    {
        $categories = Categories::where("name", $req->name)->get();
        if (count($categories) > 0) return false;
        return true;
    }

    function deleteCategory(Request $req)
    {
        if ($this->checkUsedCategory($req->uuid) <= 0) {
            $result = Categories::where("uuid", $req->uuid)->delete();
            return ["success" => "success"];;
        } else {
            return ["error" => "Used Category!"];
        }
    }

    function checkUsedCategory($uuid)
    {
        $usedCategory = ProductCategory::where("product_category", $uuid)->get()->count();
        if ($usedCategory <= 0) {
            return true;
        } else {
            return false;
        }
    }
}
