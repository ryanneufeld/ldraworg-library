<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

use App\Models\User;
use App\Models\Part;
use App\Models\PartType;
use App\Models\PartEvent;
use App\Models\PartCategory;

use App\LDraw\LibraryOperations;
use App\LDraw\FileUtils;

use App\Http\Requests\PartSubmitRequest;
use App\Http\Requests\PartHeaderEditRequest;

use App\Jobs\UpdateZip;

class PartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $unofficial = $request->route()->getName() == 'tracker.index';
      $input = $request->all();
      
      $subset = [1 => 'Certified', 2 => 'Needs Admin Review', 3 => 'Needs More Votes', 4 => 'Uncertified Subfiles', 5 => 'Hold', 6 => '2 (or more) Certify Votes', 7 => '1 Certify Vote'];
      $users = User::whereHas('parts', function (Builder $query) use ($unofficial) {
        if ($unofficial) {
          $query->unofficial();
        }
        else {
          $query->official();
        }
      })->orderBy('name')->pluck('name', 'id')->all();
      $part_types = PartType::pluck('name', 'id')->all();

      if ($unofficial) {
        $parts = Part::unofficial();
      }
      else {
        $parts = Part::official();
      }

      if ($unofficial && isset($input['subset']) && array_key_exists($input['subset'], $subset)) {
        if ($input['subset'] == 6) {
          $parts->where('vote_sort', '<>', 5)->where('vote_sort', '<>', 1)->whereHas('votes', function ($q) {
            $q->where('vote_type_code', 'C');
          }, '>=', 2);
        }
        elseif ($input['subset'] == 7) {
          $parts->where('vote_sort', '<>', 5)->where('vote_sort', '<>', 1)->whereHas('votes', function ($q) {
            $q->where('vote_type_code', 'C');
          }, '=', 1);
        }
        else {
          $parts = $parts->where('vote_sort', $input['subset']);
        }
      }
        
      if (isset($input['user_id']) && array_key_exists($input['user_id'], $users))
        $parts = $parts->where('user_id', $input['user_id']);
      if (isset($input['part_type_id']) && array_key_exists($input['part_type_id'], $part_types))
        $parts = $parts->where('part_type_id', $input['part_type_id']);
      $parts = $parts->orderBy('vote_sort')->
        orderBy('part_type_id')->
        orderBy('filename')->
        cursor();

      return view('part.list',[
        'unofficial' => $unofficial,
        'parts' => $parts,
        'subset' => $subset,
        'users' => $users,
        'part_types' => $part_types,
      ]);  
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Part  $part
     * @return \Illuminate\Http\Response
     */
    public function show(Part $part)
    {
      $part->load('events','history','subparts','parents');
      $part->events->load('part_event_type', 'user', 'part', 'vote_type');
      $part->user->load('license');
      $part->votes->load('user','type');
      $urlpattern = '#https?:\/\/(?:www\.)?[a-zA-Z0-9@:%._\+~\#=-]{1,256}\.[a-zA-Z0-9()]{1,6}\b(?:[a-zA-Z0-9()@:%_\+.~\#?&\/=-]*)#u';

      foreach ($part->events as $e) {
        if(!is_null($e->comment)) {
          $e->comment = FileUtils::dos2unix($e->comment);
          $e->comment = preg_replace('#\n{3,}#us', "\n\n", $e->comment);
          $e->comment = preg_replace($urlpattern, '<a href="$0">$0</a>', $e->comment);
          $e->comment = nl2br($e->comment);
        }  
      }
      return view('part.show', [
        'part' => $part, 
      ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      $this->authorize('create', Part::class);
      
      return view('tracker.submit');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PartSubmitRequest $request)
    {
      $this->authorize('create', Part::class);
      $filedata = $request->validated();
      $user = User::find($filedata['user_id']);
      $pt = PartType::find($filedata['part_type_id']);
      $parts = new Collection;
      foreach($filedata['partfile'] as $file) {
        $parts->add(Part::updateOrCreateFromFile($file, $user, $pt, $filedata['comment'] ?? null));
      }
      return view('tracker.postsubmit', ['parts' => $parts]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Part  $part
     * @return \Illuminate\Http\Response
     */
    public function edit(Part $part)
    {
      $this->authorize('update', $part);
      return view('tracker.edit', ['part' => $part]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Part  $part
     * @return \Illuminate\Http\Response
     */
    public function update(PartHeaderEditRequest $request, Part $part)
    {
      $this->authorize('update', $part);
      $data = $request->validated();
      
      if (!empty($data['description'])) {
        $part->description = $data['description'];
        $cat = str_replace(['~','|','=','_'], '', mb_strstr($data['description'], " ", true));
        if ($c = PartCategory::findByName($cat)) $part->part_category_id = $c->id;
      }

      if (!empty($data['part_type_id'])) {
        $part->part_type_id = $data['part_type_id'];
      }
      $part->part_type_qualifier_id = $data['part_type_qualifier_id'] ?? null;
      empty($data['help']) ? $part->setHelp('', true) : $part->setHelp($data['help'], true);
      empty($data['keywords']) ? $part->setKeywords('', true) : $part->setKeywords($data['keywords'], true);
      empty($data['history']) ? $part->setHistory('', true) : $part->setHistory($data['history'], true);
      $part->cmdline = $data['cmdline'] ?? null;
      
      if(!empty($data['part_category_id']) && $data['part_category_id'] != $part->part_category_id) $part->part_category_id = $data['part_category_id'];

      $part->save();
      $part->refresh();
      $part->refreshHeader();
      
      // Post an event
      PartEvent::createFromType('edit', Auth::user(), $part, $data['editcomment'] ?? null);
      Auth::user()->notification_parts()->syncWithoutDetaching([$part->id]);
      UpdateZip::dispatch($part);

      return redirect()->route('tracker.show', [$part])->with('status','Header update successful');
    }

    public function delete(Part $part) {
      return view('part.delete', compact('part'));
    }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Part  $part
     * @return \Illuminate\Http\Response
     */
    public function destroy(Part $part)
    {
      $this->authorize('delete', $part);
      if ($part->parents->count() > 0) return back();
      $part->delete();
      return redirect()->route('tracker.activity');
    }

    /**
     * Update the image of the part.
     *
     * @param  \App\Models\Part  $part
     * @return \Illuminate\Http\Response
     */
    public function updateimage(Part $part)
    {
      $this->authorize('update', $part);
      $part->updateImage();
      return redirect()->route('tracker.show', [$part])->with('status','Part image update queued');
    }

    public function weekly(Request $request) {
      
      $parts = Part::with('type')->unofficial()->
        where('official_part_id', null)->
        where(function (Builder $query) {
          $query->orWhereRelation('type', 'type', 'Part')->orWhereRelation('type', 'type', 'Shortcut');
        });
      if ($request->has('order') && $request->input('order') == 'asc') {
        $parts = $parts->oldest();
      } 
      else {
        $parts = $parts->latest();
      }
      return view('tracker.weekly', ['parts' => $parts->get()]);
    }
    
    public function updatesubparts (Part $part) {
      $this->authorize('update', $part);
      $part->updateSubparts(true);
      return redirect()->route('tracker.show', [$part])->with('status','Part dependencies updated');
    }
}
