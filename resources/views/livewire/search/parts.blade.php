<div>
  <div class="ui form">
    <div class="field">
      <label>Search terms:</label>
      <input type="text" wire:model="search">
    </div>
    <x-form.select wire:ignore name="scope" id="scope" label="Search Scope:" :options="$scopeOptions" selected="{{$scope}}"/> 
    <div class="equal width fields">
      <x-form.select-user wire:ignore name="user_id" id="user_id" selected="{{$user_id}}" />
      <x-form.select-part-type wire:ignore name="part_types" id="part_types" :selected="$part_types" multiple />
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
