<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Conditions;
class ConditionController extends Controller
{
    //
    function getConditions(Request $req){
        $result = Categories::where("uuid", $req->$uuid)->get();
        return $result;
    }

    function addCondition(Request $req){
        $conditions = new Conditions();
        $conditions->uuid=$req->uuid;
        $conditions->name=$req->name;
        $conditions->description=$req->description;

        $error="";

        if($this->checkExistingCondition($req)){
            $conditions->save();
        }else{
            $error="The condition is exisitng.";
        }
        return $error;
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
