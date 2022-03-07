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
        $folder = 'public/images/product/'.$product_uuid;

        if(!Storage::exists($folder)) {
            Storage::makeDirectory($folder, 0775, true, true);
        }

        if(count($images) > 0) {
            $i = 0;
            foreach($images as $file) {
                $extension = $file->getClientOriginalExtension();
                $filename = $product_uuid ."_" . (string) $i;
                $result = $file->storeAs($folder, $filename.".".$extension);
                $i++;

                $productImage = new ProductImage();
                $productImage->uuid = Str::uuid()->toString();
                $productImage->product = $product_uuid;
                $productImage->path = $result;
                $productImage->save();

            }
            return "success";
        }
        else {
            return "No files";
        }

    }

    function getProductImages (Request $req) {
        return ProductImage::where('product', $req->product_uuid)
            ->orderBy('name','asc')
            ->get();
    }

}
