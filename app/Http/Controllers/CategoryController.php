<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categories;
use App\Models\ProductCategory;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    function getCategories(){
        $result = Categories::all();
        return $result;
    }

    function addCategory(Request $req){
        $categories = new Categories();
        $categories->uuid=$req->uuid;
        $categories->name=$req->name;
        $categories->description=$req->description;

        $error="";

        if($this->checkExistingCategory($req)){
            $categories->save();
        }else{
            $error="The categoriy is exisitng.";
        }
        return $error;
    }

    function addProductCategory($product, $category){
        $productCategory = new ProductCategory();
        $productCategory->uuid = Str::uuid();
        $productCategory->product = $product;
        $productCategory->product_category = $category;

        return  $productCategory->save();
    }

    function checkExistingCategory(Request $req){
        $categories = Categories::where("name", $req->name)->get();
        if(count($categories) > 0) return false;
        return true;
    }

    function deleteCategory(Request $req){
        $result = Categories::where("uuid",$req->uuid)->delete();
        return $result;
    }
}
