<button wire:click="toggleFlag" @class([
    'ui',
    'yellow' => Auth::user()->notification_parts->contains($part->id),
    'labeled icon button',
  ])>
    <i class="bell icon"></i>
      {{Auth::user()->notification_parts->contains($part->id) ? 'Tracking' : 'Track'}}
</button>
