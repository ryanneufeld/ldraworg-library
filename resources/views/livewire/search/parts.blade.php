<div>
  <form class="ui form" wire:submit="searchpart" wire:loading.class="loading">
    <div class="field">
      <label>Search terms:</label>
      <input type="text" wire:model="search">
    </div>
    <div wire:ignore class="field">
        <x-form.select name="scope" wire:change="$set('scope', $event.target.value, false)" label="Search Scope:" :options="$scopeOptions" selected="{{$scope}}"/>
    </div>     
    <div class="three fields">
        <div class="field">
            <div wire:ignore class="field">
                <x-form.select.user name="user_id" wire:change="$set('user_id', $event.target.value, false)" selected="{{$user_id}}" />
            </div>
            <div class="two fields">
                <div class="inline field">
                    <x-form.checkbox wire:model="include_history" type="toggle" label="Include HISTORY" />
                </div>
                <div class="inline field">
                    <x-form.checkbox wire:model="exclude_user" type="toggle" label="Exclude Parts" />
                </div>
            </div>    
        </div>    
        <div wire:ignore class="field">
            <x-form.select.part-status name="status" wire:change="$set('status', $event.target.value, false)" label="Status (Unofficial Only)" selected="{{$status}}" />
        </div>    
        <div wire:ignore class="field">
            <x-form.select.part-type name="part_types" class="multiple" wire:change="$set('part_types', $event.target.value, false)" selected="{{$part_types}}" />
        </div>
    </div>
    <div class="field">
        <button class="ui button" type="submit">Search</button>
    </div>
  </form>
  @if(!empty($search))
    @if(!is_null($uparts))
      <div class="ui medium header">Matched {{$ucount ?? 0}} Unofficial Parts</div>
      {{ $uparts->onEachSide(1)->links('livewire.paginate-menu') }}
      <x-part.table :parts="$uparts" none=""/>
    @endif
    @if(!is_null($oparts))   
      <div class="ui medium header">Matched {{$ocount ?? 0}} Official Parts</div>
      {{ $oparts->onEachSide(1)->links('livewire.paginate-menu') }}
      <x-part.table :parts="$oparts" none=""/>
    @endif    
  @endif  
</div>
