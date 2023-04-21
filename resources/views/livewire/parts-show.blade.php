<div>
  {{ $parts->links('livewire.paginate-menu') }}

  <div class="ui accordion">
    <div class="title">
      <i class="dropdown icon"></i>
      Filters
    </div>
    <div @class(['active' => $filtersActive , 'content'])>
    <form class="ui equal width form" wire:submit.prevent="dtChange">
        <div class="fields">
          <x-form.select wire:ignore name="itemsPerPage" id="itemsPerPage" label="Parts Per Page" :options="$pageItems" selected="{{$itemsPerPage}}" />
          @if($unofficial)
          <x-form.select wire:ignore name="subset" id="subset" class="clearable" label="Status" placeholder="Status" :options="$subsetItems" selected="{{$subset}}" />
          @endif
          <x-form.select wire:ignore name="user_id" id="user_id" class="search clearable" label="User" placeholder="User" :options="$userItems" selected="{{$user_id}}" />
          <x-form.select wire:ignore name="part_types" id="part_types" class="clearable" label="Type" placeholder="Part Type" :options="$parttypeItems" :selected="$part_types" multiple />
        </div>  
    </form>
    </div>
  </div>    

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
