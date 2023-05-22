<div>
    <div class="ui top attached tabular summarymenu menu">
        <a class="item active" data-tab="normal">Normal Edit</a>
        <a class="item" data-tab="manual">Manual Edit</a>
    </div>
    <div class="ui bottom attached tab segment active" data-tab="normal">
        <div class="ui relaxed divided list" wire:sortable="updateItemOrder">
            @foreach ($summary->items as $i => $item)
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
            <x-form.select-unofficial-part name="newPartId" wire:model="newPartId" label="Add Part" withDescription actionInput formId="part" buttonLabel="Add" selected="{{$newPartId}}"/>
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
    <div class="ui bottom attached tab segment" data-tab="manual">        
        <form class="ui form" id="manual" wire:submit.prevent="processManualEntry">
            <div class="ui field">
                <label>Manual Edit</label>
                <textarea rows="30" wire:model.defer="manualEntry">{{$summary->toString()}}</textarea>
            </div>    
            <div class="ui field">
                <button form="manual" class="ui button">Edit</button>
            </div>
        </form>
    </div>    
</div>
