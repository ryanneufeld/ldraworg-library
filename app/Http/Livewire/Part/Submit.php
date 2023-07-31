<?php

namespace App\Http\Livewire\Part;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Jobs\UpdateZip;
use App\Events\PartSubmitted;
use App\Models\Part;
use App\Models\PartType;
use App\Models\User;

class Submit extends Component
{
    use AuthorizesRequests, WithFileUploads;

    public $partfiles = [];
    public $replace = false;
    public $officialfix = false;
    public $comments = '';
    public $proxy_user_id = null;
    
    public function rules()
    {
        return [
            'comments' => 'nullable|string',
            'proxy_user_id' => ['nullable', 'exists:users,id', new \App\Rules\ProxySubmit()],
            'partfiles' => 'required',
            'partfiles.*' => [
                'file',
                'mimetypes:text/plain,image/png',
                new \App\Rules\LDrawFile(),
                new \App\Rules\FileReplace(),
                new \App\Rules\FileOfficial(),
            ],
        ];
    }
    
    public function submit()
    {
        $this->validate();

        $this->authorize('create', Part::class);
        $manager = App::make(\App\LDraw\PartManager::class);
        if (!is_null($this->proxy_user_id)) {
            $user = User::find($this->proxy_user_id);
        } else {
            $user = Auth::user();
        }
        
        $parts = new Collection;
        foreach($this->partfiles as $file) {
            if ($file->getMimeType() == 'text/plain') {
                $part = $manager->addOrChangePart($file->get());
            } else {
                $image = imagecreatefrompng($file->path());
                imagesavealpha($image, true);
                $part = $manager->addOrChangePart($image, basename($file->getClientOriginalName()), $user, $this->guessPartType($file->getClientOriginalName()));
            }
            $user->notification_parts()->syncWithoutDetaching([$part->id]);
            UpdateZip::dispatch($part);
            PartSubmitted::dispatch($part, $user, $this->comments);
            $parts->add($part);
        }
    }

    public function render()
    {
        $this->dispatchBrowserEvent('jquery');
        return view('livewire.part.submit');
    }
    
    public function guessPartType(string $filename): PartType
    {
        $manager = App::make(\App\LDraw\PartManager::class);
        $p = Part::firstWhere('filename', 'LIKE', "%{$filename}");
        //Texmap exists, use that type
        if (!is_null($p)) {
            return $p->type;
        }
        // Texmap is used in one of the submitted files, use the type appropriate for that part
        foreach ($this->partfiles as $file) {
            if ($file->getMimeType() == 'text/plain' && stripos($filename, $file->get() !== false)) {
                $type = $manager->parser->parse($file->get())->type;
                $pt = PartType::firstWhere('type', $type);
                $textype = PartType::firstWhere('type', "{$pt->type}_Texmap");
                if (!is_null($textype)) {
                    return $textype;
                }
            }
        }
        return PartType::firstWhere('type', 'Texmap');
    }
}
