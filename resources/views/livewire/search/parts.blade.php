<div>
  <div class="ui form">
    <div class="field">
      <label>Search terms:</label>
      <input type="text" wire:model="search">
    </div>
    <x-form.select name="scope" wire:model="scope" label="Search Scope:" :options="$scopeOptions" selected="{{$scope}}"/> 
    <div class="ui three column stackable grid">
      <div class="column">
        <x-form.select-user name="user_id" wire:model="user_id" selected="{{$user_id}}" />
        <div class="field">
          <div class="ui toggle checkbox">
            <input type="checkbox" wire:model="exclude_user" tabindex="0" class="hidden">
            <label>Exclude</label>
          </div>
        </div>
      </div>
      <div class="column">
        <x-form.select-part-status name="status" wire:model="status" label="Status (Unofficial Only)" selected="{{$status}}" />
      </div>
      <div class="column">
        <x-form.select-part-type name="part_types" wire:model="part_types" :selected="$part_types" multiple />
      </div>
    </div>  
  </div>
  @if(!empty($search))
    @if(!is_null($uparts))
      <div class="ui medium header">Matched {{$ucount ?? 0}} Unofficial Parts</div>
      {{ $uparts->links('livewire.paginate-menu') }}
      <x-part.table :parts="$uparts" none=""/>
    @endif
    @if(!is_null($oparts))   
      <div class="ui medium header">Matched {{$ocount ?? 0}} Official Parts</div>
      {{ $oparts->links('livewire.paginate-menu') }}
      <x-part.table :parts="$oparts" none=""/>
    @endif    
  @endif  
</div>
