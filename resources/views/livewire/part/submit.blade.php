 <x-slot:title>
     Parts Tracker File Submit Form
</x-slot>
<x-slot:breadcrumbs>
    <x-breadcrumb-item class="active" item="Submit" />
</x-slot>    
<div>
    <div class="text-2xl font-bold">
        Parts Tracker File Submit Form
    </div>

    <p>
        Use this form to upload <span class="fold-bold">new</span> files to the 
        Parts Tracker and to update already-submitted <span class="fold-bold">unofficial</span> files.
    </p>
    <div>
        @if (count($this->part_errors) > 0)
            <x-message type="error">
                @foreach($this->part_errors as $error)
                    {{$error}}<br>
                @endforeach
            </x-message>
        @endif
    </div>
    <form wire:submit="create">
        {{ $this->form }}

        <x-filament::button type="submit">
            Submit
        </x-filament::button>
    </form>

    <p>
        To submit a fix for an <span class="fold-bold">existing file</spna>,  email the file to 
        <a href="mailto:parts@ldraw.org">parts@ldraw.org</a>, and it will be manually posted to the tracker.
    </p>
    <p>
        Uploaded files should appear almost immediately in the Parts Tracker list.
    </p>
    <x-filament::modal id="post-submit" width="5xl" :close-by-clicking-away="false" :close-button="false">
        <x-slot name="trigger">
            <x-filament::button>
                Open modal
            </x-filament::button>
        </x-slot>
        <x-slot name="heading">
            Submit Successful
        </x-slot>
        <p>
            The following files passed validation checks and have been submitted to the Parts Tracker
        </p>
        <div class="grid grid-cols-3">
            @foreach($submitted_parts as $p)
                <div class="border px-2"><img src="{{$p['image']}}"></div>
                <div class="border px-2">{{$p['filename']}}</div>
                <div class="border px-2"><a href="{{$p['route']}}">{{$p['description']}}</a></div>
            @endforeach    
        </div>
        <x-filament::button wire:click="postSubmit">
            Ok
        </x-filament::button>
    </x-filament::modal>

</div>
