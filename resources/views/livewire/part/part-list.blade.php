<div>
  <div class="ui form">
    <div @class(['ui', 'three' => !$unofficial, 'four' => $unofficial, 'column stackable grid'])>
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

    </div>
    {{--
    <div class="equal width fields">
      <x-form.select-page-items wire:ignore name="itemsPerPage" id="itemsPerPage" selected="{{$itemsPerPage}}" />
      @if ($unofficial)
        <x-form.select wire:ignore name="status" id="status" class="clearable" label="Status" placeholder="Status" :options="$statusOptions" selected="{{$status}}" />
      @endif
      <div class="fields">  
        <x-form.select-user wire:ignore name="user_id" id="user_id" :$unofficial selected="{{$user_id}}" />
        <div class="field">
          <div class="ui toggle checkbox">
              <input type="checkbox" wire:model="exclude_user" tabindex="0" class="hidden">
              <label>Exclude</label>
          </div>
        </div>
      </div>   
      <x-form.select-part-type wire:ignore name="part_types" id="part_types" :selected="$part_types" multiple />
    </div>  
  </div>
--}}
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
