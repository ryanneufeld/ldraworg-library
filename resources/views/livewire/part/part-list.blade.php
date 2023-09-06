<div>
  <div class="ui form">
    <div @class(['ui', 'three' => !$unofficial, 'five' => $unofficial, 'column stackable grid'])>
      <div class="column">
        <div wire:ignore class="field">
            <x-form.select.page-items name="itemsPerPage" wire:change="$set('itemsPerPage', $event.target.value)" selected="{{$itemsPerPage}}" />
        </div>    
      </div>
      @if ($unofficial)
      <div class="column">
        <div wire:ignore class="field">
            <x-form.select.part-status name="status" wire:change="$set('status', $event.target.value)" selected="{{$status}}" />
        </div>     
      </div>
      @endif
      <div class="column">
        <div wire:ignore class="field">
            <x-form.select.user name="user_id" wire:change="$set('user_id', $event.target.value)" :$unofficial selected="{{$user_id}}" />
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
            <x-form.select.part-type name="part_types" class="multiple" wire:change="$set('part_types', $event.target.value)" selected="{{$part_types}}" />
        </div>
      </div>
      @if ($unofficial && Auth::check())
      <div class="column">
        <br>
        <div class="field">
          <div class="ui toggle checkbox">
              <input type="checkbox" wire:model.live="exclude_reviews" tabindex="0" class="hidden">
              <label>Exclude My Reviewed Parts</label>
          </div>
        </div>
      </div>
      @endif
    </div>
  </div>  
  {{ $parts->onEachSide(1)->links('livewire.paginate-menu') }}
  <x-part.table title="{{$unofficial ? 'Unofficial' : 'Official'}} Part List" :parts="$parts" />
  {{ $parts->onEachSide(1)->links('livewire.paginate-menu') }}
</div>
