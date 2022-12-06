<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
          'comment' => 'exclude_unless:vote_type,H,M|required|string',
      ]);

      $input = $request->all();

      if ($input['vote_type'] == 'N') {
        return $this->destroy($request, $vote);
      }

      $event = new PartEvent(['comment' => $input['comment'] ?? null]);
      
      if ($input['vote_type'] == 'M') {
        $event->part_event_type()->associate(PartEventType::firstWhere('slug','comment'));
      }
      else {
        if (isset($vote)) {
          $vote->vote_type_code = $input['vote_type'];
        }
        else {
          $vote = new Vote;
          $vote->part()->associate($part);
          $vote->user()->associate($request->user());
          $vote->vote_type_code = $input['vote_type'];
          $vote->save();
        }
        $event->vote_type()->associate($input['vote_type']);
        $event->part_event_type()->associate(PartEventType::firstWhere('slug','review'));
      }

      $event->user()->associate($request->user());
      $event->part()->associate($vote->part);
      $event->release()->associate(PartRelease::firstWhere('short','unof'));
      $event->save();

      return redirect()->route('tracker.show', $part->id);
      
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
      foreach($part->parents as $parent) {
        $parent->updateUncertifiedSubpartsCache();
      }

      $event = new PartEvent(['comment' => $input['comment'] ?? null]);
      $event->user()->associate($request->user());
      $event->part()->associate($part);
      $event->part_event_type()->associate(PartEventType::firstWhere('slug','review'));
      $event->release()->associate(PartRelease::firstWhere('short','unof'));
      $event->save();

      return redirect()->route('tracker.show', $part->id);
    }
}
