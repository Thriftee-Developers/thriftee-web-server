<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    //
    function uploadImages(Request $req)
    {
        $files = $req->file('file');
        $folder = public_path('../public/storage/'.$req->folder_name.'/');

        if(!Storage::exists($folder)) {
            Storage::makeDirectory($folder, 0775, true, true);
        }

        $paths = array();
        if(!empty($files)) {
            foreach($files as $file) {
                $result = Storage::disk(['drivers' => 'local', 'root' => $folder])
                    ->put($file->getClientOriginalName(), file_get_contents($file));

                array_push($paths, $result);
            }
        }

        return $paths;
    }
}
