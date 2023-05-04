@aware($aware)
<div class="equal width fields">
    @if(strpos($items, 'page') !== false)
        <x-form.select wire:ignore name="itemsPerPage" id="itemsPerPage" label="Parts Per Page" :options="$pageItems" selected="{{$itemsPerPage}}" />
    @endif
    @if($unofficial && strpos($items, 'status') !== false)
        <x-form.select wire:ignore name="status" id="status" class="clearable" label="Status" placeholder="Status" :options="$subsetItems" selected="{{$status}}" />
    @endif
    @if(strpos($items, 'user') !== false)
        <x-form.select wire:ignore name="user_id" id="user_id" class="search clearable" label="User" placeholder="User" :options="$userItems" selected="{{$user_id}}" />
    @endif
    @if(strpos($items, 'parttype') !== false)
        <x-form.select wire:ignore name="part_types" id="part_types" class="clearable" label="Type" placeholder="Part Type" :options="$parttypeItems" :selected="$part_types" multiple />
    @endif
</div>  
