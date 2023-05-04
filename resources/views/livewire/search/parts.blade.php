<div>
  <form class="ui form" wire:submit.prevent="render">
    <div class="field">
      <label>Search terms:</label>
      <div class="ui action input">
        <input type="text" wire:model.defer="search">
        <button class="ui button">Go</button>
      </div>
    </div>
    <x-form.select wire:ignore name="scope" id="scope" label="Search Scope:" width="four" :options="$scopeItems" selected="{{$scope}}" defer /> 
    <x-part.filter-bar items="user,parttype"/>
  </form>
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
