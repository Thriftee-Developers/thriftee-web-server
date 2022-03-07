<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            $paths = array();
            $i = 0;
            foreach($files as $file) {
                $result = $file->storeAs($folder, $req->product_uuid."_".(string)$i);
                $paths[] = $result;
                $i++;
            }
        }
        else {
            $paths = "No files";
        }

        return $paths;
    }

}
