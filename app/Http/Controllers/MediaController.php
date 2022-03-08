<?php

namespace App\Http\Controllers;

use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    //
    function uploadProductImages($images, $product_uuid)
    {
        $path = 'images/product/'.$product_uuid;
        $folder = 'public/'.$path;

        if(!Storage::exists($folder)) {
            Storage::makeDirectory($folder, 0775, true, true);
        }

        if(count($images) > 0) {
            $i = 0;
            foreach($images as $file) {
                $extension = $file->getClientOriginalExtension();
                $filename = $product_uuid ."_" . (string) $i;
                $file->storeAs($folder, $filename.".".$extension);
                $i++;

                $productImage = new ProductImage();
                $productImage->uuid = Str::uuid()->toString();
                $productImage->product = $product_uuid;
                $productImage->name = $filename;
                $productImage->path = $path;
                $productImage->save();

            }
            return ["success" => "success"];
        }
        else {
            return ["error" => "No files."];
        }

    }

    function getProductImages (Request $req) {
        return ProductImage::where('product', $req->product_uuid)
            ->orderBy('path','asc')
            ->get();
    }

}
