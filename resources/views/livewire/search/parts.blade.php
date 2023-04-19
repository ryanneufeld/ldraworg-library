<div>
  <form class="ui form" wire:submit.prevent="search">
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
  @if(!empty($search) && (!is_null($uparts) || !is_null($oparts)))
      <x-part.table title="Matched {{$uparts->count() ?? 0}} Unofficial Parts" :parts="$uparts" none=""/>
      <x-part.table title="Matched {{$oparts->count() ?? 0}} Official Parts" :parts="$oparts" none=""/>
  @endif  
</div>
