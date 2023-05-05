<?php

namespace App\Http\Controllers\Part;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\PartEvent;

class LatestPartsController extends Controller
{
    public function index(Request $request) {
        $events = PartEvent::with(['part'])->where('initial_submit', true)->whereHas('parts', function ($q) {
            $q->whereRelation('type', 'folder', 'parts/');
        })->latest()->take(8)->get();
        return \App\Http\Resources\LatestPartsResource::collection($events);
    }
    
}