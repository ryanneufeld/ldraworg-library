<x-slot:title>
    Sticker Sheet Search
</x-slot>
<x-slot:breadcrumbs>
  <x-breadcrumb-item class="active" item="Pattern Search" />
</x-slot> 
<div> 
    <div class="text-3xl font-bold">
        <span>Sticker Sheet Part Search</span>
    </div>  
    <form class="p-2" wire:submit="doSearch">
        {{ $this->form }}

        <x-filament::button type="submit">
            Submit
        </x-filament::button>
    </form>
    <div @class(["rounded border p-2"])>
        @if (!is_null($parts))
            @foreach($parts as $spart)
                @if($loop->first)
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-2 items-stretch">
                @endif
                <x-part.suffixitem :part="$spart" wire:key="{{$spart->id}}" />
                @if($loop->last)
                    </div>
                @endif
            @endforeach    
        @else
            <p>
                None
            </p>
        @endif
    </div>
</div>