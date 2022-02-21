<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\ProductCondition;

class ProductConditionController extends Controller
{
    //Show the product condition by passing a paramater product or product condition or the same.
    function getProductCondition(Request $req){
        $result = ProductCondition::where("product", $req->product)->orWhere("product_condition", $req->product_condition)->get();
        return $result;
    }

    function addProductCondition(Request $req){
        $productCondition = new ProductCondition();
        $productCondition->uuid=$req->uuid;
        $productCondition->product=$req->product;
        $productCondition->product_condition=$req->product_condition;

        $error="";

        if($this->checkExistingCondition($req)){
            $productCondition->save();
        }else{
            $error="The condition is exisitng.";
        }
        return $error;
    }

    function checkExistingCondition(Request $req){
        $condition = ProductCondition::where("product", $req->product)->where("product_condition", $req->product_condition)->get();
        if(count($condition) > 0) return false;
        return true;
    }

    function deleteProductCondition(Request $req){
        $result = ProductCondition::where("uuid",$req->uuid)->delete();
        return $result;
    }
}
