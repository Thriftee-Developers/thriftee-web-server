<?php

namespace App\Http\Controllers;

use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    //
    function makeDirectory(Request $req)
    {
        if(!Storage::exists($req->folder_name)) {
            $result = Storage::makeDirectory($req->folder_name, 0755);
            if($result) {
                return ["success" => "success"];
            }
            else {
                return ["error" => "Error"];
            }
        }
        else {
            return ["error" => "Folder already existed"];
        }
    }

    function uploadProductImages($images, $product_uuid)
    {
        $path = 'images/product/'.$product_uuid;
        $folder = 'public/'.$path;

        if(!Storage::exists($folder)) {
            Storage::makeDirectory($folder, 0755);
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
                $productImage->path = $path."/".$filename.".".$extension;
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
