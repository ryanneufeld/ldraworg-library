<div>
    @if($getRecord()->isUnofficial())
        <x-part.status :part="$getRecord()" show-status />
    @endif
</div>
