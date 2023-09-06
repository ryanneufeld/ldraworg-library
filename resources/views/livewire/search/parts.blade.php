<div>
  <div class="ui form">
    <div class="field">
      <label>Search terms:</label>
      <input type="text" wire:model.live="search">
    </div>
    <div wire:ignore class="field">
        <x-form.select name="scope" wire:change="$set('scope', $event.target.value)" label="Search Scope:" :options="$scopeOptions" selected="{{$scope}}"/>
    </div>     
    <div class="ui three column stackable grid">
      <div class="column">
        <div wire:ignore class="field">
            <x-form.select.user name="user_id" wire:change="$set('user_id', $event.target.value)" selected="{{$user_id}}" />
        </div>    
        <div class="field">
          <div class="ui toggle checkbox">
            <input type="checkbox" wire:model.live="exclude_user" tabindex="0" class="hidden">
            <label>Exclude</label>
          </div>
        </div>
      </div>
      <div class="column">
        <div wire:ignore class="field">
            <x-form.select.part-status name="status" wire:change="$set('status', $event.target.value)" label="Status (Unofficial Only)" selected="{{$status}}" />
        </div>    
      </div>
      <div class="column">
        <div wire:ignore class="field">
            <x-form.select.part-type name="part_types" class="multiple" wire:change="$set('part_types', $event.target.value)" selected="{{$part_types}}" />
        </div>
      </div>
    </div>  
  </div>
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
