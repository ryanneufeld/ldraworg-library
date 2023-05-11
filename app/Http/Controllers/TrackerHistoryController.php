<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TrackerHistoryController extends Controller
{
    public function show(Request $request) {
        $history = \App\Models\TrackerHistory::latest()->get();
        $data = [];
        foreach($history as $h) {  
            $data[] = [
                'certified' => $h->history_data[1],
                'needsreview' => $h->history_data[2],
                'needsvotes' => $h->history_data[3],
                'subparts' => $h->history_data[4],
                'held' => $h->history_data[5],
                'date' => date_format($h->created_at, 'Y-m-d'),          
            ];
        }
        return view('tracker.history', compact('data'));
    }
}
