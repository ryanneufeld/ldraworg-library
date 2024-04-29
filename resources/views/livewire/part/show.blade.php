<div x-data="{ webgl: true }"> 
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
        <div class="flex flex-col md:flex-row divide-y md:divide-x bg-white border rounded-md w-fit cursor-pointer">
            {{ $this->downloadAction }}
            @if ($this->downloadZipAction->isVisible())
                {{ $this->downloadZipAction }}
            @endif
            @if ($this->patternPartAction->isVisible())
                {{ $this->patternPartAction }}
            @endif
            @if ($this->stickerSearchAction->isVisible())
                {{ $this->stickerSearchAction }}
            @endif
            @if ($this->adminCertifyAllAction->isVisible())
                {{ $this->adminCertifyAllAction }}
            @endif
            @if ($this->certifyAllAction->isVisible())
                {{ $this->certifyAllAction }}
            @endif
            @if ($this->editHeaderAction->isVisible())
                {{ $this->editHeaderAction }}
            @endif
            @if ($this->editNumberAction->isVisible())
                {{ $this->editNumberAction }}
            @endif
            @if ($this->updateImageAction->isVisible())
                {{ $this->updateImageAction }}
            @endif
            @if ($this->recheckPartAction->isVisible())
                {{ $this->recheckPartAction }}
            @endif
            @if ($this->updateSubpartsAction->isVisible())
                {{ $this->updateSubpartsAction }}
            @endif
            @if ($this->retieFixAction->isVisible())
                {{ $this->retieFixAction }}
            @endif
            @if ($this->deleteAction->isVisible())
                {{ $this->deleteAction }}
            @endif
            {{ $this->webglViewAction }}
        </div>
        <div @class([
                'text-3xl font-bold p-2 w-fit',
                'bg-green-200' => !$part->isUnofficial(),
                'bg-yellow-200' => $part->isUnofficial()
            ])>
                {{ucfirst($part->libFolder())}} File <span id="filename">{{ $part->filename }}</span>
        </div>
        <div>
            @if ($this->viewFixAction->isVisible())
                {{ $this->viewFixAction }}
            @endif
            @if ($part->isUnofficial())
                @if ($this->toggleTrackedAction->isVisible())
                    {{ $this->toggleTrackedAction }}
                @endif
                @if ($this->toggleDeleteFlagAction->isVisible())
                    {{ $this->toggleDeleteFlagAction }}
                @elseif($part->delete_flag)
                    <x-filament::button
                        icon="fas-flag"
                        color="danger"
                    >
                        Flagged for Deletion
                    </x-filament::button>
                @endif
                @if ($this->toggleManualHoldAction->isVisible())
                    {{ $this->toggleManualHold }}
                @endif
            @endif
        </div>
        <div class="flex flex-col md:flex-row-reverse gap-2">
            <img @class([
                    'w-fit h-fit p-4 border rounded',
                    'bg-green-200' => !$part->isUnofficial(),
                    'bg-yellow-200' => $part->isUnofficial()
                ])
                wire:click="$dispatch('open-modal', { id: 'ldbi' })"
                src="{{$image}}" alt="{{ $part->description }}" title="{{ $part->description }}"
            >
            <div class="justify-self-start w-full">
                <div class="text-lg font-bold">File Header:</div>
                <code class="whitespace-pre-wrap break-words font-mono">{{ trim($part->header) }}</code>
            </div>    
        </div>
        @if($part->isUnofficial())
            <div class="text-lg font-bold">Status:</div>
            <x-part.status :$part show-status />
            @if (!$part->can_release)
                <x-message compact type="warning">
                    <x-slot:header>
                        This part is not releaseable
                    </x-slot:header>
                    <ul>
                        @foreach($part->part_check_messages['errors'] as $error)
                            <li>{{$error}}</li>
                        @endforeach
                    </ul>
                </x-message>        
            @endif
            <div class="text-md font-bold">Current Votes:</div>
            <x-vote.table :votes="$part->votes" />
            @if (count($part->missing_parts) > 0)
                <div class="text-md font-bold">Missing Part References:</div>
                @foreach($part->missing_parts as $missing)
                    <div class="text-red-500">{{ $missing }}</div>
                @endforeach
            @endif
            <livewire:tables.part-dependencies-table :$part parents />
            <livewire:tables.part-dependencies-table :$part/>
            <x-accordion id="officialParts">
                <x-slot name="header" class="text-md font-bold">
                    Official parents and subparts
                </x-slot>
                <livewire:tables.part-dependencies-table :$part official parents />
                <livewire:tables.part-dependencies-table :$part official />
                </x-accordion>
        @else
            <livewire:tables.part-dependencies-table :$part official parents />
            <livewire:tables.part-dependencies-table :$part official />
        @endif
        <x-event.list :$part />
        @can('vote', [\App\Models\Vote::class, $this->part])
            <div id="voteForm"></div>
            <form wire:submit="postVote">
                {{ $this->form }}
                <button class="border rounded mt-2 py-2 px-4 bg-yellow-500" type="submit">
                    Send
                </button>
            </form>
        @endcan
        <div class="flex flex-col md:flex-row divide-y md:divide-x bg-white border rounded-md w-fit cursor-pointer">
            {{ $this->downloadAction }}
            @if ($this->downloadZipAction->isVisible())
                {{ $this->downloadZipAction }}
            @endif
            @if ($this->patternPartAction->isVisible())
                {{ $this->patternPartAction }}
            @endif
            @if ($this->stickerSearchAction->isVisible())
                {{ $this->stickerSearchAction }}
            @endif
            @if ($this->adminCertifyAllAction->isVisible())
                {{ $this->adminCertifyAllAction }}
            @endif
            @if ($this->certifyAllAction->isVisible())
                {{ $this->certifyAllAction }}
            @endif
            @if ($this->editHeaderAction->isVisible())
                {{ $this->editHeaderAction }}
            @endif
            @if ($this->editNumberAction->isVisible())
                {{ $this->editNumberAction }}
            @endif
            @if ($this->updateImageAction->isVisible())
                {{ $this->updateImageAction }}
            @endif
            @if ($this->recheckPartAction->isVisible())
                {{ $this->recheckPartAction }}
            @endif
            @if ($this->updateSubpartsAction->isVisible())
                {{ $this->updateSubpartsAction }}
            @endif
            @if ($this->retieFixAction->isVisible())
                {{ $this->retieFixAction }}
            @endif
            @if ($this->deleteAction->isVisible())
                {{ $this->deleteAction }}
            @endif
            {{ $this->webglViewAction }}
        </div>
        <x-part.attribution :part="$part" />
    </div>
    <x-filament::modal id="ldbi" alignment="center" width="7xl">
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
                    wire:click="$dispatch('ldbi-default-mode')"
                />
                <x-filament::icon-button
                    icon="fas-paint-brush"
                    size="lg"
                    label="Harlequin (random color) mode"
                    class="border"
                    wire:click="$dispatch('ldbi-harlequin-mode')"
                />
                <x-filament::icon-button
                    icon="fas-leaf"
                    size="lg"
                    label="Back Face Culling (BFC) mode"
                    class="border"
                    wire:click="$dispatch('ldbi-bfc-mode')"
                />
                <x-filament::icon-button
                    icon="fas-dot-circle"
                    size="lg"
                    label="Toggle Stud Logos"
                    class="border"
                    wire:click="$dispatch('ldbi-stud-logos')"
                />
                <x-filament::icon-button
                    icon="fas-arrows-alt"
                    size="lg"
                    label="Toggle Show Origin"
                    class="border"
                    wire:click="$dispatch('ldbi-show-origin')"
                />
                <x-filament::icon-button
                    icon="fas-eye"
                    size="lg"
                    label="Toggle Photo Mode"
                    class="border"
                    wire:click="$dispatch('ldbi-physical-mode')"
                />
            </div>
            <div id="ldbi-container" class="border w-full h-[80vh]"> 
                <canvas id="ldbi-canvas" class="size-full"></canvas>
            </div>
        </div>
    </x-filament::modal>
    <x-filament-actions::modals />
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
                    fetch('/ldbi/part/' + part_id)
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

            $wire.on('ldbi-default-mode', () => {
                scene.default_mode();
            });

            $wire.on('ldbi-harlequin-mode', () => {
                scene.harlequin_mode();
            });

            $wire.on('ldbi-bfc-mode', () => {
                scene.bfc_mode();
            });

            $wire.on('ldbi-stud-logos', () => {
                if (LDR.Options.studLogo == 1) {
                    LDR.Options.studLogo = 0;
                } else {
                    LDR.Options.studLogo = 1;
                }
                scene.reload();
            });

            $wire.on('ldbi-show-origin', () => {
                scene.axesHelper.visible = !scene.axesHelper.visible;
                scene.reload();
            });

            $wire.on('ldbi-physical-mode', () => {
                if (scene.loader.physicalRenderingAge > 0) {
                    scene.setPhysicalRenderingAge(0);
                }
                else {
                    scene.setPhysicalRenderingAge(20);
                }
            });
        </script>
        @endscript
    @endpush

</div>
