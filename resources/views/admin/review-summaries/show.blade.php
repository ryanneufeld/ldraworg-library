<x-layout.tracker>
    <x-slot:title>{{$summary->header}}</x-slot>
    <x-slot:breadcrumbs>
        <x-breadcrumb-item class="active" item="Review Summary" />
    </x-slot>    
    
    <h2 class="ui header">{{$summary->header}}</h2>

    <div class="ui eight column padded doubling grid">
    @foreach($summary->items()->with('part')->orderBy('order')->get() as $item)
        @if(!is_null($item->heading))
            </div>
            @empty($item->heading)
                <div class="ui divider"></div>
            @else
                <h4 class="ui horizontal divider header">{{$item->heading}}</h4>
            @endempty
            <div class="ui eight column padded doubling grid">
        @else      
            <div class="column"><x-part.suffixitem :part="$item->part" showobsolete/></div>
        @endif
    @endforeach
    </div>            
</x-layout.tracker>