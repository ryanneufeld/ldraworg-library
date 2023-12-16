@props(['submenu' => false])
<div {{ $attributes->class(['ui' => !$submenu, 'menu']) }}>
  {{$slot}}
</div>  