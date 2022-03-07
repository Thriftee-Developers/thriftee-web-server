<?php

namespace App\Http\Controllers;

use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    //
    function uploadProductImages(Request $req)
    {
        $folder = 'public/'.$req->folder_name;

        if(!Storage::exists($folder)) {
            Storage::makeDirectory($folder, 0775, true, true);
        }

        $files = $req->allFiles();

        if(count($files) > 0) {
            $i = 0;
            foreach($files as $file) {
                $extension = $file->getClientOriginalExtension();
                $filename = $req->product_uuid ."_" . (string) $i;
                $result = $file->storeAs($folder, $filename.".".$extension);
                $i++;

                $productImage = new ProductImage();
                $productImage->uuid = Str::uuid()->toString();
                $productImage->product = $req->product_uuid;
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
