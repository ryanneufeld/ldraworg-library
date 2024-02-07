<div x-data="{ webgl: true }"> 
    @if(session('status'))
        <x-message.toast type="info" header="{{session('status')}}" />
    @endif
    <x-slot:title>
        File Detail {{ $part->filename }}
    </x-slot>
    <x-slot:breadcrumbs>
        <x-breadcrumb-item class="active" item="Part Detail" />
    </x-slot>    
    @push('meta')
        <meta name="description" content="{{$part->description}}">

        <!-- Facebook Meta Tags -->
        <meta property="og:url" content="{{Request::url()}}">
        <meta property="og:type" content="website">
        <meta property="og:title" content="File Detail {{ $part->filename }}">
        <meta property="og:description" content="{{$part->description}}">
        <meta property="og:image" content="{{$image}}">

        <!-- Twitter Meta Tags -->
        <meta name="twitter:card" content="summary_large_image">
        <meta property="twitter:domain" content="library.ldraw.org">
        <meta property="twitter:url" content="{{Request::url()}}">
        <meta name="twitter:title" content="File Detail {{ $part->filename }}">
        <meta name="twitter:description" content="{{$part->description}}">
        <meta name="twitter:image" content="{{$image}}">
    @endpush

    <div class="flex flex-col space-y-4">
        <x-menu.part-detail :part="$part" />
        <div class="text-3xl font-bold">
            <span @class([
                'bg-lime-200' => !$part->isUnofficial(),
                'bg-yellow-200' => $part->isUnofficial()
            ])>
                {{ucfirst($part->libFolder())}} File <span id="filename">{{ $part->filename }}</span>
            </span>
        </div>
       
        <div>
            @isset ($part->unofficial_part_id)
                <x-filament::button 
                    href="{{ route('tracker.show', $part->unofficial_part_id) }}"
                    icon="fas-copy"
                    color="gray"
                    tag="a"
                    label="View unofficial version of part"
                >
                    View unofficial version of part
                </x-filament::button>
            @endisset
            @isset ($part->official_part_id)
                <x-filament::button 
                    href="{{ route('official.show', $part->official_part_id) }}"
                    icon="fas-copy"
                    color="gray"
                    tag="a"
                >
                    View official version of part
                </x-filament::button>
            @endisset
            @if ($part->isUnofficial() && Auth::check())
                <x-filament::button
                    wire:click="toggleTracked" 
                    icon="fas-bell"
                    color="{{Auth::user()->notification_parts->contains($part->id) ? 'yellow' : 'gray'}}"
                >
                    {{Auth::user()->notification_parts->contains($part->id) ? 'Tracking' : 'Track'}}
                </x-filament::button>
                @can('part.flag.delete')
                    <x-filament::button
                        wire:click="toggleDeleteFlag" 
                        icon="fas-flag"
                        color="{{$part->delete_flag ? 'red' : 'gray'}}"
                    >
                        {{$part->delete_flag ? 'Flagged' : 'Flag'}} for Deletion
                    </x-filament::button>
                @else
                    @if($part->delete_flag)
                        <x-filament::button
                            icon="fas-flag"
                            color="danger"
                        >
                            Flagged for Deletion
                        </x-filament::button>
                    @endif       
                @endcan    
                @can('part.flag.manual-hold')
                    <x-filament::button
                        wire:click="toggleManualHold" 
                        icon="fas-flag"
                        color="{{$part->manual_hold_flag ? 'red' : 'gray'}}"
                    >
                        {{$part->manual_hold_flag ? 'On' : 'Place on'}} Administrative Hold
                    </x-filament::button>
                @endcan    
            @endif
        </div>
        <div class="flex flex-row-reverse flex-wrap">
            <div @class([
                'shrink p-4 place-content-center place-self-center border rounded',
                'bg-lime-200' => !$part->isUnofficial(),
                'bg-yellow-200' => $part->isUnofficial()
            ])>
                <a href="{{$image}}">
                    <img src="{{$image}}" alt='part image' title="{{ $part->description }}" >
                </a>
            </div>
            <div class="justify-self-end">
                <div class="text-lg font-bold">File Header:</div>
                <code class="whitespace-pre-wrap break-words font-mono">{{ trim($part->header) }}</code>
            </div>    
        </div>
        @if($part->isUnofficial())
            <div class="text-lg font-bold">Status:</div>
            <x-part.status :$part show-status /><br>
            <x-part.part-check-message :$part />
            {{$this->table}}
            <livewire:tables.related-parts title="Unofficial parent parts" :$part parents/>
            <livewire:tables.related-parts title="Unofficial subparts" :$part />
            <x-accordion id="officialParts">
                <x-slot name="header" class="text-md font-bold">
                    Official parents and subparts
                </x-slot>
                <livewire:tables.related-parts title="Official parent parts" :$part official parents/>
                <livewire:tables.related-parts title="Official subparts" :$part official/>
            </x-accordion>
        @else
            <livewire:tables.related-parts title="Official parent parts" :$part official parents/>
            <livewire:tables.related-parts title="Official subparts" :$part official/>
        @endif    
        @if($part->isUnofficial())
            <x-event.list title="File events" :events="$part->events" />
            <div id="voteForm"></div>
            @if (Auth::check() && (Auth::user()->can('create', [\App\Models\Vote::class, $part]) || Auth::user()->can('update', [$part->votes()->firstWhere('user_id', Auth::user()->id)])))    
                <form wire:submit="postVote">
                    {{ $this->form }}
                    <button class="border rounded" type="submit">
                        Submit
                    </button>
                </form>
            @endif
        @endif
        <x-menu.part-detail :part="$part" />
        <x-part.attribution :part="$part" />
    </div>
    <x-filament::modal id="ldbi" alignment="center" width="7xl" >
        <x-slot name="heading">
            3D View
        </x-slot>
        <div class="flex flex-col w-full h-full">
            <div class="flex flex-row space-x-2 p-2 mb-2">
                <x-filament::icon-button
                    icon="fas-undo"
                    size="lg"
                    label="Normal mode"
                    class="border"
                />
                <x-filament::icon-button
                    icon="fas-paint-brush"
                    size="lg"
                    label="Harlequin (random color) mode"
                    class="border"
                />
            </div>
            <div id="ldbi-container" class="border w-full min-h-[75vh]"> 
                <canvas id="ldbi-canvas" class="size-full"></canvas>
            </div>
        </div>
    </x-filament::modal>
    @if($part->isUnofficial())     
        <x-filament::modal id="delete-part">
            <x-slot name="heading">
                Delete Part
            </x-slot>
            <p>
                Are you sure you want to delete {{$part->filename}}? This action cannot be easily undone.
            </p>
            <x-filament::button wire:click="deletePart">
                Yes
            </x-filament::button>
            <x-filament::button wire:click="$dispatch('close-modal', { id: 'delete-part' })">
                No
            </x-filament::button>
        </x-filament::modal>
    @endif
    @push('scripts')
        <x-layout.ldbi-scripts />
        <script type="text/javascript">
            var scene;
        </script>    
        @script
        <script>
            let part_id = {{$part->id}};
            var part_paths;


            LDR.Options.bgColor = 0xFFFFFF;

            LDR.Colors.envMapPrefix = '/assets/ldbi/textures/cube/';    
            LDR.Colors.textureMaterialPrefix = '/assets/ldbi/textures/materials/';

            $wire.on('open-modal', (modal) => {
                let idToUrl = function(id) {
                    if (part_paths[id]) {
                        return [part_paths[id]];
                    }
                    else {
                        return [id];
                    }
                };

                let idToTextureUrl = function(id) {
                    if (part_paths[id]) {
                        return part_paths[id];
                    }
                    else {
                        return id;
                    }
                };
                if (modal.id == 'ldbi' && WEBGL.isWebGLAvailable() && !scene) {
                    // pre-fetch the paths to the subfiles used to speed up loading
                    fetch('/api/' + part_id + '/ldbi')
                        .then(response => response.json())
                        .then((response) => {
                            part_paths = response;
                            scene = new LDrawOrg.Model(
                                document.getElementById('ldbi-canvas'), 
                                document.getElementById('filename').innerHTML.replace(/^(parts\/|p\/)/, ''),
                                {idToUrl: idToUrl, idToTextureUrl: idToTextureUrl}
                            );
                            window.addEventListener('resize', () => scene.onChange());
                        })
                }
            });
        </script>
        @endscript
    @endpush

</div>
