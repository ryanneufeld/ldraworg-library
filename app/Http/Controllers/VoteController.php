<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Models\Vote;
use App\Models\VoteType;
use App\Models\Part;
use App\Models\PartEvent;
use App\Models\PartEventType;
use App\Models\PartRelease;

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
    public function store(Part $part, Request $request)
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
      return view('tracker.vote',['part' => $vote->part, 'vote' => $vote]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Vote  $vote
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Vote $vote)
    {
      $this->authorize('update', $vote);

      return $this->postVote($vote->part, $vote, $request);
   }
    
    protected function postVote(Part $part, Vote $vote = null, Request $request) {
      $vts = array_merge(VoteType::all()->pluck('code')->all(),['N','M']);
      $validated = $request->validate([
          'vote_type' => ['required' , Rule::in($vts)],
          'comment' => 'required_if:vote_type,N,H|nullable|string',
      ]);

      if ($validated['vote_type'] == 'N') {
        return $this->destroy($request, $vote);
      }

      $event = new PartEvent(['comment' => $validated['comment'] ?? null]);
      
      if ($validated['vote_type'] == 'M') {
        $event->part_event_type()->associate(PartEventType::firstWhere('slug','comment'));
      }
      else {
        if (isset($vote)) {
          $vote->vote_type_code = $validated['vote_type'];
          $vote->save();
        }
        else {
          $vote = Vote::create([
            'part_id' => $part->id,
            'user_id' => $request->user->id,
            'vote_type_code' => $validated['vote_type'],
          ]);
        }
        $event->vote_type()->associate($validated['vote_type']);
        $event->part_event_type()->associate(PartEventType::firstWhere('slug','review'));
        $part->updateVoteSummary();
      }

      $event->user()->associate($request->user());
      $event->part()->associate($part);
      $event->release()->associate(PartRelease::unofficial());
      $event->save();

      return redirect()->route('tracker.show', [$part])->with('status','Vote succesfully posted');
      
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
      $part = $vote->part;
      $vote->delete();
      $part->updateVoteSummary();

      PartEvent::create([
       'comment' => $request->input('comment') ?? null,
       'user_id' => $request->user()->id,
       'part_id' => $part->id,
       'part_event_type_id' => PartEventType::firstWhere('slug','review')->id,
       'part_release_id' => PartRelease::unofficial()->id
      ]);

      return redirect()->route('tracker.show', $part->id);
    }
}
