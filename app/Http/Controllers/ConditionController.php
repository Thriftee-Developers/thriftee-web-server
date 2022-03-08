<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Conditions;
use App\Models\ProductCondition;
use Illuminate\Support\Str;

class ConditionController extends Controller
{
    function getConditions(){
        $result = Conditions::all();
        return $result;
    }
    
    function getConditionByProduct(Request $req){
        $result = ProductCondition::where("product", $req->product)->get();
        return $result;
    }

    function addCondition(Request $req){
        $conditions = new Conditions();
        $conditions->uuid=Str::uuid();
        $conditions->name=$req->name;
        $conditions->description=$req->description;

        $error="";

        if($this->checkExistingCondition($req)){
            $conditions->save();
            return ["success" => "success"];
        }else{
            return ["error" => "The condition is exisitng."];
        }
    }

    function addProductCondition($product, $condition){
        $productCondition = new ProductCondition();
        $productCondition->uuid = Str::uuid();
        $productCondition->product = $product;
        $productCondition->product_condition = $condition;

        return $productCondition->save();
    }

    function checkExistingCondition(Request $req){
        $condition = Conditions::where("name", $req->name)->get();
        if(count($condition) > 0) return false;
        return true;
    }

    function deleteCondition(Request $req){
        $result = Conditions::where("uuid",$req->uuid)->delete();
        return $result;
    }
}
