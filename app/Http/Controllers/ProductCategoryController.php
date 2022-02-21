<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\ProductCategory;

class ProductCategoryController extends Controller
{
    function getProductCategory(Request $req){
        $result = ProductCategory::where("product", $req->product)->orWhere("product_category", $req->product_category)->get();
        return $result;
    }

    function addProductCategory(Request $req){
        $productCategory = new ProductCategory();
        $productCategory->uuid=$req->uuid;
        $productCategory->product=$req->product;
        $productCategory->product_category=$req->product_category;

        $error="";

        if($this->checkExistingCategory($req)){
            $productCategory->save();
        }else{
            $error="The category is exisitng.";
        }
        return $error;
    }

    function checkExistingCategory(Request $req){
        $category = ProductCategory::where("product", $req->product)->where("product_category", $req->product_category)->get();
        if(count($category) > 0) return false;
        return true;
    }

    function deleteProductCategory(Request $req){
        $result = ProductCategory::where("uuid",$req->uuid)->delete();
        return $result;
    }
}
