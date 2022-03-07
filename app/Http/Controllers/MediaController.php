<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    //
    function uploadFiles(Request $req)
    {
        $folder = 'public/'.$req->folder_name;

        if(!Storage::exists($folder)) {
            Storage::makeDirectory($folder, 0775, true, true);
        }

        $files = $req->file('media');
        if($req->hasFile('media')) {
            $paths = array();
            //$paths = $files->storeAs($folder, $files->getClientOriginalName());
            foreach($files as $file) {
                $result = $file->storeAs($folder, $file->getClientOriginalName());
                // $result = Storage::disk([
                //     public_path('storage') => storage_path('app/public'),
                //     public_path('images') => storage_path('app/images')
                // ])
                // ->put($file->getClientOriginalName(), file_get_contents($file));

                $paths[] = $result;
            }
        }
        else {
            $paths = "No files";
        }

        return $paths;
    }
}
