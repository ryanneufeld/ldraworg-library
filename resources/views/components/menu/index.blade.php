@props(['submenu' => false])
<ul @class(['flex flex-row' => !$submenu])>
  {{$slot}}
</ul>  