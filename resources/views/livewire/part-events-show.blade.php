<div>
    <div class="ui accordion">
        <div class="title">
          <i class="dropdown icon"></i>
          Filters
        </div>
        <div @class(['active' => $filtersActive , 'content'])>
        <div class="ui form">
            <div class="equal width fields">
                <div wire:ignore class="field">
                    <x-form.select.page-items name="itemsPerPage" wire:change="$set('itemsPerPage', $event.target.value)" :options="[20,40,80,100]" selected="{{$itemsPerPage}}" />
                </div>    
                <div wire:ignore class="field">
                    <x-form.select.event-type name="types" wire:change="$set('types', $event.target.value)" selected="{{$types}}" />
                </div>    
                <div wire:ignore class="field">
                    <x-form.calendar label="Start Date/Time" name="dt" value="{{$dt}}" placeholder="Date/Time" wire:change="$set('dt', $event.target.value)" />
                </div>
                <div wire:ignore class="field">      
                    <x-form.select name="order" wire:change="$set('order', $event.target.value)" label="Order" :options="$orderItems" selected="{{$order}}" />
                </div>    
                <div class="field">
                    <div class="ui checkbox">
                        <input type="checkbox" wire:model.live="unofficial" tabindex="0" class="hidden">
                        <label>Unofficial Part Events Only</label>
                    </div>
                </div> 
            </div>  
        </div>
        </div>
    </div>    
    {{ $events->onEachSide(1)->links('livewire.paginate-menu') }}
    <x-event.table :events="$events" />
    {{ $events->onEachSide(1)->links('livewire.paginate-menu') }}
</div>
