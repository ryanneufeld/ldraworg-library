<div>
  <div class="ui form">
    <div @class(['ui', 'three' => !$unofficial, 'five' => $unofficial, 'column stackable grid'])>
      <div class="column">
        <x-form.select-page-items wire:ignore name="itemsPerPage" id="itemsPerPage" selected="{{$itemsPerPage}}" />
      </div>
      @if ($unofficial)
      <div class="column">
          <x-form.select-part-status wire:ignore name="status" id="status" selected="{{$status}}" />
      </div>
      @endif
      <div class="column">
        <x-form.select-user wire:ignore name="user_id" id="user_id" :$unofficial selected="{{$user_id}}" />
        <div class="field">
          <div class="ui toggle checkbox">
              <input type="checkbox" wire:model="exclude_user" tabindex="0" class="hidden">
              <label>Exclude</label>
          </div>
        </div>
      </div>
      <div class="column">
        <x-form.select-part-type wire:ignore name="part_types" id="part_types" :selected="$part_types" multiple />
      </div>
      @if ($unofficial)
      <div class="column">
        <br>
        <div class="field">
          <div class="ui toggle checkbox">
              <input type="checkbox" wire:model="exclude_reviews" tabindex="0" class="hidden">
              <label>Exclude My Reviewed Parts</label>
          </div>
        </div>
      </div>
      @endif
    </div>
  {{ $parts->links('livewire.paginate-menu') }}
  <x-part.table title="{{$unofficial ? 'Unofficial' : 'Official'}} Part List" :parts="$parts" />
  {{ $parts->links('livewire.paginate-menu') }}
  @push('scripts')
  <script>
      $( function() {
          $('.ui.accordion').accordion();
      });    
  </script>    
  @endpush
</div>
