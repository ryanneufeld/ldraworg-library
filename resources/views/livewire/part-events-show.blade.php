<div>
    <div class="ui accordion">
        <div class="title">
          <i class="dropdown icon"></i>
          Filters
        </div>
        <div @class(['active' => $filtersActive , 'content'])>
        <div class="ui form">
            <div class="equal width fields">
                <x-form.select-page-items wire:ignore name="itemsPerPage" id="itemsPerPage" :options="[20,40,80,100]" selected="{{$itemsPerPage}}" />
                <x-form.select-event-type wire:ignore name="types" id="types" :selected="$types" multiple/>
                <div wire:ignore class="field">
                    <label>Start Date/Time</label>
                    <div class="ui calendar" id="standard_calendar">
                        <div class="ui fluid input left icon">
                            <i class="calendar icon"></i>
                            <input type="text" placeholder="Date/Time" id="dt">
                        </div>
                    </div>
                </div>      
                <x-form.select wire:ignore name="order" id="order" label="Order" :options="$orderItems" selected="{{$order}}" />
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
    @push('scripts')
    <script>
        $( function() {
            $('.ui.checkbox').checkbox();
            $('.ui.accordion').accordion();
            $('#standard_calendar').calendar({
                type: 'datetime',
                initialDate: @js($dt),
                formatter: {
                    datetime: 'YYYY-MM-DD HH:mm:ss'
                },
                disableMinute: true	  
            });
            $('#dt').on('change', function (e) {
                var data = $('#dt').val();
                @this.set('dt', data);
            });
        });
    </script>    
    @endpush  
</div>
