<div>
    <div class="ui relaxed divided list" wire:sortable="updateItemOrder">
        @foreach ($summary->items()->with('part')->orderBy('order')->get() as $i => $item)
            <div class="item" wire:sortable.item="{{ $item->id }}" wire:key="item-{{ $item->id }}">
                <i wire:sortable.handle class="large github middle aligned icon"></i>
                <div class="content">
                    @if(is_null($item->heading))
                        {{$item->part->filename}} - {{$item->part->description}}
                    @else
                        Heading: {{$item->heading == '' ? 'Blank' : $item->heading}}
                    @endif
                    <button class="ui button" wire:click="removeItem({{ $item->id }})">Remove</button>
                </div>        
            </div>
        @endforeach
    </div>
    <form class="ui form" id="part" wire:submit.prevent="addItem">
        <x-form.select-unofficial-part wire:ignore name="newPartId" id="newPartId" label="Add Part" withDescription actionInput formId="part" buttonLabel="Add" selected="{{$newPartId}}"/>
    </form>    
    <form class="ui form" id="heading" wire:submit.prevent="addHeading">
        <div class="ui field">
            <label>Add Heading</label>
            <div class="ui action input">
                <input type="text" wire:model="newHeading" placeholder="New Heading">
                <button form="heading" class="ui button">Add</button>
            </div>
        </div>    
    </form>    
</div>
