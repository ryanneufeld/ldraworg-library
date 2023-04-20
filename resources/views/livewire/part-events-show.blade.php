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
                <div class="field">
                    <label>Items per page</label>
                    <select class="ui selection dropdown" wire:model="itemsPerPage">
                        <option value="20">20</option>
                        <option value="40">40</option>
                        <option value="80">80</option>
                        <option value="100">100</option>
                    </select>
                </div>
                <div class="field">
                    <label>Start Date/Time</label>
                    <div class="ui fluid input left icon">
                        <i class="calendar icon"></i>
                        <input type="text" wire:model="dt" placeholder="Date/Time">
                    </div>
                </div>      
                <div class="field">
                    <label>Order</label>
                    <select class="ui selection dropdown" wire:model="order">
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
</div>
