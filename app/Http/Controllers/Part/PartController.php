<?php

namespace App\Http\Controllers\Part;

use App\Events\PartSubmitted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

use App\Models\User;
use App\Models\Part;
use App\Models\PartType;
use App\Models\PartEvent;
use App\Models\PartCategory;

use App\Http\Controllers\Controller;

use App\Http\Requests\PartSubmitRequest;
use App\Http\Requests\PartHeaderEditRequest;

use App\Jobs\UpdateZip;
use App\LDraw\PartManager;

class PartController extends Controller
{
    public function __construct(
        public PartManager $manager,
    ) {}
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $unofficial = $request->route()->getName() == 'tracker.index';

        return view('part.list', compact('unofficial'));
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
                $e->comment = $this->manager->parser::dos2unix($e->comment);
                $e->comment = preg_replace('#\n{3,}#us', "\n\n", $e->comment);
                $e->comment = preg_replace($urlpattern, '<a href="$0">$0</a>', $e->comment);
                $e->comment = nl2br($e->comment);
            }  
        }
        return view('part.show', compact('part'));
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
            if ($file->getMimeType() == 'text/plain') {
                $part = $this->manager->addOrChangePart($file->get());
            } else {
                $image = imagecreatefrompng($file->path());
                imagesavealpha($image, true);
                $part = $this->manager->addOrChangePart($image, basename($file->getClientOriginalName()), $user, $pt);
            }
            Auth::user()->notification_parts()->syncWithoutDetaching([$part->id]);
            UpdateZip::dispatch($part);
            PartSubmitted::dispatch($part, Auth::user(), $filedata['comment']);
            $parts->add($part);
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
            if ($c = PartCategory::firstWhere('category', $cat)) {
              $part->part_category_id = $c->id;
            } 
        }

        if (!empty($data['part_type_id'])) {
            $part->part_type_id = $data['part_type_id'];
        }
        $part->part_type_qualifier_id = $data['part_type_qualifier_id'] ?? null;
        $help = explode("\n", $data['help']);
        $keywords = explode("\n", $data['keywords']);
        $history =  explode("\n", $data['history']);
//        empty($data['help']) ? $part->setHelp('', true) : $part->setHelp($data['help'], true);
//        empty($data['keywords']) ? $part->setKeywords('', true) : $part->setKeywords($data['keywords'], true);
//        empty($data['history']) ? $part->setHistory('', true) : $part->setHistory($data['history'], true);
        $part->cmdline = $data['cmdline'] ?? null;
        
        if(!empty($data['part_category_id']) && $data['part_category_id'] != $part->part_category_id) {
            $part->part_category_id = $data['part_category_id'];
        } 

        $part->save();
        $part->refresh();
        $part->refreshHeader();
        
        // Post an event
        PartEvent::create([
            'part_event_type_id' => \App\Models\PartEventType::firstWhere('slug', 'edit')->id,
            'user_id' => Auth::user()->id,
            'part_id' => $part->id,
            'part_release_id' => null,
            'comment' => $data['editcomment'] ?? null,
        ]);
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
        if ($part->parents->count() > 0) {
            return back();
        }
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
        $this->manager->updatePartImage($part);
        return redirect()->route('tracker.show', [$part])->with('status','Part image updated');
    }
    
    public function updatesubparts (Part $part) {
        $this->authorize('update', $part);
        $part->setSubparts($this->manager->parser->getSubparts($part->body->body));
        return redirect()->route('tracker.show', [$part])->with('status','Part dependencies updated');
    }
}
