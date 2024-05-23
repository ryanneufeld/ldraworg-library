<div>
    @if($getRecord()->isUnofficial())
        <x-part.status :part="$getRecord()->load('descendantsAndSelf', 'votes')" show-status />
    @endif
</div>
