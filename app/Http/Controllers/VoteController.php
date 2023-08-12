<?php

namespace App\Http\Controllers;

use App\Events\PartComment;
use App\Events\PartReviewed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Requests\VoteRequest;
use App\Models\Vote;
use App\Models\Part;
use App\Models\PartEvent;
use App\Models\PartEventType;

class VoteController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Part $part)
    {
        $this->authorize('create', [Vote::class, $part]);
        return view('tracker.vote', ['part' => $part, 'vote' => null]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Part $part, VoteRequest $request)
    {
        $this->authorize('create', [Vote::class, $part]);
        return $this->postVote($part, null, $request);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Vote  $vote
     * @return \Illuminate\Http\Response
     */
    public function edit(Vote $vote)
    {
        $this->authorize('update', $vote);
        return view('tracker.vote', ['part' => $vote->part, 'vote' => $vote]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Vote  $vote
     * @return \Illuminate\Http\Response
     */
    public function update(VoteRequest $request, Vote $vote)
    {
        $this->authorize('update', $vote);

        return $this->postVote($vote->part, $vote, $request);
   }
    
    protected function postVote(Part $part, ?Vote $vote, VoteRequest $request) {
        $validated = $request->validated();

        if ($validated['vote_type'] == 'N') {
            return $this->destroy($request, $vote);
        }

        $event = ['comment' => $validated['comment'] ?? null];
        
        if ($validated['vote_type'] == 'M') {
            $event['part_event_type_id'] = PartEventType::firstWhere('slug','comment')->id;
            PartComment::dispatch($part, Auth::user(), $validated['comment'] ?? null);
        }
        else {
            Auth::user()->castVote($part, \App\Models\VoteType::firstWhere('code', $validated['vote_type']));
            $event['vote_type_code'] = $validated['vote_type'];
            $event['part_event_type_id'] = PartEventType::firstWhere('slug', 'review')->id;
            $part->updateVoteData();
            PartReviewed::dispatch($part, Auth::user(), $validated['vote_type'], $validated['comment'] ?? null);
        }

        Auth::user()->notification_parts()->syncWithoutDetaching([$part->id]);
        return redirect()->route('tracker.show', $part)->with('status', 'Vote succesfully posted');        
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Vote  $vote
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Vote $vote)
    {
        $this->authorize('delete', $vote);

        $pid = $vote->part->id;
        Auth::user()->cancelVote($vote->part);
        PartReviewed::dispatch($vote->part, Auth::user(), null, $request->input('comment') ?? null);
        return redirect()->route('tracker.show', $pid)->with('status', 'Vote succesfully canceled');
    }
}
