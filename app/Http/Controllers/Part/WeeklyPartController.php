<?php

namespace App\Http\Controllers\Part;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Part;

class WeeklyPartController extends Controller
{
    public function __invoke(Request $request)
    {
        $parts = Part::with('type')->
            select()->
            addSelect(\Illuminate\Support\Facades\DB::raw("STR_TO_DATE(CONCAT(yearweek(created_at,2),' Sunday'), '%X%V %W') as date"))->
            unofficial()->
            where('official_part_id', null)->
            where(function (Builder $query) {
            $query->orWhereRelation('type', 'type', 'Part')->orWhereRelation('type', 'type', 'Shortcut');
            })->
            when($request->has('order') && $request->input('order') == 'asc', function (Builder $q){
                $q->oldest();
            }, function (Builder $q) {
                $q->latest();
            })->get()->groupBy('date');
      
        return view('tracker.weekly', compact('parts'));
    }
}
