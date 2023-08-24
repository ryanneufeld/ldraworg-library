<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Part;
use App\Models\Vote;

class UserDashboardController extends Controller
{
    public function __construct() {
        $this->middleware('auth');
    }

    public function __invoke() {
        $user = Auth::user();
        $partSort = [['vote_sort','asc'],['type.folder', 'asc'],['description', 'asc']];
        $submits = Part::unofficial()
            ->whereHas('events', function($q){
                $q->whereRelation('part_event_type', 'slug', 'submit')->where('user_id', Auth::user()->id);
            })
            ->get()
            ->sortBy($partSort);
        $userready = Part::userReady()
            ->orderby('vote_sort')
            ->orderBy('part_type_id')
            ->oldest()
            ->get();    
        $submitIds = $submits->pluck('id')->all();
        $votes = Vote::with(['part'])->where('user_id', $user->id)->take(500)->get()->sortBy([['vote_type_code','asc'],['part.type.folder', 'asc'],['part.description', 'asc']]);
        $tracked = $user->notification_parts()->take(500)->get()->sortBy($partSort);
        $events = \App\Models\PartEvent::unofficial()
            ->whereHas('part', function ($q) use ($submitIds) {
                $q->whereIn('id', $submitIds);
            })->latest()->take(500)->get();
        return view('dashboard.index', compact('submits', 'votes', 'tracked', 'events', 'userready'));
    }
  
}
