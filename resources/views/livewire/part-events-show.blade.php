<div>
<div class="ui tiny compact menu">
    @if(!$events->onFirstPage())
    <a class="item" wire:click="previousPage" wire:loading.attr="disabled">Prior</a>
    @endif
    @if($events->hasMorePages())
    <a class="item" wire:click="nextPage" wire:loading.attr="disabled">Next</a>
    @endif
    @if(!$events->onFirstPage())
    <a class="item" wire:click="gotoPage(1)" wire:loading.attr="disabled">Newest</a>
    @endif
    </div>
    <div class="ui accordion">
        <div class="title">
          <i class="dropdown icon"></i>
          Filters
        </div>
        <div @class(['active' => $filtersActive , 'content'])>
        <form class="ui equal width form" wire:submit.prevent="dtChange">
            <div class="fields">
                <div wire:ignore class="field">
                    <label>Items per page</label>
                    <select class="ui selection dropdown" id="itemsPerPage">
                        <option value="20">20</option>
                        <option value="40">40</option>
                        <option value="80">80</option>
                        <option value="100">100</option>
                    </select>
                </div>
                <div wire:ignore class="field">
                    <label>Event Type</label>
                    <select class="ui clearable selection dropdown" multiple id="parteventtype">
                        @foreach($eventtypes as $eventtype)
                            <option value="{{$eventtype->id}}">{{$eventtype->name}}</option>
                        @endforeach    
                    </select>
                </div>
                <div wire:ignore class="field">
                    <label>Start Date/Time</label>
                    <div class="ui calendar" id="standard_calendar">
                        <div class="ui fluid input left icon">
                            <i class="calendar icon"></i>
                            <input type="text" placeholder="Date/Time" id="dt">
                        </div>
                    </div>
                </div>      
                <div wire:ignore class="field">
                    <label>Order</label>
                    <select class="ui selection dropdown" id="order">
                        <option value="latest">Newest First</option>
                        <option value="oldest">Oldest First</option>
                    </select>
                </div>
                <div class="field">
                    <div class="ui checkbox">
                        <input type="checkbox" wire:model="unofficial" tabindex="0" class="hidden">
                        <label>Unofficial Part Events Only</label>
                    </div>
                </div> 
            </div>  
        </form>
        </div>
    </div>    
    <x-event.table :events="$events" />   
    <div class="ui tiny compact menu">
    @if(!$events->onFirstPage())
    <a class="item" wire:click="previousPage" wire:loading.attr="disabled">Prior</a>
    @endif
    @if($events->hasMorePages())
    <a class="item" wire:click="nextPage" wire:loading.attr="disabled">Next</a>
    @endif
    @if(!$events->onFirstPage())
    <a class="item" wire:click="gotoPage(1)" wire:loading.attr="disabled">Newest</a>
    @endif
    </div>
    @push('scripts')
    <script>
        $( function() {
            $('.ui.checkbox').checkbox();
            $('.ui.accordion').accordion();
            $('#standard_calendar').calendar({
                type: 'datetime',
                formatter: {
                    datetime: 'YYYY-MM-DD HH:mm:ss'
                },
                disableMinute: true	  
            });
            $('#parteventtype').on('change', function (e) {
                var data = $('#parteventtype').val();
                @this.set('types', data);
            });
            $('#order').on('change', function (e) {
                var data = $('#order option:selected').val();
                @this.set('order', data);
            });
            $('#itemsPerPage').on('change', function (e) {
                var data = $('#itemsPerPage option:selected').val();
                @this.set('itemsPerPage', data);
            });
            $('#dt').on('change', function (e) {
                var data = $('#dt').val();
                @this.set('dt', data);
            });
        });
    </script>    
    @endpush  
</div>
