<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categories;

class CategoryController extends Controller
{
    //
    function getCategories(Request $req){
        $result = Categories::where("uuid", $req->$uuid)->get();
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
