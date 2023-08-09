<?php

namespace App\Http\Controllers\Search;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PartSearchResource;
use App\Models\Part;

class QuickSearchController extends Controller
{
    public function __invoke(Request $request) {
        $input = $request->all();
        if (!empty($input['s']) && is_string($input['s'])) {
            $json_limit = config('ldraw.search.quicksearch.limit');
            $uparts = Part::unofficial()->searchPart($input['s'], 'header')->orderBy('filename')->take($json_limit)->get();
            $oparts = Part::official()->searchPart($input['s'], 'header')->orderBy('filename')->take($json_limit)->get();
            return [
                'results' => [
                    'oparts' => ['name' => "Official\nParts", 'results' => PartSearchResource::collection($oparts)],
                    'uparts' => ['name' => "Unofficial\nParts", 'results' => PartSearchResource::collection($uparts)],
                ]
            ];
        }
        
        return response(400);
    }
}
