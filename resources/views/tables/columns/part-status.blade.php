<div>
    @if($getRecord()->isUnofficial())
        <x-part.status :part="$getRecord()" show-status />
    @else
        @isset ($getRecord()->unofficial_part_id)
            <a href="{{ route('tracker.show', $getRecord()->unofficial_part_id) }}">Updated part on tracker</a>
        @endisset
    @endif    
</div>
