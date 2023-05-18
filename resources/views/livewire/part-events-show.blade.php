<div>
    <div class="ui accordion">
        <div class="title">
          <i class="dropdown icon"></i>
          Filters
        </div>
        <div @class(['active' => $filtersActive , 'content'])>
        <div class="ui form">
            <div class="equal width fields">
                <x-form.select-page-items name="itemsPerPage" wire:model="itemsPerPage" :options="[20,40,80,100]" selected="{{$itemsPerPage}}" />
                <x-form.select-event-type name="types" wire:model="types" :selected="$types" multiple/>
                <div class="field">
                    <label>Start Date/Time</label>
                    <div class="ui calendar" id="standard_calendar">
                        <div class="ui fluid input left icon">
                            <i class="calendar icon"></i>
                            <input type="text" id="dt" placeholder="Date/Time" wire:model.lazy="dt">
                        </div>
                    </div>
                </div>      
                <x-form.select name="order" wire:model="order" label="Order" :options="$orderItems" selected="{{$order}}" />
                <div class="field">
                    <div class="ui checkbox">
                        <input type="checkbox" wire:model="unofficial" tabindex="0" class="hidden">
                        <label>Unofficial Part Events Only</label>
                    </div>
                </div> 
            </div>  
        </div>
        </div>
    </div>    
    {{ $events->links('livewire.paginate-menu') }}
    <x-event.table :events="$events" />
    {{ $events->links('livewire.paginate-menu') }}
     
</div>
