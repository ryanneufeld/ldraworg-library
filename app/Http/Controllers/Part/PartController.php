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
use App\Models\PartTypeQualifier;

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
        $part->load('events', 'history', 'subparts', 'parents');
        $part->events->load('part_event_type', 'user', 'part', 'vote_type');
        $part->user->load('license');
        $part->votes->load('user', 'type');
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
        $parts = new Collection();
        foreach($data['partfiles'] as $file) {
            if ($file->getMimeType() == 'text/plain') {
                $part = $this->manager->addOrChangePartFromText($file->get());
            } else {
                $image = imagecreatefrompng($file->path());
                imagesavealpha($image, true);
                $part = $this->manager->addOrChangePartFromImage(
                    $file->path(),
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
        $parts->each(function (Part $p) {
            $this->manager->loadSubpartsFromBody($p);
        });
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
        $changes = ['old' => [], 'new' => []];
        if ($data['description'] !== $part->description) {
            $changes['old']['description'] = $part->description;
            $changes['new']['description'] = $data['description'];
            $part->description = $data['description'];
            if ($part->type->folder === 'parts/') {
                $cat = $this->manager->parser->getDescriptionCategory($part->description);
                $cat = PartCategory::firstWhere('category', $cat);
                if (!is_null($cat) && $part->part_category_id !== $cat->id) {
                    $part->part_category_id = $cat->id;
                }    
            }
        }

        if ($part->type->folder === 'parts/' && 
            !is_null($data['part_category_id']) && 
            $part->part_category_id !== (int)$data['part_category_id']
        ) {
            $cat = PartCategory::find($data['part_category_id']);
            $changes['old']['category'] = $part->category->category;
            $changes['new']['category'] = $cat->category;
            $part->part_category_id = $cat->id;
        }

        if ($part->type->folder === 'parts/' && (int)$data['part_type_id'] !== $part->part_type_id) {
            $pt = PartType::find($data['part_type_id']);
            $changes['old']['type'] = $part->type->type;
            $changes['new']['type'] = $pt->type;
            $part->part_type_id = $pt->id;
        }
        
        if (!is_null($data['part_type_qualifier_id'])) {
            $pq = PartTypeQualifier::find($data['part_type_qualifier_id']);
        } else {
            $pq = null;
        }
        if ($part->part_type_qualifier_id !== ($pq->id ?? null)) {
            $changes['old']['qual'] = $part->type_qualifier->type ?? '';
            $changes['new']['qual'] = $pq->type ?? '';
            $part->part_type_qualifier_id = $pq->id ?? null;
        }

        if (!is_null($data['help']) && trim($data['help']) !== '') {
            $newHelp = "0 !HELP " . str_replace(["\n","\r"], ["\n0 !HELP ",''], $data['help']);
            $newHelp = $this->manager->parser->getHelp($newHelp);
        } else {
            $newHelp = [];
        }

        $partHelp = $part->help->pluck('text')->all();
        if ($partHelp !== $newHelp) {
            $changes['old']['help'] = "0 !HELP " . implode("\n0 !HELP ", $partHelp);
            $changes['new']['help'] = "0 !HELP " . implode("\n0 !HELP ", $newHelp);
            $part->setHelp($newHelp);    
        }

        if (!is_null($data['keywords'])) {
            $newKeywords = '0 !KEYWORDS ' . str_replace(["\n","\r"], [', ',''], $data['keywords']);
            $newKeywords = $this->manager->parser->getKeywords($newKeywords);
        } else {
            $newKeywords = [];
        }

        $partKeywords = $part->keywords->pluck('keyword')->all();
        if ($partKeywords !== $newKeywords) {
            $changes['old']['keywords'] = implode(", ", $partKeywords);
            $changes['new']['keywords'] = implode(", ", $newKeywords);
            $part->setKeywords($newKeywords);    
        }

        $newHistory = $this->manager->parser->getHistory($data['history'] ?? '');
        $partHistory = [];
        if ($part->history->count() > 0) {
            foreach($part->history as $h) {
                $partHistory[] = $h->toString();
            }
        }
        $partHistory = implode("\n", $partHistory);
        if ($this->manager->parser->getHistory($partHistory) !== $newHistory) {
            $changes['old']['history'] = $partHistory;
            $part->setHistory($newHistory ?? []);
            $part->refresh();    
            $changes['new']['history'] = '';
            if ($part->history->count() > 0) {
                foreach($part->history as $h) {
                    $changes['new']['history'] .= $h->toString() . "\n";
                }
            }
        }

        if ($part->cmdline !== ($data['cmdline'] ?? null)) {
            $changes['old']['cmdline'] = $part->cmdline ?? '';
            $changes['new']['cmdline'] = $data['cmdline'] ?? '';
            $part->cmdline = $data['cmdline'] ?? null;
        $partHistory = [];
        if ($part->history->count() > 0) {
            foreach($part->history as $h) {
                $partHistory[] = $h->toString();
            }
        }
        $partHistory = implode("\n", $partHistory);
        }

        if (count($changes['new']) > 0) {
            $part->save();
            $part->refresh();
            $part->generateHeader();
            
            // Post an event
            PartHeaderEdited::dispatch($part, Auth::user(), $changes, $data['editcomment'] ?? null);
            Auth::user()->notification_parts()->syncWithoutDetaching([$part->id]);
            UpdateZip::dispatch($part);    
            return redirect()->route('tracker.show', [$part])->with('status', 'Header update successful');
        }

        return redirect()->route('tracker.show', [$part])->with('status', 'No changes made');

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
        if (is_null($part->official_part_id) && $part->parents->count() > 0) {
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
        return redirect()->route('tracker.show', [$part])->with('status', 'Part image updated');
    }
    
    public function updatesubparts (Part $part) {
        $this->authorize('update', $part);
        $this->manager->loadSubpartsFromBody($part);
        return redirect()->route('tracker.show', [$part])->with('status', 'Part dependencies updated');
    }
}
