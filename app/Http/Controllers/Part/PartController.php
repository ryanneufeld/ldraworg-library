<?php

namespace App\Http\Controllers\Part;

use App\Events\PartHeaderEdited;
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
        $data = $request->validated();
        if (!is_null($data['proxy_user_id'])) {
            $user = User::find($data['proxy_user_id']);
        } else {
            $user = Auth::user();
        }
        
        $parts = new Collection;
        foreach($data['partfiles'] as $file) {
            if ($file->getMimeType() == 'text/plain') {
                $part = $this->manager->addOrChangePart($file->get());
            } else {
                $image = imagecreatefrompng($file->path());
                imagesavealpha($image, true);
                $part = $this->manager->addOrChangePart(
                    $image, 
                    basename($file->getClientOriginalName()), 
                    $user, 
                    $this->guessPartType($file->getClientOriginalName(), $data['partfiles'])
                );
            }
            $user->notification_parts()->syncWithoutDetaching([$part->id]);
            UpdateZip::dispatch($part);
            PartSubmitted::dispatch($part, $user, $data['comments']);
            $parts->add($part);
        }
        return view('tracker.postsubmit', ['parts' => $parts]);
    }

    protected function guessPartType(string $filename, array $partfiles): PartType
    {
        $p = Part::firstWhere('filename', 'LIKE', "%{$filename}");
        //Texmap exists, use that type
        if (!is_null($p)) {
            return $p->type;
        }
        // Texmap is used in one of the submitted files, use the type appropriate for that part
        foreach ($partfiles as $file) {
            if ($file->getMimeType() == 'text/plain' && stripos($filename, $file->get() !== false)) {
                $type = $this->manager->parser->parse($file->get())->type;
                $pt = PartType::firstWhere('type', $type);
                $textype = PartType::firstWhere('type', "{$pt->type}_Texmap");
                if (!is_null($textype)) {
                    return $textype;
                }
            }
        }
        return PartType::firstWhere('type', 'Part_Texmap');
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
        $help = empty($data['help']) ? [] : explode("\n", $this->manager->parser->dos2unix($data['help']));
        $keywords = empty($data['keywords']) ? '' : str_replace(["\n","\r"], [', ',''], $data['keywords']);
        $part->setHelp($help);
        $part->setKeywords($this->manager->parser->getKeywords('0 !KEYWORDS ' . $keywords));
        $part->setHistory($this->manager->parser->getHistory($this->manager->parser->dos2unix($data['history']) ?? '') ?? []);
        $part->cmdline = $data['cmdline'] ?? null;
        
        if(!empty($data['part_category_id']) && $data['part_category_id'] != $part->part_category_id) {
            $part->part_category_id = $data['part_category_id'];
        } 

        $part->save();
        $part->refresh();
        $part->generateHeader();
        
        // Post an event
        PartHeaderEdited::dispatch($part, Auth::user(), $data['editcomment'] ?? null);
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
