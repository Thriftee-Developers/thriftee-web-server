<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    //
    function uploadFiles(Request $req)
    {
        $files = $req->file('media');
        $folder = 'public/'.$req->folder_name;

        if(!Storage::exists($folder)) {
            Storage::makeDirectory($folder, 0775, true, true);
        }

        if($req->hasFile('media')) {
            $paths = array();
            if(!empty($files)) {
                foreach($files as $file) {
                    $result = Storage::disk(['drivers' => 'local', 'root' => $folder])
                        ->put($file->getClientOriginalName(), file_get_contents($file));

                    $paths[] = $result;
                }
            }
        }
        else {
            $paths = "No files";
        }

        return $paths;
    }
}
