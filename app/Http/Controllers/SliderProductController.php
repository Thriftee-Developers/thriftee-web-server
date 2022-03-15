<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\SliderProduct;

class SliderProductController extends Controller
{
    function getAllSliderProduct(){
        $result = SliderProduct::all();
        return $result;
    }
    //
    function addSliderProduct(Request $req){
        $sliderProduct = new SliderProduct();
        $sliderProduct->product = $req->product;
        $sliderProduct->description = $req->description;

        if($this->checkSliderProduct($req->product)){
            $sliderProduct->save();
            return ["success" => "success"];
        }else{
            return ["error" => "Product is slider."];
        }
    }

    function updateSliderProduct(Request $req){
        $sliderProduct = SliderProduct::where("product",$req->product)->first();
        if($sliderProduct){
            $result = $sliderProduct->update(["description"=> $req->description]);
            return ["success" => "success"];
        }else{
            return ["error" => "There's no match product id."];
        }
    }

    function checkSliderProduct($uuid){
        $result = SliderProduct::where("product",$uuid)->get();
        if(count($result)>0){
            return false;
        }
        return true;
    }

    function deleteSliderProduct(Request $req){
        $result = SliderProduct::where("product",$req->product)->delete();
        return $result;
    }
}
