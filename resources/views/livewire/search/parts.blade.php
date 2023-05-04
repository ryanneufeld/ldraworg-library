<div>
  <form class="ui form" wire:submit.prevent="render">
    <div class="field">
      <label>Search terms:</label>
      <div class="ui action input">
        <input type="text" wire:model="search">
        <button class="ui button">Go</button>
      </div>
    </div> 
    <div class="four wide field">
      <label>Search Scope:</label>
      <select wire:model="scope">
        <option value="filename" @selected($scope == 'filename')>Filename only</option>
        <option value="description" @selected($scope == 'description')>Filename and description</option>
        <option value="header" @selected($scope == 'header')>Filename and file header</option>
        <option value="file" @selected($scope == 'file')>Filename and entire file</option>
      </select>
    </div>  
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
