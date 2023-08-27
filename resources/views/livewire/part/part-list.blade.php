<div>
  <div class="ui form">
    <div @class(['ui', 'three' => !$unofficial, 'five' => $unofficial, 'column stackable grid'])>
      <div class="column">
        <x-form.select-page-items name="itemsPerPage" wire:model.live="itemsPerPage" selected="{{$itemsPerPage}}" />
      </div>
      @if ($unofficial)
      <div class="column">
          <x-form.select-part-status name="status" wire:model.live="status" selected="{{$status}}" />
      </div>
      @endif
      <div class="column">
        <x-form.select-user name="user_id" wire:model.live="user_id" :$unofficial selected="{{$user_id}}" />
        <div class="field">
          <div class="ui toggle checkbox">
              <input type="checkbox" wire:model.live="exclude_user" tabindex="0" class="hidden">
              <label>Exclude</label>
          </div>
        </div>
      </div>
      <div class="column">
        <x-form.select-part-type name="part_types" wire:model.live="part_types" :selected="$part_types" multiple />
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
