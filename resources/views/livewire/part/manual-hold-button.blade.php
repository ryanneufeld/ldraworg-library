<button wire:click="toggleFlag" @class([
  'ui',
  'red' => $part->manual_hold_flag,
  'labeled icon button',
])>
  <i class="flag icon"></i>
    {{$part->manual_hold_flag ? 'On' : 'Place on'}} Administrative Hold
</button>    
