<?php

namespace App\Http\Controllers\Part;

use App\Http\Controllers\Controller;
use App\Models\Part;

class PartDownloadController extends Controller
{
    /**
     * Stream download from database.
     *
     * @param  \App\Models\Part  $part
     * @return \Illuminate\Http\Response
     */

    public function __invoke(Part $part) {
        if ($part->updated_at->format('U') < date_format(date_create(request()->header('If-Modified-Since', date('r', 0))), 'U')
        ) {
            return response(null ,304)->header('Last-Modified', $part->updated_at->format('r'));
        } else {
            return response()->streamDownload(function() use ($part) { 
                echo $part->get(); 
            }, 
            basename($part->filename), 
            [
                'Content-Type' => $part->isTexmap() ? 'image/png' : 'text/plain',
                'Last-Modified' => $part->updated_at->format('r')
            ]);
        }
    }
}
