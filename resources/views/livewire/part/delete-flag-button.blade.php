<button wire:click="toggleFlag" @class([
  'ui',
  'red' => $part->delete_flag,
  'labeled icon button',
])>
  <i class="flag icon"></i>
    {{$part->delete_flag ? 'Flagged' : 'Flag'}} for Deletion
</button>    
