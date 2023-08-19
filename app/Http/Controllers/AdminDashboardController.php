<?php

namespace App\Http\Controllers;

use App\Models\Part;

class AdminDashboardController extends Controller
{
    public function __construct() {
        $this->middleware('auth');
    }
    
    public function __invoke() {
        $delete_flags = Part::where('delete_flag', true)->orderby('filename')->get();
        $manual_hold_flags = Part::where('manual_hold_flag', true)->orderby('filename')->get();
        $adminreadyparts = Part::unofficial()
            ->where('vote_sort', 2)
            ->whereIn('part_type_id', [1, 6])
            ->whereDoesntHave('descendants', function ($q){
                $q->where('vote_sort', '>', 2);
            })
            ->orderby('vote_sort')
            ->orderBy('part_type_id')
            ->orderBy('filename')
            ->get();
        $prims = Part::official()->notLicenseName('CC_BY_4')->whereHas('type', function ($q) {
            $q->whereIn('type', ['Primitive', '48_Primitive', '8_Primitive']);
        })->withCount('parents')->orderBy('parents_count', 'desc')->take(100)->get();
        return view('admin.dashboard', compact('delete_flags', 'manual_hold_flags', 'adminreadyparts', 'prims'));
    }
  
}
