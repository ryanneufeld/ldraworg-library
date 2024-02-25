<x-layout.tracker>
    <x-slot:title>{{$summary->header}}</x-slot>
    <x-slot:breadcrumbs>
        <x-breadcrumb-item class="active" item="Review Summary" />
    </x-slot>    
    
    <div class="text-2xl font-bold">{{$summary->header}}</div>
    <div class="flex flex-col space-y-2">
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-2 items-stretch p-2">
            @foreach($summary->items()->with('part')->orderBy('order')->get() as $item)
                @if(!is_null($item->heading))
                    </div>
                    @empty($item->heading)
                        <hr>
                    @else
                        <div class="text-lg font-bold">{{$item->heading}}</div>
                    @endempty
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-2 items-stretch">
                @else      
                    <x-part.summaryitem :part="$item->part" />
                @endif
            @endforeach
        </div>
    </div>            
</x-layout.tracker>